<?php

function includeIfExists($f)
{
    global $APP;
    if (file_exists($f))
        include($f);
}

$_CACHE = false;
function cachedQuery($query)
{
    global $DB;
    $cname = md5($query);
    $t = cacheGet($cname);
    if (!empty($t)) return $t;
    $t = array();
    $q = $DB->Query($query);
    while ($r = $DB->fetchArray($q))
    $t[] = $r;
    cacheSet($cname, $t);
    return $t;
}

function cacheSet($var, $data)
{
    global $DB, $_CACHE;
    cacheGet(false);

    $_CACHE[$var] = array('mtime'=>time(),'data'=>$data);
    $filename = GTROOT . '/cache/global.php';

    file_put_contents($filename, serialize($_CACHE));


    @chmod($filename, 0666);
}

function cacheGet($var, $ttl = 3600)
{
	if(defined('SKIP_CACHE'))
		return false;
    global $_CACHE;
    if (false === $_CACHE) {
        $_CACHE = array();
        $filename = GTROOT . '/cache/global.php';

        if (! @filesize($filename)) {
            return false;
        }

        $_CACHE = unserialize(file_get_contents($filename));
    }
	if(!is_array($_CACHE[$var]) || ($_CACHE[$var]['mtime']<time()-$ttl && $ttl!=0))
		return false;
    return $_CACHE[$var]['data'];
}
function cacheSetVars($file, $data)
{
    $file = md5($file);

    $filename = GTROOT . '/cache/' . $file . '.php';

    file_put_contents($filename, serialize($data));

    @chmod($filename, 0666);
}

function cacheGetVars($file, $ttl = 3600)
{
	if(defined('SKIP_CACHE'))
		return false;
    $file = md5($file);
    $filename = GTROOT . '/cache/' . $file . '.php';

    if (! @filesize($filename) || (filemtime($filename)<time()-$ttl && $ttl!=0)) {
        return false;
    }

    return unserialize(file_get_contents($filename));
}

function monthNormalize($x)
{
    $x = (int)$x;
    if (abs($x) > 12) $x %= 12;
    if ($x < 1)
        $x = 12 + $x;
    return $x;
}

$__INFORMERS = 0;
function InformerCode($iid, $warn = false)
{
    global $__INFORMERS;
    $__INFORMERS++;
    $code = '';
    $onClick = '';
    $onMouseUp = '';
    $img = $warn?'exclamation':'question';
    $onMouseDown = " onMouseDown=\"callInformer($iid,$__INFORMERS);this.src='/images/{$img}_mark.gif';\" ";
    $onMouseMove = " onMouseMove=\"this.src='/images/{$img}_mark_over.gif'\" ";
    $onMouseOut = " onMouseOut=\"this.src='/images/{$img}_mark.gif'\" ";

    $code = " <img $onMouseDown $onMouseOut $onMouseMove id=\"informerimage$__INFORMERS\" src=\"/images/{$img}_mark.gif\" width=\"14\" height=\"13\">";

    return $code;
}

function InformerText($iid)
{
    global $TABLES, $CONF;
    $iiq = db_query("SELECT * FROM `$TABLES[informer]` WHERE inf_id='$iid'");
    echo "<div class=plate id=informerplate>";
    if (!db_num_rows($iiq))
        echo "<center><b>Материал не найден ($iid)</b></center>";
    else {
        $ii = db_fetch_array($iiq);
        echo "<b>$ii[inf_title]</b><br>$ii[inf_desc]";
    }

    echo "</div>";
}

function Report($level, $text, $ip = null, $info1 = '', $info2 = '', $info3 = '')
{
    global $TABLES, $CONF, $ADMIN;
    $level = (int)$level;
    $text = addslashes($text);
    $info1 = addslashes($info1);
    $info2 = addslashes($info2);
    $info3 = addslashes($info3);
    if ($ip === null) $ip = get_user_ip();
    if (!empty($ADMIN))
        $info3 = $ADMIN['adm_login'];
    db_query("INSERT INTO `$TABLES[logs]` (`le_level`,`le_timestamp`,`le_ip`,`le_text`,`le_info1`,`le_info2`,`le_info3`) " .
        "VALUES('$level',UNIX_TIMESTAMP(),'$ip','$text','$info1','$info2','$info3')");
}

function getConfBeforeDate($conf_item, $date)
{
    global $TABLES, $CONF;
    $date = (int)$date;

    if (!isset($CONF[$conf_item])) return null;

    $ret = db_qresult("SELECT `$conf_item` FROM `$TABLES[mainconf]` WHERE `conf_version`<='$date' ORDER BY `conf_version` DESC LIMIT 1");

    return $ret;
}

function getConfAfterDate($conf_item, $date)
{
    global $TABLES, $CONF;
    $date = (int)$date;

    if (!isset($CONF[$conf_item])) return null;

    $ret = db_qresult("SELECT `$conf_item` FROM `$TABLES[mainconf]` WHERE `conf_version`>='$date' ORDER BY `conf_version` DESC LIMIT 1");

    return $ret;
}

function ProduceOptList($optarr, $sel = false)
{
    if (empty($optarr)) return '';
    if (is_array($optarr)) {
        $r = '';
        foreach($optarr as $k => $v) {
            $sl = ($sel !== false && $v == $sel)?'selected':'';
            $r .= "<option value=\"" . htmlspecialchars($v) . "\" $sl>" . htmlspecialchars($k) . "</option>";
        }
        return $r;
    }else {
        return "<option value=\"" . htmlspecialchars($optarr) . "\">" . htmlspecialchars($optarr) . "</option>";
    }
}

function getLoadAvg()
{
    $server_load = '';
    if (file_exists('/proc/loadavg')) {
        if ($fh = @fopen('/proc/loadavg', 'r')) {
            $data = @fread($fh, 6);
            @fclose($fh);

            $load_avg = explode(" ", $data);

            $server_load = trim($load_avg[0]);
        }
    }
    return $server_load;
}

function stripJunk($str)
{
    $str = strip_tags($str);
    // strip parts of tags
    $str = ereg_replace('^(.*)\>', '', $str);
    $str = ereg_replace('\<(.*)$', '', $str);
    // strip parts of words
    $str = substr($str, strpos($str, ' ') + 1);
    $str = substr($str, 0, strrpos($str, ' '));

    return $str;
}

function GTSize($size)
{
    global $SZSUFFIX, $SZAMOUNT;
    if ($size >= $SZAMOUNT['g']) {
        $suff = $SZSUFFIX['g'];
        $ms = $size / $SZAMOUNT['g'];
    } elseif ($size >= $SZAMOUNT['m']) {
        $suff = $SZSUFFIX['m'];
        $ms = $size / $SZAMOUNT['m'];
    } elseif ($size >= $SZAMOUNT['k']) {
        $suff = $SZSUFFIX['k'];
        $ms = $size / $SZAMOUNT['k'];
    } else {
        $suff = $SZSUFFIX['b'];
        $ms = $size;
    }
    $ms = round($ms, 3);

    return $ms . " " . $suff;
}

function GTTime($sec)
{
    global $TMSUFFIX, $TMAMOUNT;
    $ret = '';
    $amounts = array_reverse($TMAMOUNT, true);
    foreach($amounts as $_l => $_a) {
        if ($sec < $_a) continue;
        $ret .= floor($sec / $_a) . ' ' . $TMSUFFIX[$_l] . ' ';
        $sec %= $_a;
    }
    return $ret;
}

function PassedVal($k)
{
    if ($_GET[$k] != '') $a = $_GET[$k];
    else
    if ($_POST[$k] != '') $a = $_POST[$k];
    else $a = false;
    return $a;
}

function putConfItem($name,
    $desc,
    $type = 'text',
    $value = '',
    $tinydesc = '',
    $checked = false, // dropdown, radio, checkbox
    $orig = 'hor', // for radio
    $cols = '20', // textarea
    $rows = '6' // textarea
    )
{
    global $__PM_TABLEcolor, $COLORS;
    $name = trim($name);
    if (!$name && $type != 'raw') return;
    $Lc = $desc . '<br><small>' . $tinydesc . '</small>';
    $Rc = '';
    switch ($type) {
        case 'raw' : $Rc = $value;
            break;
        case 'text': $Rc = "<input type='text' name=\"$name\" value=\"$value\">";
            break;
        case 'passwd': $Rc = "<input type='password' name=\"$name\" value=\"$value\">";
            break;
        case 'file': $Rc = "<input type='file' name=\"$name\" >";
            break;
        case 'textarea': $Rc = "<textarea name='$name' cols='$cols' rows='$rows'>$value</textarea>";
            break;
        case 'checkbox': $ch = ($checked)? 'checked':'';
            $Rc = "<input type=\"checkbox\" name=\"$name\" value=\"$value\" $ch>";
            break;
        // case 'radio': $Rc = "<input type='text' name='$name' value='$value'>";
        // break;
        case 'dropdown': $Rc = "<select size='1' name=\"$name\">";
            foreach($value as $val => $text) {
                $ch = ($checked == $val)?'selected':'';
                $Rc .= "<option value=\"$val\" $ch>$text</option>\n";
            }
            $Rc .= '</select>';
    }
    put("<tr><td class=row" . ($__PM_TABLEcolor % 2 + 1) . ">$Lc</td><td class=row" . ($__PM_TABLEcolor % 2 + 1) . ">$Rc</td></tr>");
    $__PM_TABLEcolor++;
}

