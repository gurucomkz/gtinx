<?php

/*
	COMPLEX COMPONENT
	NEWS

*/
/*
	TODO
	+ передавать шаблон в подкомпоненты
	фильтр по группе блоков		GROUP
	фильтр по типу блоков
	фильтр по папке в типе
	абстракция
		имена переменных, которые означают группу/тип/папку
	+ постраничность
	+	вкл/выкл
	+	кол-во элементов на странице
	комментарии
		вкл/выкл
		кол-во элементов на странице
	АВТО: ссылка на список статей	
	АВТО: ссылка на статью статей с форматом
	поддержка DISPLAY_PROPERTIES
	поддержка DISPLAY_VALUES
	+ сортировка
*/

if(isset($arVariables['NEWS_BACK_URL'])) 	{$APP->Raise("Use of deprecated NEWS_BACK_URL. Use BACK_URL instead");	$arVariables['BACK_URL'] = $arVariables['NEWS_BACK_URL'];	}
if(isset($arVariables['NEWS_NEXT_URL'])) 	{$APP->Raise("Use of deprecated NEWS_NEXT_URL. Use NEXT_URL instead");	$arVariables['NEXT_URL'] = $arVariables['NEWS_NEXT_URL'];	}
if(isset($arVariables['DBLOCK_TYPE_ID'])) 	{$APP->Raise("Use of deprecated DBLOCK_TYPE_ID. Use TYPE instead");  		$arVariables['TYPE'] = $arVariables['DBLOCK_TYPE_ID'];	}
if(isset($arVariables['NEWS_LIMIT'])) 		{$APP->Raise("Use of deprecated NEWS_LIMIT. Use PAGINATION_LIMIT instead");  $arVariables['PAGINATION_LIMIT'] = $arVariables['NEWS_LIMIT'];	}
if(isset($arVariables['NEWS_TYPE_NAME'])) 	{$APP->Raise("Use of deprecated NEWS_TYPE_NAME. Use TYPE_VAR instead");  $arVariables['TYPE_VAR'] = $arVariables['NEWS_TYPE_NAME'];	}

if(!$arVariables['BACK_URL'])
	$arVariables['BACK_URL'] = $APP->GetCurPage();

if(0>=(int)$arVariables['PAGINATION_LIMIT'])
	$arVariables['PAGINATION_LIMIT'] = 10;
if(!$arVariables['TYPE_VAR'])
	$arVariables['TYPE_VAR'] = 'ID';
if(!$arVariables['SORTBY']) 
	$arVariables['SORTBY'] = 'SORT';
if(!$arVariables['SORTDIR']) 
	$arVariables['SORTDIR'] = 'ASC';
if(!$arVariables['SHOW_DATE'])
	$arVariables['SHOW_DATE'] = 'Y';
if(!$arVariables['SET_PAGE_TITLE'])
	$arVariables['SET_PAGE_TITLE'] = 'Y';
if(!$arVariables['SHOW_TYPE_DESC'])
	$arVariables['SHOW_TYPE_DESC'] = 'N';
if(!$arVariables['SHOW_GROUP_DESC'])
	$arVariables['SHOW_GROUP_DESC'] = 'N';
if(!$arVariables['SHOW_SUBTYPE_DESC'])
	$arVariables['SHOW_SUBTYPE_DESC'] = 'N';

$sQParam = $_GET[$arVariables['TYPE_VAR']];
if (isset($sQParam)) {
    $APP->IncludeComponent("gtinx:news:gtinx:news.detail", $comTemplate, $arVariables );
	/*Array(
		'BACK_URL' => $arVariables['NEWS_BACK_URL'], 
		'TYPE_NAME' => $arVariables['NEWS_TYPE_NAME'], 
		'COMMENTS'=>$arVariables['NEWS_COMMENTS']
		)*/
} else {
	$APP->IncludeComponent("gtinx:news:gtinx:news.list", $comTemplate, $arVariables );
	/*Array(
		'GROUP' => $arVariables['GROUP'], 
		'TYPE' => $arVariables['TYPE'], 
		'SUBTYPE' => $arVariables['SUBTYPE'], 
		'DBLOCK_TYPE_ID' => $arVariables['DBLOCK_TYPE_ID'], 
		'LIMIT' => $arVariables['NEWS_LIMIT'], 
		'TYPE_NAME' => $arVariables['NEWS_TYPE_NAME'], 
		'NEXT_URL' => $arVariables['NEWS_NEXT_URL'], 
		'COMMENTS'=>$arVariables['NEWS_COMMENTS']
		)*/
}
?>