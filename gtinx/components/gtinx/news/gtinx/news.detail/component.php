<?php

/*
	COMPLEX COMPONENT DESCEDANT
	NEWS.DETAIL

*/

/*
	TODO
	комментарии
		вкл/выкл
		кол-во элементов на странице
	АВТО: ссылка на список статей с форматом
	АВТО: ссылка на статью
	поддержка DISPLAY_PROPERTIES
	поддержка DISPLAY_VALUES
*/

if(isset($arVariables['TYPE_NAME'])) 	{GTApp::Raise("Use of deprecated TYPE_NAME. Use TYPE_VAR instead");  $arVariables['TYPE_VAR'] = $arVariables['TYPE_NAME'];	}

//init
LoadClass("GTDBlock");
$sBackUrl = $arVariables['BACK_URL'];
$sQParam = $_GET[$arVariables['TYPE_VAR']];

//body
$arRes = GTDBlock::Get('*',Array($arVariables['TYPE_VAR'] => $sQParam, 'ACTIVE' => 1), 'SORT', Array(0,1),array('PROP_KEY'));

if($arVariables['SET_PAGE_TITLE']=='Y'){
	$APP->SetPageTitle($arRes[0]['TITLE'],true);
}
$arResult['CONTENT'] = $arRes[0];
$arResult['BACK_URL'] = $sBackUrl;
$APP->IncludeComponentTemplate($comTemplate);
if($arVariables['COMMENTS']=='Y'){
	$APP->IncludeComponent("gtinx:comments", $comTemplate, Array('ACTION_URL' => $APP->GetCurPage(false,false), 'COMMENTS_SUBSYSTEM' => 'dblock', 'COMMENTS_ELEMENT_ID' => $arRes[0]['ID'], 'COMMENTS_LIMIT' => 5));
}
?>