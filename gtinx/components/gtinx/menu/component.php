<?
//$menuDescFile = GTDOCROOT.'/'.SITE_DIR.'/.menu.'.$arVariables['ROOT_MENU_TYPE'].'.php';
//if(!file_exists($menuDescFile)) 
//	return;

if(!$arVariables['CACHE']) 
	$arVariables['CACHE'] = 'N';
if($arVariables['CACHE'] == 'Y' && !isset($arVariables['CACHE_TIME']))
	$arVariables['CACHE_TIME'] = 3600;
$arVariables['CACHE_TIME'] = (int)$arVariables['CACHE_TIME'];
$cacheName = 
	'menu:'.
	$arVariables['MAX_LEVEL'].':'.
	$arVariables['ROOT_MENU_TYPE'].':'.
	$arVariables['CHILD_MENU_TYPE'].':'.
	$arVariables['MAX_LEVEL'].':'.
	$arVariables['FORCE_ROOT'].':'.
	$APP->GetCurDir()
	;
	
$arVariables['MAX_LEVEL'] = (int)$arVariables['MAX_LEVEL'];
if($arVariables['MAX_LEVEL']==0)$arVariables['MAX_LEVEL'] = 1;

$GTMENUITEMS = false;
if($arVariables['CACHE']=='Y' && $arVariables['CACHE_TIME']>=0){
	$GTMENUITEMS = cacheGetVars($cacheName,$arVariables['CACHE_TIME']);
}

if(!$GTMENUITEMS){
	$APP->registerMenuType($arVariables['ROOT_MENU_TYPE']);
	if($arVariables['CHILD_MENU_TYPE'])
		$APP->registerMenuType($arVariables['CHILD_MENU_TYPE']);
	$GTMENUITEMS = $APP->GetMenuTree($arVariables['ROOT_MENU_TYPE'],$arVariables['CHILD_MENU_TYPE'],1,$arVariables['MAX_LEVEL'],$arVariables['FORCE_ROOT']);	
	if($arVariables['CACHE']=='Y' && $arVariables['CACHE_TIME']>=0)
		cacheGetVars($cacheName,$GTMENUITEMS);
}

$arResult = array();
foreach($GTMENUITEMS as $mItem){
	
	$arResult[] = $mItem;
}
$APP->IncludeComponentTemplate($comTemplate);
?>