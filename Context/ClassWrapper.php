<?php
/**
 * Decorator for accessing to class-look contexts:
 *
 * $context->class->proc(...)
 * is typically translated to class_proc() call.
 */
abstract class Mapper_Context_ClassWrapper extends Mapper_Context
{
    /**
     * Parent context.
     *
     * @var Mapper_Context
     */
    private $_parentContext;


    /**
     * Name of the context.
     *
     * @var string
     */
    private $_name;

    /**
     * Class context has associated ROWTYPE which may be used by
     * called procedures.
     *
     * @return array
     */
    abstract public function getRowtype();

    /**
     * Get parent context.
     *
     * @return Mapper_Context
     */
    protected function _getContext()
    {
        return $this->_parentContext;
    }

    /**
     * This class is only a wrapper for other context.
     *
     * @param Mapper_Context   $context
     * @param string $name     Prefix of all procedures called in this context
     *                         (by default, use the last component of defived class).
     */
    public function __construct(Mapper_Context $parentContext, $name)
    {
        $this->_parentContext = $parentContext;
        $this->_name = $name;
        $this->setFactory(new Mapper_Factory_PrefixWrapper($parentContext->getFactory(), $name));
    }

    /**
     * Redirect to wrapped context.
     *
     * @param string $procName
     * @param array $procParams
     * @param bool $isUpdate
     * @return array
     */
    public function invokeNativeProc($procName, array $procParams, $isUpdate)
    {
        return $this->_parentContext->invokeNativeProc($procName, $procParams, $isUpdate);
    }

    /**
     * Redirect to wrapped context.
     *
     * @return string
     */
    public function getParamListClass()
	{
		return $this->_parentContext->getParamListClass();
	}
	
	/**
	 * Redirect to wrapped context.
	 * 
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		if (method_exists($this->_parentContext, $method)) {
			return call_user_func_array(array($this->_parentContext, $method), $args);
		} else {
			return parent::__call($method, $args);
		}
	}
}

