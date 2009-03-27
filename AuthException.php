<?php

/**
 * Custom database-generated exception.
 */
class Mapper_AuthException extends Mapper_Exception
{

	/**
	 * Array with exceptions, field => exception.
	 *
	 * @var array
	 */
	private $_exceptions = array();

	/**
	 * Original exception
	 *
	 * @var Exception
	 */
	private $_origException = null;

	/**
	 * Generator procedure name.
	 *
	 * @var string
	 */
	private $_procedureName = null;

	/**
	 * Constructor.
	 *
	 * @param Exception $origException    If present, means a single
	 *                                    exception caused this one
	 *                                    (usually DataException).
	 * @param string $_procedureName      Invoker (if present).
	 */
	public function __construct($origException = null, $procedureName = null)
	{
		parent::__construct(($procedureName? $procedureName . ": " : ""));
	    $this->_procedureName = $procedureName;
		$this->_origException = $origException;
	}

	/**
	 * Return parent exceptions associated to field (if present).
	 * Exceptions may be associated not always.
	 *
	 * @return array
	 */
	public function getExceptions()
	{
		return $this->_exceptions;
	}

	/**
	 * Return generator procedure or null of no procedure
	 * is assigned to this exception.
	 *
	 * @return string
	 */
	public function getProcedureName()
	{
		return $this->_procedureName;
	}
}