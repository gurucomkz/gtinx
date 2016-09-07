<?php

/*
	STANDALONE COMPONENT
	NEWS.LIST

*/

if(isset($arVariables['DBLOCK_TYPE_ID'])) 	{$APP->Raise("Use of deprecated DBLOCK_TYPE_ID. Use TYPE instead");  		$arVariables['TYPE'] = $arVariables['DBLOCK_TYPE_ID'];	}
if(isset($arVariables['LIMIT'])) 		{$APP->Raise("Use of deprecated LIMIT. Use PAGINATION_LIMIT instead");  $arVariables['PAGINATION_LIMIT'] = $arVariables['LIMIT'];	}
if(isset($arVariables['TYPE_NAME'])) 	{$APP->Raise("Use of deprecated TYPE_NAME. Use TYPE_VAR instead");  $arVariables['TYPE_VAR'] = $arVariables['TYPE_NAME'];	}

$iTypeId = (int)$arVariables['TYPE'];
$iGroupId = (int)$arVariables['GROUP'];
$iSubTypeId = (int)$arVariables['SUBTYPE'];
$iLimit = (int)$arVariables['PAGINATION_LIMIT'];
$iPage = (int)$_GET['page'];
if($iPage<1) $iPage = 1;

// body
$arCriteria = Array('ACTIVE' => 1 /*,'GROUP_ACTIVE'=>1*/);
if($iTypeId || $iGroupId || $iSubTypeId){
	if($iTypeId) $arCriteria['TYPE_ID']=$iTypeId;
	if($iGroupId) $arCriteria['GROUP_ID']=$iGroupId;
	if($iSubTypeId) $arCriteria['SUBTYPE']=$iSubTypeId;
}

if($arVariables['FORCE_LANG']) $arCriteria['FORCE_LANG'] = true;
if(!$arVariables['SORTBY']) $arVariables['SORTBY'] = 'SORT';
if(!$arVariables['SORTDIR']) $arVariables['SORTDIR'] = 'ASC';
if(!$arVariables['CACHE']) 
	$arVariables['CACHE'] = 'N';
if($arVariables['CACHE'] == 'Y' && !isset($arVariables['CACHE_TIME']))
	$arVariables['CACHE_TIME'] = 3600;
$arVariables['CACHE_TIME'] = (int)$arVariables['CACHE_TIME'];

//d($arCriteria);	
//assign urls
$arResUrls = array();
$nextUrl = $arVariables['NEXT_URL'];
$nextUrl .= FALSE===strpos('?',$nextUrl)?'?':'&amp;';

$stored = false;
$arRes = false;
$cacheName = 
	'news.list:'.
	$arVariables['TYPE'].':'.
	$arVariables['GROUP'].':'.
	$arVariables['SUBTYPE'].':'.
	$arVariables['SORTBY'].':'.
	$arVariables['SORTDIR'].':'.
	$arVariables['PAGINATION'].':'.
	$arVariables['PAGINATION_LIMIT'].':'.
	$iPage.':'.
	$arVariables['NEXT_URL'];

if($arVariables['CACHE']=='Y' && $arVariables['CACHE_TIME']>=0){
	$stored = cacheGetVars($cacheName,$arVariables['CACHE_TIME']);
	if($stored && is_array($stored))
		list($arRes,$iPages,$arResult['PPT']) = $stored;
}
if($arVariables['FILTER_BY'] && $arVariables['FILTER_PARAM'] && trim(PassedVal($arVariables['FILTER_PARAM']))!==''){
	$arCriteria[$arVariables['FILTER_BY']] = '~%'.PassedVal($arVariables['FILTER_PARAM']).'%';
}

if(!$arRes){
	if ($arVariables['PAGINATION'] == 'Y') {
		$iStart = ($iLimit * $iPage) - $iLimit;

		$arRes = GTDBlock::Get('*', $arCriteria, array($arVariables['SORTBY'],$arVariables['SORTDIR']), Array($iStart, $iLimit), array('PROP_KEY','ID'),$iPages);

		$arResult['PPT'] = renderPager(userCleanQUrl(), $iPages, $iPage, $iLimit);
	} else {
		$arRes = GTDBlock::Get('*', $arCriteria, array($arVariables['SORTBY'],$arVariables['SORTDIR']), Array(0, $iLimit), array('PROP_KEY','ID'));
	}
	if($arVariables['CACHE']=='Y' && $arVariables['CACHE_TIME']>=0)
		cacheSetVars($cacheName, array($arRes,$iPages,$arResult['PPT']));

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
//d($arVariables);
$arVariables['DISPLAY_PROPERTIES'] = verifyArray($arVariables['DISPLAY_PROPERTIES']);
$arIds = array();
foreach($arRes as $_k =>$_v){
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