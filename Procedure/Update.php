<?php
/**
 * Calling prototype:
 *
 * $proc->invoke(int $id, array $properties)
 */
abstract class Mapper_Procedure_Update extends Mapper_Procedure_Writable
{
    protected function _selfCheck()
    {
        parent::_selfCheck();
        $in = $this->getInParams();
        assert('Mapper_Type_ParamList::getFieldType($in, $this->getIdField()) instanceof Mapper_Type_Identifier');
    }

    /**
     * Returns the name of input ID parameter.
     *
     * @return string
     */
    public function getIdField()
    {
        return 'id';
    }

    /**
     * UPDATE procedure returns boolean.
     *
     * @return mixed
     */
    public function getOutParams()
    {
        return new DB_Pgsql_Type_Boolean();
    }    
    
    public function invoke($id, $values)
    {
        $values = (array) $values;
        $values[$this->getIdField()] = $id;
        $rows = parent::invoke($values);
        return $rows;
    }
}