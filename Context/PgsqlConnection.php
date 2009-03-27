<?php

/**
 * Context of a single database connection.
 * May be used to call a procedure for a single connection.
 */
class Mapper_Context_PgsqlConnection extends Mapper_Context
{
	private $_conn;

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
     * Calls a native stored procedure and returns its rowset output.
     * This method knows nothing about Mapper_Procedure abstraction.
	 *
     * @param string $procName   Procedure name.
     * @param array $procParams  List of values (always strings or NULL).
     * @param bool $isUpdate     If the procedure plans to update, true.
     * @return array             Array of objects representing rows.
	 */
    public function invokeNativeProc($procName, array $procParams, $isUpdate)
	{
		$sql = $this->_getQuery($procName, $procParams);
        try {
        	return !!$isUpdate
        	    ? $this->_conn->update($sql, $procParams)
        	    : $this->_conn->query($sql, $procParams);
        } catch (DB_Micro_Exception $e) {
            $this->_translateException($e, $procName);
        }
        return array();
	}

	/**
	 * Return SQL query to be executed.
	 *
	 * @return string
	 */
	protected function _getQuery($procName, $procParams)
	{
        return "SELECT * FROM {$procName}(" . join(", ", $procParams? array_fill(0, count($procParams), '?') : array()) . ")";
	}

    /**
     * Wraps an exception thrown by throw() stored procedure.
     *
     * @param DB_Micro_Exception $e
     */
    private function _translateException(DB_Micro_Exception $e, $procName)
    {
        $m = null;
        if (preg_match('/ERROR:\s*THROW:\s*(.*)/Am', $e->getMessage(), $m)) {
        	$type = new DB_Pgsql_Type_Hstore(new DB_Pgsql_Type_String());
            $fields = $type->input($m[1]);
            $e = new Mapper_DataException($e, $procName);
            foreach ($fields as $name => $error) {
                $e->addError($name, $error);
            }
        } elseif (preg_match('/ERROR:\s*AUTH: DENY/Am', $e->getMessage(), $m)) {
            $e = new Mapper_AuthException($e, $procName);
        }
        throw $this->_convertException($e);
    }

    /**
     * Some applications must convert Mapper_Exception into
     * their own exceptions.
     *
     * @param Mapper_Exception $e
     * @return Exception  New exception to be thrown.
     */
    protected function _convertException(Exception $e)
    {
    	return $e;
    }


    /**
    * Proxy for connection's beginTransaction().
    *
    * @throws Exception on fail
    */
    public function beginTransaction()
    {
    	return $this->_conn->beginTransaction();
    }

    /**
     * Proxy for connection's commit().
     *
     * @throws Exception on fail
     */
    public function commit()
    {
        return $this->_conn->commit();
    }

    /**
     * Proxy for connection's rollBack().
     *
     * @throws Exception on fail
     */
    public function rollBack()
    {
        return $this->_conn->rollBack();
    }
}
