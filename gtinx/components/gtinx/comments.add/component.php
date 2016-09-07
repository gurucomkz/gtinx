<?php
// init
if(!$arVariables['ACTION_URL'])
	$arVariables['ACTION_URL'] = $APP->GetCurrentPage(false,false);
$sParent = $arVariables['PARENT'];
$iParentId = $arVariables['PARENT_ID'];
$iAuthorId = $APP->GetCurrentUserID();
$sCode = secMatch($_POST['code']);
// body

function checkCaptcha($sCode){
	return true;
}

function checkAuth($iUserId){
	return true;
}
$arResult['USERAUTH']=checkAuth($iAuthorId);
$sComment = htmlspecialchars(addslashes($_POST['comment']));
if (isset($_POST['submit']) && empty($sComment)) {
	$arResult['ERRORS'][]="Поле сообщения пустое";
}elseif(!checkCaptcha()){
	$arResult['ERRORS'][]="Проверочный код не верен!";
}
else $DB->Query("INSERT INTO `g_comments` (C_TEXT, C_ID, AUTHOR_ID, P_NAME, P_ID, CREATED, UPDATED) VALUES ('{$sComment}', '0', $iAuthorId, '$sParent', $iParentId, " . time() . ", " . time() . ")");

$APP->IncludeComponentTemplate($comTemplate);

?>