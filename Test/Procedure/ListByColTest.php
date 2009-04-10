<?php
class Mapper_Test_Procedure_ListByColTest extends PHPUnit_Framework_TestCase
{
	private $_contextMock;
	
	public function setUp()
	{
		$this->_contextMock = $this->getMock("Mapper_Test_Util_NullContextStub", array('invokeNativeProc'));
	}
	
    public function testInvoke()
    {
    	$this->_contextMock
    	   ->expects($this->once())
    	   ->method('invokeNativeProc')
    	   ->with('listbycoltestobj_list_by_id', array('{"1","2","3"}'), false)
    	   ->will($this->returnValue(array(
    	       (object)array('id' => 1, 'name' => 'aa'),
               (object)array('id' => 2, 'name' => 'bb'),
               (object)array('id' => 3, 'name' => 'cc'),
    	   )));
        $this->_contextMock->listbycoltestobj_list_by_id(array(1, 2, 3));
    }    

    public function testInvokeWithScalar()
    {
    	$this->_contextMock
    	   ->expects($this->once())
    	   ->method('invokeNativeProc')
    	   ->with('listbycoltestobj_list_by_id', array('{"10"}'), false)
    	   ->will($this->returnValue(array(
    	       (object)array('id' => 10, 'name' => 'aa'),
    	   )));
        $this->_contextMock->listbycoltestobj_list_by_id(10);
    }    

    public function testInvokeWithNull()
    {
    	$this->_contextMock
    	   ->expects($this->once())
    	   ->method('invokeNativeProc')
    	   ->with('listbycoltestobj_list_by_id', array('{NULL}'), false)
    	   ->will($this->returnValue(array(
    	       (object)array('id' => 10, 'name' => 'aa'),
    	   )));
        $this->_contextMock->listbycoltestobj_list_by_id(null);
    }    
}


class Mapper_Procedure_listbycoltestobj_list_by_id extends Mapper_Procedure_ListByCol
{
    public function getInParams()
    {
        return array(
            'id' => new DB_Pgsql_Type_Array(new DB_Pgsql_Type_String()),
        );
    }

    public function getOutParams()
    {
        return array(
            'id' => new Mapper_Type_Identifier(),
            'name' => new DB_Pgsql_Type_String(),
        );
    }
}
