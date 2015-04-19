<?php

namespace Digilist\SnakeDumper\Dumper\Sql;

use Digilist\SnakeDumper\Configuration\Table\DataDependentFilterConfiguration;
use Digilist\SnakeDumper\Configuration\Table\TableConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Table;

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
    public function executeSelectQuery(
        TableConfiguration $tableConfig = null,
        Table $table,
        $collectedValues
    )
    {
        if ($tableConfig != null && $tableConfig->getQuery() != null) {
            $result = $this->connection->prepare($tableConfig->getQuery());
            $result->execute();

            return $result;
        }

        $qb = $this->buildSelectQuery($tableConfig, $table, $collectedValues);

        $result = $qb->execute();

        return $result;
    }

    /**
     * @param TableConfiguration $tableConfig
     * @param Table              $table
     * @param array              $collectedValues
     *
     * @return QueryBuilder
     */
    public function buildSelectQuery(
        TableConfiguration $tableConfig = null,
        Table $table,
        $collectedValues = array()
    )
    {
        if ($tableConfig != null && $tableConfig->getQuery() != null) {
            throw new \InvalidArgumentException('If a query is predefined, you cannot build the select query!');
        }

        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($table->getQuotedName($this->connection->getDatabasePlatform()), 't');

        if ($tableConfig != null) {
            $this->addFiltersToSelectQuery($qb, $tableConfig, $collectedValues);

            if ($tableConfig->getLimit() != null) {
                $qb->setMaxResults($tableConfig->getLimit());
            }
            if ($tableConfig->getOrderBy() != null) {
                $qb->add('orderBy', $tableConfig->getOrderBy());
            }
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
    public function addFiltersToSelectQuery(QueryBuilder $qb, TableConfiguration $tableConfig, $collectedValues)
    {
        $paramIndex = 0;
        foreach ($tableConfig->getColumns() as $column) {
            $filters = $column->getFilters();

            foreach ($filters as $filter) {
                if ($filter instanceof DataDependentFilterConfiguration) {
                    if (!isset($collectedValues[$filter->getTable()])) {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'The table %s has not been dumped before %s',
                                $filter->getTable(),
                                $tableConfig->getName()
                            )
                        );
                    }
                    if (!isset($collectedValues[$filter->getTable()][$filter->getColumn()])) {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'The column %s on table %s has not been dumped.',
                                $filter->getTable(),
                                $tableConfig->getName()
                            )
                        );
                    }

                    $filter->setValue($collectedValues[$filter->getTable()][$filter->getColumn()]);
                }

                if ($filter->getOperator() === 'in' || $filter->getOperator() === 'notIn') {
                    // the in and notIn operator expects an array which needs different handling

                    $param = array();
                    foreach ((array) $filter->getValue() as $valueIndex => $value) {
                        $tmpParam = 'param_' . $paramIndex . '_' . $valueIndex;
                        $param[] = ':' . $tmpParam;

                        $qb->setParameter($tmpParam, $value);
                    }
                } else {
                    $param = ':param_' . $paramIndex;

                    $qb->setParameter('param_' . $paramIndex, $filter->getValue());
                }

                $expr = call_user_func_array(array($qb->expr(), $filter->getOperator()), array(
                    $this->connection->getDatabasePlatform()->quoteIdentifier($column->getName()),
                    $param
                ));
                $qb->andWhere($expr);

                $paramIndex++;
            }
        }
    }
}
