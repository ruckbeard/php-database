<?php
class Database {
    public $host =     DB_HOST;
    public $username = DB_USER;
    public $password = DB_PASS;
    public $db_name =  DB_NAME;
    
    public $link;
    public $error;
    
    private $last_query = "";
    private $query_string = array(
        "SELECT" => "SELECT *", 
        "FROM" => "", 
        "JOIN" => "", 
        "WHERE" => "",
        "GROUP BY" => "",
        "HAVING" => "",
        "ORDER BY" => "",
        "LIMIT" => ""
    );
    
    private $set = array();
    private $insert = "";
    
    public function __construct() {
        //call connect function
        $this->connect();
    }
    
    /**
     * @brief Connect to the database.
     * @return If the connection fails, output the error and return false.
     */
    private function connect() {
        $this->link = new mysqli($this->host, $this->username, $this->password, $this->db_name);
        
        if (!$this->link) {
            $this->error = "Connection Failed: " . $this->link->connect_error;
            return false;
        }
    }
    
    /**
     * Escapes information entered by users and makes it safe for the database
     * @param  string $value The information to be escaped
     * @return string The escaped string
     */
    public function escape($value) {
        return $this->link->real_escape_string($value);
    }
    
    /**
     * @brief Query the database with a with a user constructed query
     * @param string $query A query string. Example "SELECT * FROM table"
     * @return If the query results have more than 0 rows, return a Query object that
     *         contains the results of the query, else return false                                                      
     */
    public function query($query) {
        $result = $this->link->query($query) or die($this->link->error.__LINE__);
        $this->last_query = $query;
        
        if ($result) {
            if (is_object($result) && $result->num_rows > 0)
                return $query = new Query($result);
            else
                return $result;
        } else {
            return false;
        }
    }
    
    /**
     * Permits you to write the SELECT portion of your query
     * @param string $select Can be the full SELECT portion, or just the fields
     * @return object Return this object                                                                  
     */
    public function select($select = "*") {
        $this->query_string["SELECT"] = $select;
        
        return $this;
    }
    
    /**
     * Permits you to write the SELECT (MAX, MIN, AVG, SUM) portion of your query
     * Accessed by calling the respective public function.
     * @param string $x      Modifies what kind of SELECT is being performed (MAX, MIN, AVG, SUM)
     * @param string $select Can be the full SELECT (MAX, MIN, AVG, SUM) portion, or just the field
     * @param string [$var   = ""] Optional variable renames resulting field, otherwise is set to field name
     * @return object Return this object                                                                                               
     */
    private function _max_min_avg_sum($x, $select, $var) {
        if (strstr($select, "SELECT") == false) {
            if ($var != "")
                $this->query_string["SELECT"] = "$x(".$select.") as " . $var;
            else
                $this->query_string["SELECT"] = "$x(".$select.")";
        } else {
            $this->query_string["SELECT"] = $select;   
        }
        
        return $this;
    }
    
    
    public function select_max($select, $var = "") {
        return $this->_max_min_avg_sum("MAX", $select, $var);
    }
    
    public function select_min($select, $var = "") {
        return $this->_max_min_avg_sum("MIN", $select, $var);
    }
    
    public function select_avg($select, $var = "") {
        return $this->_max_min_avg_sum("AVG", $select, $var);
    }
    
    public function select_sum($select, $var = "") {
        return $this->_max_min_avg_sum("SUM", $select, $var);
    }
    
    /**
     * Permits you to write the FROM portion of your query, can be specified in the get() function
     * @param string $from Can be the full FROM portion, or just the table
     * @return object Return this object
     */
    public function from($from) {
        $this->query_string["FROM"] = $from;
        
        return $this;
    }
    
    /**
     * Permits you to write the JOIN portion of your query, can be called multiple times
     * @param  string $join        The table to be joined
     * @param  string $join_colums The columns with matching foreign keys
     * @param  string [$param      = ""] Modifies the type of join statement such as inner, outer, left, right
     * @return object Return database object
     */
    public function join($join, $join_columns = "", $param = "") {
        $prefix = $param == "" ? "JOIN " : strtoupper($param) . " JOIN ";
        
        if ($join_columns != "")
            $join = $prefix.$join." ON ".$join_columns;
        
        if ($this->query_string["JOIN"] != "") 
            $join = " ".$join; 
        
        $this->query_string["JOIN"] .= $join;
            
        return $this;
    }
    
