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

    /**
     * Execute the procedure.
     *
     * @param array $ids  You may pass a scalar value here too, it will be 
     *                    converted to array with a single element. Note that
     *                    if you pass null, it will be treated as array(null)!
     * @param $args
     * @return Mapper_Aggregate
     */
    public function invoke($ids, $args = array())
    {
        $ids = $ids !== null ? (array)$ids : array(null);
        if (count($ids)) {
            $args[$this->getColumnNameListBy()] = $ids;
            return parent::invoke($args);
        } else {
            return new Mapper_Aggregate(array(), $this->getContext());
        }
    }
}
