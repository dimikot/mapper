<?php
class Mapper_Test_Type_ParamListTest extends DB_Pgsql_Type_Test_Util_TypeTestCase
{
    protected function _getPairsInput()
    {
    	return array_merge(
            array(
		    ),
            $this->_getPairsOutput()
        );
    }

    protected function _getPairsOutput()
    {
    	return array(
            array(
                new Mapper_Type_ParamList(array(
                    'a' => new DB_Pgsql_Type_String(),
                    'b' => new DB_Pgsql_Type_String(),
                    'c' => new DB_Pgsql_Type_String()
                )),
                array('a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc'),
                array('a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc'),
            ),
            array(
                new Mapper_Type_ParamList(array(
                    'any' => array(
                        'a' => new DB_Pgsql_Type_Array(new DB_Pgsql_Type_String()),
                    ),
                )),
                array('a' => array('aa', 'aaa')),
                array('any' => '("{""aa"",""aaa""}")'),
            ),
            array(
                new Mapper_Type_ParamList(array(
                    'any' => array(
                        'a' => new DB_Pgsql_Type_Array(
                            new DB_Pgsql_Type_Array(
                                new DB_Pgsql_Type_String()
                            )
                        ),
                    ),
                )),
                array('a' => array(array('aa', 'aaa'))),
                array('any' => '("{{""aa"",""aaa""}}")'),
            ),
            array(
                new Mapper_Type_ParamList(array(
                    'any' => array(
                        'a' => new DB_Pgsql_Type_String(),
                        'b' => new DB_Pgsql_Type_String(),
                    ),
                    'c' => new DB_Pgsql_Type_String()
                )),
	            array('a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc'),
	            array('any' => '("aaa","bbb")', 'c' => 'ccc'),
	        ),
            array(
                new Mapper_Type_ParamList(array(
                    'any' => array(
                        'a' => new DB_Pgsql_Type_String(),
                        'b' => new DB_Pgsql_Type_String(),
                        'x' => array(
                            'y' => new DB_Pgsql_Type_String(),
                            'z' => new DB_Pgsql_Type_String(),
                        ),
                    ),
                    'c' => new DB_Pgsql_Type_String()
                )),
                array('a' => 'aaa', 'b' => 'bbb', 'y' => 'yyy', 'z' => 'zzz', 'c' => 'ccc'),
                array('any' => '("aaa","bbb","(""yyy"",""zzz"")")', 'c' => 'ccc'),
            ),
            array(
                new Mapper_Type_ParamList(array(
                    'any' => array(
                        'a' => new DB_Pgsql_Type_String(),
                        'b' => new DB_Pgsql_Type_String(),
                        'x' => array(
                            'y' => new DB_Pgsql_Type_String(),
                            'z' => new DB_Pgsql_Type_String(),
                        ),
                    ),
                    'other' => array(
                        'k' => new DB_Pgsql_Type_String(),
                        'l' => array(
                            'm' => new DB_Pgsql_Type_String(),
                        ),
                    ),
                    'c' => new DB_Pgsql_Type_String()
                )),
                array('a' => 'aaa', 'b' => 'bbb', 'y' => 'yyy', 'z' => 'zzz', 'k' => 'kkk', 'm' => 'mmm', 'c' => 'ccc'),
                array('any' => '("aaa","bbb","(""yyy"",""zzz"")")', 'other' => '("kkk","(""mmm"")")', 'c' => 'ccc'),
            ),
        );
    }
}
