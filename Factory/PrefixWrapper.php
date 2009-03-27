<?php

/**
 * Factory decorator for Class context.
 * Prepend all loaded object names with a specified name prefix.
 */
class Mapper_Factory_PrefixWrapper extends Mapper_Factory
{
    private $_parentFactory = null;
    private $_name = null;

    public function __construct(Mapper_Factory $parentFactory, $name)
    {
        $this->_parentFactory = $parentFactory;
        $this->_name = $name;
    }

    public function create(Mapper_Context $context, $name, $parentName = null)
    {
        if ($parentName && 0 === strpos($parentName, $this->_name . '_')) {
        	$parentName = substr($parentName, strlen($this->_name) + 1);
        }

        if (0 === strpos($name, $this->_name . '_')) {
        	$name = substr($name, strlen($this->_name) + 1);
        }

        return $this->_parentFactory->create(
            $context,
            $this->_name . "_" . $name,
            $this->_name . ($parentName ? "/" . $parentName : "")
        );
    }
}
