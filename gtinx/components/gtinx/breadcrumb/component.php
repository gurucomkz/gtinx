<?
	
$cdir = $APP->entryDir;
$items = array();
$stop = $havefile = false;
$auxTypes = array('');
if($APP->Conf('multilang_sync'))
	array_unshift($auxTypes,'.'.SITE_LANG);
do {
	if (strlen(stripslashes($cdir)) > 1 ) {
		foreach($auxTypes as $auxType){
			$dirDescFile = GTDOCROOT . '/' . $cdir . '/.section' . $auxType . '.php';
			$fEx = file_exists($dirDescFile);
			if($auxType && !$fEx) continue;
			$curdesc = capitalize(basename($cdir));
			if($fEx){
				$SECTION = array();
				@include ($dirDescFile);
				if(isset($SECTION['TITLE']))
					$curdesc = $SECTION['TITLE'];
			}
			$items[] = array('URL'=>preg_replace('/\/{2,}/','/',SITE_DIR.$cdir.'/'),'TITLE'=>$curdesc);
			if(!empty($SECTION)) 
				break;
		}
	}else { $stop = true; break; }
	$cdir = dirname($cdir);
} while (!$stop);

if(count($items) && $items[count($items)-1]['URL'] == SITE_DIR)
	array_pop($items);
//print_r($items);
$arResult['CHAIN'] = array_reverse($items);
$aux = $APP->getBreadCrumbs();
foreach($aux as $auxb)
	$arResult['CHAIN'][] = array('URL'=>$auxb[1],'TITLE'=>$auxb[0]);

if(!$arVariables['INCLUDE_MAIN'] && count($arResult['CHAIN'])<1) return;
$APP->IncludeComponentTemplate($comTemplate);
?>