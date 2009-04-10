<?php
class Mapper_Test_Context_Connection_DummyNestedTransactionTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * Returns container
     *
     * @param class $mock
     * @return Mapper_Test_Context_Connection_ContainerTestMock
     */
    private function _getContainer($mock = null)
    {
        if ($mock === null) {
            $mock = new Mapper_Test_Context_Connection_Mock();
        }
        return new Mapper_Context_Connection_DummyNestedTransaction($mock);
    }

    public function testSimple()
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

        $class->beginTransaction();
        {
            $class->beginTransaction();
            $class->commit();

            $class->beginTransaction();
            {
                $class->beginTransaction();
                $class->commit();
            }
            $class->commit();
        }
        $class->commit();
    }

    public function testSimpleBeginCommit()
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

        $class->beginTransaction();
        $class->commit();
    }

    public function testSimpleBeginRollback()
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

        $class->beginTransaction();
        $class->rollBack();
    }

    public function testSimpleTwoBeginCommit()
    {
        $mock = $this->getMock(
            'Mapper_Test_Context_Connection_Mock',
            array('beginTransaction', 'commit')
        );

        $mock->expects($this->at(0))
            ->method('beginTransaction');

        $mock->expects($this->at(1))
            ->method('commit');

        $mock->expects($this->at(2))
            ->method('beginTransaction');

        $mock->expects($this->at(3))
            ->method('commit');

        $class = $this->_getContainer($mock);

        $class->beginTransaction();
        $class->commit();

        $class->beginTransaction();
        $class->commit();
    }

    public function testOverflowCommit()
    {
        $mock = $this->getMock(
            'Mapper_Test_Context_Connection_Mock',
            array('beginTransaction')
        );

        $mock->expects($this->at(0))
            ->method('beginTransaction');

        $class = $this->_getContainer($mock);

        $class->beginTransaction();
        {
            $class->beginTransaction();
            $class->commit();

            $class->beginTransaction();
            {
                $class->beginTransaction();
                $class->commit();
            }
            $class->commit();
        }
        $class->commit();

        try {
            // Otherflow commit
            $class->commit();
            $this->fail();
        } catch (Mapper_Exception $e) {
            //..
        }
    }

    public function testOverflowRollBack()
    {
        $mock = $this->getMock(
            'Mapper_Test_Context_Connection_Mock',
            array('beginTransaction')
        );

        $mock->expects($this->at(0))
            ->method('beginTransaction');

        $class = $this->_getContainer($mock);

        $class->beginTransaction();
        {
            $class->beginTransaction();
            $class->commit();

            $class->beginTransaction();
            {
                $class->beginTransaction();
                $class->rollBack();
            }
            $class->rollBack();
        }
        $class->rollBack();

        try {
            // Otherflow commit
            $class->rollBack();
            $this->fail();
        } catch (Mapper_Exception $e) {
            //..
        }
    }

    public function testSingleCommit()
    {
        try {
            // Otherflow commit
            $this->_getContainer()->commit();
            $this->fail();
        } catch (Mapper_Exception $e) {
            //..
        }
    }

    public function testSingleRollBack()
    {
        try {
            // Otherflow commit
            $this->_getContainer()->rollBack();
            $this->fail();
        } catch (Mapper_Exception $e) {
            //..
        }
    }


}