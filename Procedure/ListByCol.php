<?php
/**
 * Calling prototype:
 *
 * $proc->invoke(int[] $ids [, array $args])
 *
 * Search by a list of column's values. The first argument of
 * the procedure must be array of values to search by.
 */
abstract class Mapper_Procedure_ListByCol extends Mapper_Procedure_List
{
    protected function _selfCheck()
    {
        parent::_selfCheck();
        $in = $this->getInParams();
        assert('is_array($in)');
        assert('count($in) >= 1');
        assert('$in[$this->getColumnNameListBy()] instanceof DB_Pgsql_Type_Array');
    }

    /**
     * Returns the column name by which the search process is
     * performed. This column must accept array of identifiers.
     *
     * By default, this is the first avaliable column.
     *
     * @return string
     */
    public function getColumnNameListBy()
    {
        $in = $this->getInParams();
        return key($in);
    }

    public function invoke($ids, $args = array())
    {
        $args[$this->getColumnNameListBy()] = (array) $ids;
        return parent::invoke($args);
    }
}
