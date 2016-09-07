<?php
// init
if(!$arVariables['ACTION_URL'])
	$arVariables['ACTION_URL'] = $APP->GetCurrentPage(false,false);
$sParent = $arVariables['SUBSYSTEM'];
$iParentId = $arVariables['ELEMENT_ID'];
$iAuthorId = $APP->GetCurrentUserID();
$sCode = secMatch($_POST['code']);
$iReplyId = (int)$_GET['REPLY_ID'];
// body
function checkCaptcha($sCode)
{
    return true;
}

function checkAuth($iUserId)
{
    return true;
}
$arResult['USERAUTH'] = checkAuth($iAuthorId);
$sComment = htmlspecialchars(addslashes($_POST['comment']));
if (isset($_POST['submit'])) {
    if (empty($sComment)) {
        $arResult['ERRORS'][] = GetMessage('EMPTY_MESSAGE_AREA');
    } elseif (!checkCaptcha()) {
        $arResult['ERRORS'][] = GetMessage('CODE_IS_WRONG');
    } elseif($iReplyId>0) {
        $hRes = $DB->query("SELECT * FROM `g_comments_i` ci WHERE ci.ID=$iReplyId");
    	//d("SELECT * FROM `g_comments` c LEFT JOIN `g_comments_i` ci ON(c.ID=ci.ID) WHERE ci.ID=$iReplyId");
    	$iLevel = $DB->numRows($hRes);
    	$iLevel+=2;
        while ($arRes = $DB->fetchAssoc($hRes)) {
            if (!empty($arRes['ID']) && !empty($arRes['CID'])) {
                $arParents[] = $arRes['ID'];
                $arParents[] = $arRes['CID'];
            }
        }
        $hRes = $DB->Query("INSERT INTO `g_comments` (C_TEXT, PARENT, LEVEL, AUTHOR_ID, SUBSYSTEM, ELEMENT_ID, CREATED, UPDATED) VALUES ('{$sComment}', $iReplyId, $iLevel, $iAuthorId, '$sParent', $iParentId, " . time() . ", " . time() . ")");
        //d("INSERT INTO `g_comments` (C_TEXT, PARENT, LEVEL, AUTHOR_ID, SUBSYSTEM, ELEMENT_ID, CREATED, UPDATED) VALUES ('{$sComment}', $iReplyId, $iLevel, $iAuthorId, '$sParent', $iParentId, " . time() . ", " . time() . ")");
        $iLastId = $DB->insertId($hRes);
        if (is_array($arParents)) {
            foreach(array_unique($arParents) as $sParent) {
                $arrSql[] = "('$iLastId', '$sParent')";
            }
        } elseif ($iReplyId !== 0) $arrSql[] = "('$iLastId', '$iReplyId')";

        if (!empty($arrSql)) {
            $DB->Query("INSERT INTO `g_comments_i` (ID, CID) VALUES " . implode($arrSql, ','));
        	//d("INSERT INTO `g_comments_i` (ID, CID) VALUES " . implode($arrSql, ','));

        }
    }else{
    	$iLevel = 1;
    	$hRes = $DB->Query("INSERT INTO `g_comments` (C_TEXT, PARENT, LEVEL, AUTHOR_ID, SUBSYSTEM, ELEMENT_ID, CREATED, UPDATED) VALUES ('{$sComment}', $iReplyId, $iLevel, $iAuthorId, '$sParent', $iParentId, " . time() . ", " . time() . ")");
    }
}

$APP->IncludeComponentTemplate($comTemplate);

?>