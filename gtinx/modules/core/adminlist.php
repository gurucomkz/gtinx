<?
//return; //this file is just example	
if(!defined('GT_ADMIN')) die();

$myCacheVar = "core:adminlist";

//$olddata = cacheGetVars($myCacheVar);
//$curdata = (int)$DB->QResult("SELECT MAX(`UPDATED`) FROM `g_usergroups`");

//if($olddata !== $curdata)
//{ 	//rebuild array
	$APP->AdmRegisterItemGroup(array(
		array("Config",'',STDITEMICON,'act=config')
	),
	'config');
	cacheSetVars($myCacheVar,$curdata);
//} //else make engine use internal info
?>