function putConfItemA($V)
{
    global $__PM_TABLEcolor, $COLORS, $__PM_CONFITEMINDEX;
    /*array(			$name,
					$desc,
					$type = 'text',
					$value = '',
					$tinydesc = '',
					$checked = false, //dropdown, radio, checkbox
					 $orig = 'hor', //for radio
					 $cols = '20',  //textarea
					 $rows = '6'    //textarea
					 $validator = ''
					 $sample = ''
					 */

    $V['name'] = trim($V['name']);
    if ($V['validator']) $V['validator'] .= "($__PM_CONFITEMINDEX);";
    if ($V['ondeactivate']) $V['ondeactivate'] .= "($__PM_CONFITEMINDEX);";

    if ($V['informer'] != '') $informer = InformerCode($V['informer']);
    if (!$V['type']) $V['type'] = "text";

    if (!$V['name'] && $V['type'] != 'raw') return;
    $Lc = '<b>' . $V['desc'] . '</b> ' . $informer . '<br><small>' . $V['tinydesc'] . '</small>';
    $Rc = '';
    $cid = " id=confitem$__PM_CONFITEMINDEX ";
    $validatorcommon = " onChange=\"$V[validator]\" onKeyUp=\"$V[validator]\" ";
    $validatorselect = " onChange=\"$V[validator]\" ";
    $ondeactivate = " ondeactivate=\"$V[ondeactivate]\" ";
    switch ($V['type']) {
        case 'raw' : $Rc = $V['value'];
            break;
        case 'text':
            $sz = $ln = '';
            if ($V['size']) $sz = " size=\"$V[size]\" ";
            if ($V['len']) $ln = " maxlength=\"$V[len]\" ";
            $Rc = "<input $cid $validatorcommon $ondeactivate type='text' name=\"$V[name]\" value=\"$V[value]\"$sz$ln>";
            break;
        case 'passwd': $Rc = "<input $cid $validatorcommon type='password' name=\"$V[name]\" value=\"$V[value]\">";
            break;
        case 'file': $Rc = "<input $cid $validatorcommon type='file' name=\"$V[name]\" >";
            break;
        case 'textarea':
            if (!$V['orig']) $V['orig'] = "hor";
            if (!$V['cols']) $V['cols'] = "20";
            if (!$V['rows']) $V['rows'] = "6";
            $Rc = "<textarea $cid $validatorcommon name='$V[name]' cols='$V[cols]' rows='$V[rows]'>$V[value]</textarea>";
            break;
        case 'checkbox': $ch = ($V['checked'])? 'checked':'';
            $Rc = "<input $cid $validatorcommon type=\"checkbox\" name=\"$V[name]\" value=\"$V[value]\" $ch>";
            break;
        // case 'radio': $Rc = "<input type='text' name='$name' value='$value'>";
        // break;
        case 'dropdown': $Rc = "<select $cid $validatorselect size='1' name=\"$V[name]\">";
            foreach($V['value'] as $val => $text) {
                $ch = ($V['checked'] == $val)?'selected':'';
                $Rc .= "<option value=\"$val\" $ch>$text</option>\n";
            }
            $Rc .= '</select>';
    }
    if ($V['sample'] != '') $sample = "<div class=ci_sample><span>Пример:</span> $V[sample]</div>";
    put("<tr class=row" . ($__PM_TABLEcolor % 2 + 1) . ">" .
        "<td class=\"cidesc$V[tdsuff]\">$Lc</td>" .
        "<td class=\"cival$V[tdsuff]\">$Rc$sample<div id=\"confitemaux$__PM_CONFITEMINDEX\" style=\"display: none;\"></div></td>" .
        "</tr>" .
        "<script>$V[validator]</script>");
    $__PM_TABLEcolor++;
    $__PM_CONFITEMINDEX++;
}

function fileCnt($fname)
{
    $f = fopen($fname, 'r');
    if ($f) {
        $out = fread($f, filesize ($fname));
        fclose($f);
        return $out;
    } else {
        echo "File not found ($fname) ";
    }
}

function myName()
{
    $name = basename(getenv('REQUEST_URI'));
    $name = substr($name, 0, strpos($name, '?'));

    return $name;
}

function IsEmpty($textfield)
{
    $textfield = trim($textfield);
    return (!isset($textfield) || empty($textfield));
}

function EmailValid($email)
{
    $ret = myStrUp($email, true);
    $ret = ereg('^([_a-z0-9-]+[\._a-z0-9-]*)@(([a-z0-9-]+\.)*([a-z0-9-]+)(\.[a-z]{2,4}))$', $ret);

    return $ret;
}

function IsClean($textfield, $mask)
{
    foreach($mask as $simb) {
        if (is_integer(strpos($textfield, $simb))) return false;
    }
    return true;
}

function StripObs($haystack)
{
    $chars = array ("/\(/", "/\)/", "/\b\.\B/", "/\b,\B/", "/\b\?/", "/\b!/", "/\b:/", "/\b;/", "/\b\'s/i", "|\b/\b|", "/\b\r\n\b/", "/\s+/", "/&amp/");
    $rep = array ("", "", "", "", "", "", "", "", "", "", "", " ", "&");
    $haystack = preg_replace($chars, $rep, $haystack);

    return $haystack;
}

function gen_string($rlen = 8, $use = 'uln')
{
    $chars = '';
    $use = strtolower($use);
    if (strpos($use, 'l') !== false) $chars .= 'abcdefghijklmnopqrstuvwxyz';
    if (strpos($use, 'n') !== false) $chars .= '0123456789';
    if (strpos($use, 'u') !== false || $chars == '') $chars .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    mt_srand((double)microtime() * 1000000);
    for($get = strlen($chars); $i < $rlen; $i++) {
        $result .= $chars[mt_rand(0, $get)];
    }
    return $result;
}

function renderPager($path, $icount, $page = 1, $ppp = 20, $pagePreffix = "")
{
    // icount - общее количество элементов
    // ppp - на странице
    $groupsize = 10; //groupsize -

    if (!$page || $page == '') $page = 1;
    if (false === strpos($path, '?')) $path .= '?';

    if ($icount <= $ppp) return;
    $start = $ppp * ($page - 1);
    $end = $start + $ppp - 1;

    $pcount = floor($icount / $ppp);
    if ($icount > $pcount * $ppp) $pcount++;
	//if($page>$pcount) $page = $pcount;

    $groups = floor($pcount / $groupsize);
    if ($pcount > $groups * $groupsize) $groups++;
    $group = ceil($page / $groupsize);
    $groupstart = ($group - 1) * $groupsize + 1;
    $groupend = ($group >= $groups) ? $groupstart + ($pcount>$groupsize?($pcount % $groupsize):$pcount) - 1 : $groupstart + $groupsize - 1;

    if ($group > 1)
        $pagescode = "<a href=\"" . $path . "&" . $pagePreffix . "page=" . ($groupstart - 1) . "\">...</a> ";
    // now create links
    for($i = $groupstart; $i <= $groupend; $i++) {
        if ($i != $page) {
            $pagescode .= " <a href=\"{$path}&" . $pagePreffix . "page=$i\">$i</a>";
        }else {
            $pagescode .= " <span class=\"active\">$i</span>";
        }
    }
    if ($groups > 1 && $group != $groups)
        $pagescode .= " <a href=\"{$path}&" . $pagePreffix . "page=" . ($group * $groupsize + 1) . "\">...</a>" ;
    return $pagescode?"<div align=\"center\" class=\"navigation\"><div>" . $pagescode . "</div></div>":'';
}

