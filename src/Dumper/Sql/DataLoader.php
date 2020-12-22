<?php

namespace Digilist\SnakeDumper\Dumper\Sql;

use Digilist\SnakeDumper\Configuration\Table\Filter\DataDependentFilter;
use Digilist\SnakeDumper\Configuration\Table\Filter\ColumnFilter;
use Digilist\SnakeDumper\Configuration\Table\Filter\CompositeFilter;
use Digilist\SnakeDumper\Configuration\Table\Filter\FilterInterface;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Digilist\SnakeDumper\Exception\UnsupportedFilterException;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Table;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * This class helps to query the appropriate data that should be dumped.
 */
class DataLoader
{

    /**
     * @var ConnectionHandler
     */
    private $connectionHandler;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConnectionHandler $connectionHandler
     * @param LoggerInterface   $logger
     */
    public function __construct(ConnectionHandler $connectionHandler, LoggerInterface $logger)
    {
        $this->connectionHandler = $connectionHandler;
        $this->logger = $logger;
    }

    /**
     * Executes the generated sql statement.
     *
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param array              $harvestedValues
     *
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws UnsupportedFilterException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function executeSelectQuery(TableConfiguration $tableConfig, Table $table, array $harvestedValues)
    {
        list($query, $parameters) = $this->buildSelectQuery($tableConfig, $table, $harvestedValues);

        $this->logger->debug('Executing select query' . $query);
        $result = $this->connectionHandler->getConnection()->prepare($query);
        $result->execute($parameters);

        return $result;
    }

    /**
     * Count the number of rows for the generated select statement.
     *
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param array              $harvestedValues
     *
     * @return int
     * @throws UnsupportedFilterException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function countRows(TableConfiguration $tableConfig, Table $table, array $harvestedValues)
    {
        list($query, $parameters) = $this->buildSelectQuery($tableConfig, $table, $harvestedValues);

        // Remove everything before the first FROM, to replace it with a SELECT 1
        $query = 'SELECT 1 ' . substr($query, stripos($query, 'FROM'));

        // The actual select is wrapped in a subquery, to consider groupings, limits etc.
        // We only want to get the number of rows that will be dumped later.
        $query = sprintf('SELECT COUNT(*) FROM (%s) AS tmp', $query);

        $result = $this->connectionHandler->getConnection()->prepare($query);
        $result->execute($parameters);

        return (int) $result->fetchAll()[0]['COUNT(*)'];
    }

    /**
     * This method creates the actual select statements and binds the parameters.
     *
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param array              $harvestedValues
     *
     * @return array
     * @throws UnsupportedFilterException
     * @throws \Doctrine\DBAL\DBALException
     */
    private function buildSelectQuery(TableConfiguration $tableConfig, Table $table, $harvestedValues)
    {
        $qb = $this->createSelectQueryBuilder($tableConfig, $table, $harvestedValues);

        $query = $qb->getSQL();
        $parameters = $qb->getParameters();

        if ($tableConfig->getQuery() != null) {
            $query = $tableConfig->getQuery();

            // Add automatic conditions to the custom query if necessary
            $parameters = [];
            if (strpos($query, '$autoConditions') !== false) {
                $parameters = $qb->getParameters();
                $query = str_replace('$autoConditions', '(' . $qb->getQueryPart('where') . ')', $query);
            }
        }

        return [trim($query), $parameters];
    }

