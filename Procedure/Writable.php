<?php
/**
 * Procedure which is able to write to the DB.
 *
 * May be used to detect if we need a master connection or not.
 */
abstract class Mapper_Procedure_Writable extends Mapper_Procedure
{
    /**
     * Writable procedure returns number of affected rows by default.
     *
     * @return mixed
     */
    public function getOutParams()
    {
        return new DB_Pgsql_Type_Int();
    }

    public function isReadonly()
    {
        return false;
    }
}