<?

$MANIFEST = array(
	'BACK_URL'=>array('string'),
	'TYPE_NAME'=>array('string'),
	'NEXT_URL'=>array('string'),
	'SHOW_DATE'=>array('enum',array('N','Y')),
	'PAGINATION'=>array('enum',array('N','Y')),
	'COMMENTS'=>array('enum',array('N','Y')),
	'LIMIT'=>array('int'),
	'PAGER_ACTIVE'=>array('enum',array(0,1)),
	'PAGER_ITEMS'=>array('int'),
);
if(LoadClass("GTDbt")){
	$arX = GTDbt::Get(array("ID",'NAME'));
	foreach($arX as $arI)
	{
		$MANIFEST['TYPE_NAME'][1][$arI['ID']] = $arI['NAME'];
	}
}
?>