    /**
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param array              $harvestedValues
     *
     * @return QueryBuilder
     * @throws UnsupportedFilterException
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createSelectQueryBuilder(TableConfiguration $tableConfig, Table $table, $harvestedValues = array())
    {
        $qb = $this->connectionHandler->getConnection()->createQueryBuilder()
            ->select('*')
            ->from($table->getQuotedName($this->connectionHandler->getPlatform()), 't');

        $this->addFiltersToSelectQuery($qb, $tableConfig, $harvestedValues);
        if ($tableConfig->getLimit() != null) {
            $qb->setMaxResults($tableConfig->getLimit());
        }
        if ($tableConfig->getOrderBy() != null) {
            $qb->add('orderBy', $tableConfig->getOrderBy());
        }

        return $qb;
    }

    /**
     * Add the configured filter to the select query.
     *
     * @param QueryBuilder       $qb
     * @param TableConfiguration $tableConfig
     * @param array              $harvestedValues
     * @throws UnsupportedFilterException
     */
    private function addFiltersToSelectQuery(QueryBuilder $qb, TableConfiguration $tableConfig, array $harvestedValues)
    {
        $paramIndex = 0;
        foreach ($tableConfig->getFilters() as $filter) {
            $expr = $this->addFilterToSelectQuery($qb, $tableConfig, $harvestedValues,  $filter, $paramIndex);
            $qb->andWhere($expr);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param TableConfiguration $tableConfig
     * @param array $harvestedValues
     * @param $filter
     * @param $paramIndex
     * @return CompositeExpression|mixed
     * @throws UnsupportedFilterException
     */
    private function addFilterToSelectQuery(QueryBuilder $qb, TableConfiguration $tableConfig, array $harvestedValues, $filter, &$paramIndex)
    {
        if ($filter instanceof ColumnFilter) {
            if ($filter instanceof DataDependentFilter) {
                $this->handleDataDependentFilter($filter, $tableConfig, $harvestedValues);
            }

            $param = $this->bindParameters($qb, $filter, $paramIndex++);
            $expr = call_user_func_array([$qb->expr(), $filter->getOperator()], [
                $this->connectionHandler->getPlatform()->quoteIdentifier($filter->getColumnName()),
                $param
            ]);
            if ($filter instanceof DataDependentFilter) {
                // also select null values
                $expr = $qb->expr()->orX(
                    $expr,
                    $qb->expr()->isNull(
                        $this->connectionHandler->getPlatform()->quoteIdentifier($filter->getColumnName())
                    )
                );
            }
            return $expr;
        }

        if ($filter instanceof CompositeFilter) {
            $filter->getFilters();
            return call_user_func_array(
                [$qb->expr(), $filter->getOperator()],
                array_map(
                    function ($childFilter) use ($qb, $tableConfig, $harvestedValues, &$paramIndex) {
                        return $this->addFilterToSelectQuery($qb, $tableConfig, $harvestedValues, $childFilter, $paramIndex);
                    },
                    $filter->getFilters()
                )
            );

        }
        throw new UnsupportedFilterException();
    }

    /**
     * Validates and modifies the data dependent filter to act like an IN-filter.
     *
     * @param DataDependentFilter $filter
     * @param TableConfiguration               $tableConfig
     * @param array                            $harvestedValues
     */
    private function handleDataDependentFilter(
        DataDependentFilter $filter,
        TableConfiguration $tableConfig,
        array $harvestedValues
    ) {
        $tableName = $tableConfig->getName();
        $referencedTable = $filter->getReferencedTable();
        $referencedColumn = $filter->getReferencedColumn();

        // Ensure the dependent table has been dumped before the current table
        if (!isset($harvestedValues[$referencedTable])) {
            throw new InvalidArgumentException(
                sprintf(
                    'The table %s has not been dumped before %s',
                    $referencedTable,
                    $tableName
                )
            );
        }

        // Ensure the necessary column was included in the dump
        if (!isset($harvestedValues[$referencedTable][$referencedColumn])) {
            throw new InvalidArgumentException(
                sprintf(
                    'The %s column of table %s has not been dumped. (dependency of %s)',
                    $referencedColumn,
                    $referencedTable,
                    $tableName
                )
            );
        }

        $filter->setValue($harvestedValues[$referencedTable][$referencedColumn]);
    }

    /**
     * Binds the parameters of the filter into the query builder.
     *
     * This function returns false, if the condition is not fulfill-able and no row can be selected at all.
     *
     * @param QueryBuilder    $qb
     * @param FilterInterface $filter
     * @param int             $paramIndex
     *
     * @return array|string|bool
     */
    private function bindParameters(QueryBuilder $qb, FilterInterface $filter, $paramIndex)
    {
        if(in_array($filter->getOperator(), [
            ColumnFilter::OPERATOR_IS_NOT_NULL,
            ColumnFilter::OPERATOR_IS_NULL,
        ])) {
            return;
        };

        $inOperator = in_array($filter->getOperator(), [
            ColumnFilter::OPERATOR_IN,
            ColumnFilter::OPERATOR_NOT_IN,
        ]);

        if ($inOperator) {
            // the IN and NOT IN operator expects an array which needs a different handling
            // -> each value in the array must be mapped to a single param

            $values = (array) $filter->getValue();
            if (empty($values)) {
                $values = array('_________UNDEFINED__________');
            }

            $param = array();
            foreach ($values as $valueIndex => $value) {
                $tmpParam = 'param_' . $paramIndex . '_' . $valueIndex;
                $param[] = ':' . $tmpParam;

                $qb->setParameter($tmpParam, $value);
            }
        } else {
            $param = ':param_' . $paramIndex;

            $qb->setParameter('param_' . $paramIndex, $filter->getValue());
        }

        return $param;
    }
}
