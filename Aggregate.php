<?php
class Mapper_Aggregate extends ArrayObject
{
	private $_idColumnName;
	private $_context;

	/**
	 * Create new aggregate.
	 * Also set a context in which contained objects are generated.
	 * 
	 * @param array $list
	 * @param Mapper_Context $context
	 * @return void
	 */
	public function __construct(array $list, Mapper_Context $context = null)
	{
		parent::__construct($list);
		$this->_context = $context;
		// Call fillAutomatic method if present.
    	if (method_exists($this->_context, $method = "fillAutomatic")) {
	    	call_user_func(array($this->_context, $method), $this);
    	}
	}

	/**
	 * Set the name of ID column of this aggregate.
	 * This is usually passed from a stored procedure.
	 *
	 * @param string $name
	 * @return voids
	 */
	public function setIdColumnName($name)
	{
		$this->_idColumnName = $name;
	}
	
    /**
     * Mass load of dependent objects to which each
     * $target[$i]->$targetColumn refers to. By other words,
     * replaces IDs of objects to loaded parent objects.
     *
     * Suppose we have a structure:
     *
     * topic(id, creator_id, mtime)
     * person(id, lastname)
     *
     * The sequence
     *
     * $db = DB::getAutoContext();
     * $topics = $db->topic_search(...);
     * $persons = $topics->loadParent($db->person_list_by_id, 'creator_id');
     * $cities = $persons->loadParent($db->city_list_by_id, 'city_id');
     *
     * - loads topics matched by a query
     * - for each of them - loads its creator using person_list_by_id
     *   stored procedure and saves it to "creator" property
     *   ("_id" suffix is always removed).
     * - for each of loaded creators - also loads cities ("city" property).
     *
     * @param ListByCol $func    Function to be used to fill.
     * @param string $colName    The column name from which object IDs will be given.
     * @param array $args        Additional procedure arguments (if present).
     * @return Mapper_Aggregate  The list of loaded objects (may be ignored).
     */
    public function fillParent(Mapper_Procedure_ListByCol $func, $colName, $args = array(), $targetProp = null)
    {
     	if ($targetProp == null) {
            // Remove '_id' suffix if present. If not, load over existing item.
            $targetProp = preg_replace('/_id$/s', '', $colName);
        }

    	$objsByValues = array();

    	foreach ($this as $targetObj) {
        	$targetObj->$targetProp = null;

        	if ($targetObj->$colName === null) {
        		continue;
        	}

            $objsByValues[$targetObj->$colName][] = $targetObj;
        }

        $rows = $func->invoke(array_keys($objsByValues), $args);

        $idCol = $func->getIdColumnName();

        foreach ($rows as $row) {
            foreach ($objsByValues[$row->$idCol] as $targetObj) {
                $targetObj->$targetProp = $row;
            }
        }

        return $rows;
    }

    /**
     * Mass load of the reverse dependensies.
     *
     * The following sample:
     *
     * TABLE topic (id, creator_id, mtime)
     * TABLE person (id, lastname)
     * TABLE education(id, person_id, school_id)
     *
     * $db = DB::getAutoContext();
     * $topics = $db->topic_search(...);
     * $creators = $topics->fillParent($db->person_list_by_id, 'creator_id');
     * $educations = $creators->fillChildren(
     *     $db->education_list_by_person_id, 'person_id', 'educations'
     * );
     *
     * Loads all topics matched by a search query; for each of them -
     * loads its creator; for each creator - loads all his educations.
     *
     * @param array $target
     * @param string $targetColumn
     * @param string $sourceColumn
     * @param array $args
     * @return Mapper_Aggregate
     */
    public function fillChildren(Mapper_Procedure_ListByCol $func, $colName, $toColName, $args = array())
    {
        $idColumn = $this->_idColumnName;
        $objsByIds = array();
        foreach ($this as $targetObj) {
            $objsByIds[$targetObj->$idColumn][] = $targetObj;
            $targetObj->$toColName = new Mapper_Aggregate(array(), $func->getContext());
        }
        $rows = $func->invoke(array_keys($objsByIds), $args);
        foreach ($rows as $row) {
            foreach ($objsByIds[$row->$colName] as $targetObj) {
                $targetObj->$toColName->append($row);
            }
        }
        return $rows;
    }
    
    /**
     * Same of fillParent(), but properties of loaded objects are
     * added as properties of source objects.
     * 
     * @param ListByCol $func    Function to be used to fill.
     * @param string $colName    The column name from which object IDs will be given.
     * @param array $args        Additional procedure arguments (if present).
     */
    public function fillPropsFrom(Mapper_Procedure_ListByCol $func, $colName, $args = array())
    {
    	$tmpProp = "__tmp_prop";
    	$result = $this->fillParent($func, $colName, $args, $tmpProp);
    	foreach ($this as $row) {
    		if ($row->$tmpProp !== null) { 
	    		foreach ($row->$tmpProp as $k => $v) {
	    			if (!property_exists($row, $k)) {
	    				$row->$k = $v;
	    			}
	    		}
    		}
    		unset($row->$tmpProp);
    	}
    	return $result;
    }
    
    /**
     * Intercept fillXxx() calls and redirect them to parent context.
     * 
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
    	if (strpos($name, 'fill') !== 0) {
    		throw new Mapper_Exception("Call to undefined method Aggregate::$name()");
    	}
    	if (!method_exists($this->_context, $name)) {
    		throw new Mapper_Exception("Cannot find method $name() within the parent context.");
    	}
    	array_unshift($args, $this);
    	return call_user_func_array(array($this->_context, $name), $args);
    }

    /**
     * Convert self to array.
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->_toArray($this);
    }

    /**
     * Return array.
     *
     * @param array $object
     */
    private function _toArray($object)
    {
        $object = (array) $object;
        foreach ($object as &$val) {
            if (!is_scalar($val) && !is_null($val)) {
                $val = $this->_toArray($val);
            }
        }
        return $object;
    }

    /**
     * Return first element of the array.
     *
     * @return array row
     * @throws Mapper_AggregateEmptyException if count() == 0
     */
    public function first()
    {
        if ($this->count() == 0) {
            throw new Mapper_AggregateEmptyException();
        }
        return $this->getIterator()->current();
    }
}
