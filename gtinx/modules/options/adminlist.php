<?
if(!defined('GT_ADMIN')) die();
$myCacheVar = "options:adminlist";

//$olddata = cacheGetVars($myCacheVar);
//$curdata = (int)$DB->QResult("SELECT MAX(`UPDATED`) FROM `g_polls`");

//if($olddata !== $curdata)
//{ 	//rebuild array
	$arUGroupsA = Array(
			array(GetMessage('LIST_SITES'),'Options Control',STDGRPICON,'act=sites'),
			array(GetMessage('TAMPLATES_SITES'),'Options Control',STDGRPICON,'act=Templates')
			);
	$APP->AdmRegisterItemGroup(array(
		array(GetMessage('OPTIONS'),'Options Control',STDGRPICON,'act=sites',Array(
			array(GetMessage('SITES'),'Options Control',STDGRPICON,'act=sites',$arUGroupsA	),
			array(GetMessage('LANGS'),'Options Control',STDGRPICON,'act=lang')
		))
	),'config');
	//cacheSetVars($myCacheVar,$curdata);
//} //else make engine use internal info
?> 