<?php

class Mapper_Test_Util_ContextStub extends Mapper_Context 
{
	public function invokeNativeProc($procName, array $procParams, $isUpdate)
	{
		$procName;
		$procParams;
		$isUpdate;
		// Do nothing, use mocks to check.
	}
	
	protected function _getProcByName($name)
	{
		// Just instantiate a given classname.
		return new $name($this);
	}
}
