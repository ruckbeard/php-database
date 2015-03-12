<?php

namespace Ruckbeard\Database;

class QueryConstructor
{
    protected $last_query = "";
    protected $query_string = array(
        "SELECT" => "SELECT *",
        "FROM" => "",
        "JOIN" => "",
        "WHERE" => "",
        "GROUP BY" => "",
        "HAVING" => "",
        "ORDER BY" => "",
        "LIMIT" => ""
    );

    protected $set = array();
    protected $insert = "";

    /**
     * Escapes information entered by users and makes it safe for the database
     * @param  string $value The information to be escaped
     * @return string The escaped string
     */
    public function escape($value)
    {
        return $this->link->real_escape_string($value);
    }

    /**
     * The last query to be run using this db object
     * @return string The last query to be run
     */
    public function lastQuery()
    {
        return $this->last_query;
    }

    public function getQueryString()
    {
        return $this->query_string;
    }

    /**
     * Permits you to write the SELECT portion of your query
     * @param string $select Can be the full SELECT portion, or just the fields
     * @return object Return this object
     */
    public function select($select = "*")
    {
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
    private function maxMinAvgSum($x, $select, $var)
    {
        if (strstr($select, "SELECT") == false) {
            if ($var != "") {
                $this->query_string["SELECT"] = "$x(" . $select . ") as " . $var;
            } else {
                $this->query_string["SELECT"] = "$x(" . $select . ")";
            }
        } else {
            $this->query_string["SELECT"] = $select;
        }

        return $this;
    }


    public function selectMax($select, $var = "")
    {
        return $this->_max_min_avg_sum("MAX", $select, $var);
    }

    public function selectMin($select, $var = "")
    {
        return $this->_max_min_avg_sum("MIN", $select, $var);
    }

    public function selectAvg($select, $var = "")
    {
        return $this->_max_min_avg_sum("AVG", $select, $var);
    }

    public function selectSum($select, $var = "")
    {
        return $this->_max_min_avg_sum("SUM", $select, $var);
    }

    /**
     * Permits you to write the FROM portion of your query, can be specified in the get() function
     * @param string $from Can be the full FROM portion, or just the table
     * @return object Return this object
     */
    public function from($from)
    {
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
    public function join($join, $join_columns = "", $param = "")
    {
        $prefix = $param == "" ? "JOIN " : strtoupper($param) . " JOIN ";

        if ($join_columns != "") {
            $join = $prefix . $join . " ON " . $join_columns;
        }

        if ($this->query_string["JOIN"] != "") {
            $join = " ".$join;
        }

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
    private function whereOrHaving($where_or_having, $and_or, $field, $field_value, $operator, $backtick)
    {
        if (!is_array($field)) {
            $field = array($field => $field_value);
        }

        $array = array();

        foreach ($field as $key => $value) {
            $prefix = $this->query_string[$where_or_having] == "" ? "" : " $and_or ";

            if ($backtick && $value != "") {
                $key = "`$key`";
            }

            if (!preg_match("/[!=<>]/", $key)) {
                $key = "$key $operator";
            }

            if ($value != '') {
                $value = " '" . $this->escape($value) . "'";
            }

            $array[] = $prefix . $key . $value;
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
    public function where($where, $where_value = "", $operator = "=", $backtick = true)
    {
        return $this->whereOrHaving("WHERE", "AND", $where, $where_value, $operator, $backtick);
    }

    /**
     * Permits you to write the WHERE portion of your query. Can be called multiple times to combine
     * WHERE statements with OR.
     * @param array||string $where Can be an associative array, or a string.
     * @return object Returns this object;
     */
    public function orWhere($where, $where_value = "", $operator = "=", $backtick = true)
    {
        return $this->whereOrHaving("WHERE", "OR", $where, $where_value, $operator, $backtick);
    }

    /**
     * Permits you to write the GROUP BY portion of the query
     * @param  string $group_by The column to group by
     * @return object Returns this database object
     */
    public function groupBy($group_by)
    {
        if (is_array($group_by)) {
            $this->query_string["GROUP BY"] = implode(", ", $group_by);
        } elseif (is_string($group_by)) {
            if ($this->query_string["GROUP BY"] != "") {
                $group_by = ", " . $group_by;
            }

            $this->query_string["GROUP BY"] .= $group_by;
        }

        return $this;
    }

    /**
     * Permits you to add the DISTINCT modifier to your query
     * @return object Returns this database object
     */
    public function distinct()
    {
        $this->query_string["SELECT"] = "SELECT DISTINCT " . strstr($this->query_string["SELECT"], "SELECT ");

        return $this;
    }

    /**
     * Permits you to write the HAVING portion of your query. Can be called multiple times to combine
     * HAVING statements with AND.
     * @param array||string $having Can be an associative array, or a string.
     * @return object Returns this object;
     */
    public function having($having, $having_value = "", $operator = "=", $backtick = true)
    {
        return $this->whereOrHaving("HAVING", "AND", $having, $having_value, $operator, $backtick);
    }

    /**
     * Permits you to write the HAVING portion of your query. Can be called multiple times to combine
     * HAVING statements with OR.
     * @param array||string $having Can be an associative array containing
     * @return object Returns this object;
     */
    public function orHaving($having, $having_value = "", $operator = "=", $backtick = true)
    {
        return $this->whereOrHaving("HAVING", "OR", $having, $having_value, $operator, $backtick);
    }

    /**
     * Permits the user to write the ORDER BY portion of the query
     * @param  string $order_by The column to order by
     * @param  string $order    The modifier to order by
     * @return object Returns this database object
     */
    public function orderBy($order_by, $order = "")
    {
        if ($order != "") {
            $order_by .= " " . strtoupper($order);
        }

        if ($this->query_string["ORDER BY"] != "") {
            $order_by = ", " . $order_by;
        }

        $this->query_string["ORDER BY"] .= $order_by;

        return $this;
    }

    /**
     * Permits the user to write the LIMIT portion of the query
     * @param  string $limit         The amount to limit the query by
     * @param  string [$offset = -1] The offset to start the limit at
     * @return object Returns this database object
     */
    public function limit($limit, $offset = -1)
    {
        if (strstr($limit, "LIMIT") == false) {
            $this->query_string["LIMIT"] = $offset != -1 ? $offset . ", " . $limit : $limit;
        } else {
            $this->query_string["LIMIT"] = $limit;
        }

        return $this;
    }

    public function resetQueryString()
    {
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
    public function set($data, $value = "", $escape = true)
    {
        if (!is_array($data) && !is_object($data)) {
            $data = array($data => $value);
        }

        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                if ($escape == true) {
                    $value = $this->escape($value);
                }

                $this->set[$key] = $value;
            }
        }

        return $this;
    }
}
