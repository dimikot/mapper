<?php
/**
 * Filesystem-based procedure and class creation factory.
 * Used by default.
 */
class Mapper_Factory_Autoload extends Mapper_Factory
{
    /**
     * Returns the classname prefix which is used to build the
     * full procedure classnames. May be overridden.
     *
     * @return string
     */
    protected function _getClsPrefix()
    {
        return 'Mapper_Procedure';
    }

    /**
     * Create a procedure or class and pass a contest and object name to them.
     * Does not perform any caching, because context-dependent.
     *
     * @param Mapper_Context $context   Context to assign to this object.
     * @param string $name              Name of the object to create.
     * @param string $parentName        Name of the context $context (if presented).
     * @return mixed
     */
    public function create(Mapper_Context $context, $name, $parentName = null)
    {
        if (!preg_match('/^\w+$/s', $name)) {
            throw new Mapper_Exception("Invalid procedure or class name: $name");
        }

        $clsPrefix = $this->_getClsPrefix();
        $className = $clsPrefix . '_' . $name;

        $origName = $name;
        if (
            $parentName
            && 0 === strpos(
                $name,
                str_replace('/', '_', $parentName) . '_'
            )
        ) {
        	$name = substr($name, strlen($parentName) + 1);
        }
        $fileName = str_replace('_', '/', $clsPrefix)
            . "/"
            . ($parentName ? $parentName . "/" : "")
            . $name
            . ".php";

        try {
            PHP_Autoload::loadFile($fileName);
        } catch (PHP_Autoload_Exception $e) {
            $e; // to remove Zend Studio notice of unused variable
        }
        if (!class_exists($className)) {
            throw new PHP_Autoload_Exception("Cannot find the class \"{$className}\" after loading \"{$fileName}\"");
        }

        return new $className($context, $origName);
    }
}
