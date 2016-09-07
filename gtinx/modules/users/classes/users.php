<?
class GTusers
{
function STATICS($ID,$REG_DATE=FALSE,$LAST_LOG=FALSE)
{
	global $DB;
	if($REG_DATE!=TRUE || $LAST_LOG!=TRUE)
	{
		$SQL_USER="SELECT `DATE_REGISTER`,`LASTLOGIN_TIME` FROM `g_users` WHERE `ID`='$ID'";
		$res=$DB->Query($SQL_USER);
		$row=$DB->fetchAssoc($res);
		$REG_DATE=$row['DATE_REGISTER'];
		$LAST_LOG=$row['LASTLOGIN_TIME'];
	}
	$current_date = mktime (0,0,0,date("m") ,date("d"),date("Y")); //дата сегодня
	$difference = ($current_date - $REG_DATE); //разница в секундах 
	$difference_in_days = round($difference / 86400); //разница в днях
	/*ALL*/
	$SQL_ALL="SELECT  SQL_CALC_FOUND_ROWS dblock.`ID`, dblock.`ID`,dblock.`AUTHOR` FROM `g_dblock` as dblock 
		LEFT JOIN `g_dblock_types` as type ON (dblock.`TYPE_ID`=type.`ID`) 
		LEFT JOIN `g_dblock_subtypes` as st ON (st.`ID`=dblock.`SUBTYPE`) 
		LEFT JOIN `g_dblock_group` as gr ON (type.GROUP_ID=gr.ID) 
 LEFT JOIN `g_dblock_multy` as `dblock_C` ON (`dblock`.`ID`=`dblock_C`.`DBLOCK_ID`)   WHERE dblock.`AUTHOR`='$ID' ORDER BY dblock.`CREATED` ASC";
	$DB->Query($SQL_ALL);
	$count['ALL']=$DB->QResult("SELECT FOUND_ROWS()");
	
	/*LAST 30 DAYS*/
	$date=date("Y-m-j h:i:s");		
	$d2=strtotime($date);
	$d1=strtotime("-30 day");
	$SQL_30_DAYS="SELECT  SQL_CALC_FOUND_ROWS dblock.`ID`, dblock.`ID`,dblock.`AUTHOR` FROM `g_dblock` as dblock 
		LEFT JOIN `g_dblock_types` as type ON (dblock.`TYPE_ID`=type.`ID`) 
		LEFT JOIN `g_dblock_subtypes` as st ON (st.`ID`=dblock.`SUBTYPE`) 
		LEFT JOIN `g_dblock_group` as gr ON (type.GROUP_ID=gr.ID) 
 LEFT JOIN `g_dblock_multy` as `dblock_C` ON (`dblock`.`ID`=`dblock_C`.`DBLOCK_ID`)   WHERE dblock.`AUTHOR`='$ID' AND 
 (dblock.`CREATED`>'$d1' AND dblock.`CREATED`<'$d2')  ORDER BY dblock.`CREATED` ASC ";
	$DB->Query($SQL_30_DAYS);
	$count['30_DAYS']=$DB->QResult("SELECT FOUND_ROWS()");
	/*LAST_DAY*/
	$d2=strtotime($date);
	$d1=strtotime("-1 day");
	$SQL_LAST_DAY="SELECT  SQL_CALC_FOUND_ROWS dblock.`ID`, dblock.`ID`,dblock.`AUTHOR` FROM `g_dblock` as dblock 
		LEFT JOIN `g_dblock_types` as type ON (dblock.`TYPE_ID`=type.`ID`) 
		LEFT JOIN `g_dblock_subtypes` as st ON (st.`ID`=dblock.`SUBTYPE`) 
		LEFT JOIN `g_dblock_group` as gr ON (type.GROUP_ID=gr.ID) 
 LEFT JOIN `g_dblock_multy` as `dblock_C` ON (`dblock`.`ID`=`dblock_C`.`DBLOCK_ID`)   WHERE dblock.`AUTHOR`='$ID' AND 
 (dblock.`CREATED`>'$d1' AND dblock.`CREATED`<'$d2')  ORDER BY dblock.`CREATED` ASC ";
	$DB->Query($SQL_LAST_DAY);
	$count['LAST_DAY']=$DB->QResult("SELECT FOUND_ROWS()");
	$count['PER_DAY']=round($count['ALL']/$difference_in_days);
	return $count;
}
function Check($b=array())
{
	foreach ($b as $key=>$val)
	{
		
		if($key=='DESC')
		{ 
			if($b['edswitch'.$key]=='ht')
			{
			$b[$key]=trim($b[$key]);
			//$b[$key.'_TYPE']=$b['edswitch'.$key];
			}
			else
			{
			//$b[$key.'_TYPE']=$b['edswitch'.$key];
			$b[$key]=nl2br(htmlspecialchars(trim($b[$key])));
			}
		}
		else
		{
			$b[$key]=stripslashes(trim($b[$key]));
		}
	}
	
	global $DB;
	$a=array(
	'ID'=>array('int',TRUE),
	'NAME'=>array('string',TRUE),
	'LAST_NAME'=>array('string',TRUE),
	'PATRONYMIC'=>array('string',FALSE),
	'WEBSITE'=>array('string',FALSE),
	'LOGIN'=>array('login',TRUE),
	'EMAIL'=>array('email',TRUE),
	'PASSWD'=>array('md5',TRUE),
	'ACTIVE'=>array('boolean',FALSE),
	'MODERAITED'=>array('boolean',FALSE),
	'ACTIVE_FROM'=>array('date',FALSE),
	'ACTIVE_TO'=>array('date',FALSE),
	'ORDER'=>array('int',FALSE),
	'RATING'=>array('int',FALSE),
	'LASTLOGIN_TIME'=>array('int',FALSE),
	'LASTLOGIN_IP'=>array('string',FALSE),
	'DATE_REGISTER'=>array('date',TRUE)				
	);
		
		$c=array();
		foreach($b as $key=>$val)
		{
	
		if(!isset($a[$key])) {$c[]=$key;}
		else{
				switch($a[$key][0])
				{
				case 'boolean':
					if($val!='1')
					{
					$b[$key]='0';
					}
					break;
				case 'int':
					if(is_numeric($val))
					{
					$val=(int)$val;
					}
					if((int)$val!==$val){$c[]=$key;}else{$b[$key]=$val;}
					break;
				case 'string':
					$val=(string)$val;
					if((string)$val!==$val || $val==' '){$c[]=$key;}else{$b[$key]=$val;}
					break;
				case 'date':
					if(empty($val)){$c[]=$key;}else{$b[$key]=strtotime($val);}
					break;
				case 'md5':
					if($val==' '){$c[]=$key;}else{$b[$key]=md5($val);}
					break;
				case 'login':
					$val=(string)$val;
					$re1='((?:[a-Zа-Я][a-zа-Я0-9_]*))';
					if ($preg=preg_match_all ("/".$re1."/is", $val, $matches))
					  {
						  $val=$matches[1][0];
					  }
					
					$login=$DB->QResult("SELECT `ID` FROM `g_users` WHERE `LOGIN`='".$val."'");
					if(!empty($login)){$c[]=$key;}else{$b[$key]=$val;}
					break;
				case 'email':
					$re1='((?:[a-z][a-z0-9_]*))(.)((?:[a-z][a-z]+))(.)((?:[a-z][a-z]+))';	# Word 2
					if ($preg=preg_match_all ("/".$re1."/is", $val, $matches))
					{
						$Email=$DB->QResult("SELECT `ID` FROM `g_users` WHERE `EMAIL`='".$val."'");
						if(!empty($Email)){$c[]=$key;}else{$b[$key]=$val;}
					}
					else{$c[]=$key;}
					break;	
				}
			}			
		}
		if($c[0]!='')
		{
		
			foreach($c as $key=>$val)
			{
				unset($b[$val]);
				if ($a[$val][1]==TRUE)
				{
					$d[]=$val;
				}
			}
		}
		if(empty($d[0])){return $b;}else{return $d;}}	

function Add($arv1,$arv2=FALSE,$uing=FALSE)
{
		global $DB;
		if(!empty($arv2))
		{
			foreach($arv2 as $val)
			{
				$req='';
				$req=$DB->QResult("SELECT `REQUIRED` FROM `g_user_fields` WHERE `ID`='".$val['FIELD_ID']."'");
				if(!empty($req))
				{
					if($req!=0 && empty($val['VALUE']))
					{
						return False;
					}
				}
			}
		}
		
		if($arv1['PASSWD']!=$arv1['PASSWD2'] && strlen($arv1['PASSWD'])<6)
		{
			return False;
		}
		
		$arv1['DATE_REGISTER']=date('Y-m-d H:m:s');
		$arv1=GTusers::Check($arv1); //d($arv1);
		if(!isset($arv1[0]) && isset($arv1['NAME']) && isset($arv1['LOGIN']) && isset($arv1['PASSWD']) && isset($arv1['EMAIL']))
		{
			$Value=$arv1;
			$VKeys = array_keys($Value);
			$sql = "INSERT INTO `g_users` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
			$res=$DB->Query($sql);
			$USER_ID=$DB->insertId();
		}
		
		if(!empty($arv2) && !empty($USER_ID))
		{	
			foreach($arv2 as $key=>$val)
			{
				$arv2[$key]['USER_ID']=$USER_ID;
			}
			foreach($arv2 as $key=>$val)
			{ 
				$R='';
				$R=$DB->QResult("SELECT `FIELD_ID` FROM `g_user_fields_values` WHERE `USER_ID`='$USER_ID' AND `FIELD_ID`='".$val['FIELD_ID']."'"); 
				
				if(empty($R))
				{	
					$var=array();
					$var=GTuserfields::Check($val);
					if(!isset($var[0]))
					{
						$Value=$var;
						$VKeys = array_keys($Value);
						$VARIEBL[]= "('".implode('\', \'',$Value)."')";
					}
				}
				else
				{
					$var=array();d($val);
					$var=GTuserfields::Check($val);
					$arVariables = GTuserfields::Check($arVariables);
					if(!isset($var[0]))
					{
						
						$s = "SELECT `ID` FROM `g_user_fields` WHERE `ID`='$var[FIELD_ID]' OR `FIELD_KEY`='$var[FIELD_KEY]' LIMIT 1";
					
						if(!($FIELD_ID = $DB->qResult($s)))
							return false;
						
						$SQLCH="SELECT COUNT(*) FROM `g_user_fields_values` WHERE `USER_ID`='".$USER_ID."' AND `FIELD_ID`='".$FIELD_ID."' LIMIT 1";
						if($DB->qResult($SQLCH))
						{
							$sql="UPDATE `g_user_fields_values` SET `VALUE`='".$var['VALUE']."', `VALUE_NUM`='".((int)$var['VALUE'])."' WHERE `USER_ID`='".$USER_ID."' AND `FIELD_ID`='".$FIELD_ID."'";
							$res=$DB->Query($sql);
							
						}
					}
				}
			}
			if($VARIEBL)
			{
			$Value=implode(',',$VARIEBL);
			$sql = "INSERT INTO `g_user_fields_values` (`".implode('`, `',$VKeys)."`) VALUES ".$Value."";
			$res=$DB->Query($sql);
			}
		}
		if(!empty($uing) && !empty($USER_ID))
		{
			$uing['USER_ID']=$USER_ID;
			GTuseringroups::AddUser($uing);
		}
		if(!empty($USER_ID))
		{
		GTfile::AddPostedFile('AVATAR', array('SUBSYSTEM'=>'GTINX::USERAVATAR','OWNER'=>$USER_ID,'TITLE'=>'AVATAR_'.$arv1['NAME']));
		return TRUE;}else{return FALSE;}
}

function Update($arv1,$arv2=FALSE,$uing=FALSE)
{
	//d($arv1);
	//die();
	global $DB;	
	if(!empty($arv2))
	{//d($arv2);
		foreach($arv2 as $val)
		{
			$req='';
			$req=$DB->QResult("SELECT `REQUIRED` FROM `g_user_fields` WHERE `ID`='".$val['FIELD_ID']."'");
			
			if(!empty($req))
			{ 
				if($req!=0 && empty($val['VALUE']))
				{
					return False;
				}
			}
		}
	}
	
	if(strlen($arv1['PASSWD'])>=6)
	{
		if($arv1['PASSWD']!=$arv1['PASSWD2'] && strlen($arv1['PASSWD'])<6)
		{
			return False;
		}
	}
	else
	{
		unset($arv1['PASSWD']);
		unset($arv1['PASSWD2']);
	}
	$ID=$arv1['ID'];
	$chs=GTusers::Get('*',Array('ID'=>$arv1['ID']));
	$chs=$chs[$arv1['ID']];
	foreach($chs as $key=>$val)
	{
		if($arv1[$key]==$val)
		{
			unset($arv1[$key]);
		}
	}
	$arv1= GTUsers::Check($arv1);
	if(!isset($arv1[0]))
	{
		$USER_ID=$ID;
		unset($arv1['ID']);
		foreach($arv1 as $keyss=>$arV)
		{
			if ($arV=='')
			{
				$arV=' ';
			}
			$K1[]="`".$keyss."`='".$arV."'";
		}
		$Value=$arv1;
		$sql="UPDATE `g_users` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'";//d($sql);
		$res=$DB->Query($sql);
	}else{return FALSE;}
	
	if(!empty($arv2) && !empty($USER_ID))
	{	
		foreach($arv2 as $key=>$val)
		{
			$arv2[$key]['USER_ID']=$USER_ID;
		}
		foreach($arv2 as $key=>$val)
		{ 
			$R='';
			$R=$DB->QResult("SELECT `FIELD_ID` FROM `g_user_fields_values` WHERE `USER_ID`='$USER_ID' AND `FIELD_ID`='".$val['FIELD_ID']."'"); 
			
			if(empty($R))
			{	
				$var=array();
				$var=GTuserfields::Check($val);
				if(!isset($var[0]))
				{
					$Value=$var;
					$VKeys = array_keys($Value);
					$VARIEBL[]= "('".implode('\', \'',$Value)."')";
				}
			}
			else
			{
				$var=array();
				$var=GTuserfields::Check($val);
				//$arVariables = GTuserfields::Check($arVariables);
				if(!isset($var[0]))
				{
					
					$s = "SELECT `ID` FROM `g_user_fields` WHERE `ID`='$var[FIELD_ID]' OR `FIELD_KEY`='$var[FIELD_KEY]' LIMIT 1";
				
					if(!($FIELD_ID = $DB->qResult($s)))
						return false;
					
					$SQLCH="SELECT COUNT(*) FROM `g_user_fields_values` WHERE `USER_ID`='".$USER_ID."' AND `FIELD_ID`='".$FIELD_ID."' LIMIT 1";
					if($DB->qResult($SQLCH))
					{
						$sql="UPDATE `g_user_fields_values` SET `VALUE`='".$var['VALUE']."', `VALUE_NUM`='".((int)$var['VALUE'])."' WHERE `USER_ID`='".$USER_ID."' AND `FIELD_ID`='".$FIELD_ID."'";
						$res=$DB->Query($sql);
						
					}
				}
			}
		}
		if($VARIEBL)
		{
		$Value=implode(',',$VARIEBL);
		$sql = "INSERT INTO `g_user_fields_values` (`".implode('`, `',$VKeys)."`) VALUES ".$Value.""; 
		$res=$DB->Query($sql);
		}
	}
	if(!empty($uing) && !empty($USER_ID))
	{
		$uing['USER_ID']=$USER_ID;
		GTuseringroups::UpdateUser($uing);
	}
	if(!empty($USER_ID))
	{
	GTfile::AddPostedFile('AVATAR', array('SUBSYSTEM'=>'GTINX::USERAVATAR','OWNER'=>$USER_ID,'TITLE'=>'AVATAR_'.$arv1['NAME']));
	return TRUE;}else{return FALSE;}		
}

function Delete($arVariables)
{
	global $DB;
	foreach($arVariables as $key=>$val)
	{
		$sql="Delete FROM `g_users` WHERE `ID`='".$val."'";
		$res=$DB->Query($sql);
		$FILE=GTFile::Get('*',array('OWNER'=>$val));
		foreach($FILE as $Key=>$Val)
		{
			GTfile::Delete($Val['ID'],TRUE);
		}
		if($res==TRUE)
		{
			$sql2="Delete FROM `g_usergroups` WHERE `USER_ID`='".$val."'";
			$res2=$DB->Query($sql2);
			$sql2="Delete FROM `g_user_fields_values` WHERE `USER_ID`='".$val."'";
			$res2=$DB->Query($sql2);
		}
	}
	if($res==TRUE){return TRUE;} else return FALSE;
}

function Get($t=FALSE,$arv=FALSE,$order=FALSE,$limit=FALSE, $FIVE=FALSE)
{
global $DB;
if($arv)
{
	foreach($arv as $key=>$val)
	if(!is_array($val))
	{
		if($val=='' || $val==' ')
		{
			unset($arv[$key]);
		}
	}
}
if ($limit)
{
	if(is_array($limit))
	{
		if(is_numeric($limit[0]) && is_numeric($limit[1]))
		{
			$limit[0]=(int)$limit[0];
			$limit[1]=(int)$limit[1];
			$LIMIT='LIMIT '.$limit[0].','.$limit[1];
		}
		else
		{
		unset($limit);
		}
	}
	elseif(is_numeric($limit))
	{
		$limit=(int)$limit;
		$LIMIT='LIMIT 0,'.$limit;
	}
}
$table['USER']=Array(
	'ID'=>'user.`ID`',
	'LOGIN'=>'user.`LOGIN`',
	'NAME'=>'user.`NAME`',
	'LAST_NAME'=>'user.`LAST_NAME`',
	'PATRONYMIC'=>'user.`PATRONYMIC`',
	'EMAIL'=>'user.`EMAIL`',
	'PASSWD'=>'user.`PASSWD`',
	'ACTIVE'=>'user.`ACTIVE`',
	'MODERAITED'=>'user.`MODERAITED`',
	'ACTIVE_FROM'=>'user.`ACTIVE_FROM`',
	'ACTIVE_TO'=>'user.`ACTIVE_TO`',
	'DATE_REGISTER'=>'user.`DATE_REGISTER`',
	'ORDER'=>'user.`ORDER`',
	'RATING'=>'user.`RATING`',
	'LASTLOGIN_TIME'=>'user.`LASTLOGIN_TIME`',
	'WEBSITE'=>'user.`WEBSITE`',
	'LASTLOGIN_IP'=>'user.`LASTLOGIN_IP`');
$table['GROUP']=Array(
	'GROUP_ID'=>'gr.`ID`',
	'GROUP_NAME'=>'gr.`NAME`',
	'GROUP_DESC'=>'gr.`DESC`',
	'GROUP_ACTIVE_FROM'=>'gr.`ACTIVE_FROM`',
	'GROUP_ACTIVE_TO'=>'gr.`ACTIVE_TO`',
	'GROUP_ACTIVE'=>'gr.`ACTIVE`',
	'GROUP_SESSION_LIFE'=>'gr.`SESSION_LIFE`',
	'GROUP_CREATED'=>'gr.`CREATED`',
	'GROUP_SU'=>'gr.`SU`',
	'GROUP_CP'=>'gr.`CP`',
	'GROUP_GROUP_KEY'=>'gr.`GROUP_KEY`');
$table['USER_GROUP']=Array(						
	'USER_GROUP_ID'=>'usgp.`ID`',
	'USER_GROUP_USER_ID'=>'usgp.`USER_ID`',
	'USER_GROUP_GROUP_ID'=>'usgp.`GROUP_ID`',
	'USER_GROUP_CREATED'=>'usgp.`CREATED`',
	'USER_GROUP_CREATOR'=>'usgp.`CREATOR`',
	'USER_GROUP_SU'=>'usgp.`SU`',
	'USER_GROUP_ACTIVE_FROM'=>'usgp.`ACTIVE_FROM`',
	'USER_GROUP_ACTIVE_TO'=>'usgp.`ACTIVE_TO`');
$table['FIELD']=Array(
	'FIELD_ID'=>'fld.`ID`',
	'FIELD_NAME'=>'fld.`NAME`',
	'FIELD_TYPE'=>'fld.`TYPE`',
	'FIELD_SORT'=>'fld.`SORT`',
	'FIELD_DEFAULT'=>'fld.`DEFAULT`',
	'FIELD_ACTIVE'=>'fld.`ACTIVE`',
	'FIELD_REQUIRED'=>'fld.`REQUIRED`',
	'FIELD_VALUES'=>'fld.`VALUES`',
	'FIELD_MULTIPLE'=>'fld.`MULTIPLE`',
	'FIELD_LENGTH'=>'fld.`LENGTH`',
	'FIELD_OPTIONS'=>'fld.`OPTIONS`',
	'FIELD_KEY'=>'fld.`FIELD_KEY`');
$table['FIELDS_VALUES']=Array(
	'USER_ID'=>'fv.`USER_ID`',
	'VALUE'=>'fv.`VALUE`',
	'VALUE_NUM'=>'fv.`VALUE_NUM`');
if($order)
{
	if(is_array($order))
	{
		if($table['GROUP'][$order[0]]){$ORD=' ORDER BY '.$table['GROUP'][$order[0]].' '.$order[1].' ';}
		elseif($table['USER'][$order[0]]){$ORD=' ORDER BY '.$table['USER'][$order[0]].' '.$order[1].' ';}
	}
	else
	{
		if($table['GROUP'][$order]){$ORD=' ORDER BY '.$table['GROUP'][$order].' ';}
	elseif($table['USER'][$order]){$ORD=' ORDER BY '.$table['USER'][$order].' ';}
	}	
}
$fives['GROUP']=Array('GROUP_ID'=>'GROUP_ID','GROUP_GROUP_KEY'=>'GROUP_GROUP_KEY');
$fives['USER']=Array('ID'=>'ID','LOGIN'=>'LOGIN','EMAIL'=>'EMAIL');
$fives['FIELDS']=Array('FIELDS'=>'VALUE');
if($FIVE)
	{	
		foreach($FIVE as $key=>$val)
		{
		if($fives['GROUP'][$val]){$F5['GROUP']=$fives['GROUP'][$val];}
		if($fives['USER'][$val]){$F5['USER']=$fives['USER'][$val];}
		if($fives['FIELDS'][$key]){$F5['FIELDS']=$val;}
		}
	}
if(is_array($t))
	{
	foreach ($t as $key=>$val)
		{
			foreach ($table as $KEY=>$VAL)
			{
				if($table[$KEY][$val])
				{
					if($KEY=='USER')
					{$tab[$KEY][]=$table[$KEY][$val];}
					else
					{$tab[$KEY][]=$table[$KEY][$val].'as '.$val;}
				}
			}
		}
	}
	
	if(($arv) && is_array($arv))
	{
		$notFoundInTables = array();
		foreach($arv as $key=>$val)
		{
			if(is_array($val))
			{
				foreach($table as $KEY=>$VAl)
				{
					switch($KEY)
						{
						case 'GROUP':
						$BY='BY2';
						break;
						case 'USER_GROUP':
						$BY='BY';
						break;
						case 'USER':
						$BY='BY';
						break;
						case 'FIELDS_VALUES':
						$BY='BY3';
						break;
						case 'FIELD':
						$BY='BY3';
						break;							
						}
					if($table[$KEY][$key])
					{
						$sql[$BY][]=$table[$KEY][$key]." IN (".implode(' , ',$val).")";
					}
				}			
			}
			else
			{
				foreach($table as $KEY=>$VAL)
				{
					if($table[$KEY][$key])
					{
						switch($KEY)
						{
						case 'GROUP':
						$BY='BY2';
						break;
						case 'USER_GROUP':
						$BY='BY';
						break;
						case 'USER':
						$BY='BY';
						break;
						case 'FIELDS_VALUES':
						$BY='BY3';
						break;
						case 'FIELD':
						$BY='BY3';
						break;							
						}
						$a=SWITCH_IT($val,$table[$KEY][$key]);
						if($a!=FALSE)
						{
						$sql[$BY][]=$a;
						}
						unset($BY);
					}
					else
						$notFoundInTables[$key]=$val;			
				}
			}
		}
		$PROPC = 0;
		foreach($notFoundInTables as $key => $VAL)
		{
			if(0!==strpos($key,'FIELD_')) continue;
			$VAL2=substr($key,6);
			if(is_numeric($VAL2))
			{
				$sql['BY'][]= "`fields`.`ID`='".$VAL2."'";
			}
			elseif($VAL2!='')
			{
				$sql['BY'][]= "\n`fields$PROPC`.`FIELD_KEY`='".$VAL2."' AND ".SWITCH_IT($VAL,"`fv$PROPC`.`VALUE`");
				$PROP .="
				LEFT JOIN `g_user_fields_values` as `fv$PROPC` ON (`fv$PROPC`.`USER_ID`=`user`.`ID`) 
				LEFT JOIN `g_user_fields` as `fields$PROPC` ON (`fv$PROPC`.`FIELD_ID`=`fields$PROPC`.`ID`)\n";
				$PROPC++;
			}
		}
	}
	
	foreach($table as $key=>$val)
	{
		if(isset($tab[$key]))
		{
			if(is_array($t))
			{
				switch($key)
				{
				case 'GROUP':
				$tabs=implode(',',$tab[$key]); $TABLES2[]=$tabs;
				break;
				case 'USER':
				$tabs=implode(',',$tab[$key]); $TABLES[]=$tabs;
				break;
				case 'USER_GROUP':
				$tabs=implode(',',$tab[$key]); $TABLES[]=$tabs;
				break;
				case 'FIELD':
				$tabs=implode(',',$tab[$key]); $TABLES3[]=$tabs;
				break;
				case 'FIELDS_VALUES':
				$tabs=implode(',',$tab[$key]); $TABLES3[]=$tabs;
				break;
				}
			}
			else{$tab=$t;}
		}
	}

		if(isset($TABLES)){
			$all='user.`ID`, '.implode(', ',$TABLES);
		}else{
$all="	user.`ID`, 
		user.`LOGIN`, 
		user.`NAME`, 
		user.`LAST_NAME`, 
		user.`PATRONYMIC`, 
		user.`EMAIL`, 
		user.`PASSWD`, 
		user.`ACTIVE`, 
		user.`MODERAITED`, 
		user.`ACTIVE_FROM`, 
		user.`ACTIVE_TO`, 
		user.`DATE_REGISTER`, 
		user.`ORDER`, 
		user.`RATING`, 
		user.`LASTLOGIN_TIME`, 
		user.`LASTLOGIN_IP`,
		user.`WEBSITE`,
		usgp.`ID` as USER_GROUP_ID, 
		usgp.`USER_ID` as USER_GROUP_USER_ID,  
		usgp.`GROUP_ID` as USER_GROUP_GROUP_ID, 
		usgp.`CREATED` as USER_GROUP_CREATED, 
		usgp.`CREATOR` as USER_GROUP_CREATOR, 
		usgp.`SU` as USER_GROUP_SU, 
		usgp.`ACTIVE_FROM` as USER_GROUP_ACTIVE_FROM, 
		usgp.`ACTIVE_TO` as USER_GROUP_ACTIVE_TO";
	}

	
	//if(!empty($sql['BYP'])){
	//	$sql['BY'][] = '('. implode(' OR ',$sql['BYP']) . ')';
	//}
	if($sql['BY'])	
		$BY=" WHERE ".implode("\n AND ",$sql['BY'])." ";
		
$from="`g_users` as user LEFT JOIN `g_usergroups` as usgp ON(usgp.`USER_ID`=user.`ID`)";
$SQL="SELECT ".$all." FROM ".$from." ".$PROP.$BY.$ORD.$LIMIT;	
//d($SQL);
$res = $DB->Query($SQL);
$ArrRes=array();
while($row=$DB->fetchAssoc($res))
{
	if(($F5['USER']) && $row[$F5['USER']]!='')
	{
		$ArrRes[$row[$F5['USER']]]=$row;
		$ArrMAP[$row['ID']]=$row[$F5['USER']];
		$ArrRes[$ArrMAP[$row['ID']]]['GROUPS']=array();
		$ArrRes[$ArrMAP[$row['ID']]]['FIELDS']=array();
	}
	else
	{
	$ArrRes[$row['ID']]=$row;
	$ArrMAP[$row['ID']]=$row['ID'];
	$ArrRes[$ArrMAP[$row['ID']]]['GROUPS']=array();
	$ArrRes[$ArrMAP[$row['ID']]]['FIELDS']=array();
	}
}
if(empty($ArrMAP)) return array();

	$user_id=array_keys($ArrMAP);
	if(isset($TABLES2))
	{
		$all2='gr.`ID` as GROUP_ID, '.implode(', ',$TABLES);
	}
	else
	{
		$all2="gr.`ID` as GROUP_ID, 
		gr.`NAME` as GROUP_NAME, 
		gr.`DESC` as GROUP_DESC, 
		gr.`ACTIVE_FROM` as GROUP_ACTIVE_FROM, 
		gr.`ACTIVE_TO` as GROUP_ACTIVE_TO, 
		gr.`ACTIVE` as GROUP_ACTIVE, 
		gr.`SESSION_LIFE` as GROUP_SESSION_LIFE, 
		gr.`CREATED` as GROUP_CREATED,
		gr.`SU` as GROUP_SU,
		gr.`CP` as GROUP_CP,
		gr.`GROUP_KEY` as GROUP_GROUP_KEY,
		usgp.`ID` as USER_GROUP_ID, 
		usgp.`ACTIVE_FROM` as USER_GROUP_ACTIVE_FROM, 
		usgp.`ACTIVE_TO` as USER_GROUP_ACTIVE_TO, 
		usgp.`USER_ID` as USER_GROUP_USER_ID, 
		usgp.`CREATED` as USER_GROUP_CREATED, 
		usgp.`GROUP_ID` as USER_GROUP_GROUP_ID";
	}
	if($sql['BY2'])
	{
		$BY2=" WHERE ".implode(' AND ',$sql['BY2'])." AND usgp.`USER_ID` IN (".implode(' , ',$user_id).")";
	}
	else
	{
		$BY2=" WHERE usgp.`USER_ID` IN (".implode(' , ',$user_id).")";
	}
$from2="`g_groups` as gr LEFT JOIN `g_usergroups` as usgp ON(usgp.`GROUP_ID`=gr.`ID`)";
$SQL2="SELECT ".$all2." FROM ".$from2." ".$BY2;

$res2 = $DB->Query($SQL2);
while($row2=$DB->fetchAssoc($res2))
{			
		if(isset($ArrMAP[$row2['USER_GROUP_USER_ID']]))
		{
			if(($F5['GROUP']) && $row2[$F5['GROUP']]!='')
			{$ArrRes[$ArrMAP[$row2['USER_GROUP_USER_ID']]]['GROUPS'][$row2[$F5['GROUP']]]=$row2;}
			else
			{$ArrRes[$ArrMAP[$row2['USER_GROUP_USER_ID']]]['GROUPS'][]=$row2;}
		}
}
	
if(isset($TABLES3))
{
$all3='fld.`ID` as FIELD_ID, '.implode(', ',$TABLES);
}
else
{
	$all3="fld.`ID` as FIELD_ID, 
	fld.`NAME` as FIELD_NAME, 
	fld.`TYPE` as FIELD_TYPE, 
	fld.`SORT` as FIELD_SORT, 
	fld.`DEFAULT` as `DEFAULT`, 
	fld.`ACTIVE` as FIELD_ACTIVE, 
	fld.`REQUIRED` as `REQUIRED`, 
	fld.`VALUES` as `VALUES`, 
	fld.`MULTIPLE` as `MULTIPLE`, 
	fld.`LENGTH` as `LENGTH`, 
	fld.`OPTIONS` as `OPTIONS`, 
	fld.`FIELD_KEY` as FIELD_KEY, 
	fv.`USER_ID` as USER_ID, 
	fv.`VALUE` as VALUE, 
	fv.`VALUE_NUM` as VALUE_NUM";
}
	if($sql['BY3'])
	{
		$BY3=" WHERE ".implode(' AND ',$sql['BY3'])." AND fv.`USER_ID` IN (".implode(' , ',$user_id).")";
	}
	else
	{
		$BY3=" WHERE fv.`USER_ID` IN (".implode(' , ',$user_id).")";
	}
$from3="`g_user_fields` fld LEFT JOIN `g_user_fields_values` as fv ON(fv.`FIELD_ID`=fld.`ID`)";
$SQL3="SELECT ".$all3." FROM ".$from3." ".$BY3;
$res3 = $DB->Query($SQL3);
	
	while($row3=$DB->fetchAssoc($res3))
	{		
		if($row3['FIELD_ACTIVE']==1)
		{
			$row3['FIELD_KEY']=trim($row3['FIELD_KEY']);
			if($row3['VALUE']=='' || $row3['VALUE']==' '){$row3['VALUE']=$row3['FIELD_DEFAULT'];}
			if(($ArrMAP[$row3['USER_ID']]) || $ArrMAP[$row3['USER_ID']]===0)
			{
				if(($F5['FIELDS']) && $row3['FIELD_KEY']!='')
				{
				
					if(($row3['FIELD_KEY']==$F5['FIELDS']) && $row3['VALUE']!='')
					{
						$Second=$row3['VALUE'];
						$L=$ArrRes[$ArrMAP[$row3['USER_ID']]];
						unset($ArrRes[$ArrMAP[$row3['USER_ID']]]);
						$ArrRes[$Second]=$L;
						$ArrRes[$Second]['FIELDS'][$row3['FIELD_KEY']]=$row3;
					}
					elseif($Second)
					{
						$ArrRes[$Second]['FIELDS'][$row3['FIELD_KEY']]=$row3;
					}
					else
					{
						$ArrRes[$ArrMAP[$row3['USER_ID']]]['FIELDS'][$row3['FIELD_KEY']]=$row3;
					}
				}
				else
				{
					if(!empty($row3['FIELD_KEY']))
					{
						$ArrRes[$ArrMAP[$row3['USER_ID']]]['FIELDS'][$row3['FIELD_KEY']]=$row3;
					}
					else
					{
						$ArrRes[$ArrMAP[$row3['USER_ID']]]['FIELDS'][$row3['FIELD_ID']]=$row3;
					}
				}
				if($row3['FIELD_TYPE']=='LINK' && !empty($row3['VALUE']) && is_numeric($row3['VALUE']))
				{	
					if(!empty($row3['VALUE'])){$DIM[]=$row3['VALUE'];}
					if(!empty($row3['FIELD_KEY']))
					{
					$arMap[$row3['VALUE']][]=Array('USER_ID'=>$ArrMAP[$row3['USER_ID']],'FIELD_KEY'=>$row3['FIELD_KEY']);
					}
					else
					{
					$arMap[$row3['VALUE']][]=Array('USER_ID'=>$ArrMAP[$row3['USER_ID']],'FIELD_KEY'=>$row3['FIELD_ID']);
					}
				}
			}
		}
	}
	
	if(isset($DIM))
	{
		$DIM=array_unique($DIM);
		$row3=GTdblock::Get('TITLE',Array('ID'=>$DIM));
		foreach($row3 as $key=>$val)
		{	
			foreach($arMap[$val['ID']] as $KEY=>$VAL)
				{	
					$ArrRes[$VAL['USER_ID']]['LINK_'.$VAL['FIELD_KEY']]=$val;
					$ArrRes[$VAL['USER_ID']]['FIELDS'][$VAL['FIELD_KEY']]['VALUE']=$val;
				}
		}
	}
	return $ArrRes;
}

function LINK($TXT)
{
	$txt=(string)$TXT;
	$txt = explode("\n", $txt);
	$re1='((?:[a-z][a-z0-9_]*)).*?(\\d+)';	# Integer Number 1
	$r=array('ACTIVE'=>1);
	foreach($txt as $Dkey=>$DVal)
	{
		if ($c=preg_match_all ("/".$re1."/is", $DVal, $matches))
		{
		  $var1=$matches[1][0];
		  $int1=$matches[2][0];
		 $r=array_merge($r,array($var1=>$int1));
		}
	}
	if(!empty($r))
	{
		$R=GTdblock::GET(Array('ID','TITLE'),$r,'TITLE','',Array('ID'));
		$S=array(GetMessage('NOT_CHOOSEN')=>'0');
		foreach($R as $RKey=>$RVal)
		{
		$S=array_merge($S,array($RVal['TITLE']=>$RVal['ID']));
		}
		$S=array_flip($S); 
	}
	return $S;
}
}
?>
