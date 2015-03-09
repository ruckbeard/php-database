# php-database
<p>A class for PHP that can be used to connect to a MySQL database, construct queries, and run queries. Similar to CodeIgniter's database class.</p>

<h3><strong>Query Functions</strong></h3>
<h4><strong>query</strong></h4>
<p>This will run a simple query on the database that will return a query object that can be used to obtain the results if it returns any rows.</p>
<pre>$db = new Database;

$query = $db->query("SELECT * FROM table");</pre>

<h4><strong>get</strong></h4>
<p>The get function accepts one optional parameter which is the table to put in the FROM portion of the query. When this is set, and no query constructor functions are called before it, it runs a basic query that selects all rows in the table. The get function can also be used run queries constructed by the query constructor functions as can be seen in the third example.</p>
<pre>$db = new Database;

$query = $db->get("table");</pre>
<em>This will create a query "SELECT * FROM table" and run it on the database</em>

<pre>$db = new Database;

$db->select("*")->from("table")->where("id","5","=");
$query = $db->get();</pre>
<em>This will create a query "SELECT * FROM table WHERE id = 5" and run it on the database</em>

<h3><strong>The Query Result</strong></h3>
<p>The query result object contains the results of the query that was run on the database. It is accessed as an object</p>
<h4><strong>result</strong></h4>
<p>The result function returns all of the results from the query. All of the results can be accessed by usings a foreach loop.</p>
<pre>$db = new Database;

$query = $db->query("SELECT * FROM table");

foreach ($query->result() as $row) {
  $row->id;
  $row->name;
  $row->date;
}</pre>

<pre>$db = new Database;

$query = $db->get("table");

foreach ($query->result() as $row) {
  $row->id;
  $row->name;
  $row->date;
}</pre>

<h4><strong>row</strong></h4>
<p>The row function returns a single row from the query results. With no parameters, it will return the first row of the results. You can add a parameter to select which row you want to get.</p>;
<pre>$db = new Database;

$query = $db->query("SELECT * FROM table");
$row = $query->row();
$row->id;
$row->name;
$row->date;</pre>
<p>or</p>
<pre>$db = new Database;

$query = $db->query("SELECT * FROM table");
$row = $query->row(5);
$row->id;
$row->name;
$row->date;</pre>
<h3><strong>Query Constructor Functions</strong></h3>
<p>The query constructor functions are a set of functions that can be called before using the get() function to write seperate portions of the query without having to manually write the query as a string. Information is automatically escaped by using this method. These functions can be chained together which can simplify the syntax and increase readability.</p>
<h4><strong>select</strong></h4>
<p>The select function writes the SELECT portion of the query.</p>
<pre>$db = new Database;

$db->select("*");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT * FROM table".</em>
<pre>$db = new Database;

$db->select("foo,bar");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT foo,bar FROM table".</em>
<pre>$db = new Database;

$db->select("SELECT id");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT id FROM table".</em>
<h4><strong>select_max</strong></h4>
<p>The select max function writes the SELECT MAX(field) portion of the query. Accepts two parameters. The first is the field to select and the second renames the resulting field</p>
<pre>$db = new Database;

$db->select_max("foo","bar");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT MAX(foo) as bar FROM table".</em>
<pre>$db = new Database;

$db->select_max("foo");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT MAX(foo) as foo FROM table".</em>
<h4><strong>select_min</strong></h4>
<p>The select min function writes the SELECT MIN(field) portion of the query. Accepts two parameters. The first is the field to select and the second renames the resulting field</p>
<pre>$db = new Database;

$db->select_min("foo","bar");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT MIN(foo) as bar FROM table".</em>
<pre>$db = new Database;

$db->select_min("foo");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT MIN(foo) as foo FROM table".</em>
<h4><strong>select_avg</strong></h4>
<p>The select avg function writes the SELECT AVG(field) portion of the query. Accepts two parameters. The first is the field to select and the second renames the resulting field</p>
<pre>$db = new Database;

$db->select_avg("foo","bar");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT AVG(foo) as bar FROM table".</em>
<pre>$db = new Database;

$db->select_avg("foo");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT AVG(foo) as foo FROM table".</em>
<h4><strong>select_sum</strong></h4>
<p>The select sum function writes the SELECT SUM(field) portion of the query. Accepts two parameters. The first is the field to select and the second renames the resulting field</p>
<pre>$db = new Database;

$db->select_sum("foo","bar");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT SUM(foo) as bar FROM table".</em>
<pre>$db = new Database;

$db->select_sum("foo");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT SUM(foo) as foo FROM table".</em>
<h4><strong>from</strong></h4>
<p>The from function will write the FROM portion of the query string.</p>
<pre>$db = new Database;

$db->from("table");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table".</em>
<pre>$db = new Database;

