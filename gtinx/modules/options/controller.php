<?
class ControllerOptions
{
	function __construct()
	{
		if($_GET['act'])
		{
			$act=$_GET['act'];
			$id='';
			$id=$_GET['id'];
			switch($act)
			{
				case 'sites':
				$this->SITES();
				break;
				case 'site':
				$this->SITES_ED($id);
				break;
				case 'create_site':
				$this->SITE_NW();
				break;
				case 'lang':
				$this->LANG();
				break;
				case 'lange':
				$this->LANGS_ED($id);
				break;
				case 'create_lang':
				$this->LANGS_NW();
				break;
				case 'Templates':
				$this->Templates();
				break;
				case 'templ_ed':
				$this->TEMPL_ED($id);
				break;
			}
		}
		
		if($_POST['ADD'])
		{
			$add='';
			$add=$_POST['ADD'];
			switch($add)
			{
				case 'EDIT_SITE':
				$this->EDIT_SITE($_POST);
				break;
				case 'EDIT_LANG':
				$this->EDIT_LANG($_POST);
				break;
				case 'CREATE_LANG';
				$this->CREATE_LANG($_POST);
				break;
				case 'EDIT_TEMP':
				$this->EDIT_TEMP($_POST);
				break;
			}
		}
	}

	function Templates()
	{
		global $APP;
		$template=GToptionsites::templates('/templates/');
		foreach($template as $key=>$val)
		{
			$arrModResult['modContent'][] =Array(
											"NAME" => $val['NAME'],
											"DESCRIPTION" => $val['DESCRIPTION'],											
											"ID" => $key);
		}
		
		$arrModResult['modHeader'] = Array(
				"NAME" => GetMessage('NAME'),
				"DESCRIPTION" => GetMessage('DESCRIPTION'),
				"ID" => GetMessage('ID')
				);
			$APP->admModSetUrl('act=templ_ed&id=', 'edit');
			
			$APP->ModCreateBreadcumbs('Сайты');
			$APP->admModDetemineActions(Array('Add', 'LIST', 'EDIT', 'DELETE'));
			$APP->admModShowElements(
				$arrModResult['modHeader'],
				$arrModResult['modContent'],
				"list");
	}
	
