<?php

namespace Ruckbeard\Database;

use Ruckbeard\Database\Result;

abstract class Execute extends QueryConstructor 
{
    /**
     * @brief Query the database with a with a user constructed query
     * @param string $query A query string. Example "SELECT * FROM table"
     * @return If the query results have more than 0 rows, return a Query object that
     *         contains the results of the query, else return false                                                      
     */
    public function query($query) 
    {
        $result = $this->link->query($query) or die($this->link->error.__LINE__);
        $this->last_query = $query;
        
        if ($result) {
            if (is_object($result) && $result->num_rows > 0) {
                return $query = new Result($result);
            } else {
                return $result;
            }
        } else {
            return false;
        }
    }
    
    
    
    /**
     * Runs a query that has been modified by the query creation functions, or by the parameters
     * of this function
     * @param  string [$table_name = ""] The table name to run on the query on, if left empty, it will be taken from the from function
     * @return object Returns a query object created by the query function
     */
    public function get($table_name = "") 
    {
        if ($table_name != "") {
            $this->from($table_name);
        }
        
        $query = "";
        foreach ($this->getQueryString() as $key => $value) {
            if ($value != "") {
                if (strstr($value, $key) == false) {
                    $query .= $key . " " . $value . " ";
                } else {
                    $query .= $value . " ";
                }
            }
        }
        
        $this->resetQueryString();
        
        return $this->query(trim($query));
    }
    
    /**
     * Inserts a query into the database
     * @param string $table The table to insert data into
     * @param array||object Data to insert into the table                                              
     */
    public function insert($table = "", $data = "") 
    {
        if ($table != "") {
            $this->from($table);
        }
        
        if ($data != "") {
            $this->set($data);
        }
        
        $query = "INSERT INTO ". $this->query_string['FROM'] .
                 " (" . implode("," , array_keys($this->set)) .
                 ") VALUES ('" . implode("','" , $this->set) . "')";
        $insert_row = $this->query(trim($query));
        
        $this->set = array();
        $this->resetQueryString();
        
        return $insert_row;
    }
    
    /**
     * Runs and UPDATE query that can be modified by the set function and from function
     * @param string $table        The table to run the update query on
     * @param array||object [$data = ""]  The data to update in the table
     * @param string [$where = ""] Where to update the data
     */
    public function update($table = "", $data = "", $where = "") 
    {
        if ($table != "") {
            $this->from($table);
        }
        
        if ($data != "") {
            $this->set($data);
        }
        
        if ($where != "") {
            $this->where($where);
        }
        
        $update = "SET ";
        foreach ($this->set as $key => $value) {
            $update .=  "$key = '$value',";
        }
        $update = substr($update, 0, strrpos($update, ","));
        
        $where = $this->query_string['WHERE'] == "" ? "" : " WHERE " . $this->query_string['WHERE'];
        
        $query = trim("UPDATE " . $this->query_string['FROM'] . " $update" . $where);
        $update_row = $this->query($query);
        
        $this->set = array();
        $this->resetQueryString();

        return $update_row;
    }
    
    /**
     * Runs a DELETE query that can be modified by the from function and the where function
     * @param string [$table = ""] The table to delete information from
     * @param string [$where = ""] Where the row is to delete
     */
    public function delete($table = "", $where = "") 
    {
        if (is_array($table)) {
            foreach ($table as $single) {
                $this->delete($single, $where);
            }
            
            $this->resetQueryString();
            
            return;
        }
        
        if ($table != "") {
            $this->from($table);
        }
        
        if ($where != "") {
            $this->where($where);
        }
        
        $where = $this->query_string['WHERE'] == "" ? "" : " WHERE " . $this->query_string['WHERE'];
        
        $query = "DELETE FROM " . $this->query_string['FROM'] . $where;
        $delete_row = $this->query(trim($query));
        
        $this->resetQueryString();
        
        return $delete_row;
    }
}