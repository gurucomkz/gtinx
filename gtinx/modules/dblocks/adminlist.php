<?
if(!defined('GT_ADMIN')) die();

$myCacheVar = "dblock:adminlist";
$olddata = cacheGetVars($myCacheVar);
$curdata = (int)$DB->QResult("SELECT MAX(`UPDATED`) FROM `g_dblock_group`")+
			(int)$DB->QResult("SELECT MAX(`UPDATED`) FROM `g_dblock_types`");

if($olddata !== $curdata)
{ 	//rebuild array

	$arAllGroups=array();
	$sql="SELECT `ID`,`NAME`,`DESC` FROM `g_dblock_group`";
	$res=$DB->Query($sql);
	while ($row=$DB->FetchArray($res))
	{
	$arUGroups=array();
		$sql2="SELECT `ID`,`NAME`,`DESC` FROM `g_dblock_types` WHERE `GROUP_ID`='".$row['ID']."'";
		$res2=$DB->Query($sql2);
		
		while($row2=$DB->FetchArray($res2))
		{
			$arST=array();
			$sql3="SELECT `ID`,`NAME` FROM `g_dblock_subtypes` WHERE `TYPE_ID`='".$row2['ID']."' AND `PARENT`='0'";
			$res3=$DB->Query($sql3);
			while($row3=$DB->FetchArray($res3))
			{
				$arST[]=array($row3['NAME'],$row3['NAME'],STDITEMICON,'act=get_subblock&type='.$row2['ID'].'&subtype='.$row3['ID'].'');
			}
			$arUGroups[]=array($row2['NAME'],$row2['DESC'],empty($arST)?STDITEMICON:STDGRPICON,'act=dblocks&id='.$row2['ID'],$arST);
		}
		$arAllGroups[] = array(
			$row['NAME'],
			$row['DESC'],
			empty($arUGroups)?STDITEMICON:STDGRPICON,
			'act=types&id='.$row['ID'],
			$arUGroups
		);
	}
	
	$arAllGroupsS=array();
	$sqlS="SELECT `ID`,`NAME`,`DESC` FROM `g_dblock_group`";
	$resS=$DB->Query($sqlS);
	while ($rowS=$DB->FetchArray($resS))
	{
	$arUGroupsS=array();
		$sql2S="SELECT `ID`,`NAME`,`DESC` FROM `g_dblock_types` WHERE `GROUP_ID`='".$rowS['ID']."'";
		$res2S=$DB->Query($sql2S);
		
		while($row2S=$DB->FetchArray($res2S))
		{
			$arSTS=array();
			$sql3S="SELECT `ID`,`NAME` FROM `g_dblock_subtypes` WHERE `TYPE_ID`='".$row2S['ID']."'";
			$res3S=$DB->Query($sql3S);
			while($row3S=$DB->FetchArray($res3S))
			{
				$arSTS[]=array($row3S['NAME'],$row3S['NAME'],STDITEMICON,'act=subtype_edit&id='.$row3S['ID'].'');
			}
			$arUGroupsS[]=array($row2S['NAME'],$row2S['DESC'],STDITEMICON,'act=type_edit&id='.$row2S['ID'],$arSTS);
		}
		$arAllGroupsS[] = array(
			$rowS['NAME'],
			$rowS['DESC'],
			STDGRPICON,
			'act=group_edit&id='.$rowS['ID'],
			$arUGroupsS
		);
	}
	
	$APP->AdmRegisterItemGroup(array(
		array(GetMessage('LIST_DATABLOCKS'),'Редактировать данные',STDGRPICON,'act=groups',$arAllGroups
		),
		array(GetMessage('EDIT_DATABLOCKS'),'Редактировать структуры данных',STDGRPICON,'act=groups',$arAllGroupsS
		),
		//array(GetMessage('EXPORT'),'Экспорт данных',STDGRPICON,'act=export'	),
		//array(GetMessage('IMPORT'),'Экспорт данных',STDGRPICON,'act=import'	),
		array(GetMessage('SEARCH_OPTIONS'),'Поиск',STDGRPICON,'act=search_options'	),
		array(GetMessage('STATISTICS'),'Статистика',STDGRPICON,'act=statistics'	),

	));
	
}
?>