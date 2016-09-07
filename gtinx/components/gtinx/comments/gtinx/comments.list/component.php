<?php
// init
global $sParent, $iParentId;
$sParent = addslashes($arVariables['SUBSYSTEM']);
$iParentId = (int)$arVariables['ELEMENT_ID'];
(int)$iLimit = $arVariables['LIMIT'];
(int)$iPage = (isset($_GET['page']))?secMatch($_GET['page']):1;
$arResult['REPLY_URL'] = userCleanQUrl(Array('REPLY_ID'));
// body
$arRes = $DB->Query("SELECT * FROM `g_comments` WHERE ELEMENT_ID={$iParentId} AND SUBSYSTEM='{$sParent}' AND LEVEL=1");


$iStart = $iPage * $iLimit - $iLimit;
$iEnd = $iStart + $iLimit;
$arResult['PPT'] = renderPager($_SERVER['PHP_SELF'], $DB->numRows($arRes), $iPage, $iLimit);

$arRes = $DB->Query("SELECT * FROM `g_comments` WHERE ELEMENT_ID={$iParentId} AND SUBSYSTEM='{$sParent}' AND LEVEL=1 LIMIT $iStart, $iLimit");
while ($arRow = $DB->fetchAssoc($arRes)) {
    $arTest[] = $arRow['ID'];
    $arParents[] = $arRow;
}
$arChildComment = array();
if(!empty($sTest)){
	$sTest = implode(',', $arTest);
	$arRes = $DB->Query("SELECT * FROM `g_comments` c LEFT JOIN `g_comments_i` ci ON (c.ID=ci.ID) WHERE ci.CID IN($sTest) ORDER BY c.LEVEL DESC");
	//d("SELECT * FROM `g_comments` c LEFT JOIN `g_comments_i` ci ON (c.ID=ci.ID) WHERE ci.CID IN($sTest) ORDER BY c.LEVEL DESC");

	$c = array();
	while ($arRow = $DB->fetchAssoc($arRes)) {
		if (!empty($c[$arRow['ID']])) {
			$arRow['CHILDREN'] = $c[$arRow['ID']];
		}
		$c[$arRow['PARENT']][$arRow['ID']] = $arRow;
	}

	$arChildComment = $c;

}
foreach($arParents as $ik => $sParent) {
	$chd = childLine($arChildComment[$sParent['ID']]);
	$arOut[] = $sParent;
	if (is_array($chd)) {
		$arOut = array_merge($arOut, $chd);
	}
}
function childLine($arr, &$arrOut)
{
    if (is_array($arr)) {
        foreach($arr as $arrItem) {
            $arChild = $arrItem['CHILDREN'];
            unset($arrItem['CHILDREN']);
            $arrOut[] = $arrItem;
            if (!empty($arChild)) {
                childLine($arChild, &$arrOut);
            }
        }
        return $arrOut;
    }
}

$arResult['COMMENT'] = $arOut;

$APP->IncludeComponentTemplate($comTemplate);

?>