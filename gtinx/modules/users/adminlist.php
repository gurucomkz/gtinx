<?
if(!defined('GT_ADMIN')) die();


$myCacheVar = "users:adminlist";

$olddata = cacheGetVars($myCacheVar);
$curdata = (int)$DB->QResult("SELECT MAX(`UPDATED`) FROM `g_groups`");

if($olddata !== $curdata)
{ 	//rebuild array
	$hGroups = $DB->Query("SELECT `ID`,`NAME`,`DESC` FROM `g_groups` ORDER BY `NAME` ASC");
	while($arGrp = $DB->FetchArray($hGroups))
	$arUGroupsA[] = array($arGrp['NAME'],$arGrp['DESC'],STDITEMICON,'act=groupusers&id='.$arGrp['ID']);
	//$hUsers = $DB->Query("SELECT `ID`,`LOGIN`,`NAME` FROM `g_users` ORDER BY `NAME` ASC");
	//while($arUsr = $DB->FetchArray($hUsers))
	//$arUGroupsU[] = array($arUsr['LOGIN'],$arUsr['NAME'],STDITEMICON,'act=edituser&id='.$arUsr['ID']);
	$arUGroups = array(array(
			GetMessage('ALL_USERS'),'List all users',STDGRPICON,'act=groupusers'//,$arUGroupsU
		),
		array(
			GetMessage('ALL_GROUPS'),'List all groups',STDGRPICON,'act=listgr',$arUGroupsA
		),
		array(
			GetMessage('ALL_FIELDS'),'List all user fields',STDITEMICON,'act=fields'
		)
	);
	
	
	$APP->AdmRegisterItemGroup(array(
		array(GetMessage('USERS'),'User Control',STDGRPICON,'act=users',$arUGroups)
	));
	cacheSetVars($myCacheVar,$curdata);
} //else make engine use internal info
?>