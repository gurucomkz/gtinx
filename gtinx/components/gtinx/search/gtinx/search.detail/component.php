<?php
if(isset($arVariables['TYPE_NAME'])) 	{GTApp::Raise("Use of deprecated TYPE_NAME. Use TYPE_VAR instead");  $arVariables['TYPE_VAR'] = $arVariables['TYPE_NAME'];	}

//init
LoadClass("GTDBlock");
$sBackUrl = $arVariables['BACK_URL'];
$sQParam = $_GET[$arVariables['TYPE_VAR']];

//body
if(!empty($arVariables['ID'])){$PARAMS['ID']=$arVariables['ID'];}
if(!empty($arVariables['LANG'])){$PARAMS['LANG']=$arVariables['LANG'];}
$PARAMS['FORCE_LANG']='1';
$arRes = GTDBlock::Get('*',$PARAMS, 'SORT', Array(0,1),array('PROP_KEY'));

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