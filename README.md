# php-database
A class for PHP that can be used to connect to a MySQL database, construct queries, and run queries. Similar to CodeIgniter's database class.

Example:

Running a simple query -

$db = new Database;

$query = $db->query("SELECT * FROM table");

This will run a simple query on the database that will return a query object that can be used to obtain the results if it returns any rows. This can be done with a foreach loop for multiple rows or if you are only expecting or want a single row there is also a function to select single row.

For multiple rows -

foreach ($query->result() as $row) {
  $row->field_1;
  $row->field_2;
  $row->field_3;
}

For a single row -

$row = $query->row();
$row->field_1;
$row->field_2;
$row->field_3;

Another way to write queries is to use the query constructing functions -

$db = new Database;

$db->select("*")->from("table");
$query = $db->get();

The select function can be used to write the SELECT portion of the query. This can be done by choosing what fields to select from and pass them into the select function as a string. The from function adds the table to the query.

A simpler way to write this would be -

$db = new Database;

$query = $db->get("table");

This is because the default SELECT portion is SELECT * and the get function accepts 3 parameters. The first being a string that writes the FROM portion if it is not empty.

The get function also produces the same query object that the query function does and is accessed in the same way.

The class can also create other queries such as INSERT, UPDATE, and DELETE queries.

The insert function can be used to create INSERT queries -

$db = new Database;

$data = array(
  field_1 => "foo",
  field_2 => "bar",
  field_3 => "foobar"
)

$db->insert("table",$data);

This will construct an INSERT query that inserts the information in the array into the table. The array key should correspond with the field names of the table and the values being the information being put into the row. The data can also be a two-dimensional array that can insert multiple rows into the same table.

$db = new Database;

$data = array(
  array (
    field_1 => "foo",
    field_2 => "bar",
    field_3 => "foobar"
  ),
  array (
    field_1 => "foo",
    field_2 => "bar",
    field_3 => "foobar"
  )
)

$db->insert("table",$data);

Another way to construct an INSERT query is to use the set function -

$db = new Database;

$db->set("field_1", "foo");
$db->set("field_2", "bar");
$db->set("field_3", "foobar");

$db->insert("table");

The update query can be constructed in almost the exact same way.

$data = array(
  field_1 => "foo",
  field_2 => "bar",
  field_3 => "foobar"
)

$db->where("id","1");
$db->update("table",$data);

or

$db = new Database;

$db->set("field_1", "foo");
$db->set("field_2", "bar");
$db->set("field_3", "foobar");

$db->where("id","1");
$db->update("table");

A delete function can also be called to create DELETE queries.

$db = new Database;

$db->from("table")->where("id","1");
$db->delete();

or

$db = new Database;

$db->delete("table", "id = 1");
