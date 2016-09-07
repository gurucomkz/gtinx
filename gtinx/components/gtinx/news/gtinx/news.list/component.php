<?php

/*
	COMPLEX COMPONENT DESCEDANT
	NEWS.LIST

	TODO:
	
	+ поддержка DISPLAY_PROPERTIES
	поддержка DISPLAY_VALUES
	+ сортировка
*/

// init

$iTypeId = $arVariables['TYPE'];
$iGroupId = $arVariables['GROUP'];
$iSubTypeId = $arVariables['SUBTYPE'];
$iLimit = (int)$arVariables['PAGINATION_LIMIT'] or 2;
$iPage = (int)$_GET['page'];
if($iPage<1)$iPage = 1;

// body
$arCriteria = Array('ACTIVE' => 1);
if($iTypeId || $iGroupId || $iSubTypeId){
	if($iTypeId) $arCriteria['TYPE_ID']=$iTypeId;
	if($iGroupId) $arCriteria['GROUP_ID']=$iGroupId;
	if($iSubTypeId) $arCriteria['SUBTYPE']=$iSubTypeId;
}
if($arVariables['FORCE_LANG']) $arCriteria['FORCE_LANG'] = true;
if(!$arVariables['SORTBY']) $arVariables['SORTBY'] = 'SORT';
if(!$arVariables['SORTDIR']) $arVariables['SORTDIR'] = 'ASC';
if ($arVariables['PAGINATION'] == 'Y') {

	//$arRes = GTDBlock::Get('*', $arCriteria, array($arVariables['SORTBY'],$arVariables['SORTDIR']),false, array('PROP_KEY','ID'));
	
	$iStart = ($iLimit * $iPage) - $iLimit;

	$arRes = GTDBlock::Get('*', $arCriteria, array($arVariables['SORTBY'],$arVariables['SORTDIR']),	Array($iStart, $iLimit), array('PROP_KEY','ID'),$iPages);
	
	//$iPages = count($arRes);
	
	$arResult['PPT'] = renderPager(userCleanQUrl(), $iPages, $iPage, $iLimit);
} else {
	$arRes = GTDBlock::Get('*', $arCriteria, array($arVariables['SORTBY'],$arVariables['SORTDIR']), Array(0, $iLimit), array('PROP_KEY','ID'));
}
//d($arRes);
$txt=str_replace("/kz","",$_SERVER['REQUEST_URI']."?ID=#ID#");
foreach ($arRes as $val)
{
	if($val['LINK']!=$txt)
	{
		GTdblock::UpLink($txt,$val['ID']);
	}

}
//assign urls
$arResUrls = array();
$nextUrl = $APP->GetCurPage();
$nextUrl .= FALSE===strpos('?',$APP->GetCurPage(false))?'?':'&amp;';


$arVariables['DISPLAY_PROPERTIES'] = verifyArray($arVariables['DISPLAY_PROPERTIES']);
$arIds = array();
foreach($arRes as $_k => $_v){
	if ($arVariables['COMMENTS'] == 'Y')
		$arIds[] = $_v['ID'];
	$_v['URL'] = $nextUrl.$arVariables['TYPE_VAR'].'='.urlencode($_v[$arVariables['TYPE_VAR']]);
	if(empty($arVariables['DISPLAY_PROPERTIES']))
		$_v['DISPLAY_PROPERTIES'] = $_v['PROPERTIES'];
	else{
		foreach($arVariables['DISPLAY_PROPERTIES'] as $_pkey=>$_prop)
			$_v['DISPLAY_PROPERTIES'][$_pkey] = $_prop;
	}
	$arResX[$_k]=$_v;
}
$arRes = $arResX;
if ($arVariables['COMMENTS'] == 'Y' && modulePresent('comments')) {
	$coms = GTComments::count('dblock', $arIds);
	$arResult['ENABLE_COMMENTS'] = "Y";
	foreach($arRes as $arItem) {
		$arItem['COMMENTS_COUNT'] = $coms[$arItem['ID']];
		$arOut[] = $arItem;
	}
} else {
	$arOut = $arRes;
	$arResult['ENABLE_COMMENTS'] = "N";
	$arVariables['COMMENTS'] = "N";
}

$arResult['NEWS'] = $arOut;

$APP->IncludeComponentTemplate($comTemplate);

?>