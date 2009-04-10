<?php
/**
 * Calling prototype:
 * 
 * none, abstract
 */
abstract class Mapper_Procedure_List extends Mapper_Procedure
{
    /**
     * Cache tags associated to this searcher.
     *
     * @var array
     */
    private $_cacheTags = array();
    
    protected function _selfCheck()
    {
        parent::_selfCheck();
        $out = $this->getOutParams();
        assert('$out instanceof DB_Pgsql_Type_Array || is_array($out)');
        assert('$out instanceof DB_Pgsql_Type_Array || isset($out[$this->getIdColumnName()])');
    }
    
    /**
     * Returns the ID column name. Each resulting row must have
     * this column set.
     *
     * @return string
     */
    public function getIdColumnName()
    {
        return 'id';
    }
    
    /**
     * Invoke the procedure.
     *
     * @return mixed    Aggregate or array of elements (if the 
     *                  function returns a single array).
     */
    public function invoke()
    {
    	$args = func_get_args();
    	$slot = $this->createSlot($args);
    	if ($slot !== null) {
    		foreach ($this->_cacheTags as $tag) {
    			$slot->addTag($tag);
    		}
    		$cached = $slot->load(); // returns false on fail
    	} else {
            $cached = false;
    	}
    	// Variable $cached always contain array('a'|'s', $data)
    	// where 'a' means that $data is Aggregate, and 's' means
    	// that it contains anything else (e.g. scalar).
    	if (!is_array($cached)) {
            $result = call_user_func_array(array('parent', 'invoke'), $args);
            if ($result instanceof Mapper_Aggregate) {
                $result->setIdColumnName($this->getIdColumnName());
            }
	    	if ($slot) {
	    		if ($result instanceof Mapper_Aggregate) {
	    			$cached = array('a', $result->getArrayCopy());
	    		} else {
	    			$cached = array('s', $result);
	    		}
	    		$slot->save($cached);
	    	}
    	} else {
    		if ($cached[0] === 'a') {
    			// Fully restore the Aggregate.
    			$result = new Mapper_Aggregate(array(), $this->getContext());
    			foreach ($cached[1] as $item) {
    			    $result->append($item);
    			}
    			$result->setIdColumnName($this->getIdColumnName());
    		} else {
    			$result = $cached[1];
    		} 
    	}
        return $result;
    }    

    /**
     * Modifier: add tags to the cache slot.
     * 
     * @param Dklab_Cache_Frontend_Tag  $tag1
     * @param Dklab_Cache_Frontend_Tag  $tag2 ...
     * @return self
     */
    public function mCacheTag()
    {
        $args = func_get_args();
        $obj = clone $this;
        $obj->_cacheTags = array_merge($obj->_cacheTags, $args);
        return $obj;
    }
    
    /**
     * Return the new cache slot used for this procedure result
     * or null if the caching is disabled.
     *
     * @param array $args   Dependencies for this slot (unspecified).
     * @return Dklab_Cache_Frontend_Slot
     */
    public function createSlot($args)
    {
    	$args; // for Zend Studio: remove warning
    	return null;
    }
}
