<?php
/**
 * Mock for tests
 *
 */
class Mapper_Test_Context_Connection_Mock implements DB_Micro_IConnection
{

    public function beginTransaction(){}

    public function commit(){}

    public function rollBack(){}

    public function query($sql, $args = array()){}

    public function update($sql, $args = array()){}
}