<?php
class Mapper_Pager
{
	private $_total;
	private $_offset;
	private $_pageSize;
	private $_limit;
	
	public function __construct($offset, $pageSize, $limit)
	{
		$this->_offset = $offset;
		$this->_pageSize = $pageSize;
		$this->_limit = $limit;
	}
	
	public function setTotal($total)
	{
		$this->_total = $total;
	}
	
    public function getTotal()
    {
        return $this->_total;
    }
	
    public function getOffset()
	{
		return $this->_offset;
	}

    public function getPageSize()
    {
        return $this->_pageSize;
    }
    
    public function getLimit()
    {
    	return $this->_limit;
    }
}
