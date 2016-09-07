<?
if(!defined('GTINX_DOUBLE_INIT_PROTECTION')){

define('GTINX_DOUBLE_INIT_PROTECTION',true);

define('GTDOCROOT',rtrim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]), "/ "));
define('GTROOT',GTDOCROOT.'/gtinx');

require(GTROOT.'/start.php');

}
//if(!$APP->UserAble('SU')) die("Site opened for admins only");
?>