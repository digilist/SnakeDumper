<?php

namespace Digilist\SnakeDumper\Configuration\Table;

class DataDependentFilter extends DefaultFilter
{

    /**
     * @return string
     */
    public function getReferencedTable()
    {
        return $this->get('referencedTable');
    }

    /**
     * @param string $referencedTable
     *
     * @return $this
     */
    public function setReferencedTable($referencedTable)
    {
        return $this->set('referencedTable', $referencedTable);
    }

    /**
     * @return string
     */
    public function getReferencedColumn()
    {
        return $this->get('referencedColumn');
    }

    /**
     * @param string $referencedColumn
     *
     * @return $this
     */
    public function setReferencedColumn($referencedColumn)
    {
        return $this->set('referencedColumn', $referencedColumn);
    }

    protected function parseConfig(array $config)
    {
        $this->setOperator('in');
        $this->setValue(array());
    }
}
