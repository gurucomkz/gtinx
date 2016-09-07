<?
class GTgroupsusers
{
	function Check($b=array())
	{
		foreach ($b as $key=>$val)
		{
			
			if($key=='DESC')
			{ if($b['edswitch'.$key]=='ht')
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
			else{
			$b[$key]=stripslashes(trim($b[$key]));
				}
		}
		foreach ($b as $key=>$val) if($val==''){unset($b[$key]);}
		global $DB;
			$a=array(
				'ID'=>array('int',TRUE),
				'NAME'=>array('string',TRUE),
				'GROUP_KEY'=>array('key',FALSE),
				'ACTIVE'=>array('boolean',FALSE),
				'ACTIVE_FROM'=>array('date',FALSE),
				'ACTIVE_TO'=>array('date',FALSE),
				'DESC'=>array('string',FALSE),
				'SU'=>array('boolean',FALSE),
				'CP'=>array('boolean',FALSE),
				'CREATED'=>array('date',TRUE),
				'UPDATED'=>array('date',FALSE),
				'SESSION_LIFE'=>array('int',FALSE));
			
			
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
						if($val==' '){$c[]=$key;}else{$b[$key]=strtotime($val);}
						break;
					case 'md5':
						if($val==' '){$c[]=$key;}else{$b[$key]=md5($val);}
						break;
					case 'key':
						$res=$DB->Query("SELECT `ID` FROM `g_groups` WHERE `GROUP_KEY`='".$val."'");
						$row=$DB->fetchAssoc($res);
						if($row['ID']==NULL)
						{
						$val=(string)$val;
						if((string)$val!==$val || $val==' '){$c[]=$key;}else{$b[$key]=$val;}
						}else{$c[]=$key;}
						break;
					}
				
				}			
			}
				if($c[0]!='')
				{
					foreach($c as $key=>$val)
					{
					unset($b[$val]);
					if ($a[$val][1]==TRUE){$d[]=$val;}
					}
				}
				if(empty($d[0])){return $b;}else{return $d;}
		
	}
	
function Add($arVariables)
	{
		global $DB;
			$arVariables['CREATED']=date('Y-m-d H:m:s');
			$arVariables= GTgroupsusers::Check($arVariables);
			if(!isset($arVariables[0]) && isset($arVariables['NAME']))
			{
			$Value=$arVariables;
			$VKeys = array_keys($Value);
			$sql = "INSERT INTO `g_groups` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
			$res=$DB->Query($sql);
			if($res==TRUE){return TRUE;}else{return FALSE;}
			}else{return FALSE;}
	}
	
function Update($arVariables)
	{
	global $DB;	
			$arVariables['UPDATED']=date('Y-m-d H:m:s');
			$arVariables= GTgroupsusers::Check($arVariables);
			if(!isset($arVariables[0]) && isset($arVariables['ID']))
			{
			$ID=$arVariables['ID'];
			unset($arVariables['ID']);
				foreach($arVariables as $keyss=>$arV)
				{
				if ($arV==''){$arV=' ';}
				$K1[]="`".$keyss."`='".$arV."'";
				}
				
				$Value=$arVariables;
				$sql="UPDATE `g_groups` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'";
				$res=$DB->Query($sql);
				if($res==TRUE)
				{return TRUE;}else{return FALSE;}
			}else{return FALSE;}
	}
function Delete($arVariables)
	{
		global $DB;
		foreach($arVariables as $key=>$val)
		{
		$sql="Delete FROM `g_groups` WHERE `ID`='".$val."'";
		$res=$DB->Query($sql);
			if($res==TRUE)
			{
			$sql2="Delete FROM `g_usergroups` WHERE `GROUP_ID`='".$val."'";
			$res2=$DB->Query($sql2);
			}
		}
		if($res==TRUE){return TRUE;} else return FALSE;
	}