    /**
     * Combines the logic of the where, or_where, having, and or_having functions into one function.
     * @param  string $where_or_having Determines whether writing a WHERE or HAVING query string portion.
     * @param  string $and_or          Determines whether to chain by AND or OR
     * @param  string $field           The field to search for the value
     * @param  string $field_value     The value to search for in the field
     * @param  string $operator        The operator to determine the logic of the search
     * @param  bool   $backtick        Turn backticks on field name on or off
     * @return object returns this database object
     */
    private function _where_or_having($where_or_having, $and_or, $field, $field_value, $operator, $backtick) {
        if (!is_array($field))
            $field = array($field => $field_value);
        
        $array = array();
        
        foreach ($field as $key => $value) {
            $prefix = $this->query_string[$where_or_having] == "" ? "" : " $and_or ";
            
            if ($backtick && $value != "")
                $key = "`$key`";
            
            if (!preg_match("/[!=<>]/", $key))
                $key = "$key $operator";
            
            if ($value != '')
                $value = " '".$this->escape($value)."'";
            
            $array[] = $prefix.$key.$value;
        }
        
        foreach ($array as $row) {
            $this->query_string[$where_or_having] .= $row;   
        }
        
        return $this;
    }
    
    /**
     * Permits you to write the WHERE portion of your query. Can be called multiple times to combine
     * WHERE statements with AND.
     * @param array||string $where Can be an associative array, or a string.
     * @return object Returns this object
     */
    public function where($where, $where_value = "", $operator = "=",$backtick = true) {
        return $this->_where_or_having("WHERE","AND",$where,$where_value,$operator,$backtick);
    }
    
    /**
     * Permits you to write the WHERE portion of your query. Can be called multiple times to combine
     * WHERE statements with OR.
     * @param array||string $where Can be an associative array, or a string.
     * @return object Returns this object;                                                              
     */
    public function or_where($where, $where_value = "", $operator = "=", $backtick = true) {
        return $this->_where_or_having("WHERE", "OR", $where, $where_value, $operator, $backtick);
    }
    
    /**
     * Permits you to write the GROUP BY portion of the query
     * @param  string $group_by The column to group by
     * @return object Returns this database object
     */
    public function group_by($group_by) {
        if (is_array($group_by))
            $this->query_string["GROUP BY"] = implode(", ", $group_by);
        else if (is_string($group_by)) {
            if ($this->query_string["GROUP BY"] != "") 
                $group_by = ", " . $group_by;
        
            $this->query_string["GROUP BY"] .= $group_by;
        }
        
        return $this;
    }
    
    /**
     * Permits you to add the DISTINCT modifier to your query
     * @return object Returns this database object
     */
    public function distinct() {
        $this->query_string["SELECT"] = "SELECT DISTINCT " . strstr($this->query_string["SELECT"], "SELECT ");
        
        return $this;
    }
    
    /**
     * Permits you to write the HAVING portion of your query. Can be called multiple times to combine
     * HAVING statements with AND.
     * @param array||string $having Can be an associative array, or a string.
     * @return object Returns this object;                                                              
     */
    public function having($having, $having_value = "", $operator = "=", $backtick = true) {
        return $this->_where_or_having("HAVING","AND",$having,$having_value,$operator,$backtick);
    }
    
    /**
     * Permits you to write the HAVING portion of your query. Can be called multiple times to combine
     * HAVING statements with OR.
     * @param array||string $having Can be an associative array containing 
     * @return object Returns this object;                                                              
     */
    public function or_having($having, $having_value = "", $operator = "=", $backtick = true) {
        return $this->_where_or_having("HAVING","OR",$having,$having_value,$operator,$backtick);
    }
    
    /**
     * Permits the user to write the ORDER BY portion of the query
     * @param  string $order_by The column to order by
     * @param  string $order    The modifier to order by
     * @return object Returns this database object
     */
    public function order_by($order_by, $order = "") {
        if ($order != "")
            $order_by .= " " . strtoupper($order);
       
        if ($this->query_string["ORDER BY"] != "")
            $order_by = ", " . $order_by;
       
        $this->query_string["ORDER BY"] .= $order_by;
        
        return $this;
    }
    
    /**
     * Permits the user to write the LIMIT portion of the query
     * @param  string $limit         The amount to limit the query by
     * @param  string [$offset = -1] The offset to start the limit at
     * @return object Returns this database object
     */
    public function limit($limit, $offset = -1) {
        if (strstr($limit, "LIMIT") == false)
            $this->query_string["LIMIT"] = $offset != -1 ? $offset . ", " . $limit : $limit;
        else
            $this->query_string["LIMIT"] = $limit;

        return $this;
    }
    
    /**
     * Runs a query that has been modified by the query creation functions, or by the parameters
     * of this function
     * @param  string [$table_name = ""] The table name to run on the query on, if left empty, it will be taken from the from function
     * @return object Returns a query object created by the query function
     */
    public function get($table_name = "") {
        if ($table_name != "")
            $this->from($table_name);
        
        $query = "";
        foreach ($this->query_string as $key => $value) {
            if ($value != "") {
                if (strstr($value, $key) == false)
                    $query .= $key . " " . $value . " ";
                else
                    $query .= $value . " ";
            }
        }
        
        $this->_reset_query_string();
        
        return $this->query(trim($query));
    }
    
