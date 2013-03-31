<?php

/**
 * Allows to call a procedure as a public method of this object.
 * Also allows to get the procedure object as a property of this class.
 */
abstract class Mapper_Context
{
	private $_factory = null;
    private $_objects = array();

    /**
     * Set active Factory for this context.
     *
     * @param Mapper_Factory $factory
     */
    public function setFactory($factory)
    {
    	$this->_factory = $factory;
    }

    /**
     * Return current context factory.
     *
     * @return Mapper_Factory
     */
    public function getFactory()
    {
        if (!$this->_factory) {
            $this->_factory = new Mapper_Factory_Autoload();
        }
        return $this->_factory;
    }

    /**
     * Calls a specified generic procedure with arguments in current context.
     * Derived classes must override this method to allow to:
     * - call a procedure in the context of one connection;
     * - call a procedure in different connections and merge the result.
     *
     * Procedure input parameters are always list of strings.
     * Returning result is always array of objects.
     *
     * YOU MUST NOT call this method in the application code!
     *
     * @param string $procName   Procedure name.
     * @param array $procParams  List of values (always strings or NULL).
     * @param bool $isUpdate     If the procedure plans to update, true.  
     * @return array             Array of objects representing rows.
     */
    abstract public function invokeNativeProc($procName, array $procParams, $isUpdate);

    /**
     * Return the name of ParamList class which is used for
     * input/output procedura arguments conversion.
     *
     * May be overriden in derived classes to implement some
     * custom parameter conversion logig (e.g. global quoting,
     * though it is not recommended).
     *
     * @return string
     */
    public function getParamListClass()
    {
    	return 'Mapper_Type_ParamList';
    }

    /**
     * Allows to call procedures in this context by their name.
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        $proc = $this->_getByName($name);
        return call_user_func_array(array($proc, 'invoke'), $args);
    }

    /**
     * Returns the procedure object when we get it by property name.
     *
     * @param string $name
     * @return Mapper_Procedure
     */
    public final function __get($name)
    {
        return $this->_getByName($name);
    }

    /**
     * Returns the procedure or class object by its name specified.
     * Throws Mapper_Exception or Exception.
     *
     * @param string $name
     * @return Mapper_Procedure or Mapper_Context_Class
     */
    private function _getByName($name)
    {
        $key = join("|", array($name, $this->getParamListClass()));

        if (!isset($this->_objects[$key])) {
            $this->_objects[$key] = $this->getFactory()->create($this, $name);
        }
        return $this->_objects[$key];
    }
}

