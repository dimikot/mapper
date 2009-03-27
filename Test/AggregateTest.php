<?php
class Mapper_Test_AggregateTest extends PHPUnit_Framework_TestCase
{
	private $_connectionMock;
	private $_otherConnectionMock;
	private $_agg;
	
	public function setUp()
	{
		$this->_connectionMock = $this->getMock("Mapper_Test_Util_NullContextStub", array('fillSomething'));
		$this->_otherConnectionMock = $this->getMock("Mapper_Test_Util_NullContextStub", array('fillOther'));
		$this->_agg = new Mapper_Aggregate(array(
			(object)array('id' => 1, 'pid' => 2),
			(object)array('id' => 2, 'pid' => 4),
			(object)array('id' => 3, 'pid' => 6),
		), $this->_connectionMock);
		$this->_agg->setIdColumnName('id');
	}
	
    public function testFillPropsFrom()
    {
    	$procMock = $this->getMock("Mapper_Procedure_aggregatetest_list_by_id", array('invoke'), array($this->_connectionMock));
    	$procMock
    	   ->expects($this->once())
    	   ->method('invoke')
    	   ->with(array(2, 4, 6), array())
    	   ->will($this->returnValue(array(
				(object)array('id' => 2, 'a' => 'aaa'),
				(object)array('id' => 4, 'a' => 'bbb'),
				(object)array('id' => 6, 'a' => 'ccc'),
    	   )));
		$this->_agg->fillPropsFrom($procMock, 'pid', array());
		$this->assertEquals(
			array(
				(object)array('id' => 1, 'pid' => 2, 'a' => 'aaa'),
				(object)array('id' => 2, 'pid' => 4, 'a' => 'bbb'),
				(object)array('id' => 3, 'pid' => 6, 'a' => 'ccc'),
			),
			$this->_agg->getArrayCopy()
		);    	   
    }
    
    public function testFillChildren()
    {
    	$procMock = $this->getMock("Mapper_Procedure_aggregatetest_list_by_id", 
    		array('invoke'), 
    		array($this->_otherConnectionMock)
    	);
    	$procMock
    	   ->expects($this->once())
    	   ->method('invoke')
    	   ->with(array(1, 2, 3), array())
    	   ->will($this->returnValue(array(
				(object)array('id' => 2, 'pid' => 1, 'a' => 'aaa'),
				(object)array('id' => 4, 'pid' => 1, 'a' => 'bbb'),
				(object)array('id' => 6, 'pid' => 3, 'a' => 'ccc'),
    	   )));
    	$this->_connectionMock
    		->expects($this->once())
    		->method('fillSomething');    		   
    	$this->_otherConnectionMock
    		->expects($this->once())
    		->method('fillOther');    		   
		$this->_agg->fillChildren($procMock, 'pid', 'children');
		$this->assertEquals('aaa', $this->_agg[0]->children[0]->a);
		$this->assertEquals('bbb', $this->_agg[0]->children[1]->a);
		$this->assertEquals('ccc', $this->_agg[2]->children[0]->a);
		// Check that returned Aggregate has _connectionMock context.
		$this->_agg->fillSomething();
		// Check that filled via fillChildren aggregate has other _otherConnectionMock context.
		$this->_agg[0]->children->fillOther();
    }
    
    public function testFillPropsFromWhenPropertiesOverlap()
    {
    	$procMock = $this->getMock("Mapper_Procedure_aggregatetest_list_by_id", array('invoke'), array($this->_connectionMock));
    	$procMock
    	   ->expects($this->once())
    	   ->method('invoke')
    	   ->with(array(2, 4, 6), array())
    	   ->will($this->returnValue(array(
				(object)array('id' => 2, 'pid' => 'aaa'),
				(object)array('id' => 4, 'pid' => 'bbb'),
				(object)array('id' => 6, 'pid' => 'ccc'),
    	   )));
		$this->_agg->fillPropsFrom($procMock, 'pid', array());
		$this->assertEquals(
			array(
				(object)array('id' => 1, 'pid' => 2),
				(object)array('id' => 2, 'pid' => 4),
				(object)array('id' => 3, 'pid' => 6),
			),
			$this->_agg->getArrayCopy()
		);    	   
    }    
}


class Mapper_Procedure_aggregatetest_list_by_id extends Mapper_Procedure_ListByCol
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
        	'id' => new DB_Pgsql_Type_String(),
        	'a'  => new DB_Pgsql_Type_String(),
        );
    }
}