    private function _reset_query_string() {
        $this->query_string = array(
            "SELECT" => "SELECT *",
            "FROM" => "",
            "JOIN" => "",
            "WHERE" => "",
            "GROUP BY" => "",
            "HAVING" => "",
            "ORDER BY" => "",
            "LIMIT" => ""
        );
    }
    
    /**
     * Permits the user to create field and value portions of INSERT and UPDATE queries
     * @param  array||object||string $data          Data can be an array, object, or string
     * @param  string [$value = ""]  If data does not contain both field and value, value can be set here
     * @param  boolean [$escape=true] If true, runs escape on all values
     * @return object Returns this database object
     */
    public function set($data, $value = "", $escape=true) {
        if (!is_array($data) && !is_object($data))
            $data = array($data => $value);
        
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                if ($escape == true)
                    $value = $this->escape($value);
                
                $this->set[$key] = $value;
            }
        }
        
        return $this;
    }
    
    /**
     * Inserts a query into the database
     * @param string $table The table to insert data into
     * @param array||object Data to insert into the table                                              
     */
    public function insert($table = "", $data = "") {
        if ($table != "")
            $this->from($table);
        
        if ($data != "")
            $this->set($data);        
        
        $query = "INSERT INTO ". $this->query_string['FROM'] ." (".implode(",",array_keys($this->set)).") VALUES ('".implode("','",$this->set)."')";
        $insert_row = $this->query(trim($query));
        
        $this->set = array();
        $this->_reset_query_string();
        
        if ($insert_row) {
            header("Location: index.php?msg=" . urlencode('Record Added'));
            exit();
        } else {
            die('Error: (' . $this->link->errorno . ') ' . $this->link->error);
        }
    }
    
    /**
     * Runs and UPDATE query that can be modified by the set function and from function
     * @param string $table        The table to run the update query on
     * @param array||object [$data = ""]  The data to update in the table
     * @param string [$where = ""] Where to update the data
     */
    public function update($table = "", $data = "", $where = "") {
        if ($table != "")
            $this->from($table);
        
        if ($data != "")
            $this->set($data);
        
        if ($where != "")
            $this->where($where);
        
        $update = "SET ";
        foreach ($this->set as $key => $value) {
            $update .=  "$key = '$value',";
        }
        $update = substr($update, 0, strrpos($update, ","));
        
        $where = $this->query_string['WHERE'] == "" ? "" : " WHERE " . $this->query_string['WHERE'];
        
        $query = trim("UPDATE " . $this->query_string['FROM'] . " $update" . $where);
        $update_row = $this->query($query);
        
        $this->set = array();
        $this->_reset_query_string();
        
        if ($update_row) {
            header("Location: index.php?msg=" . urlencode('Record Updated'));
            exit();
        } else {
            die('Error: (' . $this->link->errorno . ') ' . $this->link->error);
        }
    }
    
    /**
     * Runs a DELETE query that can be modified by the from function and the where function
     * @param string [$table = ""] The table to delete information from
     * @param string [$where = ""] Where the row is to delete
     */
    public function delete($table = "", $where = "") {
        if (is_array($table)) {
            foreach ($table as $single) {
                $this->delete($single, $where);
            }
            
            $this->_reset_query_string();
            
            return;
        }
        
        if ($table != "")
            $this->from($table);
        
        if ($where != "")
            $this->where($where);
        
        $where = $this->query_string['WHERE'] == "" ? "" : " WHERE " . $this->query_string['WHERE'];
        
        $query = "DELETE FROM " . $this->query_string['FROM'] . $where;
        $delete_row = $this->query(trim($query));
        
        $this->_reset_query_string();
        
        if ($delete_row) {
            header("Location: index.php?msg=" . urlencode('Record Deleted'));
        } else {
            die('Error: (' . $this->link->errorno . ') ' . $this->link->error);
        }
    }
    
    /**
     * The last query to be run using this db object
     * @return string The last query to be run
     */
    public function last_query() {
        return $this->last_query;
    }
}

class Query {
    public $query;
    public $object = array();
    private $position = 0;
    
    public function __construct($query) {
        $this->query = $query;
        $this->position = 0;
    }
    
    /**
     * Gets the results of the query that was run
     * @param  [[Type]] [$obj = false] [[Description]]
     * @return [[Type]] [[Description]]
     */
    public function result($obj = false) {
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
    
    public function row($pos = 0, $obj = false) {
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
    
    private function has_next($array) {
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
    
    private function has_prev($array) {
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
    
    public function next_row() {
        if ($this->has_next($this->object))
            next($this->object);
        return current($this->object);
    }
    
    public function prev_row() {
        if ($this->has_prev($this->object))
            prev($this->object);
        return current($this->object);
    }
    
    public function first_row() {
        reset($this->object);
        return current($this->object);
    }
    
    public function last_row() {
        end($this->object);
        return current($this->object);
    }
}