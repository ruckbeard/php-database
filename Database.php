<?php
class Database {
    public $host =     DB_HOST;
    public $username = DB_USER;
    public $password = DB_PASS;
    public $db_name =  DB_NAME;
    
    public $link;
    public $error;
    
    private $last_query = "";
    private $query = "";
    private $select = "SELECT *";
    private $from = "";
    private $join = "";
    private $where = "";
    private $group_by = "";
    private $distinct = "";
    private $having = "";
    private $order_by = "";
    private $limit = "";
    
    private $set_insert = "";
    private $set_update = "";
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
        if ($result->num_rows > 0) {
            return $query = new Query($result);
        } else {
            return false;
        }
    }
    
    /**
     * Permits you to write the SELECT portion of your query
     * @param string $select Can be the full SELECT portion, or just the fields
     * @return object Return this object                                                                  
     */
    public function select($select) {
        if (strstr($select, "SELECT") == false) {
            $this->select = "SELECT " . $select;
        } else {
            $this->select = $select;
        }
        return $this;
    }
    
    /**
     * Permits you to write the SELECT MAX portion of your query
     * @param string $select     Can be the full SELECT MAX portion, or just the field
     * @param string [$var = ""] Optional variable renames resulting field, otherwise is set to field name
     * @return object Return this object                                                                                               
     */
    public function select_max($select, $var = "") {
        if (strstr($select, "SELECT") == false) {
            $this->select = "SELECT MAX(" . $select . ") as " . $var == "" ? $select : $var;
        } else {
            $this->select = $select;
        }
        return $this;
    }
    
    /**
     * Permits you to write the SELECT MIN portion of your query
     * @param string $select     Can be the full SELECT MIN portion, or just the field
     * @param string [$var = ""] Optional variable renames resulting field, otherwise is set to field name
     * @return object Return this object
     */
    public function select_min($select, $var = "") {
        if (strstr($select, "SELECT") == false) {
            $this->select = "SELECT MIN(" . $select . ") as " . $var == "" ? $select : $var;
        } else {
            $this->select = $select;
        }
        return $this;
    }
    
    /**
     * Permits you to write the SELECT AVG portion of your query
     * @param string $select     Can be the full SELECT AVG portion, or just the field
     * @param string [$var = ""] Optional variable renames resulting field, otherwise is set to field name
     * @return object Returns this object                                                                                               
     */
    public function select_avg($select, $var = "") {
        if (strstr($select, "SELECT") == false) {
            $this->select = "SELECT AVG(" . $select . ") as " . $var == "" ? $select : $var;
        } else {
            $this->select = $select;
        }
        return $this;
    }
    
    /**
     * Permits you to write the SELECT SUM portion of your query
     * @param string $select     Can be the full SELECT SUM portion, or just the field
     * @param string [$var = ""] Optional variable renames resulting field, otherwise is set to field name
     */
    public function select_sum($select, $var = "") {
        if (strstr($select, "SELECT") == false) {
            $this->select = "SELECT SUM(" . $select . ") as " . $var == "" ? $select : $var;
        } else {
            $this->select = $select;
        }
        return $this;
    }
    
    /**
     * Permits you to write the FROM portion of your query, can be specified in the get() function
     * @param string $from Can be the full FROM portion, or just the table
     * @return object Return this object
     */
    public function from($from) {
        $this->from = $from;
        return $this;
    }
    
    /**
     * Permits you to write the JOIN portion of your query, can be called multiple times
     * @param  string $join        The table to be joined
     * @param  string $join_colums The columns with matching foreign keys
     * @param  string [$param      = ""] Modifies the type of join statement such as inner, outer, left, right
     * @return object Return database object
     */
    public function join($join, $join_colums, $param = "") {
        if (strstr($param == "" ? $param . $this->join : strtoupper($param . " " . $this->join), $param == "" ? $param . "JOIN" : strtoupper($param . " "  . "JOIN")) == false) {
            if (strstr($param == "" ? $param . $join : strtoupper($param . " " . $join), $param == "" ? $param . "JOIN" : strtoupper($param . " "  . "JOIN")) == false) {
                $this->join = $param == "" ? $param . "JOIN " . $join . " ON " . $join_colums : strtoupper($param . " ") . "JOIN " . $join . " ON " . $join_colums;
            } else {
                $this->join = $join;
            }
        } else {
            if (strstr($param == "" ? $param . $join : strtoupper($param . " " . $join), $param == "" ? $param . "JOIN" : strtoupper($param . " "  . "JOIN")) == false) {
                $this->join .= " " . $param == "" ? $param . "JOIN " . $join . " ON " . $join_colums : strtoupper($param . " ") . "JOIN " . $join . " ON " . $join_colums;
            } else {
                $this->join .= " " . $join;
            }
        }
        return $this;
    }
    
    /**
     * Permits you to write the WHERE portion of your query. Can be called multiple times to combine
     * WHERE statements with AND.
     * @param array||string $where Can be an associative array, or a string.
     * @return object Returns this object
     */
    public function where($where, $where_value = "", $operator = "=") {
        if (is_array($where)) {
            $keys_and_values = array();
            foreach ($where as $key => $value) {
                if (preg_match("/[!=<>]/", $key))
                    $keys_and_values[] = $key . " '" . $this->escape($value) ."'";
                else
                    $keys_and_values[] = $key . " $operator '" . $this->escape($value) ."'";
            }
            if (strstr($this->where, "WHERE") == false)
                $this->where = "WHERE " . implode(" AND ", $keys_and_values);
            else
                $this->where .= implode(" AND ", $keys_and_values);
        } else if (is_string($where)) {
            if (strstr($where, "WHERE") == false) {
                if (strstr($this->where, "WHERE") == false) {
                    if ($where_value != "") {
                        if (preg_match("/[!=<>]/", $where))
                            $this->where = "WHERE $where '" . $this->escape($where_value) . "'";
                        else
                            $this->where = "WHERE $where $operator '" . $this->escape($where_value) . "'";
                    } else {
                        $this->where = "WHERE $where";
                    }
                } else {
                    if ($where_value != "") {
                        if (preg_match("/[!=<>]/", $where))
                            $this->where = " AND $where '" . $this->escape($where_value) . "'";
                        else
                            $this->where = " AND $where $operator '" . $this->escape($where_value) . "'";
                    } else {
                        $this->where = " AND $where";
                    }
                }
            } else {
                $this->where = $where;
            }
        }
        return $this;
    }
    
    /**
     * Permits you to write the WHERE portion of your query. Can be called multiple times to combine
     * WHERE statements with OR.
     * @param array||string $where Can be an associative array, or a string.
     * @return object Returns this object;                                                              
     */
    public function or_where($where, $where_value = "", $operator = "=") {
        if (is_array($where)) {
            $keys_and_values = array();
            foreach ($where as $key => $value) {
                if (preg_match("/[!=<>]/", $key))
                    $keys_and_values[] = $key . " '" . $this->escape($value) ."'";
                else
                    $keys_and_values[] = $key . " $operator '" . $this->escape($value) ."'";
            }
            if (strstr($this->where, "WHERE") == false)
                $this->where = "WHERE " . implode(" OR ", $keys_and_values);
            else
                $this->where .= implode(" OR ", $keys_and_values);
        } else if (is_string($where)) {
            if (strstr($where, "WHERE") == false) {
                if (strstr($this->where, "WHERE") == false) {
                    if ($where_value != "") {
                        if (preg_match("/[!=<>]/", $where))
                            $this->where = "WHERE $where '" . $this->escape($where_value) . "'";
                        else
                            $this->where = "WHERE $where $operator '" . $this->escape($where_value) . "'";
                    } else {
                        $this->where = "WHERE $where";
                    }
                } else {
                    if ($where_value != "") {
                        if (preg_match("/[!=<>]/", $where))
                            $this->where = " OR $where '" . $this->escape($where_value) . "'";
                        else
                            $this->where = " OR $where $operator '" . $this->escape($where_value) . "'";
                    } else {
                        $this->where = " OR $where";
                    }
                }
            } else {
                $this->where = $where;
            }
        }
        return $this;
    }
    
    /**
     * Permits you to write the GROUP BY portion of the query
     * @param  string $group_by The column to group by
     * @return object Returns this database object
     */
    public function group_by($group_by) {
        if (is_array($group_by)) {
            $this->group_by = "GROUP BY " . implode(", ", $group_by);
        } else if (is_string($group_by)) {
            if (strstr($group_by, "GROUP BY") == false) {
                $this->group_by = "GROUP BY " . $group_by;
            } else {
                $this->group_by = $group_by;
            }
        }
        return $this;
    }
    
    /**
     * Permits you to add the DISTINCT modifier to your query
     * @return object Returns this database object
     */
    public function distinct() {
        if ($this->select == "SELECT *")
            $this->select = "SELECT DISTINCT *";
        else {
            $select_fields = strstr($this->select, "SELECT ");
            $this->select = "SELECT DISTINCT $select_fields";
        }
        return $this;
    }
    
    /**
     * Permits you to write the HAVING portion of your query. Can be called multiple times to combine
     * HAVING statements with AND.
     * @param array||string $having Can be an associative array, or a string.
     * @return object Returns this object;                                                              
     */
    public function having($having, $having_value = "", $operator = "=") {
        if (is_array($having)) {
            $keys_and_values = array();
            foreach ($having as $key => $value) {
                if (preg_match("/[!=<>]/", $key))
                    $keys_and_values[] = $key . " '" . $this->escape($value) ."'";
                else
                    $keys_and_values[] = $key . " $operator '" . $this->escape($value) ."'";
            }
            if (strstr($this->having, "HAVING") == false)
                $this->having = "HAVING " . implode(" AND ", $keys_and_values);
            else
                $this->having .= implode(" AND ", $keys_and_values);
        } else if (is_string($having)) {
            if (strstr($having, "HAVING") == false) {
                if (strstr($this->having, "HAVING") == false) {
                    if ($having_value != "") {
                        if (preg_match("/[!=<>]/", $having))
                            $this->having = "HAVING $having '" . $this->escape($having_value) ."'";
                        else
                            $this->having = "HAVING $having $operator '" . $this->escape($having_value) ."'";
                    } else {
                        $this->having = "HAVING $having";
                    }
                } else {
                    if ($having_value != "") {
                        if (preg_match("/[!=<>]/", $having))
                            $this->having = " AND $having '" . $this->escape($having_value) ."'";
                        else
                            $this->having = " AND $having $operator '" . $this->escape($having_value) ."'";
                    } else {
                        $this->having = " AND $having";
                    }
                }
            } else {
                $this->having = $having;
            }
        }
        return $this;
    }
    
    /**
     * Permits you to write the HAVING portion of your query. Can be called multiple times to combine
     * HAVING statements with OR.
     * @param array||string $having Can be an associative array containing 
     * @return object Returns this object;                                                              
     */
    public function having_or($having, $having_value = "", $operaotr = "=") {
        if (is_array($having)) {
            $keys_and_values = array();
            foreach ($having as $key => $value) {
                if (preg_match("/[!=<>]/", $key))
                    $keys_and_values[] = $key . " '" . $this->escape($value) ."'";
                else
                    $keys_and_values[] = $key . " $operator '" . $this->escape($value) ."'";
            }
            if (strstr($this->having, "HAVING") == false)
                $this->having = "HAVING " . implode(" AND ", $keys_and_values);
            else
                $this->having .= implode(" OR ", $keys_and_values);
        } else if (is_string($having)) {
            if (strstr($having, "HAVING") == false) {
                if (strstr($this->having, "HAVING") == false) {
                    if ($having_value != "") {
                        if (preg_match("/[!=<>]/", $having))
                            $this->having = "HAVING $having '" . $this->escape($having_value) ."'";
                        else
                            $this->having = "HAVING $having $operator '" . $this->escape($having_value) ."'";
                    } else {
                        $this->having = "HAVING $having";
                    }
                } else {
                    if ($having_value != "") {
                        if (preg_match("/[!=<>]/", $having))
                            $this->having = " OR $having '" . $this->escape($having_value) ."'";
                        else
                            $this->having = " OR $having $operator '" . $this->escape($having_value) ."'";
                    } else {
                        $this->having = " OR $having";
                    }
                }

            } else {
                $this->having = $having;
            }
        }
        return $this;
    }
    
    /**
     * Permits the user to write the ORDER BY portion of the query
     * @param  string $order_by The column to order by
     * @param  string $order    The modifier to order by
     * @return object Returns this database object
     */
    public function order_by($order_by, $order) {
        if (strstr($this->order_by, "ORDER BY") == false) {
            if (strstr($order_by, "ORDER BY") == false) {
                $this->order_by = "ORDER BY " . $order_by . " " . strtoupper($order);
            } else {
                $this->order_by = $order_by;
            }
        } else {
            if (strstr($order_by, "ORDER BY") == false) {
                $this->order_by .= ", " . $order_by . " " . strtoupper($order);
            } else {
                $this->order_by .= ", " . $order_by;
            }
        }
        return $this;
    }
    
    /**
     * Permits the user to write the LIMIT portion of the query
     * @param  string $limit         The amount to limit the query by
     * @param  string [$offset = -1] The offset to start the limit at
     * @return object Returns this database object
     */
    public function limit($limit, $offset = -1) {
        if (strstr($limit, "LIMIT") == false) {
            $this->limit = "LIMIT " . $offset != -1 ? $offset . ", " . $limit : $limit;
        } else {
            $this->limit = $limit;
        }
        return $this;
    }
    
    /**
     * Runs a query that has been modified by the query creation functions, or by the parameters
     * of this function
     * @param  string [$table_name = ""] The table name to run on the query on, if left empty, it will be taken from the from function
     * @param  string [$limit = -1]      Adds a limit to the query, can be set by the limit function
     * @param  string [$offset = -1]     Adds an offset to the query's limit. can be set by the limit function
     * @return object Returns a query object created by the query function
     */
    public function get($table_name = "", $limit = -1, $offset = -1) {
        if ($table_name != "")
            $this->from($table_name);
        if ($limit != -1)
            $this->limit($limit, $offset);
        $query = "$this->select FROM $this->from $this->join $this->where $this->group_by $this->having $this->order_by $this->limit";
        $this->select = "SELECT *";
        $this->from = "";
        $this->join = "";
        $this->where = "";
        $this->group_by = "";
        $this->having = "";
        $this->order_by = "";
        $this->limit = "";
        return $this->query(trim($query));
    }
    
    /**
     * Queries the database to get all fields from a table or specific row. Can also be 
     * limited and offset.
     * @param  string $table_name = ""  The table name to select from
     * @param  string [$where = ""]  Adds a where statement to the query
     * @param  integer [$offset = -1] Adds an offset to the query
     * @param  integer [$limit = -1]  Adds a limit to the query
     * @return object  if num_rows on the query result returns greater than 0, return
     *                  an object that contains the results of the query, else return false                                                     
     */
    public function get_where($table_name = "", $where = "", $limit = -1, $offset = -1) {
        if ($table_name != "")
            $this->from($table_name);
        if ($where != "")
            $this->where($where);
        if ($limit != -1)
            $this->limit($limit, $offset);
        $query = "$this->select FROM $this->from $this->join $this->where $this->group_by $this->having $this->order_by $this->limit";
        $this->select = "SELECT *";
        $this->from = "";
        $this->join = "";
        $this->where = "";
        $this->group_by = "";
        $this->having = "";
        $this->order_by = "";
        $this->limit = "";
        return $this->query(trim($query));
    }
    
    /**
     * Permits the user to create field and value portions of INSERT and UPDATE queries
     * @param  array||object||string $data          Data can be an array, object, or string
     * @param  string [$value = ""]  If data does not contain both field and value, value can be set here
     * @param  boolean [$escape=true] If true, runs escape on all values
     * @return object Returns this database object
     */
    public function set($data, $value = "", $escape=true) {
        if (!is_object($data) && is_array($data[0])) {
            $keys = array();
            $values = "('";
            foreach ($data as $key => $value) {
                foreach ($value as $key_2 => $value_2) {
                    if (!array_search($key_2, $keys,true))
                        $keys[] = $key_2;
                    $values .= $this->escape($value_2) . "','";
                }
                $values = substr($values, 0, strrpos($values, ","));
                $values .= "),(";
            }
            $keys = array_unique($keys);
            $values = substr($values, 0, strrpos($values, ","));
            $this->set_insert = "(".implode(",",$keys).") VALUES $values";
        } else if (is_array($data) || is_object($data)) {
            $keys = array();
            $values = array();
            $update = "";
            foreach ($data as $key => $value) {
                $keys[] = $key;
                $values[] = $this->escape($value);
                $update .= "$key = '$value',";
            }
            $update = substr($update, 0, strrpos($update, ","));
            $this->set_insert = "(".implode(",",$keys).") VALUES ('".implode("','",$values)."')";
            $this->set_update = "SET $update";
        } else if (is_string($data)) {
            if ($this->set_insert == "") {
                if ($escape == true)
                    $this->set_insert = "($data) VALUES ('".$this->escape($value)."')";
                else
                    $this->set_insert = "($data) VALUES ('$value')";
            } else {
                $before = substr($this->set_insert, 0, strpos($this->set_insert,')'));
                $after = substr($this->set_insert, strpos($this->set_insert,')'), strrpos($this->set_insert,')') + 1);
                $this->set_insert = $before . ",$data" . $after;
                $this->set_insert = substr($this->set_insert, 0, strrpos($this->set_insert, ")"));
                if ($escape == true)
                    $this->set_insert .= ",'" .$this->escape($value) ."')";
                else
                    $this->set_insert .= ",'$value')";
            }
            if ($this->set_update == "") {
                if ($escape == true)
                    $this->set_update = "SET $data = '".$this->escape($value)."'";
                else
                    $this->set_update = "SET $data = '$value'";
            } else {
                if ($escape == true)
                    $this->set_update .= ", $data = '".$this->escape($value)."'";
                else
                    $this->set_update .= ", $data = '$value'";
            }
        }
        return $this;
    }
    
    /**
     * Inserts a query into the database
     * @param string $table The table to insert data into
     * @param array||object Data to insert into the table                                              
     */
    public function insert($table, $data = "") {
        if ($data != "")
            $this->set($data);
        $query = "INSERT INTO $table $this->set_insert";
        $insert_row = $this->link->query($query) or die($this->link->error.__LINE__);
        $this->last_query = $query;
        $this->set_insert = "";
        $this->set_update = "";
        if ($insert_row) {
            header("Location: index.php?msg=" . urlencode('Record Added'));
            exit();
        } else {
            die('Error: (' . $this->link->errorno . ') ' . $this->link->error);
        }
    }
    
    /*public function insert_string($table_name, $data) {
        $keys = array();
        $values = array();
        foreach ($data as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }
        $query = "INSERT INTO $table_name ('{".implode("}','{",$keys)."}') VALUES (".implode(",",$values).")";
        $insert_row = $this->link->query($query) or die($this->link->error.__LINE__);
        $this->last_query = $query;
        if ($insert_row) {
            header("Location: index.php?msg=" . urlencode('Record Added'));
            exit();
        } else {
            die('Error: (' . $this->link->errorno . ') ' . $this->link->error);
        }
    }*/
    
    /**
     * Runs and UPDATE query that can be modified by the set function and from function
     * @param string $table        The table to run the update query on
     * @param array||object [$data = ""]  The data to update in the table
     * @param string [$where = ""] Where to update the data
     */
    public function update($table, $data = "", $where = "") {
        if ($data != "")
            $this->set($data);
        if ($where != "")
            $this->where($where);
        $query = trim("UPDATE $table $this->set_update $this->where");
        $update_row = $this->link->query($query) or die($this->link->error.__LINE__);
        $this->last_query = $query;
        $this->set_insert = "";
        $this->set_update = "";
        if ($update_row) {
            header("Location: index.php?msg=" . urlencode('Record Updated'));
            exit();
        } else {
            die('Error: (' . $this->link->errorno . ') ' . $this->link->error);
        }
    }
    
    /*public function update_string($table_name, $data, $where="") {
        $keys_and_values = array();
        foreach ($data as $key => $value) {
            $keys_and_values[] = $key . " = '" . $value ."'";
        }
        if ($where != "")
            $this->where($where);
        $query = "UPDATE $table_name SET ('{".implode("}', '{",$keys_and_values)."}') $this->where";
        $insert_row = $this->link->query($query) or die($this->link->error.__LINE__);
        $this->last_query = $query;
        $this->where = "";
        if ($insert_row) {
            header("Location: index.php?msg=" . urlencode('Record Updated'));
            exit();
        } else {
            die('Error: (' . $this->link->errorno . ') ' . $this->link->error);
        }
    }*/
    
    /**
     * Runs a DELETE query that can be modified by the from function and the where function
     * @param string [$table = ""] The table to delete information from
     * @param string [$where = ""] Where the row is to delete
     */
    public function delete($table = "", $where = "") {
        if (!is_array($table)) {
            if ($table != "")
                $this->from($table);
            if ($where != "")
                $this->where($where);
            $query = "DELETE FROM $this->from $this->where";
            $delete_row = $this->link->query(trim($query)) or die($this->link->error.__LINE__);
            $this->last_query = $query;
            $this->from = "";
            $this->where = "";
            if ($delete_row) {
                header("Location: index.php?msg=" . urlencode('Record Deleted'));
            } else {
                die('Error: (' . $this->link->errorno . ') ' . $this->link->error);
            }
        } else if (is_array($table)) {
            $query = "";
            if ($where != "")
                $this->where($where);
            foreach ($table as $key => $value) {
                $this->from($value);
                $query .= "DELETE FROM $this->from $this->where;";
            }
            $query = substr($query, 0, strrpos($query, ";"));
            $delete_row = $this->link->multi_query(trim($query)) or die($this->link->error.__LINE__);
            $this->last_query = $query;
            $this->from = "";
            $this->where = "";
            if ($delete_row) {
                header("Location: index.php?msg=" . urlencode('Record Deleted'));
            } else {
                die('Error: (' . $this->link->errorno . ') ' . $this->link->error);
            }
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