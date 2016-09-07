<?
////ADMIN INDEX
define('GTDOCROOT',rtrim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]), "/ "));
define('GTROOT',GTDOCROOT.'/gtinx');
define("GT_ADMIN",true);

require(GTROOT.'/start.php');




define('ADMIN_SKIN_PATH','/gtinx/admin/skins/.default');
define('STDITEMICON',ADMIN_SKIN_PATH.'/images/itemicon.gif');
define('STDGRPICON',ADMIN_SKIN_PATH.'/images/rpgicon.gif');
if($APP->UserAble('SU')){
	//routing
	$sRunMod = secMatch($_REQUEST['mod']);
	if(!$sRunMod) $sRunMod = 'core';
	$APP->AdmLoadModule($sRunMod);

	//fetch modules

	$hndModDir = opendir(GTROOT.'/modules');
	while($sOneDir = readdir($hndModDir)){
		if($sOneDir!='.' && $sOneDir!='..' && is_dir(GTROOT.'/modules/'.$sOneDir))
			$APP->LoadModuleAdmData($sOneDir);
	}
	closedir($hndModDir);

	require(GTDOCROOT.ADMIN_SKIN_PATH.'/skin.php');
}else{
	$APP->RequireAuth();
}
$APP->Omega();
?>