$db->select("foo");
$db->from("table");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT foo FROM table".</em>
<h4><strong>join</strong></h4>
<p>The join function will write the JOIN portion of the query string. It accepts three parameters. The first parameter is the table to join. The second parameter contains the foreign key to join the queries by. The third parameter is optional. It sets what type of JOIN, such as left, right, or inner.</p>
<pre>$db = new Database;

$db->select("table.*, table2.name");
$db->join("table2", "table.id = table2.id", "inner");
$query = $db->get("table");</pre>
<em>This will create and run the query string  "SELECT table.*, table2.name FROM table INNER JOIN table2 ON table.id = table2.id".</em>
<h4><strong>where</strong></h4>
<p>The where function will write the WHERE portion of the query string. The function accepts three parameters. The first parameter can be written three different ways. It can be just the field, or the field and an operator. It can also be the field, operator, and data to find in the field. The second parameter is optional and should be set if the first parameter is set as just the field with optional operator. The second parameter is the data to search the field for. The third option can be set the operator if it hasn't been set in the first parameter. The default of the third parameter is "=". The first parameter can also be an array to chain with AND. The function can also be called multiple times to chain with AND.</p>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->where("id = 1");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table WHERE id = '1'".</em>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->where("id =", "1");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table WHERE id = '1'".</em>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->where("id", "1");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table WHERE id = '1'".</em>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->where("id","1","=");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table WHERE id = '1'".</em>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->where("id", "1");
$db->where("name", "bob");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table WHERE id = '1' AND name = 'bob'".</em>
<h4><strong>or_where</strong></h4>
<p>The where_or function will write the WHERE portion of the query string. The function accepts three parameters. The first parameter can be written three different ways. It can be just the field, or the field and an operator. It can also be the field, operator, and data to find in the field. The second parameter is optional and should be set if the first parameter is set as just the field with optional operator. The second parameter is the data to search the field for. The third option can be set the operator if it hasn't been set in the first parameter. The default of the third parameter is "=". The first parameter can also be an array to chain with OR. The function can also be called multiple times to chain with OR.</p>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->where("id", "1");
$db->where("name", "bob");
$db->where_or("name", "ted");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table WHERE id = '1' AND name = 'bob' OR name = 'ted'".</em>
<h4><strong>group_by</strong></h4>
<p>The group_by function will write the GROUP BY portion of the query string. The function accepts one parameter which can be a string of values to group by, or an array of multiple values.</p>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->group_by("title, date");
$query = $db->get();</pre>
<em>This will create and run the query string "SELECT * FROM table GROUP BY title, date"</em>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->group_by(array("title","date"));
$query = $db->get();</pre>
<em>This will create and run the query string "SELECT * FROM table GROUP BY title, date"</em>
<h4><strong>distinct</strong></h4>
<p>The distinct function will allow you to add DISTINCT to the SELECT portion of the query string.</p>
<pre>$db = new Database;

$db->distinct();
$query = $db->get("table");</pre>
<em>This will create and run the query string "SELECT DISTINCT * FROM table"</em>
<h4><strong>having</strong></h4>
<p>The having function will write the HAVING portion of the query string. The function accepts three parameters. The first parameter can be written three different ways. It can be just the field, or the field and an operator. It can also be the field, operator, and data to find in the field. The second parameter is optional and should be set if the first parameter is set as just the field with optional operator. The second parameter is the data to search the field for. The third option can be set the operator if it hasn't been set in the first parameter. The default of the third parameter is "=". The first parameter can also be an array to chain with AND. The function can also be called multiple times to chain with AND.</p>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->having("id = 1");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table HAVING id = '1'".</em>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->having("id =", "1");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table HAVING id = '1'".</em>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->having("id", "1");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table HAVING id = '1'".</em>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->having("id","1","=");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table HAVING id = '1'".</em>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->having("id", "1");
$db->having("name", "bob");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table HAVING id = '1' AND name = 'bob'".</em>
<h4><strong>or_having</strong></h4>
<p>The having_or function will write the HAVING portion of the query string. The function accepts three parameters. The first parameter can be written three different ways. It can be just the field, or the field and an operator. It can also be the field, operator, and data to find in the field. The second parameter is optional and should be set if the first parameter is set as just the field with optional operator. The second parameter is the data to search the field for. The third option can be set the operator if it hasn't been set in the first parameter. The default of the third parameter is "=". The first parameter can also be an array to chain with OR. The function can also be called multiple times to chain with OR.</p>
<pre>$db = new Database;

