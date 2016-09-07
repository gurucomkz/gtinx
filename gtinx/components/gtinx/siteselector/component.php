<?


$sq = cachedQuery("SELECT * FROM `g_sites` WHERE `site_enabled`='1' ORDER BY `site_default` DESC");
$arResult = $preResult = array();
foreach($sq as $site)
{
	$ITEM = array(
		'NAME'=>$site['site_name'],
		'DOMAINS'=>verifyArray($site['site_domains']),
		'CURRENT'=>0,
		'LANG'=>trim($site['site_lang'])
		);
	$ITEM['DIR'] = $site['site_dir']{0}=='/' ? $site['site_dir'] : '/'.$site['site_dir'];
	//if($site['site_domains'] != ''){
		//FIXME!!! need domain distinguish
	//}
	//else{
		//if(strpos($ITEM['DIR'],$_SERVER['REQUEST_URI'])===0) 
		//	$ITEM['CURRENT'] = 1;
	//}
	if($APP->Conf('multilang_sync')){
		$ITEM['URL'] = 
			$ITEM["DIR"].stripSiteDir($APP->GetCurPage(false,false));
	}else{
		$ITEM['URL'] = 
			(is_array($ITEM['DOMAINS']) && strlen($ITEM['DOMAINS'][0]) > 0 || strlen($arSite['DOMAINS']) > 0?'http://':'').
			(is_array($ITEM["DOMAINS"]) ? $ITEM["DOMAINS"][0] : $ITEM["DOMAINS"]).
			$ITEM["DIR"];
	}
	$ITEM['URL'] = preg_replace('/\/{2,}/','/',$ITEM['URL']);
		
	$preResult[] = $ITEM;
}
function __SiteSelectorCmp($a,$b){
	$al =strlen($a['DIR']);
	$bl =strlen($b['DIR']);
	if( $al == $bl ) return 0;
	return ( $al > $bl )? -1 : 1;
}
usort($preResult, "__SiteSelectorCmp");
//
$haveCurrent = false;
foreach($preResult as $ITEM){
	if(strpos($_SERVER['REQUEST_URI'],$ITEM['DIR'])===0 && !$haveCurrent) 
		$ITEM['CURRENT'] = $haveCurrent = 1;
	$arResult['SITES'][] = $ITEM;
}

$APP->IncludeComponentTemplate($comTemplate);
?>