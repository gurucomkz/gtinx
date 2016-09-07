<?php
//init
$sBackUrl = $arVariables['BACK_URL'];
$sQParam = $_GET[$arVariables['TYPE_NAME']];
if(isset($arVariables['TYPE_NAME'])) 	{GTApp::Raise("Use of deprecated TYPE_NAME. Use TYPE_VAR instead");  $arVariables['TYPE_VAR'] = $arVariables['TYPE_NAME'];	}

//body
$arRes = GTdblock::Get('*',Array($arVariables['TYPE_VAR'] => $sQParam, 'ACTIVE' => 1), 'SORT', Array(0,1));
$txt=$_SERVER['REQUEST_URI']."?ID=#ID#";
foreach ($arRes as $val)
{
	if($val['LINK']!=$txt)
	{
		GTdblock::UpLink($txt,$val['ID']);
	}

}
$arResult['CONTENT'] = $arRes[0];
$arResult['BACK_URL'] = $sBackUrl;
$APP->IncludeComponentTemplate($comTemplate);
if($arVariables['COMMENTS']=='Y'){
	$APP->IncludeComponent("gtinx:comments", ".default", Array('ACTION_URL' => $APP->GetCurPage(false,false), 'COMMENTS_SUBSYSTEM' => 'dblock', 'COMMENTS_ELEMENT_ID' => $arRes[0]['ID'], 'COMMENTS_LIMIT' => 5));
}
?>