function Get($t=FALSE,$arv=FALSE,$order=FALSE,$limit=FALSE, $FIVE=FALSE)
	{
		global $DB;
		if($arv){
		foreach($arv as $key=>$val)
			if(!is_array($val)){if($val=='' || $val==' '){unset($arv[$key]);}}
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
								'USER_ID'=>'user.`ID`',
								'USER_LOGIN'=>'user.`LOGIN`',
								'USER_NAME'=>'user.`NAME`',
								'USER_LAST_NAME'=>'user.`LAST_NAME`',
								'USER_PATRONYMIC'=>'user.`PATRONYMIC`',
								'USER_EMAIL'=>'user.`EMAIL`',
								'USER_PASSWD'=>'user.`PASSWD`',
								'USER_ACTIVE'=>'user.`ACTIVE`',
								'USER_ACTIVE_FROM'=>'user.`ACTIVE_FROM`',
								'USER_ACTIVE_TO'=>'user.`ACTIVE_TO`',
								'USER_DATE_REGISTER'=>'user.`DATE_REGISTER`',
								'USER_ORDER'=>'user.`ORDER`',
								'USER_RATING'=>'user.`RATING`',
								'USER_LASTLOGIN_TIME'=>'user.`LASTLOGIN_TIME`',
								'USER_LASTLOGIN_IP'=>'user.`LASTLOGIN_IP`');
		$table['GROUP']=Array(
								'ID'=>'gr.`ID`',
								'NAME'=>'gr.`NAME`',
								'DESC'=>'gr.`DESC`',
								'ACTIVE_FROM'=>'gr.`ACTIVE_FROM`',
								'ACTIVE_TO'=>'gr.`ACTIVE_TO`',
								'ACTIVE'=>'gr.`ACTIVE`',
								'SESSION_LIFE'=>'gr.`SESSION_LIFE`',
								'CREATED'=>'gr.`CREATED`',
								'SU'=>'gr.`SU`',
								'CP'=>'gr.`CP`',
								'GROUP_KEY'=>'gr.`GROUP_KEY`');
		$table['USERGROUP']=Array(
								
								'USERGROUP_ID'=>'usgp.`ID`',
								'USERGROUP_USER_ID'=>'usgp.`USER_ID`',
								'USERGROUP_LOGIN'=>'usgp.`LOGIN`',
								'USERGROUP_GROUP_ID'=>'usgp.`GROUP_ID`',
								'USERGROUP_CREATED'=>'usgp.`CREATED`',
								'USERGROUP_CREATOR'=>'usgp.`CREATOR`',
								'USERGROUP_SU'=>'usgp.`SU`',
								'USERGROUP_ACTIVE_FROM'=>'usgp.`ACTIVE_FROM`',
								'USERGROUP_ACTIVE_TO'=>'usgp.`ACTIVE_TO`');
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
								'FIELDS_VALUES_USER_ID'=>'fv.`USER_ID`',
								'FIELDS_VALUES_FIELD_ID'=>'fv.`FIELD_ID`',
								'VALUE'=>'fv.`VALUE`',
								'VALUE_NUM'=>'fv.`VALUE_NUM`',
								'FIELDS_VALUES_LOGIN'=>'fv.`LOGIN`',
								'FIELDS_VALUES_FIELD_KEY'=>'fv.`FIELD_KEY`');
		$fives['GROUP']=Array('ID'=>'ID','GROUP_KEY'=>'GROUP_KEY');
		$fives['USER']=Array('USER_ID'=>'USER_ID','USER_LOGIN'=>'USER_LOGIN','USER_EMAIL'=>'USER_EMAIL');
		$fives['FIELDS']=Array('FIELDS'=>'VALUE');
		if($order)
		{
			if($table['GROUP'][$order]){$ORD='\nORDER BY '.$table['GROUP'][$order].' ';}			
		}
		
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
						if($KEY=='GROUP')
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
							$BY='BY';
							break;
							case 'USER_GROUP':
							$BY='BY2';
							break;
							case 'USER':
							$BY='BY2';
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
							$BY='BY';
							break;
							case 'USER_GROUP':
							$BY='BY2';
							break;
							case 'USER':
							$BY='BY2';
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
							$notFoundInTables[$key]=$VAL;			
					}
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
					$tabs=implode(',',$tab[$key]); $TABLES[]=$tabs;
					break;
					case 'USER':
					$tabs=implode(',',$tab[$key]); $TABLES2[]=$tabs;
					break;
					case 'USER_GROUP':
					$tabs=implode(',',$tab[$key]); $TABLES2[]=$tabs;
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
		
		
		
		if(isset($TABLES)){$all='gr.`ID`, '.implode(', ',$TABLES);}else{
		$all="	gr.`ID`,
				gr.`NAME`,
				gr.`DESC`,
				gr.`ACTIVE_FROM`,
				gr.`ACTIVE_TO`,
				gr.`ACTIVE`,
				gr.`SESSION_LIFE`,
				gr.`CREATED`,
				gr.`SU`,
				gr.`CP`,
				gr.`GROUP_KEY`";}
		if($sql['BY'])
		{$BY=" WHERE ".implode(' AND ',$sql['BY'])." ";}
		$from="`g_groups` as gr";
		$SQL="SELECT ".$all." FROM ".$from." ".$BY.$ORD.$LIMIT;	
		$res = $DB->Query($SQL);
		$ArrRes=array();
		while($row=$DB->fetchAssoc($res))
		{
			if(($F5['GROUP']) && $row[$F5['GROUP']]!='')
			{
				$ArrRes[$row[$F5['GROUP']]]=$row;
				$ArrMAP[$row['ID']]=$row[$F5['GROUP']];
				$ArrRes[$ArrMAP[$row['ID']]]['USERS']=array();
			}
			else
			{
			$ArrRes[]=$row;
			$ArrMAP[$row['ID']]=count($ArrRes)-1;
			$ArrRes[$ArrMAP[$row['ID']]]['USERS']=array();
			}
		}
		
		$user_id=array_keys($ArrMAP);
		if(isset($TABLES2)){$all2='user.`ID` as USER_ID, '.implode(', ',$TABLES);}else{
		$all2="	user.`ID` as USER_ID,
				user.`LOGIN` as USER_LOGIN,
				user.`NAME` as USER_NAME,
				user.`LAST_NAME` as USER_LAST_NAME,
				user.`PATRONYMIC` as USER_PATRONYMIC,
				user.`EMAIL` as USER_EMAIL,
				user.`PASSWD` as USER_PASSWD,
				user.`ACTIVE` as USER_ACTIVE,
				user.`ACTIVE_FROM` as USER_ACTIVE_FROM,
				user.`ACTIVE_TO` as USER_ACTIVE_TO,
				user.`DATE_REGISTER` as USER_DATE_REGISTER,
				user.`ORDER` as USER_ORDER,
				user.`RATING` as USER_RATING,
				user.`LASTLOGIN_TIME` as USER_LASTLOGIN_TIME,
				user.`LASTLOGIN_IP` as USER_LASTLOGIN_IP,
				usgp.`ID` as USERGROUP_ID, 
				usgp.`USER_ID` as USERGROUP_USER_ID, 
				usgp.`GROUP_ID` as USERGROUP_GROUP_ID, 
				usgp.`CREATED` as USERGROUP_CREATED, 
				usgp.`CREATOR` as USERGROUP_CREATOR, 
				usgp.`SU` as USERGROUP_SU, 
				usgp.`ACTIVE_FROM` as USERGROUP_ACTIVE_FROM, 
				usgp.`ACTIVE_TO` as USERGROUP_ACTIVE_TO";}
		if($sql['BY2'])
		{$BY2=" WHERE ".implode(' AND ',$sql['BY2'])." AND usgp.`GROUP_ID` IN (".implode(' , ',$user_id).")";}
		else
		{
		$BY2=" WHERE usgp.`GROUP_ID` IN (".implode(' , ',$user_id).")";
		}
		$from2="`g_users` as user LEFT JOIN `g_usergroups` as usgp ON(usgp.`USER_ID`=user.`ID`)";
		$SQL2="SELECT ".$all2." FROM ".$from2." ".$BY2;
		$res2 = $DB->Query($SQL2);
		while($row2=$DB->fetchAssoc($res2))
		{			
				if(($ArrMAP[$row2['USERGROUP_GROUP_ID']]) || $ArrMAP[$row2['USERGROUP_GROUP_ID']]===0)
				{
					if(($F5['USER']) && $row2[$F5['USER']]!='')
					{
					$ArrRes[$ArrMAP[$row2['USERGROUP_GROUP_ID']]]['USERS'][$row2[$F5['USER']]]=$row2;
					$A[$row2['USER_ID']]=$row2[$F5['USER']];
					$ArrRes[$ArrMAP[$row2['USERGROUP_GROUP_ID']]]['USERS'][$A[$row2['USER_ID']]]['FIELDS']=array();
					}
					else
					{
					$ArrRes[$ArrMAP[$row2['USERGROUP_GROUP_ID']]]['USERS'][]=$row2;	
					$A[$row2['USER_ID']]= count($ArrRes[$ArrMAP[$row2['USERGROUP_GROUP_ID']]]['USERS'])-1;
					$ArrRes[$ArrMAP[$row2['USERGROUP_GROUP_ID']]]['USERS'][$A[$row2['USER_ID']]]['FIELDS']=array();
					}
				}
		}
		
		$user_id=array_keys($A);
		if(isset($TABLES3)){$all3='fld.`ID` as FIELD_ID,  '.implode(', ',$TABLES);}else{
		$all3="fld.`ID` as FIELD_ID, 
				fld.`NAME` as FIELD_NAME, 
				fld.`TYPE` as FIELD_TYPE, 
				fld.`SORT` as FIELD_SORT, 
				fld.`DEFAULT` as FIELD_DEFAULT, 
				fld.`ACTIVE` as FIELD_ACTIVE, 
				fld.`REQUIRED` as FIELD_REQUIRED, 
				fld.`VALUES` as FIELD_VALUES, 
				fld.`MULTIPLE` as FIELD_MULTIPLE, 
				fld.`LENGTH` as FIELD_LENGTH, 
				fld.`OPTIONS` as FIELD_OPTIONS, 
				fld.`FIELD_KEY` as FIELD_KEY, 
				fv.`USER_ID` as FIELDS_VALUES_USER_ID, 
				
				fv.`VALUE` as VALUE, 
				fv.`VALUE_NUM` as VALUE_NUM";}
		if($sql['BY3'])
		{$BY3=" WHERE ".implode(' AND ',$sql['BY3'])." AND fv.`USER_ID` IN (".implode(' , ',$user_id).")";}
		else
		{
		$BY3=" WHERE fv.`USER_ID` IN (".implode(' , ',$user_id).")";
		}
		$from3="`g_user_fields` fld LEFT JOIN `g_user_fields_values` as fv ON(fv.`FIELD_ID`=fld.`ID`)";
		$SQL3="SELECT ".$all3." FROM ".$from3." ".$BY3;
		$res3 = $DB->Query($SQL3);
		foreach($ArrRes as $key=>$val)
				{
				foreach ($val['USERS'] as $Key=>$Val)
					{
					$ch[$Val['USER_ID']]=array('MAIN'=>$key,'USER'=>$Key,'ID'=>$Val['USER_ID']);
					$SS[$Key]=$Val['USER_ID'];
					}
				}
		while($row3=$DB->fetchAssoc($res3))
		{		
				
				if($ch[$row3['FIELDS_VALUES_USER_ID']]['ID']==$row3['FIELDS_VALUES_USER_ID'])
				{
					if(($F5['FIELDS']) && $row3['FIELD_KEY']!='')
					{
						if($row3['FIELD_KEY']==$F5['FIELDS'] && $row3['VALUE']!=''){
						$Second=$row3['VALUE'];
						$L=$ArrRes[$ch[$row3['FIELDS_VALUES_USER_ID']]['MAIN']]['USERS'][$ch[$row3['FIELDS_VALUES_USER_ID']]['USER']];
						unset($ArrRes[$ch[$row3['FIELDS_VALUES_USER_ID']]['MAIN']]['USERS'][$ch[$row3['FIELDS_VALUES_USER_ID']]['USER']]);
						$ArrRes[$ch[$row3['FIELDS_VALUES_USER_ID']]['MAIN']]['USERS'][$row3['VALUE']]=$L;
						$ArrRes[$ch[$row3['FIELDS_VALUES_USER_ID']]['MAIN']]['USERS'][$row3['VALUE']]['FIELDS'][$row3['FIELD_KEY']]=$row3;}
						elseif($Second){
							$ArrRes[$ch[$row3['FIELDS_VALUES_USER_ID']]['MAIN']]['USERS'][$Second]['FIELDS'][$row3['FIELD_KEY']]=$row3;
						}
						else
						{
							$ArrRes[$ch[$row3['FIELDS_VALUES_USER_ID']]['MAIN']]['USERS'][$ch[$row3['FIELDS_VALUES_USER_ID']]['USER']]['FIELDS'][$row3['FIELD_KEY']]=$row3;
						}
					}
					elseif($row3['FIELD_KEY']!='')
					{
						$ArrRes[$ch[$row3['FIELDS_VALUES_USER_ID']]['MAIN']]['USERS'][$ch[$row3['FIELDS_VALUES_USER_ID']]['USER']]['FIELDS'][$row3['FIELD_KEY']]=$row3;
					}
					else
					{
						$ArrRes[$ch[$row3['FIELDS_VALUES_USER_ID']]['MAIN']]['USERS'][$ch[$row3['FIELDS_VALUES_USER_ID']]['USER']]['FIELDS'][]=$row3;
					}
				}	
		}
		return $ArrRes;
	}
}
?>