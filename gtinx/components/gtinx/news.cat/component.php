<?php

/*
	STANDALONE COMPONENT
	NEWS.CAT

*/

$iTypeId = (int)$arVariables['TYPE'];
//$iGroupId = (int)$arVariables['GROUP'];
$iSubTypeId = (int)$arVariables['SUBTYPE'];
$iLimit = (int)$arVariables['PAGINATION_LIMIT'];
$iSTId = (int)$_GET['ID'];
$iNId = (int)$_GET['ITEM'];
$iPage = (int)$_GET['page'];
if($iPage<1) $iPage = 1;

// body
$arCriteria = Array('ACTIVE' => 1 );
if($iTypeId || $iGroupId || $iSubTypeId){
	if($iTypeId) $arCriteria['TYPE_ID']=$iTypeId;
	//if($iGroupId) $arCriteria['GROUP_ID']=$iGroupId;
	if($iSubTypeId) $arCriteria['SUBTYPE']=$iSubTypeId;
}

$arDbl = GTDblocktype::Get(array('TITLE'),array('ID'=>$iTypeId));
if(empty($arDbl))
{
	return GTAPP::Raise('Document Type not found');
}

if(!$arVariables['SORTBY']) $arVariables['SORTBY'] = 'SORT';
if(!$arVariables['SORTDIR']) $arVariables['SORTDIR'] = 'ASC';
if(!$arVariables['CACHE']) 
	$arVariables['CACHE'] = 'N';
if($arVariables['CACHE'] == 'Y' && !isset($arVariables['CACHE_TIME']))
	$arVariables['CACHE_TIME'] = 3600;
$arVariables['CACHE_TIME'] = (int)$arVariables['CACHE_TIME'];

$nextUrl = $APP->GetCurPage();
$nextUrl .= FALSE===strpos('?',$nextUrl)?'?':'&amp;';

$r = array();
if(!$iNId){
	if($iSTId){
		$thisST = GTdblocksubtype::Get('*',array('ID'=>$iSTId),$arVariables['SORTBY']);
		$thisST = $thisST[0];
		$arCriteria['PARENT']=$iSTId;
		unset($arVariables['SUBTYPE']);
	}
	$r = GTdblocksubtype::Get('*',$arCriteria,$arVariables['SORTBY']);
	if(empty($r)){
		if($iSTId)
			$APP->SetPageTitle($thisST['NAME'],true);
		$arCriteria['SUBTYPE']=$iSTId;
		if ($arVariables['PAGINATION'] == 'Y') {
			$iStart = ($iLimit * $iPage) - $iLimit;

			$r = GTDBlock::Get('*', $arCriteria, array($arVariables['SORTBY'],$arVariables['SORTDIR']), Array($iStart, $iLimit), array('PROP_KEY','ID'),$iPages);
			
			$arResult['PPT'] = renderPager(userCleanQUrl(), $iPages, $iPage, $iLimit);
		} else {
			$r = GTDBlock::Get('*', $arCriteria, array($arVariables['SORTBY'],$arVariables['SORTDIR']), Array(0, $iLimit), array('PROP_KEY','ID')); 
		}
		$arResult['NEWS'] = array();
		foreach($r as $_k=>$_v){
			$_v['NEXT_URL'] = $nextUrl.'ITEM='.$_v['ID'];
			$arResult['NEWS'][$_k]=$_v;
		}
		
	}else{
		if($iSTId)
			$APP->addToBreadCrumbs($thisST['NAME'],$nextUrl.'ID='.$_v['ID']);
		$arResult['SUBTYPES'] = array();
		foreach($r as $_k=>$_v){
			$_v['NEXT_URL'] = $nextUrl.'ID='.$_v['ID'];
			$arResult['SUBTYPES'][$_k]=$_v;
		}
	}
}else{
	$arCriteria['ID']=$iNId;
	$r = GTdblock::Get('*',$arCriteria,$arVariables['SORTBY']);
	foreach($r as $_k=>$_v){
		$_v['NEXT_URL'] = $nextUrl.'ITEM='.$_v['ID'];
		$arResult['NEWS'][$_k]=$_v;
		$APP->SetPageTitle($_v['TITLE'],true);
	}
	$arResult['BACK_URL']=$nextUrl.'ID='.$_v['SUBTYPE'];
	$thisST = GTdblocksubtype::Get('*',array('ID'=>$_v['SUBTYPE']),$arVariables['SORTBY']);
	$thisST = $thisST[0];
	$APP->addToBreadCrumbs($thisST['NAME'],$nextUrl.'ID='.$thisST['ID']);
}

$APP->IncludeComponentTemplate($comTemplate);
?>