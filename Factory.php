<?php
abstract class Mapper_Factory
{
    /**
     * Create a procedure or class and pass a contest and object name to them.
     * Does not perform any caching, because context-dependent.
     *
     * @param Mapper_Context $context   Context to assign to this object.
     * @param string $name              Name of the object to create.
     * @param string $parentName        Name of the context $context (if presented).
     * @return mixed
     */
    abstract public function create(Mapper_Context $context, $name, $parentName = null);
}
