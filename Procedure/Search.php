<?php
/**
 * Calling prototype:
 * 
 * $proc->invoke(array $properties, $pager)
 */
abstract class Mapper_Procedure_Search extends Mapper_Procedure_List
{
	/**
	 * Part of the procedure's name which by default will be
	 * replaced by "_skel" or "_data" suffix.
	 */
	const DEFAULT_NAME_RE = '/_search(?=_|$)/s';
	
    /**
     * Returns the LIMIT input column name. This column must always 
     * be present in getInParams() and is filled from the Pager.
     *
     * @return string
     */
    public function getLimitColumnName()
    {
        return 'limit';
    }
	
    /**
     * Executes the procedure returning thelimited number of rows.
     * Also modifies $pager: sets the total count of found rows.
     *
     * @param array $args
     * @param Mapper_Pager $pager
     * @return Mapper_Aggregate
     */
    public function invoke($args, Mapper_Pager $pager)
    {
        $skel = $this->fetchSkel($args, $pager->getLimit());
        $pager->setTotal(count($skel));
        
        if (!count($skel)) {
            return new Mapper_Aggregate(array(), $this->_getContext());
        }

        // Limit the skeleton.
        $limitedSkel = array_slice($skel, $pager->getOffset(), $pager->getPageSize());
        
        // Load the data.
        $procData = new Mapper_Procedure_ListCustom(
            $this->_getContext(),
            $this->_getDataName(),
            array('skel' => new DB_Pgsql_Type_Array(new DB_Pgsql_Type_String())),
            $this->getOutParams(),
            $this->createSlot($limitedSkel)
        );
        return $procData->invoke(array('skel' => $limitedSkel));
    }
    
    public function count($args, $limit)
    {
        $skel = $this->fetchSkel($args, $limit);
        return count($skel);
    }

    private  function fetchSkel($args, $limit)
    {
        // Fetch the skeleton.
        $procSkel = new Mapper_Procedure_ListCustom(
            $this->_getContext(),
            $this->_getSkelName(),
            $this->getInParams(),
            new DB_Pgsql_Type_Array(new DB_Pgsql_Type_String()),
            $this->createSlot(array($args, $limit))
        );
        $args[$this->getLimitColumnName()] = $limit;
        $skel = $procSkel->invoke($args);
        return $skel;
    }
    
    //
    // Internal methods & overrides.
    //
	
    protected function _selfCheck()
    {
        parent::_selfCheck();
        $in = $this->getInParams();
        assert('preg_match(self::DEFAULT_NAME_RE, parent::getName())');
        assert('is_array($in)');
        assert('isset($in[$this->getLimitColumnName()])');
        assert('$in[$this->getLimitColumnName()] instanceof DB_Pgsql_Type_Int');
    }   
        
    public function getName()
    {
    	throw new Mapper_Exception("Mapper_Procedure_Search::getName() is meaningless");
    }
    
    protected function _getSkelName()
    {
    	return preg_replace(self::DEFAULT_NAME_RE, '_skel', parent::getName());
    }
    
    protected function _getDataName()
    {
        return preg_replace(self::DEFAULT_NAME_RE, '_data', parent::getName());
    }
}
