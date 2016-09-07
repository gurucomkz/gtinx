<?
////DURECT INDEX
define('GTDOCROOT',rtrim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]), "/ "));
define('GTROOT',GTDOCROOT.'/gtinx');
define('GTINX_DIRECT_OUTPUT',true);

require(GTROOT.'/start.php');

//routing
$sRunMod = secMatch($_REQUEST['mod']);
if(!$sRunMod) $sRunMod = 'core';
$APP->loadModule($sRunMod);


$APP->Omega();
?>