<?php
class Mapper_Test_Context_Connection_ContainerTest extends PHPUnit_Framework_ProcedureTestCase
{
    /**
     * Returns container
     *
     * @param class $mock
     * @return Mapper_Test_Context_Connection_ContainerTestMock
     */
    private function _getContainer($mock)
    {
        return new Mapper_Test_Context_Connection_ContainerTestMock($mock);
    }

    public function testBegin()
    {
        $mock = $this->getMock(
            'Mapper_Test_Context_Connection_Mock',
            array('beginTransaction')
        );

        $mock->expects($this->at(0))
            ->method('beginTransaction');

        $class = $this->_getContainer($mock);

        $this->assertFalse($class->transactionStarted());

        $class->beginTransaction();

        $this->assertTrue($class->transactionStarted());
    }

    public function testUpdate()
    {
        $mock = $this->getMock(
            'Mapper_Test_Context_Connection_Mock',
            array('update')
        );

        $mock->expects($this->at(0))
            ->method('update')
            ->with(
                $this->equalTo('SQL'),
                $this->equalTo(array('args'))
            );

        $class = $this->_getContainer($mock);

        $class->update('SQL', array('args'));
    }

    public function testQuery()
    {
        $mock = $this->getMock(
            'Mapper_Test_Context_Connection_Mock',
            array('query')
        );

        $mock->expects($this->at(0))
            ->method('query')
            ->with(
                $this->equalTo('SQL'),
                $this->equalTo(array('args'))
            );

        $class = $this->_getContainer($mock);

        $class->query('SQL', array('args'));
    }

    public function testCommit()
    {
        $mock = $this->getMock(
            'Mapper_Test_Context_Connection_Mock',
            array('beginTransaction', 'commit')
        );

        $mock->expects($this->at(0))
            ->method('beginTransaction');

        $mock->expects($this->at(1))
            ->method('commit');

        $class = $this->_getContainer($mock);

        $this->assertFalse($class->transactionStarted());

        $class->beginTransaction();

        $this->assertTrue($class->transactionStarted());

        $class->commit();

        $this->assertFalse($class->transactionStarted());
    }

    public function testRollBack()
    {
        $mock = $this->getMock(
            'Mapper_Test_Context_Connection_Mock',
            array('beginTransaction', 'rollBack')
        );

        $mock->expects($this->at(0))
            ->method('beginTransaction');

        $mock->expects($this->at(1))
            ->method('rollBack');

        $class = $this->_getContainer($mock);

        $this->assertFalse($class->transactionStarted());

        $class->beginTransaction();

        $this->assertTrue($class->transactionStarted());

        $class->rollBack();

        $this->assertFalse($class->transactionStarted());
    }

    public function testGetConnection()
    {
        $example = new Mapper_Test_Context_Connection_Mock();

        $class = $this->_getContainer($example);

        $this->assertEquals(
            $example,
            $class->getConnection()
        );
    }
}

class Mapper_Test_Context_Connection_ContainerTestMock
    extends Mapper_Context_Connection_Container
{

    /**
     * Returns boolean
     *
     * @return boolean
     */
    public function transactionStarted()
    {
        return parent::_transactionStarted();
    }

    public function getConnection()
    {
        return parent::_getConnection();
    }
}