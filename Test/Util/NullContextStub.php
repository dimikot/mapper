<?php

class Mapper_Test_Util_NullContextStub extends Mapper_Context 
{
	public function invokeNativeProc($procName, array $procParams, $isUpdate)
	{
		$procName;
		$procParams;
		$isUpdate;
		// Do nothing, use mocks to check.
	}
}
