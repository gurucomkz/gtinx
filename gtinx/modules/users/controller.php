<?
//error_reporting(E_ALL);
class UserControl
{
//controller fo users
	function __construct()
	{
		if($_POST)
		{
			$POST = $_POST;
		}
		if($_GET)
		{
			$GET= $_GET;
		}
		if(isset($GET))
			{
				foreach($GET as $key=>$val)
				{
				switch($key)
					{
						case 'act':
							switch($val)
							{
							case 'users':
								$this->ListUsers();
								break;
							case 'users_filter':
								$this->ListUsers($POST);
								break;
							case 'groupusers':
							if(!isset($GET['id']))
								{
								$this->ListUsers();
								}
							else
							{
							$this->GetUserByGroup($GET['id']);
							}
								break;
							case 'usersG_filter':
								$this->GetUserByGroup($GET['id'],$POST);
								break;
							case 'del_group':
								$this->Del_Group($POST['checkID']);
								break;
							case 'adduser':
								$this->AddFormUser();
								break;
							case 'edituser':
								$this->Edit_User($GET['id']);
								break;
							case 'del_user':
								$this->Del_Users($POST['checkID']);
								break;
							case 'listgr':
								$this->ListGroups();
								break;
							case 'group_filter':
								$this->ListGroups($POST);
								break;
							case 'addgroup':
								$this->AddFormGroup();
								break;
							case 'editgroup':
								$this->GetFromGroup($GET['id']);
								break;
							case 'fields':
								$this->Fields();
								break;
							case 'fields_filter':
								$this->Fields($POST);
								break;
							case 'new_field':
								$this->New_Field();
								break;
							case 'edit_field':
								$this->Edit_Field($GET['id']);
								break;
							case 'del_type':
								$this->Del_Field($POST['checkID']);
								break;
							}
							break;
						
					}
				}
			}
		if(isset($POST))
			{
				foreach($POST as $key=>$val)
				{
				
					switch($key)
					{
						case 'ADD':
							switch($val)
							{
							case 'NEW_USER':
								$this->AddUser($POST);
								break;
							case 'EDIT_USER':
								$this->UpdateUser($POST);
								break;
							case 'NEW_GROUP':
								$this->AddGroup($POST);
								break;
							case 'EDIT_GROUP':
								$this->UpdateGroup($POST);
								break;
							case 'NEW_FIELDS':
								$this->NEW_FIELDS($POST);
								break;
							case 'EDIT_FIELDS':
								$this->EDIT_FIELDS($POST);
								break;
							}
						break;
						
					}
				}
			}
	}
	
	function Fields($PARAMS)
	{
		global $DB, $APP;
		if($PARAMS){
		if($PARAMS['NAME']!=FALSE)$PARAMS['NAME']='~%'.$PARAMS['NAME'].'%';
		if($PARAMS['TYPE']!=FALSE)$PARAMS['TYPE']='~%'.$PARAMS['TYPE'].'%';
		if($PARAMS['FIELD_KEY']!=FALSE)$PARAMS['FIELD_KEY']='~'.$PARAMS['FIELD_KEY'];
		if($PARAMS['ACTIVE'][0]!='3'){$PARAMS['ACTIVE']=$PARAMS['ACTIVE'][0];} else {unset($PARAMS['ACTIVE']);}
		}
		$row=GTuserfields::Get('',$PARAMS);
		foreach($row as $key=>$val)
		{
			if($row[$key]['ACTIVE']!=0)
			{$row[$key]['ACTIVE'] = GetMessage('YES');}else{$row[$key]['ACTIVE'] = GetMessage('NO');}
			 $arrModResult['modContent'][] =Array(
											"NAME" => $row[$key]['NAME'],
											"TYPE" => $row[$key]['TYPE'],
											"SORT" => $row[$key]['SORT'],
											"ACTIVE" => $row[$key]['ACTIVE'],
											"ID" => $row[$key]['ID']);
		}
			$arrModResult['modHeader'] = Array(
											"NAME" =>GetMessage('NAME'),
											"TYPE" => GetMessage('TYPE'),
											"SORT" => GetMessage('SORT'),
											"ACTIVE" => GetMessage('ACTIVE'),
											"ID" => GetMessage('ID')
											);
			$APP->admModSetUrl('act=edit_field&id=', 'edit');
			$APP->admModSetUrl('act=new_field', 'add');
			$APP->admModSetUrl('act=del_type', 'action');
			$APP->admModSetUrl('act=fields_filter', 'filter');
			$APP->ModCreateBreadcumbs(GetMessage('USERS'),'?mod=users&act=users');
			$APP->ModCreateBreadcumbs(GetMessage('ALL_FIELDS'));
				$APP->admModDetemineActions(Array('AdD', 'LIST', 'EDIT', 'DELETE'));
				$APP->admModShowElements($arrModResult['modHeader'],$arrModResult['modContent'],"list",
				Array('NAME', 'FIELD_KEY', 'TYPE', 'ID','ACTIVE'=>Array('3'=>' ',0=>GetMessage('NO'),1=>GetMessage('YES'))));
	}
	function Del_Field($arVar)
	{
		$res=GTuserfields::DeleteFields($arVar);
		header("Location: ./?mod=users&act=fields");
		die();
	}
	
