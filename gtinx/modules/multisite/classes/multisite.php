<?php

class GTMultisite{

	function __default(){
		global $APP;
		$DB = $APP->DB;
		$arModResult['modHeader'] = Array(
            "ID" => GetMessage('SITE_ID'),
            "DIR" => GetMessage('SITE_DIR'),
            "DOMAINS" => GetMessage('SITE_DOMAINS'),
            "TEMPLATE" => GetMessage('SITE_TEMPLATE'),
            "LANG" => GetMessage('SITE_LANG'),
            "NAME" => GetMessage('SITE_NAME'),
            "ENABLED" => GetMessage('SITE_ENABLED'),
            "DEFAULT" => GetMessage('SITE_DEFAULT')
            );
		$APP->admModSetUrl('act=edit&id=', 'edit');
		$APP->admModSetUrl('moded=add', 'add');
		$hRes = $DB->Query('SELECT * FROM `g_sites`');
		while($arRes = $DB->fetchAssoc($hRes)){
			$arModResult['modContent'][] = $arRes;
			//d($arRes);
		}

		$APP->addControlUserButton('test button', 'testparam=1');

		$APP->admModDetemineActions(Array('AdD', 'LIST', 'EDIT', 'DELETE'));
		$APP->admModShowElements($arModResult['modHeader'],
            $arModResult['modContent'],
            "list",
            Array('NAME', 'SORT', 'ID', 'PARENT' => Array('param1', 'param2', 'param3', 'param4'))
            );
	}
}

?>