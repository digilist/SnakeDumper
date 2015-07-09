<?php

namespace Digilist\SnakeDumper\Dumper\Sql;

use Digilist\SnakeDumper\Configuration\Table\DataDependentFilter;
use Digilist\SnakeDumper\Configuration\Table\DefaultFilter;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Table;

/**
 * This class helps to query the appropriate data that should be dumped.
 */
class DataSelector
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param array              $collectedValues
     *
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws \Doctrine\DBAL\DBALException
     */
    public function executeSelectQuery(TableConfiguration $tableConfig, Table $table, $collectedValues)
    {
        $qb = $this->createSelectQueryBuilder($tableConfig, $table, $collectedValues);

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

        $result = $this->connection->prepare($query);
        $result->execute($parameters);

        return $result;
    }

    /**
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param array              $collectedValues
     *
     * @return QueryBuilder
     */
    private function createSelectQueryBuilder(TableConfiguration $tableConfig, Table $table, $collectedValues = array())
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($table->getQuotedName($this->connection->getDatabasePlatform()), 't');

        $this->addFiltersToSelectQuery($qb, $tableConfig, $collectedValues);
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
     * @param array              $collectedValues
     */
    private function addFiltersToSelectQuery(QueryBuilder $qb, TableConfiguration $tableConfig, array $collectedValues)
    {
        $paramIndex = 0;
        foreach ($tableConfig->getFilters() as $filter) {
            if ($filter instanceof DataDependentFilter) {
                $this->handleDataDependentFilter($filter, $tableConfig, $collectedValues);
            }

            $param = $this->bindParameters($qb, $filter, $paramIndex);
            $expr = call_user_func_array(array($qb->expr(), $filter->getOperator()), array(
                $this->connection->getDatabasePlatform()->quoteIdentifier($filter->getColumnName()),
                $param
            ));

            if ($filter instanceof DataDependentFilter) {
                // also select null values
                $expr = $qb->expr()->orX(
                    $expr,
                    $qb->expr()->isNull(
                        $this->connection->getDatabasePlatform()->quoteIdentifier($filter->getColumnName())
                    )
                );
            }

            $qb->andWhere($expr);

            $paramIndex++;
        }
    }

    /**
     * Validates and modifies the data dependent filter to act like a IN-filter.
     *
     * @param DataDependentFilter $filter
     * @param TableConfiguration               $tableConfig
     * @param array                            $collectedValues
     */
    private function handleDataDependentFilter(
        DataDependentFilter $filter,
        TableConfiguration $tableConfig,
        array $collectedValues
    ) {
        if (!isset($collectedValues[$filter->getReferencedTable()])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The table %s has not been dumped before %s',
                    $filter->getReferencedTable(),
                    $tableConfig->getName()
                )
            );
        }
        if (!isset($collectedValues[$filter->getReferencedTable()][$filter->getReferencedColumn()])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The column %s on table %s has not been dumped.',
                    $filter->getReferencedTable(),
                    $tableConfig->getName()
                )
            );
        }

        $filter->setValue($collectedValues[$filter->getReferencedTable()][$filter->getReferencedColumn()]);
    }

    /**
     * Binds the parameters of the filter into the query builder.
     *
     * This function returns false, if the condition is not fulfill-able and no row can be selected at all.
     *
     * @param QueryBuilder        $qb
     * @param DefaultFilter $filter
     * @param int                 $paramIndex
     *
     * @return array|string|bool
     */
    private function bindParameters(QueryBuilder $qb, DefaultFilter $filter, $paramIndex)
    {
        if ($filter->getOperator() === 'in' || $filter->getOperator() === 'notIn') {
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