	function New_Field()
	{
		global $DB, $APP , $arrModResult;
		$types=array('STRING','TEXT','FILE','PASS','IMAGE','DATE','NUMBER','URL','LINK','LIST');
		$type=array();
		foreach($types as $key=>$val)
		{
			$typ[$key]=array($val=>$val);
			$type=array_merge($type,$typ[$key]);
		}
		$arrModResult['modHeader'] = Array(
		GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('NAME'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('TYPE'), "NAME" => "TYPE", "VIEW" => 1, "TYPE" => "select", "VALUE" => $type),
		Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>'Да', 2=>'Нет')),
		Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('DEFAULT'), "NAME" => "DEFAULT", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('REQUIRED'), "NAME" => "REQUIRED", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>'Да', 2=>'Нет')),
		Array("DESC" => GetMessage('MULTIPLE'), "NAME" => "MULTIPLE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>'Да', 2=>'Нет')),
		Array("DESC" => GetMessage('USER_EDITABLE'), "NAME" => "USER_EDITABLE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>'Да', 2=>'Нет')),
		Array("DESC" => GetMessage('VALUES'), "NAME" => "VALUES", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => ''),
		Array("DESC" => GetMessage('LENGTH'), "NAME" => "LENGTH", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('OPTIONS'), "NAME" => "OPTIONS", "TYPE" => "string", "VALUE" =>''),
		Array("DESC" => GetMessage('KEY'), "NAME" => "FIELD_KEY", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => "NEW_FIELDS"))
		);
		$APP->ModCreateBreadcumbs(GetMessage('USERS'),'?mod=users&act=users');
		$APP->ModCreateBreadcumbs(GetMessage('ALL_FIELDS'),'?mod=users&act=fields');
		$APP->ModCreateBreadcumbs(GetMessage('NEW_FIELDS'));
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
function Edit_Field($ID)
	{
		global $DB, $APP , $arrModResult;
		$row=GTuserfields::Get('',Array('ID'=>$ID));
		
		$row=$row[0];
		$types=array('STRING','TEXT','FILE','PASS','IMAGE','DATE','NUMBER','URL','LINK','LIST');
		$type=array();
		foreach($types as $key=>$val)
		{
			$typ[$key]=array($val=>$val);
			$type=array_merge($type,$typ[$key]);
		}	
		$arrModResult['modHeader'] = Array(
		GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('NAME'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['NAME']),
		Array("DESC" => GetMessage('TYPE'), "NAME" => "TYPE", "VIEW" => 1, "TYPE" => "select:".$row['TYPE'], "VALUE" => $type),
		Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio:".$row['ACTIVE'], "VALUE" => Array(1 =>GetMessage('YES'), 0=>GetMessage('NO'))),
		Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['SORT']),
		Array("DESC" =>GetMessage('DEFAULT'), "NAME" => "DEFAULT", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['DEFAULT']),
		Array("DESC" => GetMessage('REQUIRED'), "NAME" => "REQUIRED", "VIEW" => 1, "TYPE" => "radio:".$row['REQUIRED'], "VALUE" => Array(1 =>GetMessage('YES'), 0=>GetMessage('NO'))),
		Array("DESC" => GetMessage('MULTIPLE'), "NAME" => "MULTIPLE", "VIEW" => 1, "TYPE" => "radio:".$row['MULTIPLE'], "VALUE" => Array(1 =>GetMessage('YES'), 0=>GetMessage('NO'))),
		Array("DESC" => GetMessage('USER_EDITABLE'), "NAME" => "USER_EDITABLE", "VIEW" => 1, "TYPE" => "radio:".$row['USER_EDITABLE'], "VALUE" => Array(1 =>GetMessage('YES'), 0=>GetMessage('NO'))),
		Array("DESC" => GetMessage('VALUES'), "NAME" => "VALUES", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $row['VALUES']),
		Array("DESC" => GetMessage('LENGTH'), "NAME" => "LENGTH", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['LENGTH']),
		Array("DESC" => GetMessage('OPTIONS'), "NAME" => "OPTIONS", "TYPE" => "string", "VALUE" =>$row['OPTION']),
		Array("DESC" => GetMessage('KEY'), "NAME" => "FIELD_KEY", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['FIELD_KEY']),
		Array("DESC" => "", "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => $row['ID']),
		Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => "EDIT_FIELDS"))
		);
		$APP->ModCreateBreadcumbs(GetMessage('USERS'),'?mod=users&act=users');
		$APP->ModCreateBreadcumbs(GetMessage('ALL_FIELDS'),'?mod=users&act=fields');
		$APP->ModCreateBreadcumbs($row['NAME']);
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
function NEW_FIELDS($arVar)
	{
		$res = GTuserfields::AddFields(array(
		'NAME'=>$arVar['NAME'],
		'TYPE'=>$arVar['TYPE'][0],
		'SORT'=>$arVar['SORT'],
		'DEFAULT'=>$arVar['DEFAULT'],
		'REQUIRED'=>$arVar['REQUIRED'],
		'MULTIPLE'=>$arVar['MULTIPLE'],
		'VALUES'=>$arVar['VALUES'],
		'ACTIVE'=>$arVar['ACTIVE'],
		'USER_EDITABLE'=>$arVar['USER_EDITABLE'],
		'LENGTH'=>$arVar['LENGTH'],
		'OPTIONS'=>$arVar['OPTIONS'],
		'FIELD_KEY'=>$arVar['FIELD_KEY'])		
		);
		if ($res===TRUE)
		{
			header("Location: ./?mod=users&act=fields");
			die();
		}
		else {GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));}
	}
	
function EDIT_FIELDS($arVar)
	{
			$update=Array(	'ID'=>$arVar['ID'],
							'NAME'=>$arVar['NAME'],
							'TYPE'=>$arVar['TYPE'][0],
							'SORT'=>$arVar['SORT'],
							'DEFAULT'=>$arVar['DEFAULT'],
							'REQUIRED'=>$arVar['REQUIRED'],
							'MULTIPLE'=>$arVar['MULTIPLE'],
							'USER_EDITABLE'=>$arVar['USER_EDITABLE'],
							'VALUES'=>$arVar['VALUES'],
							'ACTIVE'=>$arVar['ACTIVE'],
							'LENGTH'=>$arVar['LENGTH'],
							'OPTIONS'=>$arVar['OPTIONS'],
							'FIELD_KEY'=>$arVar['FIELD_KEY']);
			
			$res = GTuserfields::UpdateFields($update);
			if ($res===TRUE)
			{
				header("Location: ./?mod=users&act=fields");
				die();
			}		
			else {GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));}
	}

function GetUserByGroup($ID,$PARAMS=FALSE)
	{
		global $DB, $APP;
		if($PARAMS){
		
		if($PARAMS['NAME']!=FALSE)$PARAMS['NAME']='~%'.$PARAMS['NAME'].'%';
		if($PARAMS['LOGIN']!=FALSE)$PARAMS['LOGIN']='~%'.$PARAMS['LOGIN'].'%';
		if($PARAMS['EMAIL']!=FALSE)$PARAMS['EMAIL']='~%'.$PARAMS['EMAIL'].'%';
		if($PARAMS['ACTIVE'][0]!='3'){$PARAMS['ACTIVE']=$PARAMS['ACTIVE'][0];} else {unset($PARAMS['ACTIVE']);}
		}
		$PARAMS['USER_GROUP_GROUP_ID']=$ID;
		$users=GTusers::Get('',$PARAMS);
		foreach($users as $key=>$val)
		{
			
			if($users[$key]['LASTLOGIN_TIME']!=0)
			{$users[$key]['LASTLOGIN_TIME'] = date('Y-m-j h:i:s', $users[$key]['LASTLOGIN_TIME']);}else{$users[$key]['LASTLOGIN_TIME']='';}
			if($users[$key]['ACTIVE']!=0)
			{$users[$key]['ACTIVE'] = GetMessage('YES');}else{$users[$key]['ACTIVE'] = GetMessage('NO');}
			 $arrModResult['modContent'][] =Array(
											"NAME" => $users[$key]['NAME'].' '.$users[$key]['LAST_NAME'],
											"LOGIN" => $users[$key]['LOGIN'],
											"ORDER" => $users[$key]['ORDER'],
											"ACTIVE" => $users[$key]['ACTIVE'],
											"EMAIL" => $users[$key]['EMAIL'],
											"LASTLOGIN" => $users[$key]['LASTLOGIN_TIME'],
											"ID" => $users[$key]['ID']);
		}
				$arrModResult['modHeader'] = Array(
											"NAME" =>GetMessage('NAME'),
											"LOGIN" => GetMessage('LOGIN'),
											"ORDER" => GetMessage('SORT'),
											"ACTIVE" => GetMessage('ACTIVE'),
											"EMAIL" => GetMessage('EMAIL'),
											"LASTLOGIN" => GetMessage('LASTLOGIN_TIME'),
											"ID" => GetMessage('ID')
											);
			$APP->admModSetUrl('act=edituser&id=', 'edit');
			$APP->admModSetUrl('act=adduser', 'add');
			$APP->admModSetUrl('act=del_user', 'action');
			$APP->admModSetUrl('act=usersG_filter&id='.$ID, 'filter');
			$APP->ModCreateBreadcumbs(GetMessage('USERS'),'?mod=users&act=users');
			$APP->ModCreateBreadcumbs(GetMessage('USERS_IN_GROUP'));
				$APP->admModDetemineActions(Array('AdD', 'LIST', 'EDIT', 'DELETE'));
				$APP->admModShowElements(
					$arrModResult['modHeader'],
					$arrModResult['modContent'],
					"list",
					Array('NAME', 'LOGIN', 'EMAIL', 'ID', 'ACTIVE'=>Array('3'=>' ',0=>GetMessage('NO'),1=>GetMessage('YES')))
					);
	}