$db->select("*");
$db->from("table");
$db->having("id", "1");
$db->having("name", "bob");
$db->having_or("name", "ted");
$query = $db->get();</pre>
<em>This will create and run the query string  "SELECT * FROM table HAVING id = '1' AND name = 'bob' OR name = 'ted'".</em>
<h4><strong>order_by</strong></h4>
<p>The order_by function will allow you to write the ORDER BY portion of the query. This function accepts two parameters. The first is the field to order by. The second is the direction of the result, which are asc, desc, or random. The function can be called multiple times for multiple fields.</p>
<pre>$db = new Database;

$db->order_by("id","desc");
$query = $db->get("table");</pre>
<em>This will create and run the query string "SELECT * FROM table ORDER BY id DESC"</em>
<pre>$db = new Database;

$db->order_by("id","desc");
$db->order_by("name","asc");
$query = $db->get("table");</pre>
<em>This will create and run the query string "SELECT * FROM table ORDER BY id DESC, name ASC"</em>
<h4><strong>limit</strong></h4>
<p>The limit function will allow you to write the LIMIT portion of the query. This function accepts two parameters. The first is amount to limit the query by. The second is an optional offset.</p>
<pre>$db = new Database;

$db->limit(10);
$query = $db->get("table");</pre>
<em>This will create and run the query string "SELECT * FROM table LIMIT 10"</em>
<pre>$db = new Database;

$db->limit(10,20);
$query = $db->get("table");</pre>
<em>This will create and run the query string "SELECT * FROM table LIMIT 20, 10"</em>
<h4><strong>Chaining Constructor Functions</strong></h4>
<p>The query constructor functions can be chained together to simplify the syntax and increase readability.</p>
<pre>$db = new Database;

$db->select("*")->from("table")->where("id","1","!=")->order_by("id","asc");
$query = $db->get();</pre>
<em>This will create and run the query string "SELECT * FROM table WHERE id != 1 ORDER BY id ASC"</em>
<h3><strong>Insert</strong></h3>
<p>The insert function can be used to create and run insert queries. The first parameter it accepts in the table and the second is the data to insert into the query string. The data can be stored in an array or an object. The second parameter is optional. The data can be set by using the set() function.</p>
<pre>$db = new Database;

$data = array(
  field_1 => "foo",
  field_2 => "bar",
  field_3 => "foobar"
)

$db->insert("table",$data);</pre>
<em>This will create and run the query "INSERT INTO table (field_1,field_2,field_3) VALUES ('foo','bar','foobar')</em>
<pre>$db = new Database;

$data = array(
  array (
    field_1 => "foo1",
    field_2 => "bar2",
    field_3 => "foobar3"
  ),
  array (
    field_1 => "foo4",
    field_2 => "bar5",
    field_3 => "foobar6"
  )
)

$db->insert("table",$data);</pre>
<em>This will create and run the query "INSERT INTO table (field_1,field_2,field_3) VALUES ('foo1','bar2','foobar3'),('foo4','bar5','foobar6')</em>
<h3><strong>Update</strong></h3>
<p>The update function will create and run an UPDATE query. The first paremeter is the table. The second parameter is the data to enter into the query string. The third parameter writes the WHERE portion of the query string. The second and third parameters are optional. The data can be set by using the set() function and the WHERE portion can be set by using the where() function.</p>
<pre>$data = array(
  field_1 => "foo",
  field_2 => "bar",
  field_3 => "foobar"
)

$db->update("table",$data,"id = 1");</pre>
<em>This will create and run the query string "UPDATE table SET field_1 = 'foo', field_2 = 'bar', field_3 = 'foobar' WHERE id = 1"</em>
<pre>$data = array(
  field_1 => "foo",
  field_2 => "bar",
  field_3 => "foobar"
)

$db->where("id","1");
$db->update("table",$data);</pre>
<em>This will create and run the query string "UPDATE table SET field_1 = 'foo', field_2 = 'bar', field_3 = 'foobar' WHERE id = 1"</em>
<h3><strong>Set</strong></h3>
<p>The set function can be used to set the data of an INSERT or UPDATE query string. It can be called multiple times to set multiple fields with data.</p>
<pre>$db = new Database;

$db->set("field_1", "foo");
$db->set("field_2", "bar");
$db->set("field_3", "foobar");
$db->insert("table");</pre>

<pre>$db = new Database;

$db->set("field_1", "foo");
$db->set("field_2", "bar");
$db->set("field_3", "foobar");
$db->where("id","1");
$db->update("table");</pre>

<h3><strong>Delete</strong></h3>
<p>The delete function can be called to create and run a DELETE query string. The first and second parameters are optional. The first parameter is the table to delete from and the second parameter writes the WHERE portion of the query string. These can also be set by using the from() and where() functions.</p>
<pre>$db = new Database;

$db->from("table")->where("id","1");
$db->delete();</pre>

or

<pre>$db = new Database;

$db->delete("table", "id = 1");</pre>


