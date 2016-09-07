<?php

/**
 */
class core {
    /**
     * Constructor
     */
    function __construct()
    {
        global $APP;
        $act = ($_GET['act']) ? secMatch($_GET['act']) : 'default';
        $act = '__' . $act;
        if (method_exists('core', $act)) $this->$act();
        else $this->__default();
        $APP->SetPageTitle('Dummy function');
    }

    function __default()
    {
        global $APP, $arrModResult;
        $arrModResult['modHeader'] = Array(
            "NAME" => "Название",
            "SORT" => "Сорт.",
            "ACTIVE" => "Акт.",
            "ELEMENTS" => "Элементов",
            "RAZDELS" => "Разделов",
            "DATE" => "Дата изм.",
            "ID" => "ID"
            );
        $arrModResult['modContent'][] =
        Array(
            "NAME" => "Название конт.",
            "SORT" => "Сорт. конт.",
            "ACTIVE" => "Акт. кот.",
            "ELEMENTS" => "Элементов конт.",
            "RAZDELS" => "Разделов конт.",
            "DATE" => "Дата изм. конт.",
            "ID" => "ID конт."
            );
        $arrModResult['modContent'][] =
        Array(
            "NAME" => "Название конт.",
            "SORT" => "Сорт. конт.",
            "ACTIVE" => "Акт. кот.",
            "ELEMENTS" => "Элементов конт.",
            "RAZDELS" => "Разделов конт.",
            "DATE" => "Дата изм. конт.",
            "ID" => "ID конт."
            );
        $arrModResult['modContent'][] =
        Array(
            "NAME" => "Название конт.",
            "SORT" => "Сорт. конт.",
            "ACTIVE" => "Акт. кот.",
            "ELEMENTS" => "Элементов конт.",
            "RAZDELS" => "Разделов конт.",
            "DATE" => "Дата изм. конт.",
            "ID" => "ID конт."
            );
        $arrModResult['modContent'][] =
        Array(
            "NAME" => "Название конт.",
            "SORT" => "Сорт. конт.",
            "ACTIVE" => "Акт. кот.",
            "ELEMENTS" => "Элементов конт.",
            "RAZDELS" => "Разделов конт.",
            "DATE" => "Дата изм. конт.",
            "ID" => "ID конт."
            );
        $arrModResult['modContent'][] =
        Array(
            "NAME" => "Название конт.",
            "SORT" => "Сорт. конт.",
            "ACTIVE" => "Акт. кот.",
            "ELEMENTS" => "Элементов конт.",
            "RAZDELS" => "Разделов конт.",
            "DATE" => "Дата изм. конт.",
            "ID" => "ID конт."
            );

        $APP->admModSetUrl('act=edit&id=', 'edit');
        $APP->admModSetUrl('moded=add', 'add');

        $APP->addControlUserButton('test button', 'testparam=1');

        $APP->admModDetemineActions(Array('AdD', 'LIST', 'EDIT', 'DELETE'));
        $APP->admModShowElements($arrModResult['modHeader'],
            $arrModResult['modContent'],
            "list",
            Array('NAME', 'SORT', 'ID', 'PARENT' => Array('param1', 'param2', 'param3', 'param4')),
            $DHTML
            );
    }

    function __edit()
    {
        global $APP , $arrModResult;
        $APP->admModSetUrl('moded=action', 'action');
        $DHTML = '<table>
<tr>
<td>222</td>
<td>333</td>
</tr>
</table>
';
        $arrModResult['modHeader'] = Array(
            GetMessage("MAIN") =>  Array(
                Array("DESC" => "Название", "NAME" => "NAME", "TYPE" => "string", "VALUE" => "Тестовое название"),
                Array("DESC" => "Сорт.", "NAME" => "SORT", "TYPE" => "string", "VALUE" => "500"),
                Array("DESC" => "Акт.", "NAME" => "ACTIVE", "TYPE" => "radio", "VALUE" => Array("1" => 'Да', 2 => 'Нет:checked', 3 => 'Подумаю')),
                Array("DESC" => "Элементов", "NAME" => "ELEMENTS", "TYPE" => "string", "VALUE" => "20"),
                Array("DESC" => "Разделов", "NAME" => "RAZDELS", "TYPE" => "string", "VALUE" => "30"),
                Array("DESC" => "Дата изм.", "NAME" => "DATE", "TYPE" => "string", "VALUE" => '111'/*date('h.m.y')*/),
                Array("DESC" => "Выпадающий списог", "NAME" => "LISTING", "TYPE" => "select:multiple:test,3", "VALUE" => Array('test' => 'оптион 1', '2' => 'оптион2', '3' => 'оптион3')),
                Array("DESC" => "ID", "NAME" => "ID", "TYPE" => "text", "VALUE" => "33"),
                Array("DESC" => "Чекбокс", "NAME" => "ckecked", "TYPE" => "checkbox", "VALUE" => 'val2:checked'),
                Array("DESC" => "Чекбокс1", "NAME" => "ckecked1", "TYPE" => "checkbox", "VALUE" => 'val1')
                ),

            GetMessage("DHTML")=>$DHTML);
//	$arrModResult['modHeader'] = Array(
//
//                Array("DESC" => "Название", "NAME" => "NAME", "TYPE" => "string", "VALUE" => "Тестовое название"),
//                Array("DESC" => "Сорт.", "NAME" => "SORT", "TYPE" => "string", "VALUE" => "500"),
//                Array("DESC" => "Акт.", "NAME" => "ACTIVE", "TYPE" => "radio", "VALUE" => Array("1" => 'Да', 2 => 'Нет:checked', 3 => 'Подумаю')),
//                Array("DESC" => "Элементов", "NAME" => "ELEMENTS", "TYPE" => "string", "VALUE" => "20"),
//                Array("DESC" => "Разделов", "NAME" => "RAZDELS", "TYPE" => "string", "VALUE" => "30"),
//                Array("DESC" => "Дата изм.", "NAME" => "DATE", "TYPE" => "string", "VALUE" => '111'/*date('h.m.y')*/),
//                Array("DESC" => "Выпадающий списог", "NAME" => "LISTING", "TYPE" => "select:multiple:test,3", "VALUE" => Array('test' => 'оптион 1', '2' => 'оптион2', '3' => 'оптион3')),
//                Array("DESC" => "ID", "NAME" => "ID", "TYPE" => "text", "VALUE" => "33"),
//                Array("DESC" => "Чекбокс", "NAME" => "ckecked", "TYPE" => "checkbox", "VALUE" => 'val2:checked'),
//                Array("DESC" => "Чекбокс1", "NAME" => "ckecked1", "TYPE" => "checkbox", "VALUE" => 'val1')
//                );

        $APP->admModShowElements($arrModResult['modHeader'], '', "edit", '', 'tabs');
    }
}

?>