/********GTuser***********GTuser*******GTuser********GTuser***********GTuser************GTuser******/
	function ListUsers($PARAMS=FALSE)
	{
		global $DB, $APP;
		if($PARAMS){
		if($PARAMS['NAME']!=FALSE)$PARAMS['NAME']='~%'.$PARAMS['NAME'].'%';
		if($PARAMS['LOGIN']!=FALSE)$PARAMS['LOGIN']='~%'.$PARAMS['LOGIN'].'%';
		if($PARAMS['EMAIL']!=FALSE)$PARAMS['EMAIL']='~%'.$PARAMS['EMAIL'].'%';
		if($PARAMS['USER_GROUP_GROUP_ID'][0]!=0){$PARAMS['USER_GROUP_GROUP_ID']=$PARAMS['USER_GROUP_GROUP_ID'][0];}else{unset($PARAMS['USER_GROUP_GROUP_ID']);}
		if($PARAMS['ACTIVE'][0]!='3'){$PARAMS['ACTIVE']=$PARAMS['ACTIVE'][0];} else {unset($PARAMS['ACTIVE']);}
		}
		$row = GTusers::Get('',$PARAMS,'ORDER');
		
			foreach($row as $key=>$val)
			{
			if($row[$key]['LASTLOGIN_TIME']!=0)
			{$row[$key]['LASTLOGIN_TIME'] = date('Y-m-j h:i:s', $row[$key]['LASTLOGIN_TIME']);}else{$row[$key]['LASTLOGIN_TIME']='';}
			if($row[$key]['ACTIVE']!=0)
			{$row[$key]['ACTIVE'] = GetMessage('YES');}else{$row[$key]['ACTIVE'] = GetMessage('NO');}
			 $arrModResult['modContent'][] =Array(
											"NAME" => $row[$key]['NAME'].' '.$row[$key]['LAST_NAME'],
											"LOGIN" => $row[$key]['LOGIN'],
											"ORDER" => $row[$key]['ORDER'],
											"ACTIVE" => $row[$key]['ACTIVE'],
											"EMAIL" => $row[$key]['EMAIL'],
											"LASTLOGIN" => $row[$key]['LASTLOGIN_TIME'],
											"ID" => $row[$key]['ID']);
			}
			$arrModResult['modHeader'] = Array(
											"NAME" =>GetMessage('NAME'),
											"LOGIN" => GetMessage('LOGIN'),
											"ORDER" => GetMessage('SORT'),
											"ACTIVE" => GetMessage('ACTIVE'),
											"EMAIL" => GetMessage('EMAIL'),
											"LASTLOGIN" => GetMessage('LASTLOGIN_TIME'),
											"ID" => GetMessage('ID')
											);
			$APP->admModSetUrl('act=edituser&id=', 'edit');
			$APP->admModSetUrl('act=adduser', 'add');
			$APP->admModSetUrl('act=del_user', 'action');
			$APP->admModSetUrl('act=users_filter', 'filter');
				$row2=GTgroupsusers::Get();
				if(!empty($row2)){
				foreach($row2 as $key=>$val)
				{
					$groups[]=array($row2[$key]['ID']=>$row2[$key]['NAME']);	
				}
				$group[]=' ';
				foreach($groups as $key=>$val)
				{
					foreach($val as $kkey=>$wal)
					{
					$group[$kkey]=$wal;
					}
				}}
			$APP->ModCreateBreadcumbs(GetMessage('USERS'),'?mod=users&act=users');
			$APP->ModCreateBreadcumbs(GetMessage('ALL_USERS'));
				$APP->admModDetemineActions(Array('AdD', 'LIST', 'EDIT', 'DELETE'));				
				$APP->admModShowElements(
					$arrModResult['modHeader'],
					$arrModResult['modContent'],
					"list",
					Array('NAME', 'LOGIN', 'EMAIL', 'ID', 'USER_GROUP_GROUP_ID' => $group, 'ACTIVE'=>Array('3'=>' ',0=>GetMessage('NO'),1=>GetMessage('YES')))
					);
	}
	
