<?php
class Mapper_Test_Procedure_SearchTest extends PHPUnit_Framework_TestCase
{
	private $_contextMock;
	
	public function setUp()
	{
		$this->_contextMock = $this->getMock("Mapper_Test_Util_NullContextStub", array('invokeNativeProc'));
	}
	
    public function testInvoke()
    {
    	$this->_contextMock
    	   ->expects($this->at(0))
           ->method('invokeNativeProc')
    	   ->with(
    	       'searchtestobj_skel_by_keywords', 
    	       array(500, 'query'),
    	       false
    	   )
           ->will($this->returnValue(array((object)array('id' => '{1,2,3}'))));
        $this->_contextMock
           ->expects($this->at(1))
           ->method('invokeNativeProc')
    	   ->with(
    	       'searchtestobj_data_by_keywords', 
    	       array('{"2","3"}'),
    	       false
    	   )
           ->will($this->returnValue($ret = array(
               (object)array('id' => 2, 'name' => 'bb'),
               (object)array('id' => 3, 'name' => 'cc'),
           )));
    	$pager = new Mapper_Pager(1, 10, 500);   
        $this->assertEquals(
            new Mapper_Aggregate($ret, $this->_contextMock),
            $this->_contextMock->searchtestobj_search_by_keywords(array('keywords' => 'query'), $pager)
        );
    }    
}


class Mapper_Procedure_searchtestobj_search_by_keywords extends Mapper_Procedure_Search
{
    public function getInParams()
    {
        return array(
            'limit'    => new DB_Pgsql_Type_Int(),
            'keywords' => new DB_Pgsql_Type_String(),
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
