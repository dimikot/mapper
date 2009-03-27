<?php
/**
 * Calling prototype:
 *
 * $proc->invoke(array $properties)
 */
abstract class Mapper_Procedure_Insert extends Mapper_Procedure_Writable
{
    /**
     * INSERT procedure always returns inserted ID.
     *
     * @return mixed
     */
    public final function getOutParams()
    {
        return new Mapper_Type_Identifier();
    }
}