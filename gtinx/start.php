<?
if(!defined('GTINX_DOUBLE_START_PROTECTION')){


define('GTINX_DOUBLE_START_PROTECTION',true);
ini_set('display_errors','On');

function getMicroTime(){         //derived from PHP manual
	   list($usec, $sec) = explode(" ",microtime());
	   return ((float)$usec + (float)$sec);
}
define('PARSESTART', getMicroTime());

require_once(GTROOT.'/config/db_credentials.php');
//require(GTROOT.'/config/config.php');
require_once(GTROOT.'/tools.php');
require_once(GTROOT.'/modules/core/classes/app.php');


header("Content-Type: text/html; charset=utf-8");

# database related stuff
if(file_exists(GTROOT.'/db/'.USE_DB.'.php')) {
	require_once (GTROOT.'/db/'.USE_DB.'.php');
}else{
	trigger_error("Can't find database driver (".USE_DB.")",ERR_FATAL);
}

handleQuotes();
#connect db

$DB = new dbclass($DB_CREDENTIALS);
unset($DB_CREDENTIALS);
define('B_PROLOG_INCLUDED',true);
$GLOBALS['APP'] = new GTApp($DB);

$APP->Alfa();

}
?>