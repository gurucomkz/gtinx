<?

if($_GET['q'])
{
	$arVariables['QUESTION']=$_GET['q'];
}
if($_GET['ID'])
{
	$arVariables['ID']=$_GET['ID'];
	if($_GET['LANG'])
	{$arVariables['LANG']=$_GET['LANG'];}
	$sQParam=$arVariables['ID'];
}
if(!$arVariables['BACK_URL'])
	$arVariables['BACK_URL'] = $APP->GetCurPage('');
$comTemplate='.default';
if (isset($sQParam)) {
    $APP->IncludeComponent("gtinx:search:gtinx:search.detail", $comTemplate, $arVariables );
} else {
	$APP->IncludeComponent("gtinx:search:gtinx:search.list", $comTemplate, $arVariables );
}
?>