function transCode($target, $source, $subj, $mimetarget = false, $ishdr = false)
{
    $encWIN = array('’', '№', '–', '”', '“', '=',
        'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
        'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
    $encDOS = array("'", "\xEF", '--', "\x22", "\x22", '=',
        "\x80", "\x81", "\x82", "\x83", "\x84", "\x85", "\xF0", "\x86", "\x87", "\x88", "\x89", "\x8A", "\x8B", "\x8C", "\x8D", "\x8E", "\x8F", "\x90", "\x91", "\x92", "\x93",
        "\x94", "\x95", "\x96", "\x97", "\x98", "\x99", "\x9A", "\x9B", "\x9C", "\x9D", "\x9E", "\x9F",
        "\xA0", "\xA1", "\xA2", "\xA3", "\xA4", "\xA5", "\xF1", "\xA6", "\xA7", "\xA8", "\xA9", "\xAA", "\xAB", "\xAC", "\xAD", "\xAE", "\xAF", "\xE0", "\xE1", "\xE2", "\xE3", "\xE4", "\xE5", "\xE6", "\xE7", "\xE8", "\xE9", "\xEA", "\xEB", "\xEC", "\xED", "\xEE", "\xEF");
    $encKOI = array("'", "\xEF", '--', "\x22", "\x22", '=',

        "б", "в", "ч", "з", "д", "е", "е", "ц", "ъ", "й", "к", "л", "м", "н", "о", "п", "р", "т", "у", "ф", "х", "ж", "и", "г", "ю", "ы", "э", "я", "ш", "э", "ь", "а", "с",
        "Б", "В", "Ч", "З", "Д", "Е", "Е", "Ц", "Ъ", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "Т", "У", "Ф", "Х", "Ж", "И", "Г", "Ю", "Ы", "Э", "Я", "Щ", "Э", "Ь", "А", "С");

    $encLAT = array("'", '#', '--', '”', '“', '=',
        'A', 'B', 'V', 'G', 'D', 'E', 'E', 'Zh', 'Z', 'I', 'I', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sch', '\'', 'Y', '\'', 'E', 'Yu', 'Ya',
        'a', 'b', 'v', 'g', 'd', 'e', 'e', 'zh', 'z', 'i', 'i', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sch', '\`', 'y', '\'', 'e', 'yu', 'ya');

    $mimeRAW = array('’', '№', '–', '”', '“', '=', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т',
        'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
        'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
    $mimeKOI = array("'", '#', '--', '&ldquo;', '&bdquo;', '=3D', '=E1', '=E2', '=F7', '=E7', '=E4', '=E5', '=B3', '=F6', '=FA', '=E9',
        '=EA', '=EB', '=EC', '=ED', '=EE', '=EF', '=F0', '=F2', '=F3', '=F4',
        '=F5', '=E6', '=E8', '=E3', '=FE', '=FB', '=FD', '=FF', '=F9', '=F8', '=FC', '=E0', '=F1',
        '=C1', '=C2', '=D7', '=C7', '=C4', '=C5', '=A3', '=D6', '=DA', '=C9', '=CA', '=CB', '=CC', '=CD', '=CE', '=CF', '=D0', '=D2', '=D3', '=D4', '=D5', '=C6', '=C8', '=C3', '=DE', '=DB', '=DD', '=DF', '=D9', '=D8', '=DC', '=C0', '=D1');
    $mimeWIN = array("'", '№', '--', '&ldquo;', '&bdquo;', '=3D', '=C0', '=C1', '=C2', '=C3', '=C4', '=C5', '=A8', '=C6', '=C7', '=C8', '=C9', '=CA', '=CB', '=CC', '=CD', '=CE', '=CF', '=D0', '=D1', '=D2', '=D3', '=D4', '=D5', '=D6', '=D7',
        '=D8', '=D9', '=DA', '=DB', '=DC', '=DD', '=DE', '=DF',
        '=E0', '=E1', '=E2', '=E3', '=E4', '=E5', '=B8', '=E6', '=E7', '=E8', '=E9', '=EA', '=EB', '=EC', '=ED', '=EE', '=EF', '=F0', '=F1', '=F2', '=F3', '=F4', '=F5', '=F6', '=F7',
        '=F8', '=F9', '=FA', '=FB', '=FC', '=FD', '=FE', '=FF');
    $EncConv = array('koi' => 'koi8-r', 'win' => 'windows-1251', 'lat' => 'iso-8859-15');
    if ($ishdr == true) {
        $x = ($target == 'koi' || $target == 'win')
        ? '=?' . $EncConv[$target] . '?Q?' .
        str_replace(' ', '_', transCode($target, $source, $subj, true)) . '?='
        : transCode($target, $source, $subj);
    } else {
        if ($target == 'koi') {
            $t = ($mimetarget)?$mimeKOI: $encKOI;
        } else
        if ($target == 'lat') {
            $t = $encLAT;
        } else
        if ($target == 'dos') {
            $t = $encDOS;
        } else
        if ($target == 'win') {
            $t = ($mimetarget)?$mimeWIN: $encWIN;
        }

        if ($source == 'koi') {
            $f = $encKOI;
        } else
        if ($source == 'lat') {
            $f = $encLAT;
        } else
        if ($source == 'dos') {
            $f = $encDOS;
        } else
        if ($source == 'win') {
            $f = $encWIN;
        }

        if ($target == 'koi' || $source == 'koi') {
            if ($f && $t) {
                $x = myReplace($f, $t, $subj);
            }
        } else {
            if ($f && $t) $x = str_replace($f, $t, $subj);
        }
    }
    return $x;
}

function myReplace($from, $to, $subj)
{
    $ln = strlen($subj);
    $ss = $subj;
    for($p = 0; $p < $ln; $p++) {
        $ss[$p] = str_replace($from, $to, $ss {$p});
    }

    return $ss;
}

function redirHtml($return = false)
{
    $rh = "

	<html>
	<head>
	<title>Подождите...</title>
	<meta http-equiv='refresh' content=\"" . PAGE_REDIRECT_DELAY . "; url=" . PAGE_REDIRECT_URL . "\" />
	<link rel=\"stylesheet\" href=\"/style/cdnr.css\">
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\" />
	</head>
	<body class=redirect>
	<table width='100%' height='85%' align='center'>
	<tr>
	  <td valign='middle'>
		  <table align=center cellpadding='4' class=redirect>
		   <tr><td width='100%' align='center' nowrap='nowrap'>" . PAGE_REDIRECT_TEXT . " <br /><br />
		   " . PAGE_REDIRECT_EXT . "<br /><br />
		   (<a href=\"" . PAGE_REDIRECT_URL . "\"><small class=redirect><b>Нажмите сюда, если не хотите ждать</b></small></a>)
		   </td></tr>
		</table>
	  </td>
	</tr>
	</table>
	</body>
	</html>

	";
    if ($return) return $rh;
    else echo $rh;
}

function mkThumb($src, $samplefile, $sqr = 100, $force = 0, $angle = 0)
{
    $imghandlers = array('jpg' => 'jpeg', 'jpeg' => 'jpeg', 'png' => 'png', 'wbmp' => 'wbmp', 'xbm' => 'xbm');

    if (@extension_loaded('gd')) {
        // create sample, else dont even try
        $fsz = @getimagesize($src);
        $targimg = $targ;
        $W = $H = $sqr;
        error_reporting(E_ALL);
        if (!file_exists($samplefile) || $force) {
            if (file_exists($samplefile)) {
                unlink($samplefile);
                echo 'ex';
            }
            if ($fsz[0] > $sqr || $fsz[1] > $sqr) {
                // here we create thumb
                // determine thumb dimension
                if ($fsz[1] <= $sqr) $H = $fsz[1] * $sqr / $fsz[0];
                elseif ($fsz[0] <= $sqr) $W = $fsz[0] * $sqr / $fsz[1];
                elseif ($fsz[0] > $fsz[1]) $H = ($sqr * $fsz[1] / $fsz[0]);
                else $W = ($sqr * $fsz[0] / $fsz[1]);
                $W = round($W);
                $H = round($H);

                $ext = strtolower(substr($src, 1 + strrpos($src, '.')));
                if (($im_dst = imagecreatetruecolor($W, $H)) && array_key_exists($ext, $imghandlers)) {
                    $HND = 'imagecreatefrom' . $imghandlers[$ext];
                    if (($im_src = $HND($src)) && @imagecopyresampled ($im_dst, $im_src, 0, 0, 0, 0, $W, $H, $fsz[0], $fsz[1])) {
                        if ($angle) {
                            $im_dst1 = imagerotate($im_dst, $angle, - 1);
                            $im_dst = $im_dst1;
                        }

                        $OUTFUNC = 'image' . $imghandlers[$ext];
                        $OUTFUNC($im_dst, $samplefile);
                        return 1;
                    }
                }
            }
        }
    }
    return @copy($src, $samplefile);
}

function getSortHref($curSortBy, $curSortType, $newSortBy, $varSortName, $varSortTypeName, $param, $text)
{
    // Выплевывает ссылку сортировки
    // $curSortBy - текущая сортировка
    // $curSortType - текущий тип сортировки (DESC or '')
    // $newSortBy - новый тип сортировки
    // $varSortName - название переменной сортировки
    // $varSortTypeName - название переменной типа сортировки
    // $param - дополнительные параметры
    if ($curSortBy == $newSortBy && $curSortType == "")
        $curSortType = "DESC";
    else
        $curSortType = "";
    return "<a href=\"" . ACTCALL_LINK . "&$varSortName=" . urlencode($newSortBy) . "&$varSortTypeName=" . urlencode($curSortType) . "&$param\">" . htmlspecialchars($text) . "</a>";
}

function translitToEn($str)
{
    $r = "йцукенгшщзхъфывапролджэячсмитьбю";
    $e = "qwertyuiop[]asdfghjkl;'zxcvbnm,.";
    $ex = "qwertyuiopasdfghjklzxcvbnm";

    $ra = str_split($r, 1);
    $ea = str_split($e, 1);
    $exa = str_split($ex, 1);
    // first check if all are
    for($i = 0; $i < strlen($str); $i++) {
        $test = $str[$i];
        if (in_array($test, $exa)) return $str;
    }

    return myReplace($ra, $ea, $str);
}

function secMatch($var, $mask = '')
{
    if (!$mask)$mask = '/^([\@0-9a-zа-я\. -]*)$/ui';
    if (!preg_match($mask, $var))
        return '';
    return $var;
}

function secMatchA($var, $mask = '')
{
    if (!$mask)$mask = '/^([^\!\#\$\%\^\&\*\(\)\[\]\<\>]*)$/i';
    if (!preg_match($mask, $var))
        return '';
    return $var;
}

if (!function_exists('isIPv4valid')) {
    function isIPv4valid($ip)
    {
        return preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/', trim($ip));
    }
}

function gfx_genCode()
{
    mt_srand ((double)microtime() * 1000000);
    $maxran = 1000000;
    return mt_rand(0, $maxran);
}

function rand_color()
{
    mt_srand ((double)microtime() * 1000000);
    return array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
}
function GtinxCaptchaDraw($rnd_num)
{
    mt_srand ((double)microtime() * 1000000);
    $maxran = 1000;

    if (!extension_loaded('gd')) die();
    $datekey = date("F j");
    $rcode = hexdec(md5($_SERVER['HTTP_USER_AGENT'] . get_user_ip() . $rnd_num . $datekey));
    $code = substr($rcode, 2, 6); //determine code
    $angdraw = mt_rand(0, 90); //grid angle
    $cellW = 8 + mt_rand(0, 6); //horizontal cell size
    $cellH = 8 + mt_rand(0, 5); //vertical cell size

    $imw = 130 + $rnd_num % 40;
    $imh = 45 + $rnd_num % 20;

    $image = imagecreate ($imw, $imh);
    $bgc = imagecolorallocate ($image, 255 - $rnd_num % 53, 255 - $rnd_num % 55, 255 - $rnd_num % 55);
    // $linecl = imagecolorallocatealpha($image, 205-$rnd_num%23, 205-$rnd_num%35, 205-$rnd_num%45, 100);
    imagefilledrectangle ($image, 0, 0, $imw, $imh, $bgc);
    for($x = 0; $x < $imw + $cellW; $x += $cellW) {
        $rcl = rand_color();
        $linecl = imagecolorallocatealpha($image, $rcl[0], $rcl[1], $rcl[2], 100);
        imageline($image, $x + (cos($angdraw) * 10), 0, $x, $imh, $linecl);
    }

    for($y = 0; $y < $imh + $cellH; $y += $cellH) {
        $rcl = rand_color();
        $linecl = imagecolorallocatealpha($image, $rcl[0], $rcl[1], $rcl[2], 100);
        imageline($image, 0, $y - (sin($angdraw) * 10), $imw, $y, $linecl);
    }
    /* */

    imagealphablending ($image , 1);
    for($i = 0; $i < 6; $i++) {
        $RN = mt_rand(0, $maxran);
        $r = $bgc + (($RN % 2) * - 1) * ($RN + time() / pow(12, $i)) % 20; //$r = ($RN+time()/pow(12,$i))%125;
        $g = $bgc + (($RN % 2) * - 1) * ($r * time() / pow(10 + $i, $i)) % ($r * 2); //   $g = ($r*time()/pow(10+$i,$i))%($r*2);
        $b = $bgc + (($RN % 2) * - 1) * ($RN + $g * time() / (pow(10 + $i * 4, $i))) % 20; // $b = ($RN+$g*time()/(pow(10+$i*4,$i)))%180;

        $text_color = imagecolorallocatealpha($image, $r, $g, $b, 127);
        imagettftext ($image, 18 + ($i + $RN) % 3, 4 + (3 - $RN % 6) * 6, ((int)(($imw - 120) / 2)) + $i * 22,/*5+*/ (int)($RN % 20 + ($imh / 2)) + $i * 1,
            $text_color,
             GTROOT."/fonts/font".(($i+$RN)%2+1).".ttf",
            //GTROOT . "/fonts/font1.ttf",
            substr($rcode, 2 + $i, 1));
    }

    Header("Content-type: image/jpeg");
    ImageJPEG($image, '', 75);
    ImageDestroy($image);
    die();
}

function GtinxCaptchaCheck($code, $rnd_num)
{
    $datekey = date("F j");
    $rcode = hexdec(md5($_SERVER['HTTP_USER_AGENT'] . get_user_ip() . $rnd_num . $datekey));
    return ($code == substr($rcode, 2, 6));
}

function get_user_ip()
{
    if (getenv('REMOTE_ADDR')) {
        $_ip = getenv('REMOTE_ADDR');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $_ip = getenv('HTTP_X_FORWARDED_FOR');
    } else {
        $_ip = getenv('HTTP_CLIENT_IP');
    }
    return $_ip;
}
function GetCurrentDomain(){
	return $_SERVER['HTTP_HOST'];
}
function CookieSet($cookiename, $cookieval = '', $cookieexp = 0)
{
    if (!empty($cookiename)) {
        setcookie($cookiename, $cookieval, $cookieexp, '/', GetCurrentDomain());
    }
}

function CookieEmpty($cookiename)
{
    CookieSet($cookiename, '', 0, '/', GetCurrentDomain());
}

function Cookie($cookiename)
{
    return $_COOKIE[$cookiename];
}

// END SESSIONS STUFF
function CheckPerson($uname, $pass, $table, $n_field, $p_field, $result = '')
{
    global $CONF;
    if (IsEmpty($uname)) {
        $result = 'uname';
        return false;
    }
    if (IsEmpty($pass)) {
        $result = 'pass';
        return false;
    }
    if (IsEmpty($table)) {
        $result = 'table';
        return false;
    }
    if (IsEmpty($n_field)) {
        $result = 'n_field';
        return false;
    }
    if (IsEmpty($p_field)) {
        $result = 'p_field';
        return false;
    }
    $passhash = md5($pass);
    $qr = db_query("SELECT * FROM $table WHERE `$n_field`='$uname' AND `$p_field`='$passhash'");
    if (db_num_rows($qr) === 1) {
        return db_fetch_array($qr);
    } else {
        return false;
    }
}

function handleQuotes()
{
    global $_GET, $_POST, $_COOKIE, $_SESSION, $_SERVER, $_FILES,
    $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_ENV_VARS, $HTTP_COOKIE_VARS, $HTTP_SESSION_VARS, $HTTP_SERVER_VARS, $HTTP_POST_FILES;

    $GL = @array('0' => $_GET, '1' => $_POST, '2' => $_ENV, '3' => $_COOKIE, '4' => $_SESSION, '5' => $_SERVER, '6' => $_FILES);
    $GL_old = @array('0' => $HTTP_GET_VARS, '1' => $HTTP_POST_VARS, '2' => $HTTP_ENV_VARS, '3' => $HTTP_COOKIE_VARS, '4' => $HTTP_SESSION_VARS, '5' => $HTTP_SERVER_VARS, '6' => $HTTP_POST_FILES);

    if (version_compare(phpversion(), '4.1.0', 'le')) {
        $GL = &$GL_old;
    }
    reset($GL);

	function _addq($V){
		if(is_array($V)){
			$r = array();
			foreach($V as $_k=>$_v)
				$r[$_k]=_addq($_v);
		}else
			$r = addslashes($V);
		return $r;
	}
    if (get_magic_quotes_gpc() === 0) {
        foreach($_GET as $k => $v) {
			$_GET[$k] = _addq($v);
        }
        foreach($_POST as $k => $v) {
			$_POST[$k] = _addq($v);
        }

        foreach($_COOKIE as $k => $v) $_COOKIE[$k] = addslashes($v);

        foreach($GL as $num => $array) {
            if (is_array($array)) {
                foreach($array as $key => $val) if(!is_array($val)) $GLOBALS["$key"] = addslashes($val);
                reset($array);
            }
        }
        foreach($GL[6] as $key => $var) {
            $name = '';
            $name = $key;
            if (is_array($var)) {
                foreach($var as $newkey => $val) $GLOBALS[$name . '_' . $newkey] = $val;
            } else {
                $GLOBALS["$key"] = addslashes($var);
            }
        }
    } else {
        foreach($GL as $num => $array) {
            if (is_array($array)) {
                foreach($array as $key => $val) $GLOBALS["$key"] = $val;
                reset($array);
            }
        }
        foreach($GL[6] as $key => $var) {
            if (is_array($var)) {
                foreach($var as $newkey => $val) $GLOBALS[$name . '_' . $newkey] = $val;
            } else {
                $GLOBALS["$key"] = $val;
            }
        }
    }
}

function WFmd5($v, $key1 = '')
{
    return md5($v . SITEKEY . $key1);
}

function verify_email_ajax_wrapper($vrfy, $ogID)
{
    $ret = '';
    $good = 1;
    switch (verify_email($vrfy)) {
        CASE - 5: $ret = '<span style="color: Brown;">Внимание! Мы смогли связаться с Вашей почтовой службой. Но она не позволяет проверить, правильно ли Вы указали свой email. Пожалуйста, убедитесь в правильности ввода</span>';
            $good = 1;
            break;
        case - 2: $ret = 'Проверка Email не удалась. Можете продолжать регистрацию, однако убедитесь ещё раз, что указали Email правильно';
            $good = 1;
            break;
        case - 1: $good = 0;
            $ret = '<span style="color: red;">ВНИМАНИЕ! Мы не обнаружили почтовый сервер для этого почтового ящика. Пожалуйста, введите E-Mail, с работоспособной почтовой службой </span>';
            break;
        case - 3:
        case - 4: $good = 0;
            $ret = '<span style="color: red;">ВНИМАНИЕ! Мы не смогли связаться с сервером для этого почтового ящика. Пожалуйста, введите E-Mail, с работоспособной почтовой службой </span>';
            break;
        case false: $good = 0;
            $ret = '<span style="color: RED;">НЕЛЬЗЯ использовать этот Email. Он не существует.</span>';
            break;
        case 1:
        case true:
        default:
            $ret = '<span style="color: green;">ОК! Мы проверили этот почтовый ящик и он признан доступным к использованию </span>';
    }

    return "oinp$ogID = document.getElementById('confitem$ogID'); " .
    "if(oinp$ogID.value=='$vrfy'){" .
    "oaux$ogID = document.getElementById('confitemaux$ogID'); " .
    "oaux$ogID.style.display = 'block'; " .
    "oaux$ogID.innerHTML = '<small>$ret</small>';
				var result = 1 == $good ? 'aff_tick.gif' : 'aff_cross.gif';
				oinp$ogID.style.backgroundImage='url(/images/'+result+')';
				oinp$ogID.style.backgroundRepeat='no-repeat';
				oinp$ogID.style.backgroundPosition='right center';" .
    "}";
}

function ajax_fill_area_select($cc, $ogID)
{
    global $TABLES;
    $INP = '';
    $q = db_query("SELECT * FROM `$TABLES[contacts_cc_areas]` WHERE `cc_id`='" . mysql_real_escape_string($cc) . "' ORDER BY `cca_id` ASC");
    while ($a = db_fetch_array($q)) {
        if (strlen($a['cca_name']) > 43) $a['cca_name'] = substr($a['cca_name'], 0, 40) . '...';
        $INP .= "oinpa$ogID.options[oinpa$ogID.options.length] = new Option(\"$a[cca_name]\",'$a[cca_id]');\r\n";
    }

    return "
		oinp$ogID = document.getElementById('confitem$ogID'); " .
    "oinpa$ogID = document.getElementById('confitem'+($ogID-1));" .
    "if(oinp$ogID.options[oinp$ogID.selectedIndex].value=='$cc' && oinpa$ogID.options.length<2){\n" .
    "
				oinpa.options[0] = null;
				$INP
				"
     . "}" ;
}

function verify_email($vrfy)
{
    global $CONF;
    require_once 'Net/SMTP.php';
    require_once "Net/DNS.php";

    $from = 'email-verify@dnr.kz';
    $esplit = explode('@', $vrfy);

    $host = $esplit[1];
    $mx = dns_get_record($host, DNS_MX);
    if (empty($mx)) return - 1;

    $hostmx = $mx[0]['target'];
    if (! ($smtp = new Net_SMTP($hostmx))) {
        Report(LL_CRASH, "Net_SMTP: Unable to instantiate Net_SMTP object on $hostmx");
        return - 2;
    }

    if (PEAR::isError($e = $smtp->connect())) {
        Report(LL_CRASH, "Net_SMTP: " . $e->getMessage());
        RETURN - 3;
    }

    if ($CONF['mailcheck_bypass']) {
        $hlist = explode("\n", $CONF['mailcheck_bypass']);
        foreach($hlist as $bypasshost) {
            if ($host == trim($bypasshost)) return - 5;
        }
    }

    if (PEAR::isError($smtp->mailFrom($from))) {
        Report(LL_CRASH, "Net_SMTP: Unable to set sender to <$from>");
        $smtp->disconnect();
        RETURN - 4;
    }

    if (PEAR::isError($res = $smtp->rcptTo($vrfy))) {
        Report(LL_CRASH, "Net_SMTP: Unable to add recipient <$vrfy>: " . $res->getMessage() . " " . print_r($smtp->getResponse(), 1));
        $smtp->disconnect();
        return false;
    }
    $smtp->disconnect();
    return 1;
}

function userauthPage()
{
    global $CLIENT, $CONF, $sessdata, $lognsucc, $nav_1, $nav_2, $TABLES, $AUTH_DIE, $nav_disable, $SIDEPANEL, $mod, $STR_SUB;
    // IPBAN function enabled only if approrpiate conf values are set
    define ('IPBAN_ENABLE', (bool)($CONF['ipban_bantime'] && $CONF['auth_ipban'] && $CONF['ipban_trycount'] && $CONF['ipban_timetotry']));
    define ('IMGTEST_ENABLE', (bool)$CONF['auth_img']);

    $act = PassedVal('act');

    $sessdata = get_user_by_session('dnr');
    $CLIENT = array();
    $derr = true;
    $mq = db_query("SELECT * FROM $TABLES[clients] WHERE cl_login='$sessdata[user_name]' LIMIT 1");
    $lognsucc = 0;
    $ip = get_user_ip();
    if (db_num_rows($mq) != 1) {
        if (IPBAN_ENABLE) {
            // check ip is banned
            $ipban = db_query("SELECT * FROM `$TABLES[site_login_ip]`
	                            WHERE `ip`='$ip' AND `failure_last`>='" . (time() - $CONF['ipban_bantime']) . "'
	                                AND `failures_total`>='$CONF[ipban_trycount]' ORDER BY `failures_begin` DESC LIMIT 1");
            if (db_num_rows($ipban) > 0) {
                $ipban = db_fetch_array($ipban);
                $secleft = $CONF['ipban_bantime'] - (time() - $ipban['failure_last']);
                $TMmin = (int)($secleft / 60);
                $TMsec = ($secleft % 60);
                if ($TMsec < 10) $TMsec = '0' . $TMsec;
                $TM = $TMmin . ':' . $TMsec;
                putRedir("ВНИМАНИЕ: Вы превысили предел возможных попыток авторизации. <br>Ваш IP-адрес блокирован.",
                    "Вы сможете снова попробовать авторизоваться через:<br> $TM", '?', $secleft + 10);
                require(PATH_BACKBONE . 'pagemaker.php');
                exit;
            }
        }

        if ($_POST['dologin']) {
            $user = '';
            if (IMGTEST_ENABLE && extension_loaded('gd') && !isCodeAppropriate($_POST['gfx_check'], $_POST['rnd_num'])) {
                puterr($STR_DC['wrong_code']);
            } else
            if (false !== ($user = CheckPerson(strtoupper($_POST['username']), $_POST['passwd'], $TABLES['clients'], 'cl_login', 'cl_passwd'))) {
                if (mk_session(strtoupper($_POST['username']), 'dnr', $user['cl_login'])) {
                    // putsucc($STR_SUB['info_succlogin']);//,$STR_SUB['info_redir'],"?" );
                    $lognsucc = 1;
                    $CLIENT = $user;
                    Report(LL_NOTICE, "USERAUTH: ($CLIENT[cl_login]) logged in ");
                }
            } else {
                if (IPBAN_ENABLE) {
                    $ipban = db_query("SELECT * FROM `$TABLES[site_login_ip]`
						                WHERE `ip`='$ip' AND `failures_begin`>='" . (time() - $CONF['ipban_timetotry']) . "'
						                AND `failures_total`<='$CONF[ipban_trycount]' ORDER BY `failures_begin` DESC LIMIT 1");

                    if (db_num_rows($ipban) > 0) {
                        // increase failures counter
                        $ipban = db_fetch_array($ipban);
                        if ($ipban['failures_total'] == ($CONF['ipban_trycount'] - 1))
                            Report(LL_FATAL, "USERAUTH: IP[$ip] Blocked for $CONF[ipban_bantime] seconds after $CONF[ipban_trycount] login failures");
                        db_query("UPDATE `$TABLES[site_login_ip]` SET `failures_total`=`failures_total`+1, `failure_last`='" . time() . "'
	                    				WHERE `ip`='$ip' AND `failures_begin`='$ipban[failures_begin]'");
                    }else
                        // add new counter
                        db_query("INSERT INTO `$TABLES[site_login_ip]` (`ip`,`failures_begin`,`failures_total`,`failure_last`) " .
                            "VALUES ('$ip','" . time() . "','1','" . time() . "')");
                }
                puterr($STR_SUB['info_loginsomeerr'], $STR_SUB['info_redir'], "?");
            }
        }
        if ($derr && !$lognsucc) {
            $SIDEPANEL .= "&nbsp;";
            put("<h1>Авторизация</h1>");
            $nav_1 = 'Авторизация';
            $nav_disable = true;
            $td = trim(str_replace(array("\r\n", "\n", "\r"), array(' ', ' ', ' '), PassedVal('transdomain')));
            // $random_num = gfx_genCode();
            $rd = trim(secMatch(strtolower(PassedVal('regdomain')), DOMAIN_MATCH));
            if ($td || $rd || $act)
                put("<h4>Для продолжения, Вам необходимо войти, используя зарегистрированный логин и пароль.<br>
		            		Если у Вас нет учётной записи на нашем сайте, зарегистрируйтесь</H4>");

            put("<div id=\"authloginhdr\" class=\"l2border\">
						<div style=\"margin-left: 20px; font-size: 15px;\"><b>Вход</b> - если у Вас уже есть учётная запись на dnr.kz</div>
					</div>
					<div id=\"authlogin\">");
            put("<form method=post action='/my/?mod=$mod&act=$act'><table border=0 cellspacing=3 cellpadding=0>
	                    <tr><td align=left><b>Логин:</b></td><td><input TABINDEX=\"1\" name=username id=loginusername type=text value=\"$_POST[username]\"></td></tr>
	                    <tr><td align=left><b>Пароль:</b></td><td nowrap><input TABINDEX=\"2\" name=passwd type=password value=\"\"></td><td>(<a href='?&amp;act=lostpw'>Забыли пароль?</a>)</td></tr>
	                    <tr><td align=right colspan=2></td></tr>");
            if ($rd)
                put("<input type=hidden name=regdomain value=\"$rd\">");
            put("<input type=hidden name=transdomain value=\"$td\">");
            $random_num = gfx_genCode();
            if (IMGTEST_ENABLE && extension_loaded('gd'))
                put("<tr class=row2><td >Шифр: </td><td><img src='?rootact=gfx&amp;random_num=$random_num' border='1' alt='Шифр' title='Шифр'></td></tr>"
                     . "<tr class=row1><td >Введите шифр:</td><td> <input TABINDEX=\"3\" type=\"text\" NAME=\"gfx_check\" SIZE=\"6\" MAXLENGTH=\"6\"></td></tr>
	                    <input type=\"hidden\" NAME=\"rnd_num\" value=\"$random_num\">");
            put(" <tr><th colspan=2 align=center><input type=submit TABINDEX=\"4\" class=\"gwbtw\" name=\"dologin\" value='Войти'></th></tr></table></form>");

            put("

					<script type=\"text/javascript\">
							function setLoginFieldFocus(){
								var lu_o = document.getElementById('loginusername');
								lu_o.focus();
								lu_o.select();
							}
							document.body.onload=setLoginFieldFocus;
					</script>");

            put("</div>
					<div id=\"authreghdr\" class=\"l2border\">
						<div style=\"margin-left: 20px; font-size: 15px;\"><b>Регистрация</b> - для создания учётной записи.</div>
					</div>
					<div id=\"authreginfo\">
						<h3>Что даст <a href=\"/my/?act=ureg&return=" . urlencode("$HTTP_PATH?mod=$mod&op=$op&act=$act") . "\">личная регистрация</a> на сайте DNR.kz?</h3>
						Пройдя личную регистрацию, вы сможете регистрировать домены KZ и управлять ими онлайн.<br>
						Кроме того, Вы можете заказать <a href=\"/hosting\">хостинг</a> для своего домена.<br><br>

						Всё управление доменами производится на сайте DNR.kz -
						первом в Казахстане онлайн-сервисе регистрации доменов. Вам доступны множество способов оплаты услуг.<br><br>
					</div>

				");

            $AUTH_DIE = 1;
            return;
        }
    } else {
        $CLIENT = db_fetch_array($mq);
        update_sessinfo($CLIENT['cl_login'], 'dnr', $nav_1, $nav_2);

        if (!$CLIENT['cl_enabled']) {
            require(PATH_BACKBONE . "/pagemaker_cldisable.php");
            exit();
        }
    }
}

function multiArrayFilter($arr, $sFilterName = 'NAME', $sFilter = '')
{
    if (is_array($sFilter)) {
        $sFilterName = strtoupper($sFilterName);
        foreach ($arr as $hk => $arrVal) {
            foreach($arrVal as $avk => $v) {
                if (array_search($v[$sFilterName], $sFilter) === false) {
                    unset($arr[$hk][$avk]);
                }
            }
        }
        return $arr;
    } else return false;
}

function cleanQUrl($arrBlock='')
{
    $sQuery = $_SERVER['QUERY_STRING'];
    $sQuery = explode('&', $sQuery);
    $arrBlock = (!empty($arrBlock))? $arrBlock : Array("PAGE", "ORDER", "FILTER", "EPP", "LANG", "DBG"); //Игнорируемые значения в URL
    foreach($sQuery as $k => $v) {
        $sParamName = substr($v, 0, strrpos($v, '='));
        if (is_numeric(array_search(strtoupper($sParamName), $arrBlock))) {
            unset($sQuery[$k]);
        }
    }

    return $sQuery;
}

function userCleanQUrl($arrBlock='')
{
	global $APP;
	$sQuery = $APP->GetCurPage(false,false);
	$sQuery = explode('&', $sQuery);
	$arrBlock = (!empty($arrBlock))? $arrBlock : Array("PAGE", "ORDER", "FILTER", "EPP", "LANG", "DBG"); //Игнорируемые значения в URL
	foreach($sQuery as $k => $v) {
		$sParamName = substr($v, 0, strrpos($v, '='));
		if (is_numeric(array_search(strtoupper($sParamName), $arrBlock))) {
			unset($sQuery[$k]);
		}
	}

	return implode('&',$sQuery);
}

function phpFileParse($src)
{
    $src = trim($src);
    $sPhpStart = '<' . '?';
    $sPhpEnd = '?' . '>';


    if (substr($src, 0, 2) == $sPhpStart) {
        $iStart = 2;
        $iFc = strlen($src);
        while ($iStart < $iFc) {
            $sCh2 = substr($src, $iStart, 2);
            if ($sCh2 == $sPhpEnd) {
                $iStart += 2;
                break;
            }
            $iStart++;
        }
        $arr['HEAD'] = substr($src, 0, $iStart);
        $src = substr($src, $iStart, $iFc);
    } elseif (preg_match("#(.*?<title>.*?</title>)(.*)$#is", $src, $arrOut)) {
        $arr['TITLE'] = $arrOut[1];
        $src = $arrOut[2];
    }
	if(strpos($src,'$APP->SetPageTitle')){
		$title = preg_match_all('#.*APP->SetPageTitle\(\'(.*)\'\)#', $src, $arrOut);
			$arr['TITLE'] = $arrOut['1']['0'];
	}
	if(strpos($src,'$APP->SetPageMeta')){
		$title = preg_match_all('#.*APP->SetPageMeta\(\'(.*)\'\)#', $src, $arrOut);
			$arr['META'] = $arrOut['1']['0'];
	}
    if (substr($src, - 2) == $sPhpEnd) {
        $iFc = strlen($src) - 2;
        while (($iFc > 0)) {
            if (substr($src, $iFc, 2) == $sPhpStart) break;
            $iFc--;
        }
        $arr['CONTENT'] = substr($src, 0, $iFc);
        $arr['FOOTER'] = substr($src, $iFc);
    }
	return $arr;
}


/*
	fucntion LoadClass
	manages loading classes by name
	If name is given in form "moduleName::className"
		tries to fetch class of name className exactly from given moduleName
	If module not specified, i.e. just "className"
		tries to find className in every module, evaluating container file using className
*/
function LoadClass($sClassName=''){
	global $APP;
	//sanitize input
	$sClassName =  strtolower(preg_replace('/[^a-z0-9_:]/i','',$sClassName));
	if(!$sClassName) return $APP->Raise("Class NOT FOUND");
	if(strpos('::',$sClassName)!==FALSE)
	{
		$arSplit = explode('::',$sClassName);
		if(count($arSplit)>2) 	//only one directory name is supported
			return false;		//FIXME!!! -- Need system log warning for debug mode
		if(class_exists($arSplit[1]))
			return true;
		$arSplit[2] = preg_replace('/^gt/','',$sClassName);
		$fn = GTROOT.'/modules/'.$arSplit[0].'/classes/'.$arSplit[2].'.php';
		if(file_exists($fn) && is_readable($fn)){
			@include_once($fn);
			return class_exists($arSplit[1]);
		}else
			return false;
	}
	else
	{
		if(class_exists($sClassName)) return true;
		$cachedName = __FUNC__.'_'.md5($sClassName); //store value instead of evaluating md5 twice
		$stored = cacheGet($cachedName);
		if($stored){ //if not first call
			//verify file exists
			if(file_exists($stored) && is_readable($stored)){
				@include $stored;
				if(class_exists($sClassName))
					return true;
			}
		}
		//else find it
		$sClassFile = preg_replace('/^gt/','',$sClassName).'.php';
		$hndModDir = opendir(GTROOT.'/modules');
		while($sOneDir = readdir($hndModDir)){
			if($sOneDir!='.' && $sOneDir!='..' && is_dir(GTROOT.'/modules/'.$sOneDir))
			{
				$fn = GTROOT.'/modules/'.$sOneDir.'/classes/'.$sClassFile;
				if(file_exists($fn) && is_readable($fn))
				{
					@include $fn;
					if(class_exists($sClassName)){

						cacheSet($cachedName,$fn);
						break;
					}
				}
			}
		}
		closedir($hndModDir);
		return class_exists($sClassName);
	}
}

function __autoload($class)
{
   LoadClass($class);
}

function jsEscape($str) {
    return addcslashes($str,"\\\'\"&\n\r<>");
}
function phpArray2JSONObject($arValues){
	$s = '';
	if(!is_array($arValues)) return '{}';
	foreach($arValues as $k=>$v){
		if($s)$s.=',';
		$s .= '"'.addslashes($k).'":'.(is_array($v)?phpArray2JSONObject($v):'"'.jsEscape($v).'"');
	}
	return "{".$s."}";
}

function componentPresent($sComName){
	$sComName = str_replace(':','/',preg_replace('/[^a-z0-9:\.]/i','',$sComName));
	if($sComName==='') return false;
	$sComDir = GTROOT.'/components/'.$sComName;
	if(!is_dir($sComDir)) return false;
	$sComFile = $sComDir.'/component.php';
	if(!is_readable($sComFile)) return false;
	return true;
}
function modulePresent($sModName){
	$sModName = preg_replace("/[^a-z0-9]/i",'',$sModName);
	if($sModName==='') return false;
	$sModDir = GTROOT.'/modules/'.$sModName;
	if(!is_dir($sModDir)) return false;
	return true;
}
function GetMessage($msg){
	$arBT = array_reverse(debug_backtrace());
	$arIF = array_reverse(get_included_files());
	//runtime cache support - disabled
	$sKeyName = $arBT[0]['file'];
	static $arSavedMessages = array();
	if(isset($arSavedMessages[$sKeyName][$msg]))
		return $arSavedMessages[$sKeyName][$msg];
	foreach($arIF as $arFD){
		$sDir = dirname($arFD).'/lang';
		$sFile = basename($arFD);
		if(!is_dir($sDir)) continue;
		$sRet = _GetMessageScanFile($sDir.'/'.SITE_LANG.'/'.$sFile,$msg);
		if($sRet!==FALSE) {
			$arSavedMessages[$sKeyName][$msg] = $MSG[$msg];
			return $sRet;
		}
		$sRet = _GetMessageScanFile($sDir.'/'.SITE_LANG.'/.lang.php',$msg);
		if($sRet!==FALSE) {
			$arSavedMessages[$sKeyName][$msg] = $MSG[$msg];
			return $sRet;
		}
	}

	return capitalize(str_replace('_',' ', $msg));
}

function capitalize($mix){
	$mix = explode(' ',$mix);
	$ret = array();
	foreach($mix as $x)
		$ret[] = strtoupper(substr($x,0,1)).strtolower(substr($x,1));
	return implode(' ',$ret);
}
/*
	function _GetMessageScanFile
	used by GetMessage() to isolate any internal variables;
*/
function _GetMessageScanFile($sLFile,$msg){
	if(!is_readable($sLFile)) return FALSE;
	//if(	eval('return 1;'))
	//{
	//	$fcnt = file_get_contents($sLFile);
	//	$fcnt = preg_replace('/^\<\?php\s/','',$fcnt);
	//	$fcnt = preg_replace('/^\<\?/','',$fcnt);
	//	$fcnt = preg_replace('/([^;])\s*\?\>$/','$1;',$fcnt);
	//	$fcnt = preg_replace('/\?\>$/','',$fcnt);
	//
	//	if(FALSE===eval($fcnt)){
	//		return FALSE;
	//	}
	//}else
		@include $sLFile;
	if(isset($MSG[$msg]))
		return $MSG[$msg];
	return FALSE;
}

function GTDateFormat($sFmt,$iTime = false,$sSkl = 'ro'){
	if(!$iTime)
		$iTime = time();

	$dt = new DateTime("@$iTime");
	//$td = setTimestamp($iTime);
	$local = array();
	for($i = 1;$i<13;$i++)
		$local[] = GetMessage('MONTH' . $sSkl . $i);
	for($i = 1;$i<13;$i++)
		$local[] = GetMessage('MONTH' . $i);
	$english = array(//'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday',
						'January','February','March','April','May','June','July','August','September','October','November','December',
						'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	return str_replace($english, $local, $dt->format($sFmt));
}

function d($a)
{
	echo '<div style="background: #AAA; overflow:auto; max-height:200px;">Start dump: ';
	echo "<pre>";
	print_r($a);
	echo "</pre>";
	echo 'end</div><br />';
}

function SWITCH_IT($val,$table)
{
	if(!is_array($val))
	{
		$arg= substr($val,0,1);
		$arg1= substr($val,1);
		switch($arg)
		{
		case '=':
		$a=$table."='".$arg1."'";
		break;
		case '<':
		$a=$table."<'".$arg1."'";
		break;
		case '>':
		$a=$table.">'".$arg1."'";
		break;
		case '~':
		$a=$table."  LIKE '".$arg1."'";
		break;
		case '!':
		$a=$table."<>'".$arg1."'";
		break;
		case '*':
		$a=$table."<='".$arg1."'";
		break;
		case '#':
		$a=$table.">='".$arg1."'";
		break;
		default:
		$a=$table."='".$val."'";
		break;
		}
		
		return $a;
	}
	elseif(is_array($val))
	{
		$ABZA=array();
		$AND=0;
		foreach($val as $ABZ)
		{
			
			if(is_numeric($ABZ))
			{
				$ABZA[]=$ABZ;
				
			}
			else
			{
				$arg= substr($ABZ,0,1);
				$arg1= substr($ABZ,1);
				$D='';
				$a='';
				switch($arg)
				{
				case '=':
				$D=$table."='".$arg1."'";
				$AND=0;
				break;
				case '<':
				$D=$table."<'".$arg1."'";
				$AND=1;
				break;
				case '>':
				$D=$table.">'".$arg1."'";
				if($AND==1){$AND=2;}
				break;
				case '~':
				$D=$table."  LIKE '".$arg1."'";
				$AND=0;
				break;
				case '!':
				$D=$table."<>'".$arg1."'";
				$AND=0;
				break;
				case '*':
				$D=$table."<='".$arg1."'";
				$AND=0;
				break;
				case '#':
				$D=$table.">='".$arg1."'";
				$AND=0;
				break;
				default:
				$a=$ABZ;
				break;
				}
				
				if($D)
				{
				$sqlOR2['BY'][]=$D;
				}
				elseif($a)
				{
				$sqlIN2['BY'][$table][]=$a;
				}
			}
				
		}
		$sqlasd['IN']=array();
		$INER='';
		if(!empty($sqlIN2['BY']))
		{
			foreach($sqlIN2['BY'] as $key=>$val)
			{
				$sqlasd['IN'][]=$table." IN ('".implode('\' ,\'',$sqlIN2['BY'][$key])."')";
				$INER=$table." IN ('".implode('\',\'',$sqlIN2['BY'][$key])."')";
			}
			$sqlIN2['BY']=array();
		}
		if(!empty($sqlOR2['BY']))
		{
			if($AND==2 || $AND==1)
			{
				$AND='AND';
			}
			else
			{
				$AND='OR';
			}
			$sql['BY'][]=" (".implode(" $AND ",array_merge($sqlasd['IN'],$sqlOR2['BY'])).") "; //d($sql);
			$sqlOR2['BY']=array();
		}
		elseif(!empty($sqlasd['IN']))
		{
			$sql['BY'][]=$INER;
		}
		if(!empty($ABZA))
		{
			$sql['BY'][]=$table." IN ('".implode('\',\'',$ABZA)."')";
		}
		//d($sql);
		return $sql['BY'];
	}
}

// 		TOOL CLASSES
class GTTool{
	private $arParams = array();
	private $mixFunc = array();

	function __construct($mixFunc,$arParams = array()){
		$this->mixFunc = $mixFunc;
		if(!is_array($arParams)) $arParams = array($arParams);
		$this->arParams = $arParams;
	}
	function Get($arOverrideParams = false){
		$arParams = $this->arParams;
		if($arOverrideParams!==false) $arParams = $arOverrideParams;
		return !is_array($this->mixFunc) && preg_match('/[^a-z0-9_]/',$this->mixFunc)
					? $this->GEval($arOverrideParams)
					: call_user_func_array($this->mixFunc,$arParams);
	}
	function GEval($arOverrideParams = false){
		function __eval($P,$__fstr){
			return eval($__fstr);
		}
		$arParams = $this->arParams;
		if($arOverrideParams!==false) $arParams = $arOverrideParams;
		return __eval($arParams);
	}
}

function verifyArray($stuff, $toInt = false){
	if(!is_array($stuff)) {
		$a = explode(',',$stuff);
		$stuff = array();
		foreach($a as $v)
			$stuff[] = $toInt?(int)trim($v):trim($v);
	}
	return $stuff;
}

function isolatedComponentCall($name, $comTemplate, $arVariables, &$arResult, $ctpath, $cpath){
	global $DB, $APP;
	error_reporting(E_ALL);
	if (file_exists($cpath . '/component.php')) {
		if(is_readable($cpath . '/.parameters.php'))
			include $cpath . '/.parameters.php';
		if(is_readable(GTDOCROOT . $ctpath . '/.parameters.php'))
			include GTDOCROOT . $ctpath . '/.parameters.php';
		try{
			@include $cpath . '/component.php';
		}catch(Exception $e){};
		//register conponent in internal list

		error_reporting(0);
		return true;
	} else
		GTApp::Raise('COM_NOT_FOUND');
	error_reporting(0);
	return false;
}

function stripSiteDir($from)
{
	return preg_replace('/^'.str_replace('/','\/',SITE_DIR).'/','/',$from);
}

function  IndexData($iParam='',$MODULE='DBLOCK')
{
	global $DB;
	$res=$DB->Query("SHOW GLOBAL VARIABLES LIKE 'max_allowed_packet'");
	$row=$DB->fetchAssoc($res);
	$max_allowed_packet=$row['Value'];
	if($MODULE=='DBLOCK')
	{	
		$iRes=GTdblock::GetSearchableContent($iParam); 
		//d($iRes);//die();
		$MULTY=GTAPP::Conf('multilang_sync');
		if(!empty($iRes))
		{
		
			$iVals=array();
			$ININDEX=array();
			$Map=array();
			$CREATED=strtotime(date('Y-m-d H:m:s'));
			foreach($iRes as $val)
			{
				$IDs[$val['ID']]=$val['ID'];
				$ID=$val['ID'];
				if($MULTY!=FALSE)
				{
					$Lang=$val['LANG'];
					$Langs[$val['LANG']]=$val['LANG'];
				}
				$LINK=$val['LINK'];
				$TITLE=strip_tags($val['TITLE']);
				$ININDEX[]=$ID;
				$SQL[]="('$ID','$TITLE','$CREATED','$MODULE','$LINK')"; 
				unset($LINK);
				unset($val['LINK']);
				unset($val['LANG']);
				unset($val['ID']);
				foreach($val as $iKey=>$iVal)
				{
					$iVal=trim($iVal);
					if(!empty($iVal))
					{
						$iVal=preg_replace('/\<\?.*\?\>/ms',' ',$iVal);
						$iVal=preg_replace('/\<script(.*)\/script\>/ims',' ',$iVal);
						$iVal=preg_replace('/[+\.,_\(\)\\:;\'"-]/imsu',' ',$iVal);
						$iVal=preg_replace('/[[:digit:]]/imsu',' ',$iVal);
						$iVal=str_replace('“',' ',$iVal);
						$iVal=str_replace('”',' ',$iVal);
						$iVal=str_replace('–',' ',$iVal);
						$iVal=str_replace("\n",' ',$iVal);
						$iVal=strip_tags($iVal);
						$iVal=str_replace('?',' ',str_replace('!',' ',str_replace('»',' ',str_replace('«',' ',str_replace('№',' ',str_replace('"',' ',str_replace('-',' ',str_replace('.',' ',str_replace(',',' ',str_replace(')',' ',str_replace('(',' ',$iVal)))))))))));
						if($MULTY!=FALSE)
						{
							$iValse[$ID][$Lang][]=explode(' ',$iVal);
						}
						else
						{
							$iValse[$ID][]=explode(' ',$iVal);
						}
					}
				}
				foreach($iValse[$ID][$Lang] as $val33)
				{
					foreach($val33 as $iV)
					{
						$iV=preg_replace('/\<\?.*\?\>/ms',' ',$iV);
						$iV=preg_replace('/\<script(.*)\/script\>/ims',' ',$iV);
						$iV=preg_replace('/[+\.,_\(\)\\:;\'"-]/imsu',' ',$iV);
						$iV=preg_replace('/[[:digit:]]/imsu',' ',$iV);
						$iV=strip_tags($iV);
						$iV=trim($iV);
						if(strlen($iV)<=2){$iV='';}
						switch($iV)
						 {
							case 'и':
								$iV=str_replace('и',' ',$iV);
							break;
							case '–':
								$iV=str_replace('–',' ',$iV);
							break;
							case 'ул':
								$iV=str_replace('ул',' ',$iV);
							break;
							case 'уг':
								$iV=str_replace('уг',' ',$iV);
							break;
							case 'пр':
								$iV=str_replace('пр',' ',$iV);
							break;
							case 'уг.пр':
								$iV=str_replace('уг.пр','',$iV);
							break;
						 }
						$iV=str_replace('?',' ',str_replace('!',' ',str_replace('»',' ',str_replace('«',' ',str_replace('№',' ',str_replace('"',' ',str_replace('-',' ',str_replace('.',' ',str_replace(',',' ',str_replace(')',' ',str_replace('(',' ',$iV)))))))))));
						$iV=str_replace('уг.пр',' ',$iV);
						$iV=str_replace('</i>см',' ',$iV);
						$iV=str_replace('<A',' ',$iV);
						$iV=str_replace('&quot',' ',$iV);
						$iV=str_replace('&nbsp',' ',$iV);
						$iV=str_replace('&gt',' ',$iV);
						$iV=str_replace('&lt',' ',$iV);
						$iV=str_replace('/i&gt',' ',$iV);
						$iV=str_replace('“',' ',$iV);
						$iV=str_replace('”',' ',$iV);
						$iV=str_replace('–',' ',$iV);
						$iV=str_replace('<br',' ',$iV);
						$iV=str_replace('/>',' ',$iV);
						$iV=str_replace('HREF="showphpID=">',' ',$iV);
						$iV=str_replace('</a>',' ',$iV);
						$iV=str_replace('"',' ',$iV);
						$iV=str_replace("'",' ',$iV);
						if(strlen($iV)>255)
						{
							$iV='';
						}
						$iV=htmlspecialchars(addslashes(trim($iV)));
						if(!empty($iV) && !is_numeric($iV))
						{
							if($MULTY!=FALSE)
							{
								$words[$ID][$Lang][]=$iV;
								
							}
							else
							{
								$words[$ID][]=$iV;
							}
							$words3[]=$iV;
							
						}
					}
				}
			}
			//d($Langs);
			//d($iValse);
			//d($words);
			//die();
			if(!empty($SQL))
			{
				$DB->Query("DELETE FROM `search_cache_elements` WHERE `EID` IN ('".implode('\',\'',$IDs)."')");
				$Nsql="INSERT IGNORE INTO `search_cache_elements` (`EID`,`ENAME`,`ELAST_CACHED`,`MODULE`,`LINK`) VALUES ".implode(',',$SQL);//d($Nsql);
				$DB->Query($Nsql);
				$Nsql="SELECT * FROM `search_cache_elements` WHERE `EID` IN ('".implode('\',\'',$IDs)."')";//d($Nsql);
				$RES=$DB->Query($Nsql);
				while($row=$DB->fetchAssoc($RES))
				{
					
					if($MULTY!=FALSE)
					{
						if(!empty($row['LINK']))
						{								
							$Liink='';
							if(empty($Liink))
							{
								//$Liink=strstr($row['LINK'],$key);d($Liink);
								$Liink=substr($row['LINK'],1,2);//d($Liink);
							}
							if(!empty($Liink))
							{
								$Map[$IDs[$row['EID']]][$Liink]=$row['CEID'];
								//$words[$IDs[$row['EID']]][$Liink][]=$iV;
								$tom=$row['CEID'];
							}
							$Liink='';							
						}
						
					}
					else{$Map[$IDs[$row['EID']]]=$row['CEID']; $tom=$row['CEID'];}
				}//d($Map);
			}
			if(!empty($ININDEX))
			{
				$inix="('".implode('\',\'',$ININDEX)."')";
				$Maps="('".implode('\',\'',$tom)."')";
				$DB->Query("UPDATE `g_dblock` SET `ININDEX`='1' WHERE `ID` IN $inix");
				$DB->Query("DELETE FROM `search_cache_constructs` WHERE `CEID` IN $Maps");
				$DB->Query("DELETE FROM `search_cache_counts` WHERE `CEID` IN $Maps");
			}
			
			if(!empty($words3))
			{
				$ins='';
				$IN='';
				$words3=array_unique($words3);
				
				$i=1;
				$massa=0;
				$txt='';
				foreach($words3 as $val)
				{
					$massa+=(strlen($val)+5)*2;
					if($massa>=$max_allowed_packet-500)
					{
						
						$i++;
						$t="NEXT";
						$massa=0;
						
					}
					else
					{
						$txt.="('$val'),";
					}
					$ins[$i]=substr($txt, 0,-1);
					if($t=="NEXT")
					{
						$t='';
						$txt='';
						$txt.="'$val',";
					}
				}
				/*foreach($iW3 as $key=>$val)
				{
					$ins[$key]="('".implode("'),('",$val)."')"; //d($ins);
				}*/
				//d($ins);
				$i=0;
				foreach($ins as $key=>$val)
				{
					$sql="INSERT IGNORE INTO `search_cache_words` (`W_TEXT`) VALUES ".$val.""; //echo $i; $i++; d($sql);
					$DB->Query($sql);
				}
				$iLasr=array();
				$COUNT=array();
				$i=0;
				
				foreach($words as $key=>$val)
				{
					if($MULTY!=FALSE)
					{
						if(!empty($val['ru']))
						{
							$IN="('".implode("','",$val['ru'])."')";
							$sql2="SELECT * FROM `search_cache_words` WHERE `W_TEXT` IN ".$IN; // echo $i; $i++; //d($sql2);
							$res=$DB->Query($sql2);
							while($row=$DB->fetchAssoc($res))
							{
								$p='';
								$p=array_search($row['W_TEXT'],$val['ru']);
								if(!empty($p))
								{
									$iArrRes[]="('".$Map[$key]['ru']."','".$row['W_ID']."','$p')";
									$COUNT[$row['W_ID']][$Map[$key]['ru']]++;
								}
							}
						}
						if(!empty($val['kz']))
						{
							$IN="('".implode("','",$val['kz'])."')";
							$sql2="SELECT * FROM `search_cache_words` WHERE `W_TEXT` IN ".$IN; // echo $i; $i++; //d($sql2);
							$res=$DB->Query($sql2);
							while($row=$DB->fetchAssoc($res))
							{
								$p='';
								$p=array_search($row['W_TEXT'],$val['kz']);
								if(!empty($p))
								{
									$iArrRes[]="('".$Map[$key]['kz']."','".$row['W_ID']."','$p')";
									$COUNT[$row['W_ID']][$Map[$key]['kz']]++;
								}
							}
						}
					}
					else
					{
						$IN="('".implode("','",$val)."')";
						$sql2="SELECT * FROM `search_cache_words` WHERE `W_TEXT` IN ".$IN; // echo $i; $i++; //d($sql2);
						$res=$DB->Query($sql2);
						while($row=$DB->fetchAssoc($res))
						{
								$iArrRes[]="('".$Map[$key]."','".$row['W_ID']."','$p')";
								$COUNT[$row['W_ID']][$Map[$key]]++;
						}
					}
				}
//d($COUNT);
				if(!empty($COUNT))
				{
					$iCMP='';
					$i=1;
					$j=1;
					foreach($COUNT as $key => $val)
					{
						foreach($val as $ikey => $ival)
						{
							$ost=($i%100);
							if($ost==0)
							{
								$j++;
							}
							$iCNT[$j][]="('$key','$ikey','$ival')";
							$i++;
						}
					}//d($iCNT);
					foreach($iCNT as $key=>$fal)
					{
						$iCMP=implode(',',$fal);
						if(!empty($iCMP))
						{
							$sql3="INSERT INTO `search_cache_counts` (`WID`,`CEID`,`COUNT`) VALUES ".$iCMP; //d($sql3);
							$DB->Query($sql3);
						}
					}
				}
				if(!empty($iArrRes))
				{
					$imp='';
					$i=1;
					$j=1;
					foreach($iArrRes as $val)
					{
						$ost=($i%100);
						if($ost==0)
						{
							$j++;
						}
						$iLasv[$j][]=$val;
						$i++;
						
						
					}//d($iLasv);
					foreach($iLasv as $key=>$fal)
					{
						$imp=implode(',',$fal);
						if(!empty($imp))
						{
							$sql3="INSERT INTO `search_cache_constructs` (`CEID`,`WID`,`POSITION`) VALUES ".$imp; //d($sql3);
							$DB->Query($sql3);
						}
					}
					
				}
				return TRUE;
			}
			
		}
	}
	elseif($MODULE=='TEXTPAGES')
	{
		GTdblock::GetSerchablePages();
		return TRUE;
	}
}

function SearchResults($searched=FALSE)
{
	if(empty($searched)){return FALSE;}
	global $DB;
	//$RELEVANCE=1;
	$searched=htmlspecialchars(trim($searched)); 
	$MAIN="SELECT * FROM `search_results` WHERE `RTEXT`='$searched'"; //d($MAIN);
	$iManR=$DB->Query($MAIN);
	$iMainA=array();
	while($iManW=$DB->fetchAssoc($iManR))
	{
		$iMainA[]=$iManW;
	}
	if(!empty($iMainA))
	{
		$iMiD=array();
		//$DB->Query("UPDATE `search_results` SET ` WHERE `RID` IN $iMiDs");
		foreach($iMainA as $val)
		{
			$iMiD[]=$val['RID'];
		}
		$iMiDs="('".implode('\',\'',$iMiD)."')";
		$iRItmA=array();
		$iSqlR="SELECT * FROM `search_results_items` WHERE `RID` IN $iMiDs ORDER BY `RRELEVANCE` DESC";
		$iRItm=$DB->Query($iSqlR);
		while($iRItmW=$DB->fetchAssoc($iRItm))
		{
			$iRItmA[]=$iRItmW;
		}
		return $iRItmA;
	}
	if(empty($iMainA))
	{
		$WORDS=explode(' ',$searched);
		$IN="('".implode("','",$WORDS)."','$searched')";
		$SQL="SELECT * FROM `search_cache_words` WHERE `W_TEXT` IN $IN"; //d($SQL);
		$iWID=array();
		$res=$DB->Query($SQL);
		while($row=$DB->fetchAssoc($res))
		{
			$iWID[]=$row['W_ID'];
			$iW_TEXT[$row['W_ID']]=$row['W_TEXT'];
		}
		
		$iWIDS="('".implode("','",$iWID)."')";
		if(empty($iWID)){return FALSE;}
		$SQL="SELECT * FROM `search_cache_constructs` WHERE `WID` IN $iWIDS";
		$iConst=array();
		$res=$DB->Query($SQL);
		while($row=$DB->fetchAssoc($res))
		{
			$iConst[]=$row;
			$iRtext[$row['CEID']][$row['WID']]=$iW_TEXT[$row['WID']];
		}
		
		$txt=array();
		if(empty($iConst)){return FALSE;}
		$i=1;
		$j=1;
		foreach($iConst as $val)
		{
			$CEID=$val['CEID'];
			$POSITION=$val['POSITION'];
			$WID=$val['WID'];
			$maxP=$POSITION+3;
			$minP=$POSITION-3;
			$mode=($i%100);
			if($mode==0)
			{
				$j++;
			}
			$SQLs[$j][]="\n(`CEID`='$CEID' AND (`POSITION`>'$minP' AND `POSITION`<'$maxP'))"; //d($SQL);
			$SQLr[$j][]="\n(`CEID`='$CEID' AND `WID`='$WID')"; //d($SQL);
			$i++;
			
		}
		//d($SQLs);
		foreach($SQLr as $key=>$val)
		{
			$Rsql="\nWHERE ".implode(' OR ',$val);
			$sql="SELECT * FROM `search_cache_counts`".$Rsql."\n ORDER BY `CEID`,`WID` ASC";
			$res=$DB->Query($sql);
			while($row=$DB->fetchAssoc($res))
			{
				$iRel[$row['CEID']][$row['WID']]=$row['COUNT'];
			}
		}
		foreach($SQLs as $key=>$val)
		{
			$Lsql="\nWHERE ".implode(' OR ',$val);
			$sql="SELECT * FROM `search_cache_constructs`".$Lsql."\n ORDER BY `CEID`,`POSITION` ASC";
			$res=$DB->Query($sql);
			while($row=$DB->fetchAssoc($res))
			{
				$iConsta[$row['CEID']][$row['POSITION']]=$row['WID'];
			}
		}
		foreach($iConsta as $key => $val)
		{
			foreach($val as $iV)
			{
				$TXT[]=$iV;
			}
		}
		//d($TXT);
		
		$TXT=array_unique($TXT);
		$TXT2="('".implode('\',\'',$TXT)."')";
		if(!empty($TXT2))
		{
			$SQL="SELECT * FROM `search_cache_words` WHERE `W_ID` IN $TXT2";
			$res=$DB->Query($SQL);
			while($row=$DB->fetchAssoc($res))
			{
				$iResIt[$row['W_ID']]=$row;
			}
		}
		//d($iResIt);
		//die();
		foreach($iConsta as $key => $val)
		{
			$SuperRel[$key]=1;
			$TXTS='';
			$i=0;
			$s=0;
			$urel='';
			
			foreach($val as $keys=>$vv)
			{ 
				$SuperRel[$key]=$SuperRel[$key]+($iRel[$key][$vv]/100);
				
				$litle='';
				if($i=0)
				{
					$s=$keys++;
					$i++;
				}
				else
				{
					if($s<$keys)
					{
						$litle="...\n";
						
						$urel=($keys-$s)/100;
						$SuperRel[$key]=$SuperRel[$key]-$urel;
						$s=$keys;
						$s++;
					}
					else
					{
						$litle=' ';
						$SuperRel[$key]=$SuperRel[$key]+0.1;
						$s++;
					}
				}
				if($iResIt[$vv])
				{
					$TXTS=$TXTS.$litle.$iResIt[$vv]['W_TEXT'];
				}
				
			}
			$summa[$key]=0;
			foreach($SubRel as $val)
			{
				$summa[$key]=$summa[$key]+$val;
			}
			
			$TS[$key]['TEXT']=$TXTS;
			$TS[$key]['RELEVANCE']=$SuperRel[$key];
		}//d($TS);
		//die('END');
		if(!empty($TS))
		{
			$TOTAL=0;
			foreach($TS as $key=>$val)
			{
				if($val['RELEVANCE']>0.5){$TOTAL++; $Md[]=$key;}
				else{$c[]=$key;}
				
			}
			foreach($c as $val)
			{
				unset($TS[$val]);
			}
			$gen=gen_string(8);
			$CREATED=strtotime(date('Y-m-d H:m:s'));
			$SQL="INSERT INTO `search_results` (`RTEXT`,`RTOTAL`,`RTIME`,`RHASH`) VALUES ('$searched','$TOTAL','$CREATED','$gen')"; //d($SQL);
			$DB->Query($SQL);
			$RID=$DB->insertId();
			$IN="('".implode('\',\'',$Md)."')";
			$RES_IT=array();
			$SQM="SELECT `EID`,`CEID`,`ENAME`,`LINK`,`MODULE` FROM `search_cache_elements` WHERE `CEID` IN $IN"; //d($SQM);
			$res=$DB->Query($SQM);
			while($row=$DB->fetchAssoc($res))
			{
				$RLINK='';
				if($row['MODULE']=='DBLOCK')
				{
				$RLINK=RLINK($row['EID'],$row['LINK']);
				$RES_IT[]=array('RID'=>$RID,'ITITLE'=>$row['ENAME'],'RSUBTEXT'=>$TS[$row['CEID']]['TEXT'],'RLINK'=>$RLINK,'RRELEVANCE'=>$TS[$row['CEID']]['RELEVANCE']);
				}
				else
				{
					$RES_IT[]=array('RID'=>$RID,'ITITLE'=>$row['ENAME'],'RSUBTEXT'=>$TS[$row['CEID']]['TEXT'],'RLINK'=>$row['LINK'],'RRELEVANCE'=>$TS[$row['CEID']]['RELEVANCE']);
				}
			}
			if(!empty($RES_IT))
			{
				foreach($RES_IT as $V)
				{
					$RI[]="('".$V['RID']."','".$V['ITITLE']."','".$V['RSUBTEXT']."','".$V['RLINK']."','".$V['RRELEVANCE']."')";
				}
				$END="INSERT INTO `search_results_items` (`RID`,`ITITLE`,`RSUBTEXT`,`RLINK`,`RRELEVANCE`) VALUES ".implode(',',$RI);
				$DB->Query($END);
			}
			
			$iRItmA=array();
			$iSqlR="SELECT * FROM `search_results_items` WHERE `RID`='$RID' ORDER BY `RRELEVANCE` DESC";
			$iRItm=$DB->Query($iSqlR);
			while($iRItmW=$DB->fetchAssoc($iRItm))
			{
				$iRItmA[]=$iRItmW;
			}
			return $iRItmA;
		}
	}
	
}
function RLINK($ID,$LINK)
{
	$iRes['LINK']=str_replace("#ID#",$ID,$LINK);
	//$iRes['LINK']=str_replace("PROPERTY[ID]",$ID,$iRes['LINK']);
	//$LINK="?ID=$ID";
	return $iRes['LINK'];
}

function mkThumbMS($src, $samplefile, $intw, $inth=0)
{
 $EXTIMT = array(
 IMAGETYPE_GIF=>'gif',
 IMAGETYPE_JPEG=>'jpeg',
 IMAGETYPE_PNG=>'png',

 IMAGETYPE_BMP=>'bmp',

 IMAGETYPE_WBMP=>'wbmp',
 IMAGETYPE_XBM=>'xbm'
 );
 $inth = $inth==0 ? $intw : $inth;
 $needw = $intw; $needh = $inth; $force = true;
 if(@extension_loaded('gd'))
 {
  //create sample, else dont even try
  $fsz = @getimagesize( $src );
 // put("Исходный размер: ".print_r($fsz,1)."<br>Обрезка под: $int");
  $targimg = $targ;
  error_reporting(E_ALL);
  if(!file_exists($samplefile) || $force)
  {
   if(file_exists($samplefile))
   {
    unlink($samplefile);
    echo 'ex';
   }
   if($fsz[0]>$needw || $fsz[1]>$needh)
   {
    $takex = 0;
    $takey = 0;
    //here we create thumb
    //determine thumb dimension
    if($fsz[0] > $fsz[1]){
     $takew = $fsz[1]*$needw/$needh;
     $takeh = $fsz[1];
     $takex = round($fsz[0]-$takew)/2;
    }else{
     $takew = $fsz[0];
     $takeh = $fsz[0]*$needh/$needw;
     //$takey = round($fsz[1]-$takeh)/2;
    }
    $takew=round($takew); $takeh=round($takeh);
    $ext = strtolower(substr($src,1+strrpos($src,'.')));
    if(($im_dst = imagecreatetruecolor($needw, $needh)) && array_key_exists($fsz[2],$EXTIMT)){
     $HND = 'imagecreatefrom'.$EXTIMT[$fsz[2]];
     if(($im_src = $HND($src)) && @imagecopyresampled ($im_dst,$im_src,0,0,$takex,$takey,$needw,$needh,$takew,$takeh))
     {
      $OUTFUNC = 'image'.$EXTIMT[$fsz[2]];
      $OUTFUNC($im_dst,$samplefile);
      imagedestroy($im_dst);
      imagedestroy($im_src);
      return 1;
     }
    }
   }
  }
 }
 return @copy($src, $samplefile);
}
?>