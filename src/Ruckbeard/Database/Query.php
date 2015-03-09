<?php

namespace Ruckbeard\Database;

class Query 
{
    public $query;
    public $object = array();
    private $position = 0;
    
    public function __construct($query) 
    {
        $this->query = $query;
        $this->position = 0;
    }
    
    /**
     * Gets the results of the query that was run
     * @param  [[Type]] [$obj = false] [[Description]]
     * @return [[Type]] [[Description]]
     */
    public function result($obj = false) 
    {
        if ((bool)$obj) {
            while ($row = $this->query->fetch_object($obj))
            {
                $this->object[] = $row;
            }
            return $this->object;
        } else {
            while ($row = $this->query->fetch_object())
            {
                $this->object[] = $row;
            }
            return $this->object;
        }
    }
    
    public function row($pos = 0, $obj = false) 
    {
        if ((bool)$obj) {
            while ($row = $this->query->fetch_object($obj))
            {
                $this->object[] = $row;
            }
            return $this->object[$pos];
        } else {
            while ($row = $this->query->fetch_object())
            {
                $this->object[] = $row;
            }
            return $this->object[$pos];
        }
    }
    
    private function hasNext($array) 
    {
        if (is_array($array)) {
            if (next($array) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    
    private function hasPrev($array) 
    {
        if (is_array($array)) {
            if (prev($array) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    
    public function nextRow() 
    {
        if ($this->has_next($this->object)) {
            next($this->object);
        }
        return current($this->object);
    }
    
    public function prevRow() 
    {
        if ($this->has_prev($this->object)) {
            prev($this->object);
        }
        return current($this->object);
    }
    
    public function firstRow() 
    {
        reset($this->object);
        return current($this->object);
    }
    
    public function lastRow() 
    {
        end($this->object);
        return current($this->object);
    }
}