<?php
/**
 * List of procedure parameters (input or output) with ability to merge
 * ROW values with plain list (input) or combine a list to ROW value (output).
 *
 * Also translates type exceptions to correct Mapper_DataException.
 *
 * Input and output are arrays (contrary to most of other types),
 * so this is a class to convert input arrays to output arrays
 * and vice versa.
 *
 * If the list contain Mapper_Type_ParamList item, it will be
 * treated as ROW(), but corresponding values will be fetched
 * from the same array as siblings. E.g.:
 *
 *     $type = new Mapper_Type_ParamList(array(
 *         'any' => array(
 *             'a' => new DB_Pgsql_Type_String(),
 *             'b' => new DB_Pgsql_Type_String(),
 *         ),
 *         'c' => new DB_Pgsql_Type_String()
 *     ));
 *
 * Then for OUTPUT:
 *
 *     print_r($type->output(array('a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc')));
 *
 * will produce:
 *
 *     array('any' => '("aaa", "bbb")', 'c' => 'ccc')
 *
 * And for INPUT:
 *
 *     print_r($type->input(array('any' => '("aaa", "bbb")', 'c' => 'ccc')));
 *
 * will produce:
 *
 *     array('a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc'))
 */
class Mapper_Type_ParamList extends DB_Pgsql_Type_Abstract_Base
{
	/**
	 * Types and names of all argumends.
	 *
	 * @var array
	 */
	private $_items;

	/**
	 * Procedure which owns this parameters list (optional).
	 *
	 * @var Mapper_Procedure
	 */
	private $_proc;

	/**
	 * Same as $_items, but ALL types are replaced by String.
	 * This is used for ROW conversions.
	 *
	 * @var array
	 */
	private $_plainItems;

	/**
	 * Same as $_items, but sub-arrays ($item) are replaced by new self($item).
	 * Used for recurrent nested arrays fetching.
	 *
	 * @var array
	 */
	private $_subItems;

	public function __construct($items, Mapper_Procedure $proc = null)
	{
		$this->_items = $items;
		$this->_proc = $proc;

        $stringType = new DB_Pgsql_Type_String();
		$this->_plainItems = $this->_subItems = array();
        foreach ($items as $field => $item) {
	        $this->_plainItems[$field] = $stringType;
	        if (is_array($item)) {
                $this->_subItems[$field] = new self($item, $proc);
            } else {
            	if (!$item instanceof DB_Pgsql_Type_Abstract_Base) {
            		throw new Mapper_Exception("Parameter $field must be instanceof DB_Pgsql_Type_Abstract_Base, " . var_dump($item, 1) . " given");
            	}
            }
        }
	}

	/**
	 * Perform output conversion of values to be passed to a stored procedure.
	 * Also translates Type exceptions to correct DataExceptions.
	 *
	 * @param array $values
	 * @return array
	 */
	public function output($values)
	{
        $dataEx = null;
		$natives = array();
		$values = (array) $values;
		foreach ($this->_items as $field => $type) {
			try {
				if (isset($this->_subItems[$field]) && null != ($subType = @$this->_subItems[$field])) {
	                $rowType = new DB_Pgsql_Type_Row($subType->_plainItems);
					$newValues = $subType->output($values);
					$natives[$field] = $rowType->output($newValues);
	            } else {
	                $natives[$field] = $type->output(isset($values[$field]) ? $values[$field] : null);
	            }
            } catch (DB_Pgsql_Type_Exception_Common $e) {
            	if (!$dataEx) {
            		$dataEx = new Mapper_DataException(null, $this->_proc ? $this->_proc->getName() : null);
            	}
                $dataEx->addError($field, "format", $e);
            }
		}
		if ($dataEx) {
			throw $dataEx;
		}
		return $natives;
	}

	public function input($natives)
	{
	    $natives = (array) $natives;
		$values = array();
        foreach ($this->_items as $field => $type) {
            if (null != ($subType = @$this->_subItems[$field])) {
            	$rowType = new DB_Pgsql_Type_Row($subType->_plainItems);
                $newNatives = $rowType->input(@$natives[$field]);
                $values = array_merge($values, $subType->input($newNatives));
            } else {
                $values[$field] = $type->input(@$natives[$field]);
            }
        }
		return $values;
	}

	/**
	 * May be used in assert() calls to validate presence of a
	 * field in input or output parameters.
	 *
	 * @param array $fields  Plain/nested list of field and types (see __construct).
	 * @param string $field  Field name to search for.
	 * @return object        Field's type or null if no field is found.
	 */
    public static function getFieldType($fields, $field)
    {
        if (isset($fields[$field])) {
        	return $fields[$field];
        }
        foreach (array_filter($fields, 'is_array') as $v) {
            $type = self::getFieldType($v, $field);
            if ($type) return $type;
        }
        return null;
    }
}