function AddFormUser()
	{
		global $DB, $APP , $arrModResult;
		$row2=GTgroupsusers::Get();
		if(!empty($row2)){
		foreach($row2 as $key=>$val)
		{
			$groups[]='<tr><td><input type="checkbox" name="GROUP_ID['.$row2[$key]['ID'].']" value="'.$row2[$key]['ID'].'"></td><td>'.$row2[$key]['NAME'].'
			</td><td><input type="text" name="ACTIVE_FROM_UG['.$row2[$key]['ID'].']" value=""></td>
			<td><input type="text" name="ACTIVE_TO_UG['.$row2[$key]['ID'].']" value=""></td></tr>';
			
		}
		$GG='<center><table border="1" cellspacing="0" cellpadding="0" style="text-align:center;";>
		<thead style="background-color:#999; color:#FFF;"><tr><td></td><td>'.GetMessage('NAME').'</td><td>'.GetMessage('ACTIVE_FROM').'</td><td>'.GetMessage('ACTIVE_TO').'</td></tr></thead>
		'.implode('',$groups).'</table></center>';
		
		}
		
		$row=GTuserfields::Get('*',Array('ACTIVE'=>1),'SORT');
		if ($row==TRUE){
		foreach($row as $key=>$val)
		{
			
			switch ($row[$key]['TYPE'])
			{
				case 'LIST':
					$FLD[]=Array("DESC" => "В Группе", "NAME" => "GROUP_ID", "TYPE" => "select", "VALUE" => '');
					break;
				case 'LINK':
					$S=GTusers::LINK($row[$key]['VALUES']);
					$FLD[]=Array("DESC" => $row[$key]['NAME'], "NAME" => 'FIELD['.$row[$key]['ID'].']', "VIEW" => 1, "TYPE" => "select", "VALUE" =>$S);
					break;
				case 'FILE':
					$FLD[]=Array("DESC" => $row[$key]['NAME'], "NAME" => "FIELD[".$row[$key]['ID']."]", "VIEW" => 1, "TYPE" => "file", "VALUE" => '');
					break;
				case 'STRING':
					$FLD[]=Array("DESC" => $row[$key]['NAME'], "NAME" => "FIELD[".$row[$key]['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
					break;
				case 'TEXT':
					$FLD[]=Array("DESC" => $row[$key]['NAME'], "NAME" => "FIELD[".$row[$key]['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '');
					break;
				case 'IMAGE':
					$FLD[]=Array("DESC" => $row[$key]['NAME'], "NAME" => "FIELD[".$row[$key]['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
					break;
				case 'NUMBER':
					$FLD[]=Array("DESC" => $row5[$kkey]['NAME'], "NAME" => "FIELD[".$row5[$kkey]['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
					break;
				default:
					$FLD[]=Array("DESC" => $row[$key]['NAME'], "NAME" => "FIELD[".$row[$key]['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
					break;
			}
		}}
		$arrModResult['modHeader'] = Array(
		GetMessage("MAIN")=> Array( 
		Array("DESC" => GetMessage('NAME'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('LAST_NAME'), "NAME" => "LAST_NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('PATRONYMIC'), "NAME" => "PATRONYMIC", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('LOGIN_TO_IN'), "NAME" => "LOGIN", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('EMAIL'), "NAME" => "EMAIL", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO'))),
		Array("DESC" => GetMessage('ACTIVE_FROM'), "NAME" => "ACTIVE_FROM", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('ACTIVE_TO'), "NAME" => "ACTIVE_TO", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('SORT'), "NAME" => "ORDER", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" =>  GetMessage('PASSWORD_IN'), "NAME" => "PASSWD[]", "VIEW" => 1, "TYPE" => "password", "VALUE" => ''),
		Array("DESC" =>  GetMessage('PASSWORD_REPEAT'), "NAME" => "PASSWD[]", "VIEW" => 1, "TYPE" => "password", "VALUE" => ''),
		Array("DESC" => GetMessage('AVATAR'), "NAME" =>"AVATAR", "VIEW" => 1, "TYPE" => "file", "VALUE" => ''),
		Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => "NEW_USER"))
		);
		if($FLD){$arrModResult['modHeader'][GetMessage('USERS_FIELDS')]=array_merge($arrModResult['modHeader'],$FLD);}
				if($GG){$arrModResult['modHeader'][GetMessage('GROUPS')]=$GG;}
		
		$APP->ModCreateBreadcumbs(GetMessage('USERS'),'?mod=users&act=users');
		$APP->ModCreateBreadcumbs(GetMessage('ALL_USERS'),'?mod=users&act=groupusers');
		$APP->ModCreateBreadcumbs(GetMessage('NEW_USERS'));
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
function Edit_User($ID)//редактор юзера
	{
		
		global $DB, $APP , $arrModResult;
		/**Статистика пользователья**/
		$cc=GTusers::STATICS($ID);
		//d($cc);
		foreach($cc as $key=>$val)
		{
			$STATIC[]=Array("DESC" => GetMessage($key), "NAME" => "", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$val.' '.GetMessage('DATAS'));
		}
		/**Конец**/
		$row = GTusers::Get('*',array('ID'=>$ID),'','',Array('GROUP_ID'));
		$AVATAR=GTFile::Get('*',array('OWNER'=>$ID,'SUBSYSTEM'=>'GTINX::USERAVATAR'));
		$AVATAR=$AVATAR[0];
		if(!empty($AVATAR))
		{
			if (file_exists(GTDOCROOT.$AVATAR['URL']))
			{
				$AVA='<img src="'.$AVATAR['URL'].'" width="150px">';
			}
			else
			{
				$AVA=GetMessage('FILE_NOT_EXIST');
			}
		}
		$ALLFILES=GTFile::Get('*',array('OWNER'=>$ID));
		
		foreach($ALLFILES as $key=>$val)
		{
			$F[]="<tr cellpadding=\"1\">
			<td><input type=\"checkbox\" name=\"FILED[]\" value=\"".$val['ID']."\"</td>
			<td>".$val['ID']."</td>
			<td>".$val['TITLE']."</td>
			<td>".$val['LOCAL_NAME']."</td>
			<td>".$val['LOCAL_DIR']."</td>
			<td>".$val['TYPE']."</td>
			<td>".$val['SIZE']."</td>
			<td>".$val['CREATED']."</td>
			<td>".$val['UPDATED']."</td>
			</tr>";
		}
		if(!empty($F))
		{
			$AllFiles="<center><table border=\"1\" cellspacing=\"0\" cellpadding=\"4\" style=\"text-align:center;\";>
			<thead style=\"background-color:#2c6fd3; color:#FFF;\"><tr>
			<td></td>
			<td>ID</td>
			<td>TITLE</td>
			<td>LOCAL_NAME</td>
			<td>LOCAL_DIR</td>
			<td>TYPE</td>
			<td>SIZE</td>
			<td>CREATED</td>
			<td>UPDATED</td>
			</tr></thead>".implode('',$F)."</table></center>";
			$AF=Array("DESC" =>'', "NAME" => "", "VIEW" => 1, "TYPE" => "raw", "VALUE" =>  $AllFiles);
		}
		if($row!==FALSE)
		{
			$row5=GTuserfields::Get('*',Array('ACTIVE'=>1),'SORT');
			foreach($row5 as $kkey=>$vval)
			{
				
				foreach($row[$ID]['FIELDS'] as $key=>$val)
				{
					if($row5[$kkey]['ID'] == $row[$ID]['FIELDS'][$key]['FIELD_ID'])
					{
						$row6[$kkey]['VAL']=$row[$ID]['FIELDS'][$key]['VALUE']; 
						$row6[$kkey]['VAL_NUM']=$row[$ID]['FIELDS'][$key]['VALUE_NUM'];
					}					
				}
				if($row6[$kkey]['VAL']=='' || $row6[$kkey]['VAL']==' '){$row6[$kkey]['VAL']=$vval['DEFAULT'];}
				switch ($vval['TYPE'])
					{
					case 'LIST':
						$FLD[]=Array("DESC" => "В Группе", "NAME" => "GROUP_ID", "TYPE" => "select", "VALUE" =>$row5['VAL']);
						break;
					case 'LINK':
						$S=GTusers::LINK($vval['VALUES']);
						$FLD[]=Array("DESC" => $vval['NAME'], "NAME" => 'LINK_'.$vval['ID'].'', "VIEW" => 1, "TYPE" => "select:".$row6[$kkey]['VAL_NUM'], "VALUE" =>$S);
					break;
					case 'FILE':
						$FLD[]=Array("DESC" => $vval['NAME'], "NAME" => "FIELD[".$vval['ID']."]", "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
						break;
					case 'STRING':
						$FLD[]=Array("DESC" => $vval['NAME'], "NAME" => "FIELD[".$vval['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => htmlspecialchars($row6[$kkey]['VAL']));
						break;
					case 'TEXT':
						$FLD[]=Array("DESC" => $vval['NAME'], "NAME" => "FIELD[".$vval['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" =>  $row6[$kkey]['VAL']);
						break;
					case 'IMAGE':
						$FLD[]=Array("DESC" => $vval['NAME'], "NAME" => "FIELD[".$vval['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row6[$kkey]['VAL']);
						break;
					case 'NUMBER':
						$FLD[]=Array("DESC" => $vval['NAME'], "NAME" => "FIELD[".$vval['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row6[$kkey]['VAL']);
						break;
					default:
						$FLD[]=Array("DESC" => $vval['NAME'], "NAME" => "FIELD[".$vval['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row6[$kkey]['VAL']);
						break;
					}
			}
			
			foreach($row[$ID]['GROUPS'] as $key=>$vaL)
			{
				if($vaL['USER_GROUP_ACTIVE_FROM']!=0){$row[$ID]['GROUPS'][ $key]['USER_GROUP_ACTIVE_FROM'] = date('Y-m-d h:i:s', $vaL['USER_GROUP_ACTIVE_FROM']);}
				if($vaL['USER_GROUP_ACTIVE_TO']!=0){$row[$ID]['GROUPS'][ $key]['USER_GROUP_ACTIVE_TO'] = date('Y-m-d h:i:s', $vaL['USER_GROUP_ACTIVE_TO']);}
				if($vaL['USER_GROUP_CREATED']!=0){$row[$ID]['GROUPS'][ $key]['USER_GROUP_CREATED'] = date('Y-m-d h:i:s', $vaL['USER_GROUP_CREATED']);}
			}
			
			if($row[$ID]['ACTIVE_FROM']!=0){$row[$ID]['ACTIVE_FROM'] = date('Y-m-d h:i:s', $row[$ID]['ACTIVE_FROM']);}else{$row[$ID]['ACTIVE_FROM']='';}
			if($row[$ID]['ACTIVE_TO']!=0){$row[$ID]['ACTIVE_TO'] = date('Y-m-d h:i:s', $row[$ID]['ACTIVE_TO']);}else{$row[$ID]['ACTIVE_TO']='';}
			if($row[$ID]['DATE_REGISTER']!=0){$row[$ID]['DATE_REGISTER'] = date('Y-m-d h:i:s', $row[$ID]['DATE_REGISTER']);}
			
			$row2=GTgroupsusers::Get();
			if(!empty($row2)){
			foreach($row2 as $key=>$val)
			{
					if($row[$ID]['GROUPS'][$row2[$key]['ID']]['GROUP_ID']==$row2[$key]['ID'])
					{
					if($row[$ID]['GROUPS'][$row2[$key]['ID']]['USER_GROUP_ACTIVE_FROM']==0){$row[$ID]['GROUPS'][$row2[$key]['ID']]['USER_GROUP_ACTIVE_FROM']='';}
					if($row[$ID]['GROUPS'][$row2[$key]['ID']]['USER_GROUP_ACTIVE_TO']==0){$row[$ID]['GROUPS'][$row2[$key]['ID']]['USER_GROUP_ACTIVE_TO']='';}
					$groups[]='<tr><td><input type="checkbox" name="GROUP_ID['.$row2[$key]['ID'].']" value="'.$row2[$key]['ID'].'" checked></td><td>'.$row2[$key]['NAME'].'
					</td><td><input type="text" name="ACTIVE_FROM_UG['.$row2[$key]['ID'].']" value="'.$row[$ID]['GROUPS'][$row2[$key]['ID']]['USER_GROUP_ACTIVE_FROM'].'"></td>
					<td><input type="text" name="ACTIVE_TO_UG['.$row2[$key]['ID'].']" value="'.$row[$ID]['GROUPS'][$row2[$key]['ID']]['USER_GROUP_ACTIVE_TO'].'"></td>
					<td>'.$row[$ID]['GROUPS'][$row2[$key]['ID']]['USER_GROUP_CREATED'].'</td></tr>';}
					else
					{$groups[]='<tr><td><input type="checkbox" name="GROUP_ID['.$row2[$key]['ID'].']" value="'.$row2[$key]['ID'].'"></td><td>'.$row2[$key]['NAME'].'
					</td><td><input type="text" name="ACTIVE_FROM_UG['.$row2[$key]['ID'].']" value=""></td>
					<td><input type="text" name="ACTIVE_TO_UG['.$row2[$key]['ID'].']" value=""></td></tr>';}
			}
			$GG='<center><table border="1" cellspacing="0" cellpadding="0" style="text-align:center;";>
			<thead style="background-color:#999; color:#FFF;"><tr><td></td><td>'.GetMessage('NAME').'</td><td>'.GetMessage('ACTIVE_FROM').'</td><td>'.GetMessage('ACTIVE_TO').'</td>
			<td>'.GetMessage('CREATED').'</td></tr></thead>
			'.implode('',$groups).'</table></center>';
			}
			
			$arrModResult['modHeader'] = Array(
			GetMessage("MAIN")=> Array(
				Array("DESC" => GetMessage('NAME'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" => htmlspecialchars($row[$ID]['NAME'])),
				Array("DESC" => GetMessage('LAST_NAME'), "NAME" => "LAST_NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" =>  htmlspecialchars($row[$ID]['LAST_NAME'])),
				Array("DESC" => GetMessage('PATRONYMIC'), "NAME" => "PATRONYMIC", "VIEW" => 1, "TYPE" => "string", "VALUE" =>  htmlspecialchars($row[$ID]['PATRONYMIC'])),
				Array("DESC" => GetMessage('LOGIN_TO_IN'), "NAME" => "LOGIN", "VIEW" => 1, "TYPE" => "string", "VALUE" => htmlspecialchars($row[$ID]['LOGIN'])),
				Array("DESC" => GetMessage('EMAIL'), "NAME" => "EMAIL", "VIEW" => 1, "TYPE" => "string", "VALUE" => htmlspecialchars($row[$ID]['EMAIL'])),
				Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio:".$row[$ID]['ACTIVE'], "VALUE" => Array("1" =>GetMessage('YES'), '0'=>GetMessage('NO'))),
				Array("DESC" => GetMessage('MODERAITED'), "NAME" => "MODERAITED", "VIEW" => 1, "TYPE" => "radio:".$row[$ID]['MODERAITED'], "VALUE" => Array("1" =>GetMessage('YES'), '0'=>GetMessage('NO'))),
				Array("DESC" => GetMessage('ACTIVE_FROM'), "NAME" => "ACTIVE_FROM", "VIEW" => 1, "TYPE" => "string", "VALUE" => htmlspecialchars($row[$ID]['ACTIVE_FROM'])),
				Array("DESC" => GetMessage('ACTIVE_TO'), "NAME" => "ACTIVE_TO", "VIEW" => 1, "TYPE" => "string", "VALUE" => htmlspecialchars($row[$ID]['ACTIVE_TO'])),
				Array("DESC" => GetMessage('DATE_REGISTER'), "NAME" => "DATE_REGISTER", "VIEW" => 1, "TYPE" => "text", "VALUE" => $row[$ID]['DATE_REGISTER']),
				Array("DESC" => GetMessage('SORT'), "NAME" => "ORDER", "VIEW" => 1, "TYPE" => "string", "VALUE" => htmlspecialchars($row[$ID]['ORDER'])),
				Array("DESC" => GetMessage('PASSWORD_IN'), "NAME" => "PASSWD[]", "VIEW" => 1, "TYPE" => "password", "VALUE" => ''),
				Array("DESC" => GetMessage('PASSWORD_REPEAT'), "NAME" => "PASSWD[]", "VIEW" => 1, "TYPE" => "password", "VALUE" => ''),
				Array("DESC" => '', "NAME" => "", "VIEW" => 1, "TYPE" => "raw", "VALUE" =>$AVA),
				Array("DESC" => GetMessage('AVATAR'), "NAME" =>"AVATAR", "VIEW" => 1, "TYPE" => "file", "VALUE" => ''),
				Array("DESC" => GetMessage('AVATAR'), "NAME" =>"AVATAR_ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => $AVATAR['ID']),
				Array("DESC" => GetMessage('ID'), "NAME" => "SHOWID", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$row[$ID]['ID']),
				Array("DESC" => "", "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$row[$ID]['ID']),
				Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => "EDIT_USER"))
				);
				if(!empty($FLD)){$arrModResult['modHeader'][GetMessage('USERS_FIELDS')]=array_merge($arrModResult['modHeader'],$FLD);}
				if(!empty($STATIC)){$arrModResult['modHeader'][GetMessage('STATIC')]=array_merge($arrModResult['modHeader'],$STATIC);}
				if(!empty($GG)){$arrModResult['modHeader'][GetMessage('GROUPS')]=$GG;}
				if(!empty($ALLFILES)){$arrModResult['modHeader'][GetMessage('ALLFILES')]=$AllFiles;}
				
				$APP->ModCreateBreadcumbs( GetMessage('USERS'),'?mod=users&act=users');
				$APP->ModCreateBreadcumbs( GetMessage('ALL_USERS'),'?mod=users&act=groupusers');
				$APP->ModCreateBreadcumbs($row[$ID]['NAME'].' '.$row[$ID]['LAST_NAME']);
				$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
		}
	}
	
function AddUser($arVar)
	{
		global $DB;
		
		$arv1=Array(
			"NAME"=>$arVar['NAME'],
			"LAST_NAME"=>$arVar['LAST_NAME'],
			"PATRONYMIC"=>$arVar['PATRONYMIC'],
			"LOGIN"=>$arVar['LOGIN'],
			"EMAIL"=>$arVar['EMAIL'],
			"PASSWD"=>$arVar['PASSWD'][0],
			"PASSWD2"=>$arVar['PASSWD'][1],
			"ACTIVE"=>$arVar['ACTIVE'],
			"ACTIVE_FROM"=>$arVar['ACTIVE_FROM'],
			"ACTIVE_TO"=>$arVar['ACTIVE_TO'],
			"ORDER"=>$arVar['ORDER']);
		foreach($arVar['FIELD'] as $key=>$val)
		{
			if(is_array($val))
			{
				foreach($val as $CAL)
				{
					$CAL_n='';
					$CAL_n=(int)$CAL;
					$FLD[]=array(
					'FIELD_ID'=>$key,
					'VALUE'=>$CAL,
					'VALUE_NUM'=>$CAL_n);
				}
			}
			else
			{
				$val_n='';
				$val_n=(int)$val;
				$FLD[]=array(
				'FIELD_ID'=>$key,
				'VALUE'=>$val,
				'VALUE_NUM'=>$val_n);
			}
		}
				$uing=array(
				"GROUP_ID"=>$arVar['GROUP_ID'],
				"ACTIVE_FROM"=>$arVar['ACTIVE_FROM_UG'],
				"ACTIVE_TO"=>$arVar['ACTIVE_TO_UG']);
		$res = GTusers::Add($arv1,$FLD,$uing);					
			
				
			if($res===TRUE)
			{
				header("Location: ./?mod=users&act=groupusers");
				die();
			}
			elseif($res=='SQL')
			{
				GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
			}
	}
	
function UpdateUser($arVar)
	{	
		if(!empty($_FILES['AVATAR']['name']))
		{
			if(!empty($arVar['AVATAR_ID']))
			{
				GTfile::Delete($arVar['AVATAR_ID'],TRUE);
			}
		}
		if(!empty($arVar['FILED']))
		{
			foreach($arVar['FILED'] as $key=>$val)
			{
			GTfile::Delete($val,TRUE);
			}
		}
		
		$update=Array("ID"=>$arVar['ID'],
			"NAME"=>$arVar['NAME'],
			"LAST_NAME"=>$arVar['LAST_NAME'],
			"PATRONYMIC"=>$arVar['PATRONYMIC'],
			"LOGIN"=>$arVar['LOGIN'],
			"EMAIL"=>$arVar['EMAIL'],
			"ACTIVE"=>$arVar['ACTIVE'],
			"MODERAITED"=>$arVar['MODERAITED'],
			"PASSWD"=>$arVar['PASSWD'][0],
			"PASSWD2"=>$arVar['PASSWD'][1],
			"ACTIVE_FROM"=>$arVar['ACTIVE_FROM'],
			"ACTIVE_TO"=>$arVar['ACTIVE_TO'],
			"ORDER"=>$arVar['ORDER']);
		
		foreach($arVar['FIELD'] as $key=>$val)
		{
			if(is_array($val))
			{
				foreach($val as $CAL)
				{
					$CAL_n='';
					$CAL_n=(int)$CAL;
					$FLD[]=array(
					'FIELD_ID'=>$key,
					'VALUE'=>$CAL,
					'VALUE_NUM'=>$CAL_n);
				}
			}
			else
			{
				$val_n='';
				$val_n=(int)$val;
				$FLD[]=array(
				'FIELD_ID'=>$key,
				'VALUE'=>$val,
				'VALUE_NUM'=>$val_n);
			}
		}
		
		$uing=array(
			"GROUP_ID"=>$arVar['GROUP_ID'],
			"ACTIVE_FROM"=>$arVar['ACTIVE_FROM_UG'],
			"ACTIVE_TO"=>$arVar['ACTIVE_TO_UG']);
			
		$res = GTusers::Update($update,$FLD,$uing);	
		
		if($res===TRUE){
			header("Location: ./?mod=users&act=groupusers");
			die();
		}
		else{GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));}
	}
	function Del_Users($arVar)
	{
		$res=GTusers::Delete($arVar);
		header("Location: ./?mod=users&act=groupusers");
		die();
	}
/*****GROUPS********GROUPS***********GROUPS**********GROUPS*********GROUPS**********GROUPS************/
	function Del_Group($arVar)
	{
		$res=GTgroupsusers::Delete($arVar);
		header("Location: ./?mod=users&act=listgr");
		die();
	}
	function ListGroups($PARAMS=FALSE)
	{
		global $DB, $APP;
		if($PARAMS){
			if($PARAMS['NAME']!=FALSE)$PARAMS['NAME']='~%'.$PARAMS['NAME'].'%';
			if($PARAMS['GROUP_KEY']!=FALSE)$PARAMS['GROUP_KEY']='~%'.$PARAMS['GROUP_KEY'].'%';
			if($PARAMS['ACTIVE'][0]!='3'){$PARAMS['ACTIVE']=$PARAMS['ACTIVE'][0];} else {unset($PARAMS['ACTIVE']);}
		}
		$row=GTgroupsusers::Get('',$PARAMS);
		foreach($row as $key=>$val)
			{
			if($row[$key]['CREATED']!=0)
			{$row[$key]['CREATED'] = date('Y-m-j h:i:s', $row[$key]['CREATED']);}
			if($row[$key]['ACTIVE']!=0)
			{$row[$key]['ACTIVE'] = GetMessage('YES');}else{$row[$key]['ACTIVE'] = GetMessage('NO');}
			 $arrModResult['modContent'][] =Array(
											"NAME" => $row[$key]['NAME'],
											"CREATED" => $row[$key]['CREATED'],
											"DESC" => $row[$key]['DESC'],
											"ACTIVE" => $row[$key]['ACTIVE'],
											"ID" => $row[$key]['ID']);
			}
			$arrModResult['modHeader'] = Array(
											"NAME" =>GetMessage('NAME'),
											"CREATED" => GetMessage('CREATED'),
											"DESC" => GetMessage('DESC'),
											"ACTIVE" => GetMessage('ACTIVE'),
											"ID" =>GetMessage('ID')
											);
			$APP->admModSetUrl('act=editgroup&id=', 'edit');
			$APP->admModSetUrl('act=addgroup', 'add');
			$APP->admModSetUrl('act=del_group', 'action');
			$APP->admModSetUrl('act=group_filter', 'filter');
			$APP->ModCreateBreadcumbs(GetMessage('USERS'),'?mod=users&act=users');
			$APP->ModCreateBreadcumbs(GetMessage('ALL_GROUPS'));
				$APP->admModDetemineActions(Array('AdD', 'LIST', 'EDIT', 'DELETE'));
				$APP->admModShowElements(
					$arrModResult['modHeader'],
					$arrModResult['modContent'],
					"list",
					Array('NAME', 'GROUP_KEY', 'ID', 'ACTIVE'=>Array('3'=>' ',0=>GetMessage('NO'),1=>GetMessage('YES')))
					);
	}
	
	function AddFormGroup()
	{
		global $DB, $APP , $arrModResult;
		
		$MODE=GTuserprivs::GetMode();
		$row=GTusers::Get();
		if(!empty($row)){
		foreach($row as $key=>$val)
		{
			$users[]='<tr>
						<td><input type="checkbox" name="ID['.$row[$key]['ID'].']" value="'.$row[$key]['ID'].'"></td>
						<td>'.$row[$key]['NAME'].' '.$row2[$key]['LAST_NAME'].'</td>
						<td><input type="text" name="ACTIVE_FROM_UG['.$row[$key]['ID'].']" value=""></td>
						<td><input type="text" name="ACTIVE_TO_UG['.$row[$key]['ID'].']" value=""></td>
						</tr>';
						
		}
		$GG='<center><table border="1" cellspacing="0" cellpadding="4" style="text-align:center;";>
			<thead style="background-color:#999; color:#FFF;"><tr><td></td><td>'.GetMessage('NAME').'</td><td>'.GetMessage('ACTIVE_FROM').'</td><td>'.GetMessage('ACTIVE_TO').'</td>
			</tr></thead>
			'.implode('',$users).'</table></center>';
		}
		foreach($MODE as $key=>$val)
				{
				foreach($val[0]as $KEY=>$VAL)
				{
				$select[]='<option value="'.$VAL.'">'.$VAL.'</option>';
				}
				
				$MD[]='<tr>
				<td><input type="checkbox" name="NAME_PRIV['.$key.']" value="'.$key.'"></td>
						<td>'.$key.'</td>
						<td><input type="text" name="ACTIVE_FROM_PRIV['.$key.']" value=""></td>
						<td><input type="text" name="ACTIVE_TO_PRIV['.$key.']" value=""></td>
						<td><input type="radio" name="ACTIVE_PRIV['.$key.']" value="1">да</td>
						<td><select name="PRIV['.$key.']">'.implode('',$select).'</select></td>
						</tr>';
				unset($select);
				
				}
				$MD1='<center><table border="1" cellspacing="0" cellpadding="4" style="text-align:center;";>
				<thead style="background-color:#999; color:#FFF;"><tr><td>1</td><td>2</td><td>'.GetMessage('ACTIVE_FROM').'</td><td>'.GetMessage('ACTIVE_TO').'</td><td>'.GetMessage('ACTIVE').'</td>
				<td>'.GetMessage('PRIV').'</td></tr></thead>
				'.implode('',$MD).'</table></center>';
		$arrModResult['modHeader'] = Array(
		GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('NAME'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => "NEW_GROUP")),
		GetMessage("PROPERTIES")=> Array(
				Array("DESC" => GetMessage('KEY'), "NAME" => "GROUP_KEY", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
				Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO'))),
				Array("DESC" => GetMessage('ACTIVE_FROM'), "NAME" => "ACTIVE_FROM", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
				Array("DESC" => GetMessage('ACTIVE_TO'), "NAME" => "ACTIVE_TO", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
				Array("DESC" => GetMessage('SESSION_LIFE'), "NAME" => "SESSION_LIFE", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
				Array("DESC" => GetMessage('SU'), "NAME" => "SU", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO')))
				),
		GetMessage("DESC")=> Array(Array("DESC" =>'', "NAME" => "DESC", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '')),
		GetMessage("USERS")=>$GG);
		$arrModResult['modHeader'][GetMessage("PRIVS")]=$MD1;
		$APP->ModCreateBreadcumbs(GetMessage('USERS'),'?mod=users&act=users');
		$APP->ModCreateBreadcumbs(GetMessage('ALL_GROUPS'),'?mod=users&act=listgr');
		$APP->ModCreateBreadcumbs(GetMessage('NEW_GROUPS'));
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	function GetFromGroup($ID) //redactor группы
	{
		global $DB, $APP , $arrModResult;
		$MODE=GTuserprivs::GetMode();
		$res = GTgroupsusers::Get('*',Array('ID'=>$ID),'','',Array('ID','USER_ID'));
		$row=$res[$ID];
		
		if($res!==FALSE)
		{
			$row2=GTusers::Get('',Array('USER_GROUP_GROUP_ID'=>$ID));
			if(!empty($row2)){
			foreach($row2 as $key=>$val)
			{
				
				if($row['USERS'][$val['ID']]['USERGROUP_ACTIVE_FROM']==0){$row['USERS'][$val['ID']]['USERGROUP_ACTIVE_FROM']='';}else{$row['USERS'][$val['ID']]['USERGROUP_ACTIVE_FROM']=date('Y-m-d h:i:s',$row['USERS'][$val['ID']]['USERGROUP_ACTIVE_FROM']);}
				if($row['USERS'][$val['ID']]['USERGROUP_ACTIVE_TO']==0){$row['USERS'][$val['ID']]['USERGROUP_ACTIVE_TO']='';}else{$row['USERS'][$val['ID']]['USERGROUP_ACTIVE_TO']=date('Y-m-d h:i:s',$row['USERS'][$val['ID']]['USERGROUP_ACTIVE_TO']);}
				if($row['USERS'][$val['ID']]['USERGROUP_CREATED']==0){$row['USERS'][$val['ID']]['USERGROUP_CREATED']='';}else{$row['USERS'][$val['ID']]['USERGROUP_CREATED']=date('Y-m-d h:i:s',$row['USERS'][$val['ID']]['USERGROUP_CREATED']);}
				
				$users[]='<tr>
						<td><input type="checkbox" name="USER_ID['.$row2[$key]['ID'].']" value="'.$row2[$key]['ID'].'" checked></td>
						<td>'.$row2[$key]['NAME'].' '.$row2[$key]['LAST_NAME'].'</td>
						<td><input type="text" name="ACTIVE_FROM_UG['.$row2[$key]['ID'].']" value="'.$row['USERS'][$val['ID']]['USERGROUP_ACTIVE_FROM'].'"></td>
						<td><input type="text" name="ACTIVE_TO_UG['.$row2[$key]['ID'].']" value="'.$row['USERS'][$val['ID']]['USERGROUP_ACTIVE_TO'].'"></td>
						<td>'.$row['USERS'][$val['ID']]['USERGROUP_CREATED'].'</td>
						</tr>';
				
				$USERS[]=$row2[$key]['ID'];
			}}
			
			$row3=GTusers::Get();
			if(!empty($row3)){
			foreach($row3 as $key=>$val)
			{
				foreach($USERS as $kkey=>$vval)
				{
					if($row3[$key]['ID']==$vval){unset($row3[$key]['ID']);}
				}
				if($row3[$key]['ID'])
				{
				$users[]='<tr>
						<td><input type="checkbox" name="USER_ID['.$row3[$key]['ID'].']" value="'.$row3[$key]['ID'].'"></td>
						<td>'.$row3[$key]['NAME'].'</td>
						<td><input type="text" name="ACTIVE_FROM_UG['.$row3[$key]['ID'].']" value=""></td>
						<td><input type="text" name="ACTIVE_TO_UG['.$row3[$key]['ID'].']" value=""></td>
						</tr>';
				}
			}}
			$GG='<center><table border="1" cellspacing="0" cellpadding="4" style="text-align:center;";>
			<thead style="background-color:#999; color:#FFF;"><tr><td></td><td>'.GetMessage('NAME').'</td><td>'.GetMessage('ACTIVE_FROM').'</td><td>'.GetMessage('ACTIVE_TO').'</td>
			<td>'.GetMessage('CREATED').'</td></tr></thead>
			'.implode('',$users).'</table></center>';
			
				if($row['ACTIVE_FROM']!=0){$row['ACTIVE_FROM'] = date('Y-m-j h:i:s', $row['ACTIVE_FROM']);}
				if($row['ACTIVE_TO']!=0){$row['ACTIVE_TO'] = date('Y-m-j h:i:s', $row['ACTIVE_TO']);}
				if($row['CREATED']!=0){$row['CREATED'] = date('Y-m-j h:i:s', $row['CREATED']);}
				if($row['UPDATED']!=0){$row['UPDATED'] = date('Y-m-j h:i:s', $row['UPDATED']);}else{$row['UPDATED']= GetMessage('NO_UPDATED');}
				foreach($MODE as $key=>$val)
				{
				$R=GTuserprivs::Get($ID,$key);
					if($R==2)
					{
						foreach($val[0]as $KEY=>$VAL)
						{
						$select[]='<option value="'.$VAL.'">'.$VAL.'</option>';
						}
						
						$MD[]='<tr>
						<td><input type="checkbox" name="NAME_PRIV['.$key.']" value="'.$key.'"></td>
								<td>'.$key.'</td>
								<td><input type="text" name="ACTIVE_FROM_PRIV['.$key.']" value=""></td>
								<td><input type="text" name="ACTIVE_TO_PRIV['.$key.']" value=""></td>
								<td><input type="radio" name="ACTIVE_PRIV['.$key.']" value="1">да</td>
								<td><select name="PRIV['.$key.']">'.implode('',$select).'</select></td>
								</tr>';
						unset($select);
					}
					else
					{
						$select[]='<option value="'.$R['NAME'].'">'.$R['NAME'].'</option>';
						foreach($val[0]as $KEY=>$VAL)
						{
							if($VAL!=$R['NAME'])
							{$select[]='<option value="'.$VAL.'">'.$VAL.'</option>';}
						}
						
						$MD[]='<tr>
						<td><input type="checkbox" name="NAME_PRIV['.$key.']" value="'.$key.'" checked></td>
								<td>'.$key.'</td>
								<td><input type="text" name="ACTIVE_FROM_PRIV['.$key.']" value="'.$R['ACTIVE_FROM'].'"></td>
								<td><input type="text" name="ACTIVE_TO_PRIV['.$key.']" value="'.$R['ACTIVE_TO'].'"></td>
								<td><input type="radio" name="ACTIVE_PRIV['.$key.']" value="'.$R['ACTIVE'].'" checked>да</td>
								<td><select name="PRIV['.$key.']">'.implode('',$select).'</select></td>
								</tr>';
						unset($select);
					}
				}
				$MD1='<center><table border="1" cellspacing="0" cellpadding="4" style="text-align:center;";>
				<thead style="background-color:#999; color:#FFF;"><tr><td>1</td><td>2</td><td>'.GetMessage('ACTIVE_FROM').'</td><td>'.GetMessage('ACTIVE_TO').'</td><td>'.GetMessage('ACTIVE').'</td>
				<td>'.GetMessage('PRIV').'</td></tr></thead>
				'.implode('',$MD).'</table></center>';
				foreach($row as $key=>$val)
				{
				$mass[]=Array("DESC" => "", "NAME" => "CHECK[".$key."]", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$val);
				}
				if($row['ACTIVE']=='1'){$act = Array("1" =>GetMessage('YES').':checked', 2=>GetMessage('NO'));}else{$act = Array("1" =>GetMessage('YES'), 2=>GetMessage('NO').':checked');}
				$arrModResult['modHeader'] = Array(
				GetMessage("MAIN")=> Array(
				Array("DESC" => GetMessage('ID'), "NAME" => "SHOWID", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$row['ID']),
				Array("DESC" => GetMessage('NAME'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['NAME']),
				Array("DESC" => "", "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$row['ID']),
				Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => "EDIT_GROUP")
				),
				GetMessage("PROPERTIES")=> Array(
				Array("DESC" => GetMessage('KEY'), "NAME" => "GROUP_KEY", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['GROUP_KEY']),
				Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => $act),
				Array("DESC" => GetMessage('ACTIVE_FROM'), "NAME" => "ACTIVE_FROM", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['ACTIVE_FROM']),
				Array("DESC" => GetMessage('ACTIVE_TO'), "NAME" => "ACTIVE_TO", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['ACTIVE_TO']),				
				Array("DESC" => GetMessage('CREATED'), "NAME" => "CREATED", "VIEW" => 1, "TYPE" => "text", "VALUE" => $row['CREATED']),
				Array("DESC" => GetMessage('UPDATED'), "NAME" => "UPDATED", "VIEW" => 1, "TYPE" => "text", "VALUE" => $row['UPDATED']),
				Array("DESC" => GetMessage('SESSION_LIFE'), "NAME" => "SESSION_LIFE", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['SESSION_LIFE']),
				Array("DESC" => GetMessage('SU'), "NAME" => "SU", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO'))),
				Array("DESC" => GetMessage('CP'), "NAME" => "CP", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO')))
				),
				GetMessage("DESC")=> Array(Array("DESC" =>'', "NAME" => "DESC", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $row['DESC'])),
				GetMessage("USERS")=>$GG);
				
				$arrModResult['modHeader'][GetMessage("MAIN")]=array_merge($arrModResult['modHeader'][GetMessage("MAIN")],$mass);
				$arrModResult['modHeader'][GetMessage("PRIVS")]=$MD1;
				$APP->ModCreateBreadcumbs(GetMessage('USERS'),'?mod=users&act=users');
				$APP->ModCreateBreadcumbs(GetMessage('ALL_GROUPS'),'?mod=users&act=listgr');
				$APP->ModCreateBreadcumbs($row['NAME']);
				$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
		}
	}
	
	function AddGroup($arVar)
	{
		global $DB;
		$arGroup=array(
			'NAME'=>$arVar['NAME'],
			'GROUP_KEY'=>$arVar['GROUP_KEY'],
			'ACTIVE'=>$arVar['ACTIVE'],
			'ACTIVE_FROM'=>$arVar['ACTIVE_FROM'],
			'ACTIVE_TO'=>$arVar['ACTIVE_TO'],
			'SU'=>$arVar['SU'],
			'CP'=>$arVar['CP'],
			'DESC'=>$arVar['DESC'],
			'SESSION_LIFE'=>$arVar['SESSION_LIFE']);
		
		$res=GTgroupsusers::Add($arGroup);
		$GROUP_ID = $DB->insertId();
		$arUser=array(
				'USER_ID'=>$arVar['ID'],
				'GROUP_ID'=>$GROUP_ID,
				'ACTIVE_FROM'=>$arVar['ACTIVE_FROM_UG'],
				'ACTIVE_TO'=>$arVar['ACTIVE_TO_UG']);
		GTuseringroups::AddGroup($arUser);
		foreach($arVar['NAME_PRIV'] as $key=>$val)
		{
			if($val!='')
			{
			$PRIV[]=Array("GROUP_ID"=>$GROUP_ID,
						"NAME"=>$arVar['PRIV'][$val],
						"ACTIVE"=>$arVar['ACTIVE_PRIV'][$val],
						"ACTIVE_FROM"=>$arVar['ACTIVE_FROM_PRIV'][$val],
						"ACTIVE_TO"=>$arVar['ACTIVE_TO_PRIV'][$val],
						"SUBSYSTEM"=>$val);
			}
		}
		GTuserprivs::Add($PRIV);
		if($res===TRUE){
			header("Location: ./?mod=users&act=listgr");
			die();
		}
		else{
			GTapp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
		}
	}
	
	function UpdateGroup($arVar)
	{
		//print_r($arVar);
		$GGroups= new GTugr;
		$UserGR= new GTusgp;
		if(isset($arVar['ID']))
		{
			foreach($arVar['NAME_PRIV'] as $key=>$val)
			{
				if($val!='')
				{
				$PRIV[]=Array("GROUP_ID"=>$arVar['ID'],
							"NAME"=>$arVar['PRIV'][$val],
							"ACTIVE"=>$arVar['ACTIVE_PRIV'][$val],
							"ACTIVE_FROM"=>$arVar['ACTIVE_FROM_PRIV'][$val],
							"ACTIVE_TO"=>$arVar['ACTIVE_TO_PRIV'][$val],
							"SUBSYSTEM"=>$val);
				}
			}
			
			GTuserprivs::Add($PRIV);
			if($arVar['ACTIVE']==''){$arVar['ACTIVE']=1;}
			$update=Array(		"ID"=>$arVar['ID'],
								"NAME"=>$arVar['NAME'],
								"ACTIVE"=>$arVar['ACTIVE'],
								"ACTIVE_FROM"=>$arVar['ACTIVE_FROM'],
								"ACTIVE_TO"=>$arVar['ACTIVE_TO'],
								"SU"=>$arVar['SU'],
								"CP"=>$arVar['CP'],
								"SESSION_LIFE"=>$arVar['SESSION_LIFE'],
								"GROUP_KEY"=>$arVar['GROUP_KEY'],
								"DESC"=>$arVar['DESC']);
			$arUser=array(
				'USER_ID'=>$arVar['USER_ID'],
				'GROUP_ID'=>$arVar['ID'],
				'ACTIVE_FROM'=>$arVar['ACTIVE_FROM_UG'],
				'ACTIVE_TO'=>$arVar['ACTIVE_TO_UG']);	
				
			GTuseringroups::UpdateGroup($arUser);
			$noup=array();
			foreach($update as $key=>$val)
			{
				if($arVar['CHECK'][$key]==$val){$noup[]=$key;}
			}
			foreach($noup as $key=>$val)
			{
			unset ($update[$val]);
			}
			
			if(!empty($update))
				{
				
					$IDD=array('ID'=>$arVar['CHECK']['ID']);
					$update=array_merge($update,$IDD);
					$res = GTgroupsusers::Update($update);	
				}
			if($res==TRUE){
				header("Location: ./?mod=users&act=listgr");
				die();
			}
			else{GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));}
			
		}
	}
}	
?>