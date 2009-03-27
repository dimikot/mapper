<?php
class Mapper_Context_Connection_TransactionWriter extends Mapper_Context_Connection_Container
{
    public function update($sql, $args = array())
    {
        $started_now = false;
        $e = null;

        if (!$this->_transactionStarted()) {
            $this->beginTransaction();
            $started_now = true;
        }

        try {
            $result = $this->_getConnection()->update($sql, $args);
        } catch (Exception $e) {
            // saved;
        }

        if ($started_now) {
            if ($e) {
                $this->rollBack();
                throw $e;
            } else {
                $this->commit();
            }
        } elseif ($e) {
            throw $e;
        }

        return $result;
    }
}