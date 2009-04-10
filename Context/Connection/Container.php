<?php
abstract class Mapper_Context_Connection_Container implements DB_Micro_IConnection
{
    /**
     * Connection
     *
     * @var DB_Micro_IConnection
     */
	private $_conn;

	/**
	 * True, if transaction started
	 *
	 * @var bool
	 */
	private $_transactionStarted = false;

	/**
	 * Creates a new single connection context.
	 *
	 * @param DB_Micro_IConnection $conn
	 */
	public function __construct(DB_Micro_IConnection $conn)
	{
		$this->_conn = $conn;
	}

    /**
     * Begin transaction
     *
     */
    public function beginTransaction()
    {
        $this->_transactionStarted = true;
    	$this->_conn->beginTransaction();
    }

    /**
     * Commit
     *
     */
    public function commit()
    {
        $this->_transactionStarted = false;
        $this->_conn->commit();
    }

    /**
     * Rollback transaction
     *
     */
    public function rollBack()
    {
        $this->_transactionStarted = false;
        $this->_conn->rollBack();
    }

    /**
     * Executes query
     *
     * @param string $sql
     * @param array $args
     * @return array
     */
    public function query($sql, $args = array())
    {
        return $this->_conn->query($sql, $args);
    }

    /**
     * Update query
     *
     * @param string $sql
     * @param array $args
     * @return array
     */
    public function update($sql, $args = array())
    {
        return $this->_conn->update($sql, $args);
    }

    /**
     * Gets connection
     *
     * @return DB_Micro_IConnection
     */
    protected function _getConnection()
    {
        return $this->_conn;
    }

    /**
     * Returns true, if transaction is started
     *
     * @return boolean
     */
    protected function _transactionStarted()
    {
    	return $this->_transactionStarted;
    }

}