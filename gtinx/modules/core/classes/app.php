<?php

class GTApp {
    var $DB;
    private $CONF;
    var $TEMPLATE = '';
    private $admSections = array('modules','config');
    private $SITE_TITLE = '';
    private $SITE_DATA = array();
    private $PAGE_HEADER = '';
    private $PAGE_META = 'FIXME!! META SHOULD BE HERE';
    private $USER = array();
    private $USERDATA = array();
    private $HTMLHEADERS = array();
	private $HTMLHEADERSUNIQID = '';
	private $PANELUNIQID = '';
    var $arResult = array();
    private $currComponent = '';
    private $currComponentVariables = array();
    private $currComponentResult = array();
    private $currModule = '';
    private $admCurrModule = '';
    private $entryPoint = '';
    private $USERPRIVS = array();
    private $USERGROUPS = array();
    private $userBreadCrumbs = array();
    var $entryDir = '';
    private $admModulesData = array();
    private $admConfigData = array();
    private $pageComponents = array();
    private $pageMenuTypes = array();
    var $OUTPUT = '';
    var $modOutput;
    var $alfaCalled = false;
    var $moduleParam = Array();
    function __construct($db_instance)
    {
	
		if(PassedVal('nocache'))
			define('SKIP_CACHE',true);
		$this->DB = $db_instance;
		$cq = $this->DB->query("SELECT * FROM `g_mainconf` ORDER BY `conf_version` DESC LIMIT 1/*Loading config*/");
		$GLOBALS['CONF'] = $this->DB->fetchAssoc($cq);
        if (!defined('GTINX_DIRECT_OUTPUT'))
            ob_start();
        //if (defined('GT_ADMIN')) {
            $this->admModulesData = cacheGetVars('admModulesData',0); //autoload adm tree data
        //}
        // $arBT = debug_backtrace();
        // $sX = preg_replace('/\/{2,}/','/',$arBT[count($arBT)-1]['file']);
        $sX = $_SERVER['SCRIPT_FILENAME'];
        $sX = str_replace(GTDOCROOT, '', $sX);
        $this->entryPoint = addslashes($sX);
        $this->entryDir = addslashes(dirname($sX));
		//determine user
    }
	function Conf($a){
		return $GLOBALS['CONF'][$a];
	}
	function GetEntryPoint(){
		return $this->entryPoint;
	}
	function CheckAuth($sUser,$sPw){
		global $DB;
		$ip = get_user_ip();
		if (defined('IPBAN_ENABLE')) {
            // check ip is banned
            $ipban = $DB->query("SELECT * FROM `g_site_login_ip`
	                            WHERE `IP`='$ip' AND `FAILURE_LAST`>='" . (time() - 5*60) . "'
	                                AND `FAILURES_TOTAL`>='3' ORDER BY `FAILURES_BEGIN` DESC LIMIT 1");
            if ($DB->NumRows($ipban) > 0) {
                return false;
            }
        }
		$q = $DB->Query("SELECT * FROM `g_users` WHERE `LOGIN`='$sUser' AND `PASSWD`='".md5($sPw)."' AND `ACTIVE`!='0'
							AND (`ACTIVE_FROM`='0' || `ACTIVE_FROM`<=UNIX_TIMESTAMP())
							AND (`ACTIVE_TO`='0' || `ACTIVE_TO`>=UNIX_TIMESTAMP()) LIMIT 1");
		if(!$DB->NumRows($q)) return false;
		$uTmp = $DB->FetchAssoc($q);
		$newsid = GTSession::Create($uTmp['LOGIN'],$uTmp['ID']);
		if(!$newsid)
			return false;

		$DB->Query("UPDATE `g_users` SET `LASTLOGIN_TIME`=UNIX_TIMESTAMP(), `LASTLOGIN_IP`='$ip'  WHERE `ID`='$uTmp[ID]' ");
		$this->USER = array('USER_NAME'=>$uTmp['LOGIN'], 'USER_ID'=>$uTmp['ID'], 'SID' => $newsid, 'IP'=>$ip);
		$this->LoadUserPrivs();
		$this->GetCurrentUser();
		return true;
	}
	function LogoutOne($sid = false){
		GTSession::Kill($sid?$sid:$this->USER['SID']);
	}

	function UserAble($privname,$subsystem=''){
		//print_r($this->USERPRIVS);
		if(is_array($this->USERPRIVS[''])&&in_array('SU',$this->USERPRIVS['']))return true;
		if(empty($this->USERPRIVS[$subsystem])) return false;
		return in_array($privname,$this->USERPRIVS[$subsystem]) ;
	}

	function LoadUserPrivs(){
		global $DB;
		$iUserId = $this->GetCurrentUserID();
		if(!$iUserId) return;

		if($r = cacheGetVars('usergroups'.$iUserId,0))
			$this->USERGROUPS = $r;
		else{
			$q = $DB->Query("SELECT `g`.* FROM  `g_usergroups` `ug`
									LEFT JOIN `g_groups` `g` ON `ug`.`GROUP_ID`=`g`.`ID`
									WHERE ug.USER_ID='$iUserId'
										AND (`ug`.`ACTIVE_FROM`='0' || `ug`.`ACTIVE_FROM`<=UNIX_TIMESTAMP())
										AND (`ug`.`ACTIVE_TO`='0' || `ug`.`ACTIVE_TO`>=UNIX_TIMESTAMP())
										AND (`g`.`ACTIVE_FROM`='0' || `g`.`ACTIVE_FROM`<=UNIX_TIMESTAMP())
										AND (`g`.`ACTIVE_TO`='0' || `g`.`ACTIVE_TO`>=UNIX_TIMESTAMP())
										AND `ug`.`ACTIVE`!='0'
										AND `g`.`ACTIVE`!='0'
									");
			while($r = $DB->fetchAssoc($q)){
				$this->USERGROUPS[$r['ID']] = $r;
				if($r['SU'])
					$this->USERPRIVS['']['SU'] = 'SU';
			}
		}

		if($r = cacheGetVars('userprivs'.$iUserId))
			$this->USERPRIVS = $r;
		elseif(!empty($this->USERGROUPS)){

			$q = cachedQuery("SELECT `NAME`, `SUBSYSTEM` FROM `g_groups_privs` WHERE `GROUP_ID` IN (".implode(',',array_keys($this->USERGROUPS)).")");
			foreach($q as $r){
				$this->USERPRIVS[$r['SUBSYSTEM']][] = $r['NAME'];
			}
			cacheSetVars('userprivs'.$iUserId,$this->USERPRIVS);
		}

	}

	function RequireAuth(){
		if($this->GetCurrentUserID()) return true;
		$this->IncludeComponent('gtinx:core.auth','.admin');
		return false;
	}

    function GetCurrentUser($forceRenew = false){
		if(!$this->GetCurrentUserID()) return array();
		if(empty($this->USERDATA) || $forceRenew){
			$ud = GTUsers::Get('*',array('ID'=>$this->GetCurrentUserID()));
			$this->USERDATA = $ud[$this->GetCurrentUserID()];
		}

		return $this->USERDATA;
	}

    function GetCurrentUserID()
    {
        return (int)($this->USER['USER_ID']);
    }

	function GetCurrentUserLogin()
    {
        return (int)($this->USER['USER_NAME']);
    }

    function IncludeComponent($name, $comTemplate = '.default', $arVariables = array())
    {
        global $APP, $DB;
        $cpath = GTROOT . '/components/' . str_replace(':', '/', $name);
        $ctpath = SITE_TEMPLATE_PATH . '/components/' . str_replace(':', '/', $name);

		$arResult = array();
		//protect internal vars from reassigning in case of folded component call
		$scompName = $this->currComponent;
		$scompVars = $this->currComponentVariables;
		$scompResult = $this->currComponentResult;

        $this->currComponent = $name;
        $this->currComponentVariables = &$arVariables;
        $this->currComponentResult = &$arResult;

		//$this->arResult = &$arResult;		//backward compatibility
		/*
        if (file_exists($cpath . '/component.php')) {
            if(is_readable($cpath . '/.parameters.php'))
				include $cpath . '/.parameters.php';
            if(is_readable(GTDOCROOT . $ctpath . '/.parameters.php'))
				include GTDOCROOT . $ctpath . '/.parameters.php';
			try{
				@include $cpath . '/component.php';
			}catch(Exception $e){};
			$this->pageComponents[] = array('name'=>$this->currComponent,'template'=>$comTemplate,'vars'=>$arVariables);
        } else {
            $this->Raise('COM_NOT_FOUND');
        }*/

		if(isolatedComponentCall($name, $comTemplate, $arVariables, $arResult, $ctpath,$cpath))
			//register conponent in internal list
			$this->pageComponents[] = array('name'=>$this->currComponent,'template'=>$comTemplate,'vars'=>$arVariables);

		$this->currComponentResult = $scompResult;
		$this->currComponent = $scompName;
		$this->currComponentVariables = $scompVars;
    }
	function registerMenuType($type){
		if(!in_array($type,$this->pageMenuTypes))
			$this->pageMenuTypes[] = $type;
	}
    function IncludeComponentTemplate($tpl)
    {
    	global $APP;
    	//error_reporting(E_ALL);
		$this->currComponent = str_replace(array(':','../','..\\'), array('/','',''), $this->currComponent);
		$tpl = str_replace(array('../','..\\'), array('',''), $tpl);
        $ctpath = GTDOCROOT . SITE_TEMPLATE_PATH . '/components/' . $this->currComponent . '/' . $tpl;
		if(!is_dir($ctpath))
		{
			$ctpath = GTROOT . '/components/' . $this->currComponent . '/templates/' . $tpl;
			if(!is_dir($ctpath))
				return $this->Raise('COM_TPL_DIR_NOT_FOUND (' . $tpl . ')');
		}
        if (file_exists($ctpath . '/style.css'))
            $this->AddPageCSS(str_replace(GTDOCROOT,'',$ctpath ). '/style.css');
        if(file_exists($ctpath . '/template.php')) 
		{
			$arVariables = $this->currComponentVariables;
            $arResult = $this->currComponentResult;
			//if(	eval('return 1;'))
			//{
			//	$fcnt = file_get_contents(GTDOCROOT . $ctpath . '/template.php');
			//	$fcnt = preg_replace('/^\<\?php\s/','',$fcnt);
			//	$fcnt = preg_replace('/^\<\?/','',$fcnt);
			//	$fcnt = preg_replace('/([^;])\s*\?\>$/','$1;',$fcnt);
			//	$fcnt = preg_replace('/\?\>$/','',$fcnt);
			//
			//	if(FALSE===eval($fcnt))
			//		$this->Raise('COM_TPL_ERROR (' . $tpl . ')');
			//}else
				@include $ctpath . '/template.php';
        } else
            $this->Raise('COM_TPL_FILE_NOT_FOUND (' . $tpl . ')');
    }
    function renderAll()
    {
        global $APP;
		if (!defined('GTINX_DIRECT_OUTPUT')) {
            // get OB
            $ob = ob_get_contents();
            // clean OB
            ob_clean();
			if(!empty($this->USER))
				GTSession::Update($this->GetCurrentUserLogin(),$this->GetCurrentUserID());
            
			// include header
            includeIfExists(GTDOCROOT . SITE_TEMPLATE_PATH . '/header.php');
			//header can be preprocessed
			$obheader = ob_get_contents();
            ob_clean();
			
            // include footer
            includeIfExists(GTDOCROOT . SITE_TEMPLATE_PATH . '/footer.php');
			$obfooter = ob_get_contents();
            ob_clean();
			
			
			if($this->HTMLHEADERSUNIQID)
				$obheader = str_replace($this->HTMLHEADERSUNIQID,$this->ShowHead(false),$obheader);
			if($this->PANELUNIQID)
				$obheader = str_replace($this->PANELUNIQID,$this->ShowPanel(false),$obheader);
			
			
            // echo OB
			echo $obheader;
			echo $ob;
			echo $obfooter;
			
			if($this->UserAble('SU')){
				$parsedur = round(getMicroTime()-PARSESTART,6);
				?><html><body><center>[Время выполнения: <?=$parsedur?> c.] [Запросов к БД: <?=$this->DB->querynum?>]<?
				if($servload = getLoadAvg()) echo " [Загрузка сервера: $servload] " ;
				?></center></body></html><?
			}
			if($this->UserAble('SU') && $_REQUEST['sqltrack']){
				?><html><body><center>
				<?=nl2br($this->DB->querylist);?>
				</center></body></html><?
			}

            ob_end_flush();
        }else
			if(!empty($this->USER))
				GTSession::Update($this->GetCurrentUserLogin(),$this->GetCurrentUserID());
        $this->Omega();
    }

	function SiteLang(){
		global $APP;
		if(!isset($APP)){	
			GTAPP::Raise('APP NOT SET');
		}
		return defined('SITE_LANG') ? SITE_LANG : GTAPP::getDeafaultLang();
	}
    function Alfa()
    {
		if($this->alfaCalled) return;
		$this->alfaCalled = true;
		
		$this->USER = GTSession::GetUser();
		if(!empty($this->USER)){
			//fill user privs
			$this->LoadUserPrivs();
			$this->GetCurrentUser();
		}
		
        // determine current site
		$sq = cachedQuery("SELECT * FROM `g_sites` WHERE `site_enabled`='1'");
		$preResult = array();
		$defaultResult = array();
		foreach($sq as $site)
		{
			$ITEM = array(
				'ID'=>$site['site_id'],
				'NAME'=>$site['site_name'],
				'DOMAINS'=>verifyArray($site['site_domains']),
				'TEMPLATE'=>trim($site['site_template']),
				'LANG'=>trim($site['site_lang'])
			);
			$ITEM['DIR'] = $site['site_dir']{0}=='/' ? $site['site_dir'] : '/'.$site['site_dir'];
			$ITEM['URL'] = 
				(is_array($ITEM['DOMAINS']) && strlen($ITEM['DOMAINS'][0]) > 0 || strlen($arSite['DOMAINS']) > 0?'http://':'').
				(is_array($ITEM["DOMAINS"]) ? $ITEM["DOMAINS"][0] : $ITEM["DOMAINS"]).
				$ITEM["DIR"];
			if($site['site_default'])
				$defaultResult = $ITEM;
			$preResult[] = $ITEM;
		}
		usort($preResult, "__AlfaSiteCmp");
		foreach($preResult as $ITEM){
			if(strpos($_SERVER['REQUEST_URI'],$ITEM['DIR'])===0) {
				$SITE_DATA = $ITEM;
				break;
			}
		}
		//d($SITE_DATA);
        // redirect to default site if urls do not match
		//if(empty($SITE_DATA)){
		//	header("Location: ".$defaultResult['URL']);
		//	die();
		//}
        // define constants
        define('CURRENT_DIR', substr($_SERVER['REQUEST_URI'], 0, 1 + strrpos('/', $_SERVER['REQUEST_URI'])));
        define('SITE_DIR', $SITE_DATA['DIR']);
        define('SITE_LANG', $SITE_DATA['LANG']);
        define('SITE_ID', $SITE_DATA['ID']);
		$this->TEMPLATE = $SITE_DATA['TEMPLATE'];
        define('SITE_TEMPLATE_PATH', '/gtinx/templates/' . $SITE_DATA['TEMPLATE']);

		//reset entry point
		if(/*defined('GT_HRU') && */$this->Conf('multilang_sync')){
			$sX = stripSiteDir($this->GetCurPage(false));
			if(!preg_match('/\.php$/',$sX)) 
				$sX .= '/index.php';
			$this->entryPoint = addslashes($sX);
			$this->entryDir = addslashes(dirname($sX));
		}
    }
    function Omega()
    {
        // some finalization
        if (defined('GT_ADMIN')) { // admin-only finalization
            cacheSetVars('admModulesData', $this->admModulesData);
        }
    }
    function AddPageCSS($url)
    {
        return $this->AddPageHeader("<link rel=\"stylesheet\" href=\"$url\" />");
    }
    function AddPageScript($url)
    {
        return $this->AddPageHeader("<script type=\"text/javascript\" src=\"$url\"></script>");
    }
    function AddPageMeta($name, $content)
    {
        return $this->AddPageHeader("<meta name=\"$name\" content=\"$content\" />");
    }
    // returns last header id
    function AddPageHeader($html)
    {
        $this->HTMLHEADERS[] = $html;
        return count($this->HTMLHEADERS) - 1;
    }
    function SetPageTitle($title, $bOnlyIfEmpty = false)
    {
		if(!$bOnlyIfEmpty && $this->SITE_TITLE) return;
		$this->SITE_TITLE = $title;
		$this->PAGE_HEADER = $title;
    }
	function SetPageHeader($header, $bOnlyIfEmpty = false)
    {
		if(!$bOnlyIfEmpty && $this->PAGE_HEADER) return;
		$this->PAGE_HEADER = $header;
    }
	function SetPageMeta($meta)
    {
        $this->PAGE_META = $meta;
    }
    function ShowTitle()
    {
        echo $this->GetTitle();
    }
    function GetTitle()
    {
		return $this->SITE_TITLE;
    }
    function ShowPageHeader()
    {
        echo $this->PAGE_HEADER;
    }
    function ShowHead($prepare = true)
    {
		if(!$this->HTMLHEADERSUNIQID)
			$this->HTMLHEADERSUNIQID = '<!--' . gen_string(36) . '-->';
		if($prepare) 
			echo $this->HTMLHEADERSUNIQID;
		else
			return implode("\r\n\t",$this->HTMLHEADERS);
    }
    function GetMenuTree($type, $sub = '', $curLevel = 1, $maxLevel = 2,$entry='',&$callUrl=FALSE)
    {
		if($type=='' && $sub!='') { $type=$sub; $sub = ''; $isSub = true; }
        $type = preg_replace('/[^a-z0-9-]/i', '', $type);
        $stop = false;
        $havefile = false;
        if ($type == '') return array();
		$entry = str_replace(array('../','..\\'),array('',''),$entry);
        $menuDir = $entry?$entry:$this->entryDir;
		if($this->Conf('multilang_sync'))
			$menuDir = stripSiteDir($menuDir);
		//d($menuDir);
		$auxTypes = array('');
		if($this->Conf('multilang_sync'))
			array_unshift($auxTypes,'.'.SITE_LANG);
		
        do {
			foreach($auxTypes as $auxType){
				$menuDescFile = GTDOCROOT . '/' . $menuDir . '/.menu.' . $type . $auxType . '.php';
				//d($menuDescFile);
				if (strlen(stripslashes($menuDir)) < 2) $stop = true;
				if (file_exists($menuDescFile)) {
					$havefile = true;
					$callUrl=$menuDescFile;
					break;
				}
			}
			$menuDir = dirname($menuDir);
        } while (!$havefile && !$stop);
        if (!$havefile)
            return array();

        $GTMENUITEMS = array();
        @include($menuDescFile);

        $arResult = array();
        foreach($GTMENUITEMS as $mItem) {
			//URL normalization

			///
			$mItem['LEVEL']=$curLevel;
			$bCPUrlMatch = $this->GetCurPage()==$mItem['URL'];
			$bCDUrlBegin = 0===strpos($this->GetCurDir(),$mItem['URL']);
			//$sUrlExtra = str_replace($this->GetCurDir(),'',$mItem['URL']);

			if(($bCPUrlMatch || $bCDUrlBegin) /*&& !($isSub && strlen($sUrlExtra))*/)
				$mItem['SELECTED']=true;
			$arResult[] = $mItem;
			if($sub !== '' && $sub != $type && $curLevel<$maxLevel){
				$arSubMenu = $this->GetMenuTree('', $sub, $curLevel+1, $maxLevel, $mItem['URL']);
				if(!empty($arSubMenu)){
					$arResult[count($arResult)-1]['ISPARENT'] = true;
					foreach($arSubMenu as $sm){
						//$sm['LEVEL']=2;
						$arResult[] = $sm;
					}
				}
			}
        }
        return $arResult;
    }
    function ShowPanel($prepare = true)
    {
		if(!$this->UserAble('SU') && !$this->UserAble('seepanel')) return;
		if(!$this->PANELUNIQID)
			$this->PANELUNIQID = '<!--' . gen_string(36) . '-->';
		if($prepare) {
			echo $this->PANELUNIQID;
			return;
		}
        $panelcont = '';
		$cUser = $this->GetCurrentUser();
        if (defined('GT_ADMIN'))
		{
            $panelclass = 'admspace';
            $panelcont = "Вы вошли как <a href=\"/gtinx/admin/?mod=users&act=edituser&id=$cUser[ID]\">$cUser[NAME] $cUser[LAST_NAME]</a>.";
        } else {
            $panelclass = 'userspace';
            $panelcont .= '<button class="gtp-bigbt" onclick="GTPanel.openPageEditor({fname:\'' . $this->entryPoint . '\',lang:\'' . SITE_LANG . '\'})">Изменить<br />страницу</button>';
            $panelcont .= '<button class="gtp-bigbt" onclick="GTPanel.newFileWizard(\'' . $this->entryDir . '\',\'' . SITE_LANG . '\')">Создать<br />страницу</button>';
            $panelcont .= '<div class="gtp-vsep"></div>';
            $panelcont .= '<button class="gtp-bigbt" onclick="GTPanel.pageOptionsDialog(\'' . $this->entryPoint . '\',\'' . SITE_LANG . '\')">Свойства<br />страницы</button>';
            $panelcont .= '<button class="gtp-bigbt" onclick="GTPanel.folderOptionsDialog(\'' . $this->entryDir . '\',\'' . SITE_LANG . '\')">Свойства<br />раздела</button>';
            $panelcont .= '<button class="gtp-bigbt" onclick="GTPanel.newFolderWizard(\'' . $this->entryDir . '\',\'' . SITE_LANG . '\')">Создать<br />раздел</button>';
            $panelcont .= '<div class="gtp-vsep"></div>';
            $panelcont .= '<div class="gtp-vgroup">';
			if(!empty($this->pageMenuTypes))
			 {
				$panelcont .= '<ul class="dropdown gtp-smlbt"><li><a href="#">Меню</a><ul class="sub_menu">';
				foreach($this->pageMenuTypes as $arMenu){
					$panelcont .= '<li><a href="javascript:(function(){GTPanel.editMenuWizard(\'' . $this->entryDir . '\',\'' . SITE_LANG . '\',\''.$arMenu.'\')})()">'.$arMenu."</a></li>";
				}
				$panelcont .= "</ul></li></ul>";
			 }
			if(!empty($this->pageComponents))
			 {
				$panelcont .= '<ul class="dropdown gtp-smlbt"><li><a href="#">Компоненты</a><ul class="sub_menu">';
				foreach($this->pageComponents as $arComp){
					$panelcont .= '<li><a href="#">'.$arComp['name']."</a></li>";
				}
				$panelcont .= "</ul></li></ul>";
			 }
			$panelcont .= '</div>';

            $panelcont .= '<div class="gtp-vsep"></div>';
        }
        $ret .= '<link rel="stylesheet" type="text/css" href="/gtinx/admin/skins/panel/panel.css" />';
        $ret .= '<!--[if lte IE 7]> <link rel="stylesheet" type="text/css" href="/gtinx/admin/skins/panel/panel-ie.css" /> <![endif]-->';

		$ret .= <<< EOF
		<div id="panelmenu" class="panel-big">
			<ul class="dropdown" id="pmenurbt">
			<li>
				<a href="#">Меню</a>
				<ul class="sub_menu">
EOF;
		$arModOptions = $this->admGetModulesOptTree();
		//d($arModOptions);
		for($x = 0; $x<count($arModOptions); $x++){
			$arItem=$arModOptions[$x];

			$ret .= '<li><a href="/gtinx/admin/'.$arItem["URL"].'">'.$arItem["NAME"]."</a>\n";
			if($arModOptions[$x+1]['LEVEL'] > $arItem['LEVEL'] )
				$ret .= "<ul>\n";
			else
				$ret .= "</li>\n";
			if( $arModOptions[$x+1]['LEVEL'] < $arItem['LEVEL'] )
				for($y = $arItem['LEVEL']; $y>$arModOptions[$x+1]['LEVEL'];$y-- )$ret .= "</ul></li>\n";
		}
$ret .= <<< EOF
				</li>
			</ul>
		</div>
        <div id="paneltop">
				<a class="panelsw" href="/"><span class="lspan"></span>Сайт<span class="rspan"></span></a>
				<a class="panelsw" href="/gtinx/admin/"><span class="lspan"></span>Панель управления<span class="rspan"></span></a>
				<a class="panellogout" href="/gtinx/direct.php?mod=core&act=logout">Выйти</a>
				<span class="paneltoptx">{$this->USERDATA['NAME']} {$this->USERDATA['LAST_NAME']}</span>

				</div>
		
        
        <script type="text/javascript" src="/gtinx/admin/skins/panel/panel.js"  charset="utf-8"></script>
        <script type="text/javascript" src="/gtinx/admin/skins/panel/editor.js" charset="utf-8"></script>
        <script type="text/javascript" src="/gtinx/admin/skins/panel/wizards.js" charset="utf-8"></script>
        <div id="panelcont" class="$panelclass">
			$panelcont
		</div>
		<div id="panelmini" style="display: none;">

		</div>
		<script>
			\$('.gtp-bigbt').button();
			\$('.gtp-smlbt').button();
		</script>

		
			<script  type="text/javascript">
			\$(function(){\$("ul.dropdown li").hover(function(){\$(this).addClass("hover");\$('ul:first',this).css('visibility', 'visible');}, 
			function(){\$(this).removeClass("hover");\$('ul:first',this).css('visibility', 'hidden');});\$("ul.dropdown li ul li:has(ul)").find("a:first").append(" &raquo; ");});
			</script>
EOF;
		
		return $ret;
    }
    function GetCurPage($stripfname = true,$stripparams = true)
    {
        $a = $_SERVER['REQUEST_URI'];
		$a = preg_replace('/&?page=[0-9]+/i', '', $a);
		$a = preg_replace('/(&|\?)+$/i', '', $a);
		if($stripparams)
			$a = preg_replace('/\?.*/i', '', $a);
		if($stripfname)
			$a = preg_replace('/\/index.php$/i', '/', $a);
        return $a;
    }
    function GetCurDir()
    {
        $a = $_SERVER['REQUEST_URI'];
        $a = preg_replace('/\/[^\/]+$/i', '/', $a);
        return $a;
    }

    function LoadModuleAdmData($sModName)
    {
        global $APP, $DB;
        $sModFullPath = GTROOT . '/modules/' . $sModName;
        if ($sModName == '.' || $sModName == '..' || !is_dir($sModFullPath))
            return array();
        $this->admCurrModule = $sModName;

        if (file_exists($sModFullPath . '/adminlist.php') &&
                is_readable($sModFullPath . '/adminlist.php')) {
            @include($sModFullPath . '/adminlist.php');
        } else {
            // fixme
        }
        $this->admCurrModule = '';
    }
    function AdmRegisterItemGroup($arInfo, $sSectionName = '')
    {
        if (!in_array($sSectionName,$this->admSections))
			$sSectionName = $this->admSections[0];
		$sModName = $this->admCurrModule;
		if($sSectionName == 'modules')
			$this->admModulesData[$sModName] = $arInfo;
		elseif($sSectionName == 'config')
			$this->admConfigData[$sModName] = $arInfo;
        // print_r($this->admModulesData);
    }
    function admGetModulesOptTree($sMod = '')
    {
        $arRet = array();
		foreach($this->admSections as $sSectionName){
			$arRet[$sSectionName] = array();
			if ($sMod != '')
				$arRet[$sSectionName] = $this->__getModuleOptTree($sMod, $sSectionName);
			if($sSectionName == 'modules')
				$pathArr = &$this->admModulesData;
			elseif($sSectionName == 'config')
				$pathArr = &$this->admConfigData;
			if(is_array($pathArr))
				foreach($pathArr as $sMod => $arData) {
					$arM = $this->__getModuleOptTree($sMod, $sSectionName);
					$arRet[$sSectionName] = array_merge($arRet[$sSectionName], $arM);
				}
		}
        return $arRet;
    }
    function __getModuleOptTree($sMod, $sSectionName = '')
    {
        $arRet = array();
		if($sSectionName == 'modules')
			$pathArr = &$this->admModulesData;
		elseif($sSectionName == 'config')
			$pathArr = &$this->admConfigData;
		if(is_array($pathArr[$sMod]))
        foreach($pathArr[$sMod] as $arItems) {
            $arRet = array_merge($arRet, $this->__getModuleOptTreeA($sMod, $arItems, 1));
        }
        // print_r($arRet);
        return $arRet;
    }
    function __getModuleOptTreeA($sMod, $arItem, $iLevel)
    {
        $arRet = array(array(
                'NAME' => $arItem[0],
                'DESC' => $arItem[1],
                'URL' => "?mod=" . $sMod . "&" . $arItem[3],
                'LEVEL' => $iLevel,
                'IMAGE' => $arItem[2]
                ));
        if (is_array($arItem[4]) && count($arItem[4])) {
            foreach($arItem[4] as $arItems) {
                $arRet = array_merge($arRet, $this->__getModuleOptTreeA($sMod, $arItems, $iLevel + 1));
            }
        }
        // print_r($arRet);
        return $arRet;
    }

    function AdmLoadModule($sModName)
    {
        if ($sModName == '') $sModName = 'core';
        $sModFullPath = GTROOT . '/modules/' . $sModName;
        if ($sModName == '.' || $sModName == '..' || !is_dir($sModFullPath)) {
            $this->Raise('Module not found');
            return false;
        }
        if (file_exists($sModFullPath . '/adminpart.php') &&
                is_readable($sModFullPath . '/adminpart.php')) {
            if (file_exists($sModFullPath . '/.prop.php')) {
                include($sModFullPath . '/.prop.php');
            }
            $this->moduleParam[$sModName] = $arrModProperties;
            $this->admCurrModule = $sModName;
            $this->modOutput->content = array();

            $sClass = $sModName;

            global $APP;

            @include($sModFullPath . '/adminpart.php');
            $this->OUTPUT .= ob_get_contents();
            // clean OB
            ob_clean();
        } else {
            // fixme
        }
    }

    function loadModule($sModName)
    {
        if ($sModName == '') $sModName = 'core';
        $sModFullPath = GTROOT . '/modules/' . $sModName;
        if ($sModName == '.' || $sModName == '..' || !is_dir($sModFullPath)) {
            $this->Raise('Module not found');
            return false;
        }
        if (file_exists($sModFullPath . '/userpart.php') &&
                is_readable($sModFullPath . '/userpart.php')) {
            if (file_exists($sModFullPath . '/.prop.php')) {
                include($sModFullPath . '/.prop.php');
            }
            $this->moduleParam[$sModName] = $arrModProperties;
            $this->currModule = $sModName;

            $sClass = $sModName;

            global $APP;

            @include($sModFullPath . '/userpart.php');
            $this->OUTPUT .= ob_get_contents();
            // clean OB
            ob_clean();
        } else {
            // fixme
        }
    }

    function Raise($exName, $exParams = array())
    {
		echo "<div style=\"border: 1px Red dashed; padding: 20px;\">FIXME!! Exception: $exName</div>";
    }

	function siteError($exName)
	{
		echo "<div style=\"border: 1px Red dashed; padding: 20px;\"><h2>Ошибка:</h2> ".implode('<br>',$exName)."</div>";
	}

    function AdmParseTemplate($arrHeader, $arrValues, &$sActType, $arrFilter = '', &$sListType)
    {
        $this->modOutput->actType = $sActType;
        if (empty($this->modOutput->breadcumbs)) {
            $this->ModCreateBreadcumbs($this->moduleParam[$this->admCurrModule]['NAME'], '?act=' . $this->admCurrModule);
        }
        $this->modOutput->name = isset($this->moduleParam[$this->admCurrModule]['NAME']) ? $this->moduleParam[$this->admCurrModule]['NAME'] : $this->admCurrModule;

        $this->modOutput->filter = $this->AdmCreateModFilter($arrFilter);
    	$this->modOutput->listType = $sListType;
        $this->modOutput->header = $arrHeader;
        $this->modOutput->content = (!empty($arrValues))? $arrValues : array();
    }

    /**
     * GTApp::ModCreateBreadcumbs()
     *
     * @param mixed $mixName
     * @param mixed $mixUrl
     * @return
     */
    function ModCreateBreadcumbs($mixName = '', $mixUrl = '')
    {
        if (is_array($mixName)) {
            if (!is_array($mixUrl)) $mixUrl = Array($mixUrl);
            foreach($mixName as $k => $sName) {
                if (!empty($mixUrl[$k])) {
                    $this->modOutput->breadcumbs .= " > <a href=\"" . $mixUrl[$k] . "\">" . $sName . "</a>";
                } else {
                    $this->modOutput->breadcumbs .= " > <span>" . $sName . "</span>";
                }
            }
        } elseif (!empty($mixName)) {
            if (!empty($mixUrl)) {
                $this->modOutput->breadcumbs .= " > <a href=\"" . $mixUrl . "\">" . $mixName . "</a>";
            } else {
                $this->modOutput->breadcumbs .= " > <span>" . $mixName . "</span>";
            }
        } else return false;
    }

    function AdmCreateModFilter($arr)
    {
        if (is_array($arr)) {
            foreach($arr as $k => $mixFilter) {
            	$k = (is_numeric($k)) ? $mixFilter : $k;
                if (is_array($mixFilter)) {
                    $arrPrint[] = Array('NAME' => GetMessage($k), 'HTML' => $this->AdmReturnHtmlField("select", $k, $mixFilter));
                } else {
                    $arrPrint[] = Array('NAME' => GetMessage($mixFilter), 'HTML' => $this->AdmReturnHtmlField("string", $k), '');
                }
            }
        } else return false;

        return $arrPrint;
    }

    function AdmParseModFields($arr, $strFilter = '', $bEmpty = false)
    {
        foreach($arr as $k => $v) {
            if (!empty($strFilter) && $strFilter != $v['TYPE']) continue;
            $arrPrint[] = Array('NAME' => $v['DESC'], 'TYPE'=> $v['TYPE'], 'HTML' => $this->AdmReturnHtmlField($v['TYPE'], $v['NAME'], ($bEmpty == true) ? '' : $v['VALUE']));
        }

        return $arrPrint;
    }

    function AdmReturnHtmlField($strType, $strName = '', $mixValue = '', $sChecked = '')
    {
        $arrType = explode(':', $strType);
        $strType = $arrType[0];
        $sP1 = $arrType[1];
        $sP2 = $arrType[2];

        switch ($strType) {
            case 'string':
                $strField = "<input type=\"text\" size=\"40\" name=\"" . $strName . "\" value=\"" . $mixValue . "\">";
                break;
            case 'checkbox':
                $arrVal = explode(':', $mixValue);
                $sChecked = ($arrVal[1] == 'checked')? 'checked' : '';
                $strField = "<input type=\"checkbox\" name=\"" . $strName . "[]\" value=\"" . $arrVal[0] . "\" $sChecked>";
                break;
            case 'radio':

                foreach($mixValue as $k => $sValue) {
                    $sChecked = ($sP1==$k)?'checked':'';

					$sLabel = $sValue;

                    $strField .= (!empty($sLabel))? '<label>' . $sLabel . '</label>' : '';
                    $strField .= "<input type=\"radio\" name=\"$strName\" value=$k $sChecked>";
                }
                break;
            case 'select':
                $sMulti = ($sP1 == "multiple" || $sP2 == "multiple")? 'multiple' : '';
                if (!empty($sP1) && $sP1 != "multiple") $sChecked = $sP1;
                if (!empty($sP2) && $sP2 != "multiple") $sChecked = $sP2;
                $arrChecked = explode(',', $sChecked);
                $strField .= "<select name=\"" . $strName . "[]\" $sMulti>";

                foreach($mixValue as $k => $v) {
                    $sChk = (array_search(trim($k), $arrChecked) !== false) ? 'selected' : '';

                    $strField .= "<option value=\"$k\" $sChk $sMulti>$v</option>";
                }
                $strField .= "</select>";
                break;
            case 'textarea':
				($sP1=='ht')? $cChecked='checked' : $sChecked='checked';

				$jsid = preg_replace('/[^a-z0-9]/i','',$strName);
                $strField =
				"<div>".
					"<input type=\"radio\" id=\"edswitchtx$jsid\" name=\"edswitch$jsid\" value=\"tx\" $sChecked><label for=\"edswitchtx$jsid\">Текст</label>".
					"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
					"<input type=\"radio\" id=\"edswitchht$jsid\" name=\"edswitch$jsid\" value=\"ht\" $cChecked><label for=\"edswitchht$jsid\">HTML</label>".
				"</div>".
				"<div id=\"gteditorplace$jsid\"></div>".
				"<textarea style=\"width:90%;\" rows=\"15\" name=\"" . $strName . "\" id=\"txedit" . $jsid . "\">" . $mixValue . "</textarea>".
				"<script type=\"text/javascript\">\n".
				"var ed$jsid; \$(function(){ ed$jsid = new GTEditor({parent:'gteditorplace$jsid',replaceNode:\$\$._('txedit$jsid')});});".
				"\$('#edswitchtx$jsid').bind('click',function(){ed$jsid.close();});".
				"\$('#edswitchht$jsid').bind('click',function(){ed$jsid.open();});";
				$strField .= ($sP1=='ht')? "\n\$(function(){ed$jsid.open();});" : '';
				$strField .= "\n</script>";
                break;
            default:
            case 'text':
                $strField = $mixValue;
                break;
            case 'hidden':
                $strField = "<input type=\"hidden\" name=\"" . $strName . "\" value=\"" . $mixValue . "\">";
                break;
        	case 'password':
        		$strField = "<input type=\"password\" name=\"" . $strName . "\" value=\"" . $mixValue . "\">";
        		break;
        	case 'submit':
        		$strField = "<input type=\"submit\" name=\"" . $strName . "\" value=\"" . $mixValue . "\">";
        		break;
        	case 'reset':
        		$strField = "<input type=\"reset\" name=\"" . $strName . "\" value=\"" . $mixValue . "\">";
        		break;
        	case 'file':
        		$strField = "<input type=\"file\" name=\"" . $strName . "\">";
        		break;
			case 'raw':
        		$strField = $mixValue;
        		break;
        }
        return $strField;
    }

    /**
     * GTApp::admModShowElements()
     *
     * @param mixed $arrHeader
     * @param string $arrValues
     * @param mixed $sActType
     * @param string $arrFilter
     * @param mixed $arrDetAct
     * @param integer $iNoTemplate
     * @return
     */
    function admModShowElements($arrHeader, $arrValues = '', $sActType, $arrFilter = '', $sListType='list', $arrDetAct = '', $iNoTemplate = 0)
    {
        $this->admCacheViewModuleSet($this->admCurrModule, $arrHeader);

        if (is_array($arrDetAct)) $this->admModDetemineActions($arrDetAct);

        if ($iNoTemplate == 0) {
            $this->modOutput->dHtml = $sDHtml;
            $this->AdmParseTemplate(&$arrHeader, &$arrValues, &$sActType, &$arrFilter, &$sListType);
        } else return array(0 => $arrHeader, 1 => $arrValues, $arrFilter);
    }

    function AdmParseModHeaderFields($arr)
    {
        return $arr;
    }

    function admCacheViewModuleSet($sModName, $arrModHeader)
    {
        $sCacheMod = cacheGet($sModName . ":" . implode('&', cleanQUrl()));

        if ($sCacheMod != $arrModHeader) {
            cacheSet($sModName . ":" . implode('&', cleanQUrl()), $arrModHeader);
        }
    }

    function admCacheGetModuleFields()
    {
        $sModName = $this->admCurrModule;
        $sCacheMod = cacheGet($sModName . ":" . implode('&', cleanQUrl()));
        if (!empty($sCacheMod)) {
            return $sCacheMod;
        }
        return false;
    }

    function admModDetemineActions($arr)
    {
        if (is_array($arr)) {
            foreach($arr as $k => $sAct) {
                $arr[$k] = strtoupper($sAct);
            }
            $this->modOutput->allowAction = $arr;
        }
    }

    function admModIsSetAction($sNeedle)
    {
        if (is_array($this->modOutput->allowAction)) {
            if (in_array(strtoupper($sNeedle), $this->modOutput->allowAction)) {
                return true;
            } else return false;
        } else return false;
    }

    function admModSetUrl($sParam, $sMode, $sMod = '')
    {
        $sMode = $sMode . "Url";
        if (!empty($sMod)) {
            $this->modOutput->$sMode = '?mod=' . $sMod . '&' . $sParam;
        } else {
            $this->modOutput->$sMode = '?mod=' . $this->admCurrModule . '&' . $sParam;
        }
    }
    function getCurrModule()
    {
        return $this->currModule?$this->currModule:$this->admCurrModule;
    }

	function addControlUserButton($sName, $sParam=''){
		$this->modOutput->userButton = Array($sName=>'?mod=' . $this->admCurrModule . '&' . $sParam);

	}

	function serviceDumpDatabase()
	{
		error_reporting(E_ALL);
		$hRes = $this->DB->query('SHOW TABLES');
		while ($arRes = $this->DB->fetchArray($hRes)) {
			//d("TRUNCATE TABLE `$arRes[0]`");
			$hRes2 = $this->DB->query("SHOW CREATE TABLE `$arRes[0]`");
			$arRes2 = $this->DB->fetchAssoc($hRes2);
			$sSqlOut .="\r\n".$arRes2['Create Table'].";\r\n\r\n";
			//d($arRes2['Create Table']);
			$hRes2 = $this->DB->query("SELECT * FROM `$arRes[0]`");
			while ($arRes2 = $this->DB->fetchAssoc($hRes2)) {
				//d(array_keys($arRes2));
				$query = "";
				foreach ( $arRes2 as $sField )
				{
					if ( is_null($sField) )
						$sField = "NULL";
					else
						$sField = "'".mysql_escape_string( $sField )."'";

					if (empty($query))
						$query = $sField;
					else{

						$query .= ', '.$sField;
					}
				}
				$sSqlOut .="INSERT INTO `$arRes[0]` (`".implode('`,`', array_keys($arRes2))."`) VALUES ($query);\n";
				//d("INSERT INTO `$arRes[0]` (`".implode('`,`', array_keys($arRes2))."`) VALUES ($query);");
			}
		}
		$sFileSql = GTROOT.'/backup/'.date('h-m-s-d.m.y').gen_string(3).'.sql';
		$fp = fopen("$sFileSql", 'w+');
		fputs($fp, $sSqlOut);
		fclose($fp);
		d($sFileSql);
	}
	
	function addToBreadCrumbs($name,$url = ''){
		if(is_array($name) || ''===trim($name)) return;
		$this->userBreadCrumbs[] = array($name,$url);
	}
	function getBreadCrumbs(){
		return $this->userBreadCrumbs;
	}
	
	// from abzal
	function getDeafaultLang()
	{
		global $DB;
		static $ID; 
		if(!$ID)
		$ID=$DB->QResult("SELECT `ID` FROM `g_lang` WHERE `DEFAULT`='1' AND `ENABLED`='1'");
		return $ID;
	}
}
		function __AlfaSiteCmp($a,$b){
			$al =strlen($a['DIR']);
			$bl =strlen($b['DIR']);
			if( $al == $bl ) return 0;
			return ( $al > $bl )? -1 : 1;
		}
?>