<?php

/**
 * Database stored procedure metainformation.
 *
 * The procedure knows nothing about how it is executed.
 * Instead, it calls its context while invokation.
 */
abstract class Mapper_Procedure
{
    /**
     * Context of this procedure.
     *
     * @var Mapper_Context
     */
    private $_context = null;
    
    /**
     * Procedure name.
     * 
     * @var string
     */
    private $_name;

    /**
     * Creates a procedure and binds it to the specified context.
     *
     * @param Mapper_Context $context
     * @param string $name
     */
    public function __construct(Mapper_Context $context, $name = null)
    {
    	$this->_context = $context;
        $this->_name = $name? $name : get_class($this);
    	$this->_selfCheck();
    }

    /**
     * Invokes the procedure in its context.
     *
     * @return mixed
     */
    public function invoke($args)
    {
    	$paramListClass = $this->_context->getParamListClass();
    	
        // Convert values to native things.
        $inParams = new $paramListClass($this->getInParams(), $this);
        $parameters = $inParams->output($args);
        
        // Invoke the procedure.
        $rows = $this->_context->invokeNativeProc($this->getName(), array_values($parameters), !$this->isReadonly());
        
        // Check if the procedure returns scalar.
        $outParams = $this->getOutParams();
        if (!is_array($outParams)) {
        	reset($rows);
            reset(current($rows));
            return $outParams->input(current(current($rows)));
        }
        
        // Convert native things back to values.
        $outParams = new $paramListClass($outParams);
        $objects = array();
        foreach ($rows as $row) {
        	$objects[] = (object)$outParams->input($row);
        }
        
        // Process the result.
        return new Mapper_Aggregate($objects, $this->_context);
    }

    /**
     * Returns the list of IN-parameters for stored procedure.
     * The order of parameters is very significant and must match
     * the procedure prototype!
     * Must be overriden in derived classes.
     *
     * @return array  Returns array("propertyName" => DB_Pgsql_Type_Abstract_Base).
     */
    abstract public function getInParams();

    /**
     * Returns the list of OUT-parameters mapping for Entity creation.
     * Must be overriden in derived classes.
     *
     * @return array  Returns array("propertyName" => "nativeName", ...).
     */
    abstract public function getOutParams();

    /**
     * Returns the procedure name.
     *
     * @return string  Procedure name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns true if this procedure is read-only
     *
     * @return bool
     */
    public function isReadonly()
    {
    	return true;
    }

    /**
     * Self-check for parameter types etc.
     * Throws an exception on failure.
     *
     * @return void
     */
    protected function _selfCheck() 
    {
    }

    public function getErrors()
    {
        return array();
    }

    /**
     * Return this procedure object calling context.
     * You may override this method.
     * 
     * @return Mapper_Context
     */
    protected function _getContext()
    {
    	return $this->_context;
    }

    /**
     * Return this procedure object calling context.
     * We need to access it while calling fillChildren().
     *
     * @return Mapper_Context
     */
    public final function getContext()
    {
    	return $this->_getContext();
    }
}