	function TEMPL_ED($Dir)
	{
		global $APP;
		$template=GToptionsites::templates('/templates/');
		$template=$template[$Dir];
		//d($template);
		$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('ID'), "NAME" => "SHOWID", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$Dir),
		Array("DESC" => '', "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$Dir),
		Array("DESC" => '', "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'EDIT_TEMP'),
		Array("DESC" => GetMessage('DIRECTORY'), "NAME" => "", "VIEW" => 1, "TYPE" => "text", "VALUE" =>"/templates/".$Dir),
		Array("DESC" => GetMessage('NAME'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$template['NAME']),
		Array("DESC" => GetMessage('DESCRIPTION'), "NAME" => "DESCRIPTION", "VIEW" => 1, "TYPE" => "textarea", "VALUE" =>$template['DESCRIPTION']),
		));
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	function EDIT_TEMP($arv)
	{
		$res=GToptionsites::UpTemp($arv);
		if($res==TRUE)
		{
			header("Location: ./?mod=options&act=sites");
			die();
		}
		else
		{
			GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
		}
	}
	
	function SITES()
	{
		global $APP;
		$r=GToptionsites::Get();
		foreach($r as $val)
		{
		if($val['site_default']!=0){$val['site_default']=GetMessage('YES');}else{$val['site_default']=GetMessage('NO');}
		if($val['site_enabled']!=0){$val['site_enabled']=GetMessage('YES');}else{$val['site_enabled']=GetMessage('NO');}
		$arrModResult['modContent'][] =Array(
											"NAME" => $val['site_name'],
											"ACTIVE" => $val['site_enabled'],											
											"DIRECTORY" =>$val['site_dir'],
											"DEFAULT" =>$val['site_default'],
											"ID" => $val['site_id']);
		}
		 $arrModResult['modHeader'] = Array(
				"NAME" => GetMessage('NAME'),
				"ACTIVE" => GetMessage('ACTIVE'),
				"DIRECTORY" => GetMessage('DIRECTORY'),
				"DEFAULT" => GetMessage('DEFAULT'),
				"ID" => GetMessage('ID')
				);
			
			$APP->admModSetUrl('act=site&id=', 'edit');
			$APP->admModSetUrl('act=create_site', 'add');
			$APP->admModSetUrl('act=del_site', 'action');
			$APP->ModCreateBreadcumbs('Сайты');
			$APP->admModDetemineActions(Array('Add', 'LIST', 'EDIT', 'DELETE'));
			$APP->admModShowElements(
				$arrModResult['modHeader'],
				$arrModResult['modContent'],
				"list");
	}
	
	
	function SITES_ED($ID)
	{
		global $APP;
		$r=GToptionsites::Get('',Array('site_id'=>$ID));
		$l=GToptionlang::Get();
		foreach($l as $key=>$val)
		{
			$SELECT[$val['ID']]=GetMessage($val['TITLE']);
		}
		$template=GToptionsites::templates('/templates/');
		
		foreach($template as $key=>$val)
		{
			$tmps[$key]=$val['NAME'];
		}
		$r=$r[0];
		$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('ID'), "NAME" => "SHOWID", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$r['site_id']),
		Array("DESC" => '', "NAME" => "site_id", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$r['site_id']),
		Array("DESC" => '', "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'EDIT_SITE'),
		Array("DESC" => GetMessage('NAME'), "NAME" => "site_name", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$r['site_name']),
		Array("DESC" => GetMessage('SITE_LANG'), "NAME" => "site_lang", "VIEW" => 1, "TYPE" => "select:".$r['site_lang'], "VALUE" =>$SELECT),
		Array("DESC" => GetMessage('DOMAINS'), "NAME" => "", "VIEW" => 1, "TYPE" => "raw", "VALUE" =>'<textarea name="site_domains" cols="30" rows="5">'.$r['site_domains'].'</textarea>'),
		Array("DESC" => GetMessage('DIRECTORY'), "NAME" => "site_dir", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$r['site_dir']),
		Array("DESC" => GetMessage('ACTIVE'), "NAME" => "site_enabled", "VIEW" => 1, "TYPE" => "radio:".$r['site_enabled'], "VALUE" =>Array( 1 =>GetMessage('YES'), 0=>GetMessage('NO'))),
		Array("DESC" => GetMessage('DEFAULT'), "NAME" => "site_default", "VIEW" => 1, "TYPE" => "radio:".$r['site_default'], "VALUE" =>Array( 1 =>GetMessage('YES'), 0=>GetMessage('NO'))),
		Array("DESC" => GetMessage('SITE_template'), "NAME" => "site_template", "VIEW" => 1, "TYPE" => "select:".$r['site_template'], "VALUE" =>$tmps)
		));
		$APP->ModCreateBreadcumbs($r['site_name']);
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	function EDIT_SITE($arv)
	{
		unset($_POST);
		foreach($arv as $key=>$val)
		{
			if(is_array($val))
			{
				$arv[$key]=$val[0];
			}
		}
		$res=GToptionsites::Update($arv);
		if($res==TRUE)
		{
			header("Location: ./?mod=options&act=sites");
			die();
		}
		else
		{
			GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
		}
	}
	/**********/
	
	function LANG()
	{
		global $APP;
		$r=GToptionlang::Get();
		foreach($r as $val)
		{
			if($val['ENABLED']!=0){$val['ENABLED']=GetMessage('YES');}else{$val['ENABLED']=GetMessage('NO');}
			if($val['DEFAULT']!=0){$val['DEFAULT']=GetMessage('YES');}else{$val['DEFAULT']=GetMessage('NO');}
			$arrModResult['modContent'][] =Array(
											"ID" => $val['ID'],
											"NAME" => $val['TITLE'],
											"ACTIVE" => $val['ENABLED'],											
											"SORT" =>$val['SORT'],
											"DEFAULT" =>$val['DEFAULT']);
		}
		 $arrModResult['modHeader'] = Array(
				"ID" => GetMessage('ID'),
				"NAME" => GetMessage('NAME'),
				"ACTIVE" => GetMessage('ACTIVE'),
				"SORT" => GetMessage('SORT'),
				"DEFAULT" => GetMessage('DEFAULT')
				);
			
			$APP->admModSetUrl('act=lange&id=', 'edit');
			$APP->admModSetUrl('act=create_lang', 'add');
			$APP->admModSetUrl('act=del_lang', 'action');
			$APP->ModCreateBreadcumbs('LANGS');
			$APP->admModDetemineActions(Array('Add', 'LIST', 'EDIT', 'DELETE'));
			$APP->admModShowElements(
				$arrModResult['modHeader'],
				$arrModResult['modContent'],
				"list"
				);
	}
	
