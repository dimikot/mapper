<?php
class Mapper_Context_Connection_RDBLink extends Mapper_Context_Connection_Container
{
    /**
	 * True, if transaction started
	 *
	 * @var bool
	 */
	private $_transactionStarted = false;

    public function beginTransaction()
    {
        $this->_transactionStarted = true;
        return $this->query('BEGIN; SELECT rdblink.rdblink_begin();');
    }

    public function commit()
    {
        $this->_transactionStarted = false;
        $this->query('SELECT rdblink.rdblink_commit(); COMMIT;');
    }

    public function rollBack()
    {
        $this->_transactionStarted = false;
        $this->query('ROLLBACK; SELECT rdblink.rdblink_rollback();');
    }

    protected function _transactionStarted()
    {
    	return $this->_transactionStarted;
    }
}