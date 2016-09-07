<?
if(!defined('GT_ADMIN')) die();


$myCacheVar = "polls:adminlist";

$olddata = cacheGetVars($myCacheVar);
$curdata = (int)$DB->QResult("SELECT MAX(`UPDATED`) FROM `g_polls`");

if($olddata !== $curdata)
{ 	//rebuild array
	$hGroups = $DB->Query("SELECT * FROM `g_polls` ORDER BY `TITLE` ASC");
	while($arGrp = $DB->FetchArray($hGroups))
	$arUGroupsA[] = array($arGrp['TITLE'],'Ответы на '.$arGrp['TITLE'],STDITEMICON,'act=polls_answers&id='.$arGrp['ID']);
	$APP->AdmRegisterItemGroup(array(
		array(GetMessage('POLLS'),'Polls Control',STDGRPICON,'act=polls',Array(
		array(GetMessage('POLLS_QUEST'),'Polls Control',STDGRPICON,'act=polls_quest'),
		array(GetMessage('POLLS_ANSWER'),'Polls Control',STDGRPICON,'act=polls_answer',$arUGroupsA)
		))
	));
	cacheSetVars($myCacheVar,$curdata);
} //else make engine use internal info
?>