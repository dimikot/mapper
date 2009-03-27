<?php
class Mapper_Context_Connection_LazyTransaction extends Mapper_Context_Connection_Container
{
    private $_transactionStarted = false;

    private $_realTransactionStarted = false;

    public function beginTransaction()
    {
        $this->_transactionStarted = true;
    }

    public function commit()
    {
        if ($this->_realTransactionStarted) {
            parent::commit();
        }
        $this->_realTransactionStarted = false;
        $this->_transactionStarted = false;
    }

    public function _transactionStarted()
    {
        return $this->_transactionStarted;
    }

    public function rollBack()
    {
        if ($this->_realTransactionStarted) {
            parent::rollBack();
        }

        $this->_realTransactionStarted = false;
        $this->_transactionStarted = false;
    }

    protected function _getBeginSql()
    {
        return 'BEGIN;';
    }

    public function query($sql, $args = array())
    {
        if ($this->_transactionStarted && !$this->_realTransactionStarted) {
            $sql = $this->_getBeginSql() . ' ' . $sql;
            $this->_realTransactionStarted = true;
        }
        return parent::query($sql, $args);
    }

    public function update($sql, $args = array())
    {
        if ($this->_transactionStarted && !$this->_realTransactionStarted) {
            $sql = $this->_getBeginSql() . ' ' . $sql;
            $this->_realTransactionStarted = true;
        }
        return parent::update($sql, $args);
    }
}