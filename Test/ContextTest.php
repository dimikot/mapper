<?php
class Mapper_Test_ContextTest extends PHPUnit_Framework_TestCase
{
	private $_connectionMock;
	
	public function setUp()
	{
		$this->_connectionMock = $this->getMock("Mapper_Test_Util_NullContextStub", array('invokeNativeProc'));
	}
	
    public function testInvoke()
    {
    	$this->_connectionMock
    	   ->expects($this->once())
    	   ->method('invokeNativeProc')
    	   ->with('contexttestentity_list_by_id', array('{"1","2","3"}'), false)
    	   ->will($this->returnValue(array(
    	       (object)array('id' => 1, 'name' => 'aa'),
               (object)array('id' => 2, 'name' => 'bb'),
               (object)array('id' => 3, 'name' => 'cc'),
    	   )));
        $this->_connectionMock->contexttestentity->list_by_id(array(1, 2, 3));
    }
}


class Mapper_Procedure_contexttestentity extends Mapper_Context_ClassWrapper
{
	public function getRowtype()
	{
		return array(
            'id' => new DB_Pgsql_Type_Numeric(),
            'b' => new DB_Pgsql_Type_Numeric(),
		);
	}
}


class Mapper_Procedure_contexttestentity_list_by_id extends Mapper_Procedure_ListByCol
{
    public function getInParams()
    {
        return array(
            'id' => new DB_Pgsql_Type_Array(new DB_Pgsql_Type_String()),
        );
    }

    public function getOutParams()
    {
        return $this->_getContext()->getRowtype();
    }
}
