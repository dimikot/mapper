<?php
/**
 * Calling prototype:
 * 
 * $proc->invoke(int $id)
 */
abstract class Mapper_Procedure_Delete extends Mapper_Procedure_Writable
{
    protected function _selfCheck()
    {
        parent::_selfCheck();
        $in = $this->getInParams();
        assert('is_array($in)');
        assert('count($in) == 1');
        assert('current($in) instanceof Mapper_Type_Identifier');
    }
    
    /**
     * DELETE procedure always returns deletion status: deleted or not
     *
     * @return mixed
     */
    public final function getOutParams()
    {
        return new DB_Pgsql_Type_Boolean();
    }

    /**
     * DELETE procedure commonly gets the single input parameter:
     * object's ID.
     *
     * @return array
     */
    public function getInParams()
    {
        return array(
           'id'   => new Mapper_Type_Identifier(),
        );
    }
        
    /**
     * DELETE procedure always returns deletion status: deleted or not.
     *
     * @param int $id  Object ID to delete.
     * @return bool
     */
    public function invoke($id)
    {
        $params = $this->getInParams();
        $idParam = key($params);
        $rows = parent::invoke(array($idParam => $id));
        return !!$rows;
    }
}