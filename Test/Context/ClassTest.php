<?php
class Mapper_Test_Context_ClassTest extends PHPUnit_Framework_TestCase
{
	private $_connectionMock;
	private $_class;
	
	public function setUp()
	{
		$this->_connectionMock = $this->getMock("Mapper_Test_Util_NullContextStub", array('invokeNativeProc'));
		$this->_class = new Mapper_Test_Context_Class_Entity($this->_connectionMock, 'entity');
	}
	
    public function testInvoke()
    {
    	$this->_connectionMock
    	   ->expects($this->once())
    	   ->method('invokeNativeProc')
    	   ->with('entity_classtestobj_list_by_id', array('{"1","2","3"}'), false)
    	   ->will($this->returnValue(array(
    	       (object)array('id' => 1, 'name' => 'aa'),
               (object)array('id' => 2, 'name' => 'bb'),
               (object)array('id' => 3, 'name' => 'cc'),
    	   )));
        $this->_class->classtestobj_list_by_id(array(1, 2, 3));
    }
    
    public function testFillRedirection()
    {
    	$connMock = $this->getMock("Mapper_Test_Util_NullContextStub", array('fillUrl'));
    	$agg = new Mapper_Aggregate(
    		array(),
    		$connMock
    	);
    	$connMock
    	   ->expects($this->once())
    	   ->method('fillUrl')
    	   ->with($agg, 10);
        $agg->fillUrl(10);
    }

	/**
	 * @expectedException Mapper_Exception
	 */
    public function testFillRedirectionBadMethodName()
    {
    	$connMock = $this->getMock("Mapper_Test_Util_NullContextStub", array('fillUrl'));
    	$agg = new Mapper_Aggregate(
    		array(),
    		$connMock
    	);
        $agg->someMethod();
    }

	/**
	 * @expectedException Mapper_Exception
	 */
    public function testFillRedirectionUndefinedMethod()
    {
    	$connMock = $this->getMock("Mapper_Test_Util_NullContextStub", array('fillUrl'));
    	$agg = new Mapper_Aggregate(
    		array(),
    		$connMock
    	);
        $agg->fillSome();
    }
}


class Mapper_Test_Context_Class_Entity extends Mapper_Context_ClassWrapper
{
	public function getRowtype()
	{
		return array(
            'id' => new DB_Pgsql_Type_Numeric(),
            'b' => new DB_Pgsql_Type_Numeric(),
		);
	}
	
	public function fillUrl($aggregate, $n)
	{
		foreach ($aggregate as $v) {
			$v->url = $n++;
		}
		return $aggregate;
	}
}


class Mapper_Procedure_entity_classtestobj_list_by_id extends Mapper_Procedure_ListByCol
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