	function LANGS_NW()
	{
		global $APP;
		$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('ID'), "NAME" => "ID", "VIEW" => 1, "TYPE" => "string", "VALUE" =>''),
		Array("DESC" => '', "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'CREATE_LANG'),
		Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE", "VIEW" => 1, "TYPE" => "string", "VALUE" =>''),
		Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" =>''),
		Array("DESC" => GetMessage('DATE_FORMAT'), "NAME" => "DATE_FORMAT", "VIEW" => 1, "TYPE" => "string", "VALUE" =>'Y-m-j'),
		Array("DESC" => GetMessage('DATE_TIME_FORMAT'), "NAME" => "DATE_TIME_FORMAT", "VIEW" => 1, "TYPE" => "string", "VALUE" =>'Y-m-j h:i:s'),
		Array("DESC" => GetMessage('CODE'), "NAME" => "CODE", "VIEW" => 1, "TYPE" => "string", "VALUE" =>'utf8'),
		Array("DESC" => GetMessage('TEXT_DIRECTION'), "NAME" => "TEXT_DIRECTION", "VIEW" => 1, "TYPE" => "select", "VALUE" =>Array('LTR' =>GetMessage('LEFT_TO_RIGHT'), 'RTL'=>GetMessage('RIGHT_TO_LEFT'))),
		Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ENABLED", "VIEW" => 1, "TYPE" => "radio", "VALUE" =>Array( 1 =>GetMessage('YES'), 0=>GetMessage('NO'))),
		Array("DESC" => GetMessage('DEFAULT'), "NAME" => "DEFAULT", "VIEW" => 1, "TYPE" => "radio", "VALUE" =>Array( 1 =>GetMessage('YES'), 0=>GetMessage('NO'))),
		));
		$APP->ModCreateBreadcumbs(GetMessage('CREATE_LANG'));
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	function LANGS_ED($ID)
	{
		global $APP;
		$l=GToptionlang::Get('',Array('ID'=>$ID));
		$l=$l[0];
		$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('ID'), "NAME" => "SHOWID", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$l['ID']),
		Array("DESC" => '', "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$l['ID']),
		Array("DESC" => '', "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'EDIT_LANG'),
		Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$l['TITLE']),
		Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$l['SORT']),
		Array("DESC" => GetMessage('DATE_FORMAT'), "NAME" => "DATE_FORMAT", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$l['DATE_FORMAT']),
		Array("DESC" => GetMessage('DATE_TIME_FORMAT'), "NAME" => "DATE_TIME_FORMAT", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$l['DATE_TIME_FORMAT']),
		Array("DESC" => GetMessage('CODE'), "NAME" => "CODE", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$l['CODE']),
		Array("DESC" => GetMessage('TEXT_DIRECTION'), "NAME" => "TEXT_DIRECTION", "VIEW" => 1, "TYPE" => "select:".$l['TEXT_DIRECTION'], "VALUE" =>Array('LTR' =>GetMessage('LEFT_TO_RIGHT'), 'RTL'=>GetMessage('RIGHT_TO_LEFT'))),
		Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ENABLED", "VIEW" => 1, "TYPE" => "radio:".$l['ENABLED'], "VALUE" =>Array( 1 =>GetMessage('YES'), 0=>GetMessage('NO'))),
		Array("DESC" => GetMessage('DEFAULT'), "NAME" => "DEFAULT", "VIEW" => 1, "TYPE" => "radio:".$l['DEFAULT'], "VALUE" =>Array( 1 =>GetMessage('YES'), 0=>GetMessage('NO'))),
		));
		$APP->ModCreateBreadcumbs($l['TITLE']);
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	function CREATE_LANG($arv)
	{
		unset($_POST);
		foreach($arv as $key=>$val)
		{
			if(is_array($val))
			{
				$arv[$key]=$val[0];
			}
		}
		$res=GToptionlang::Add($arv);
		if($res==TRUE)
		{
			header("Location: ./?mod=options&act=lang");
			die();
		}
		else
		{
			GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
		}
	}
	
	function EDIT_LANG($arv)
	{
		unset($_POST);
		foreach($arv as $key=>$val)
		{
			if(is_array($val))
			{
				$arv[$key]=$val[0];
			}
		}
		$res=GToptionlang::Update($arv);
		if($res==TRUE)
		{
			header("Location: ./?mod=options&act=lang");
			die();
		}
		else
		{
			GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
		}
	}
}
?>