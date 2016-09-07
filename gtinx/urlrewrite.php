<?
define('GT_HRU',true);
define('GTDOCROOT',rtrim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]), "/ "));
define('GTROOT',GTDOCROOT.'/gtinx');

require(GTROOT.'/start.php');


if($APP->Conf('multilang_sync')){
	$EP = preg_replace('/^'.str_replace('/','\/',SITE_DIR).'/','',$APP->GetCurPage(false));
	if(!preg_match('/\.php$/',$EP)) 
		$EP .= '/index.php';
	$EPx = preg_replace('/\.php$/','.'.SITE_LANG.'.php',$EP);
	if(file_exists(GTDOCROOT.'/'.$EPx))
		include(GTDOCROOT.'/'.$EPx);
	else
		include(GTDOCROOT.'/'.$EP);
}
?>