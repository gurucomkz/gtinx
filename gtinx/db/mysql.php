<?php

/*
 * Written by Shevchenko Sergey aka Volhv
 * Email <ssh_volhv@bk.ru>
 * ICQ 268709362
 */


 /*
 TODO:

 functions like
 db_SELECT, db_UPDATE, db_DELETE etc..
 to hide structure from user when syntax is wrong

 */

define('DBCONTROL',true);

class dbclass {
        var $querynum = 0;
        var $querylist = "";
        var $handler = '';
        var $dbname = '';
        var $dbuser = '';
		var $trackQueries = false;
        var $mysqlCache = false;
        var $dbhost = 'localhost';

		function __construct($cred)
		{
			$this->pconnect = (bool)$cred['pconnect'];
			$this->Connect($cred['host'],$cred['user'],$cred['pass'],$cred['name']);
		}

        function Connect($dbhost="localhost", $dbuser, $dbpw, $dbname)
		{
			global $db_pconnect;
			if($this->pconnect) {
				$this->handler = @mysql_pconnect($dbhost, $dbuser, $dbpw) or GTApp::Raise(@mysql_error());
			} else {
				$this->handler = @mysql_connect($dbhost, $dbuser, $dbpw) or GTApp::Raise(@mysql_error());
			}
			if($this->handler) {
				@mysql_select_db($dbname, $this->handler) or GTApp::Raise(@mysql_error());
				$this->dbname = $dbname;
				$this->dbuser = $dbuser;
				$this->dbhost = $dbhost;
			};
			//determine if mysql query cache supported
			// It is faster than file-level cache
			$mysqlCache = cacheGet('mysqlQueryCache');
			if($mysqlCache){
				$this->mysqlCache = $mysqlCache;
			}else{
				$_x = $this->fetchAssoc($this->Query("SHOW VARIABLES LIKE 'query_cache_type'"));
				$this->mysqlCache = 'ON'==$_x['Value'];
			}

			includeIfExists(GTROOT.'/config/after_connect.php');
        }

        function Close() {
			if($this->db_connect_id) {
				if($this->query_result) {
					@mysql_free_result($this->query_result);
				}
				$result = @mysql_close($this->handler);
				return $result;
			}else{
				return false;
			}
        }

        function fetchArray($query) {
			$query = @mysql_fetch_array($query);
			return $query;
        }

        function fetchAssoc ($query) {
			$query = mysql_fetch_assoc ($query);
			return $query;
        }

        function Query($sql) {
			global $APP;
			$query = @mysql_query($sql, $this->handler) or GTApp::Raise(@mysql_error()."<br />\r\n$sql");
			$this->querynum++;
			if($this->trackQueries)
				$this->querylist .= "$sql\n\n\n\n";
			return $query;
        }

		function cachedQuery($sql) {
			if($this->mysqlCache){
				$q = $this->Query($query);
				while ($r = $this->fetchArray($q))
					$t[] = $r;
				return $t;
			}else{
				$cname = md5($query);
				$t = cacheGetVars($cname,0);
				if (!empty($t)) return $t;
				$t = array();
				$q = $DB->Query($query);
				while ($r = $DB->fetchArray($q))
					$t[] = $r;
				cacheSet($cname, $t);
				return $t;
			}
		}

        function Result($query, $row = 0) {
			$query = @mysql_result($query, $row);
			return $query;
        }

        function insertId() {
			$id = @mysql_insert_id($this->handler);
			return $id;
        }

        function fetchRow($query) {
			$query = @mysql_fetch_row($query);
			return $query;
        }

        function fetchField($query) {
			$query = @mysql_fetch_field($query);
			return $query;
        }

        function numRows($query) {
			$query = @mysql_num_rows($query);
			return $query;

        }

        function fieldName($offset, $query_id = 0) {
			$result = @mysql_field_name($query_id, $offset);
			return $result;
        }

        function numFields($query_id = 0) {
			$result = @mysql_num_fields($query_id);
			return $result;
        }
        function affectedRows(){
			$result = @mysql_affected_rows($this->handler);
			return $result;
        }
		function QResult($q,$row = 0){
			return $this->Result($this->Query($q),$row);
		}
		/**Abzal**/
		public function QueryA($sql)
		{
			$this->sql=$sql;
			return new fetchResults(mysql_query($this->sql,$this->handler));

		}
}
/**Abzal**/
class fetchResults {

 public $result;
function __construct($result=null){
 $this->result=$result;
}
public function Fetch()
	{
		return $this->row=mysql_fetch_assoc($this->result);
	}

}

?>