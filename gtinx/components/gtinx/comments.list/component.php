<?php
//init
$sParent = $arVariables['PARENT'];
$iParentId = $arVariables['PARENT_ID'];

//body
$arRes = $DB->Query("SELECT * FROM `g_comments` WHERE P_ID={$iParentId} AND P_NAME='{$sParent}'");
//echo "SELECT * FROM `g_comments` WHERE P_ID={$iParentId} AND P_NAME='{$sParent}'";

while($arRow = $DB->fetchArray($arRes)){
	$arResult['COMMENT'][] = $arRow;
}

//print_r($arReturn);

$APP->IncludeComponentTemplate($comTemplate);
?>