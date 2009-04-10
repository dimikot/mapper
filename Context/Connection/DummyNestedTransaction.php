<?php
class Mapper_Context_Connection_DummyNestedTransaction extends Mapper_Context_Connection_Container
{
    /**
     * Counter for counting level
     *
     * @var int
     */
    private $_counter = 0;

    /**
     * Flag, if transaction is aborted
     *
     * @var boolean
     */
    private $_isDead = false;

    public function beginTransaction()
    {
        if ($this->_counter == 0) {
            parent::beginTransaction();
        }

        $this->_counter ++;
    }

    public function commit()
    {
        if ($this->_isDead) {
            throw new Mapper_Exception(__CLASS__ . ': You cannot use commit, because it have been rolled back');
        }

        $this->_counter --;

        if ($this->_counter == 0) {
            parent::commit();
        } elseif ($this->_counter < 0) {
            throw new Mapper_Exception(
                __CLASS__ . ': No transaction started'
            );
        }
    }

    public function rollBack()
    {
        $this->_counter --;

        if ($this->_counter == 0) {
            $this->_isDead = false;
            parent::rollBack();
        } elseif ($this->_counter < 0) {
            throw new Mapper_Exception(
                __CLASS__ . ': No transaction started'
            );
        }

        $this->_isDead = true;
    }
}