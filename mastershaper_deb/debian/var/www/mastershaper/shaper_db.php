<?

define('VERSION', '0.44');

/***************************************************************************
 *
 * Copyright (c) by Andreas Unterkircher, unki@netshadow.at
 * All rights reserved
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 ***************************************************************************/

/* from pear "DB" package. use "pear install DB" if you don't have this file */
require_once('DB.php');

class MSDB {

   var $db;
   var $parent;
   var $is_connected;
   var $last_error;

   /* Class constructor */
   function MSDB($parent)
   {
      $this->parent = $parent;

      /* We are starting disconnected */
      $this->setConnStatus(false);

      /* Connect to MySQL Database */
      $this->db_connect();

   } // MSDB()
	  
   function db_close()
   {
      /* Disconnect from MySQL Database */
      $this->db_disconnect();

   } // db_close()
    
   function db_connect()
   {
      $options = array(
	 'debug' => 2,
	 'portability' => DB_PORTABILITY_ALL,
      );

      /* Prepare DSN string */
      $dsn = "mysql://". MYSQL_USER .":". MYSQL_PASS ."@". MYSQL_HOST ."/". MYSQL_DB;
			
      /* Open connection to databse */
      $this->db = DB::connect($dsn, $options);

      /* Errors? */
      if(DB::isError($this->db)) {
	 $this->printError($this->db->getDebugInfo());
	 $this->setConnStatus(false);
      }
      else
	 $this->setConnStatus(true);

   } // db_connect()

   function db_disconnect()
   {
      $this->db->disconnect();

   } // db_disconnect()

   function db_query($query = "", $mode = DB_FETCHMODE_OBJECT)
   {
      if($this->getConnStatus()) {

	 /* Query result should be a object */
	 $this->db->setFetchMode($mode);
	
	 /* run query */
	 $result = $this->db->query($query);
			
	 /* Errors? */
	 if(DB::isError($result))
	    die($result->getDebugInfo());
	
	 return $result;
      }
      else 
	 $this->printError("Can't execute query - we are not connected!");

   } // db_query()

   function db_fetchSingleRow($query = "") 
   {
      if($this->getConnStatus()) {
	 /* get the first row */
	 $result = $this->db->getRow($query, DB_FETCHMODE_OBJECT);
	
	 /* Errors? */
	 if(DB::isError($result))
	    die($result->getDebugInfo());

	 return $result;
      }
      else 
	 $this->printError("Can't fetch row - we are not connected!");
      
   } // db_fetchSingleRow()

   function db_getNumRows($query = "")
   {
      /* Execute query */
      $result = $this->db_query($query);

      /* Errors? */
      if(DB::isError($result)) 
	 die($result->getDebugInfo());

      return $result->numRows();

   } // db_getNumRows()

   function db_getid()
   {
      /* Get the last primary key ID from execute query */
      return mysql_insert_id($this->db->connection);
      
   } // db_getid()

   function db_check_table_exists($table_name = "")
   {
      if($this->getConnStatus()) {
	 $result = $this->db_query("SHOW TABLES");
	 $tables_in = "tables_in_". MYSQL_DB;
	
	 while($row = $result->fetchRow()) {
	    if($row->$tables_in == $table_name)
	       return 1;
	 }
	 return 0;
      }
      else
	 $this->printError("Can't check table - we are not connected!");
	 
   } // db_check_table_exists()

   function db_rename_table($old, $new)
   {
      if($this->getConnStatus()) {
	 if($this->db_check_table_exists($old)) {
	    if(!$this->db_check_table_exists($new))
	       $this->db_query("RENAME TABLE ". $old ." TO ". $new);
	    else
	       $this->printError("Can't rename table ". $old ." - ". $new ." already exists!");
	 }
      }
      else
	 $this->printError("Can't check table - we are not connected!");
	 
   } // db_rename_table()

   function db_drop_table($table_name)
   {
      if($this->getConnStatus()) {
	 if($this->db_check_table_exists($table_name))
	    $this->db_query("DROP TABLE ". $table_name);
      }
      else
	 $this->printError("Can't check table - we are not connected!");

   } // db_drop_table()

   function db_truncate_table($table_name)
   {
      if($this->getConnStatus()) {
	 if($this->db_check_table_exists($table_name)) 
	    $this->db_query("TRUNCATE TABLE ". $table_name);
      }
      else
	 $this->printError("Can't check table - we are not connected!");

   } // db_truncate_table()

   function db_check_column_exists($table_name, $column)
   {
      $result = $this->db_query("DESC ". $table_name, DB_FETCHMODE_ORDERED);

      while($row = $result->fetchRow()) {

	 if(in_array($column, $row))
	    return 1;

      }

      return 0;

   } // db_check_column_exists()

   function db_check_index_exists($table_name, $index_name)
   {
      $result = $this->db_query("DESC ". $table_name, DB_FETCHMODE_ORDERED);

      while($row = $result->fetchRow()) {

         if(in_array("KEY `". $index_name ."`", $row))
	    return 1;

      }

      return 0;

   } // db_check_index_exists()

   function db_alter_table($table_name, $option, $column, $param1 = "", $param2 = "")
   {
      if($this->getConnStatus()) {
                   
	 if($this->db_check_table_exists($table_name)) {

	    switch(strtolower($option)) {
	
	       case 'add':
					
                  if(!$this->db_check_column_exists($table_name, $column))
		     $this->db_query("ALTER TABLE ". $table_name ." ADD ". $column ." ". $param1);

		  break;

	       case 'change':
					
                  if($this->db_check_column_exists($table_name, $column))
		     $this->db_query("ALTER TABLE ". $table_name ." CHANGE ". $column ." ". $param1);

		  break;

	       case 'drop':

                  if($this->db_check_column_exists($table_name, $column))
		     $this->db_query("ALTER TABLE ". $table_name ." DROP ". $column);
		  break;

               case 'dropidx':
	          
		  if($this->db_check_index_exists($table_name, $column))
		     $this->db_query("ALTER TABLE ". $table_name ." DROP INDEX ". $column);
		  break;

	    }
	 }
      }
      else
	 $this->printError("Can't check table structure - we are not connected!");

   } // db_alter_table()

   function getVersion()
   {
      if($this->db_check_table_exists("shaper_settings")) {
	 $result = $this->db_fetchSingleRow("SELECT setting_value FROM shaper_settings WHERE setting_key like 'version'");
	 return $result->setting_value;
      }
      else
	 return 0;
	 
   } // getVersion()

   function setVersion($version)
   {
      $this->db_query("REPLACE INTO shaper_settings (setting_key, setting_value) VALUES ('version', '". $version ."')");
      
   } // setVersion()

   function setConnStatus($status)
   {
      $this->is_connected = $status;
      
   } // setConnStatus()

   function getConnStatus()
   {
      return $this->is_connected;

   } // getConnStatus()

   function printError($string)
   {
      if(!defined('DB_NOERROR')) 
	 print "<br />". $string ."<br />\n";

      $this->last_error = $string;
	 
   } // printError()

}

?>
