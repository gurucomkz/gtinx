<?
class GTuserfields
{
	function AddFields($arVariables)
	{	
		global $DB;
			$arVariables= GTuserfields::Check($arVariables);
			if(!isset($arVariables[0]) && isset($arVariables['NAME']))
			{
			$Value=$arVariables;
			$VKeys = array_keys($Value);
			$sql = "INSERT INTO `g_user_fields` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
			$res=$DB->Query($sql);
			if($res==TRUE){return TRUE;}else{return 'SQL';}
			}else{return FALSE;}
	}
	
	function UpdateFields($arVariables)
	{
		global $DB;	
		$ID=$arVariables['ID'];
		$r=GTuserfields::Get('',Array('ID'=>$ID));
		$r=$r[0];
		foreach($r as $key=>$val)
		{
			if($arVariables[$key]==$val)
			{
				unset($arVariables[$key]);
			}
		}
		$arVariables= GTuserfields::Check($arVariables);
		if(!isset($arVariables[0]) && count($arVariables)>=1)
		{
			foreach($arVariables as $keyss=>$arV)
			{
			if ($arV==''){$arV=' ';}
			$K1[]="`".$keyss."`='".$arV."'";
			}
			$sql="UPDATE `g_user_fields` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'";
			$res=$DB->Query($sql);
			if($res==TRUE)
			{return TRUE;}else{return FALSE;}
		}else{return FALSE;}
	}
	
	function AddFieldValue($arVar)
	{
		global $DB;
		foreach($arVar as $val)
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
			$req2=$DB->QResult("SELECT `ID` FROM `g_user_fields` WHERE `ID`='".$val['FIELD_ID']."'");
			if(!empty($req2))
			{
				GTuserfields::UpdateFieldValue($val);
				return true;
			}
		}
		
		foreach($arVar as $key=>$val)
		{
			
			//$arVariables=$arVar;
			if(is_array($val))
			{
				foreach($val as $kkey=>$vval)
				{
					if($vval!=FALSE) 
					{$arVariables[$kkey]=htmlspecialchars(trim((string)$vval));}
				}
			}
			$arVariables=GTuserfields::Check($arVariables);
			if(!isset($arVariables[0]))
			{
			$Value=$arVariables;
			$VKeys = array_keys($Value);
			$sql = "INSERT INTO `g_user_fields_values` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')"; 
			$res=$DB->Query($sql);
			}
		}
	}
	
	function UpdateFieldValue($arVariables)
	{
		global $DB;	
		$arVariables = GTuserfields::Check($arVariables);
		if(!isset($arVariables[0]))
		{
			$USER_ID=$arVariables['USER_ID'];
			//accept BOTH ID and KEY!
			$s = "SELECT `ID` FROM `g_user_fields` WHERE `ID`='$arVariables[FIELD_ID]' OR `FIELD_KEY`='$arVariables[FIELD_KEY]' LIMIT 1";
		//	d($s);
			if(!($FIELD_ID = $DB->qResult($s))) //check whether this field actually exists!!!!!! 
				return false;
			
			$SQLCH="SELECT COUNT(*) FROM `g_user_fields_values` WHERE `USER_ID`='".$USER_ID."' AND `FIELD_ID`='".$FIELD_ID."' LIMIT 1";
			if($DB->qResult($SQLCH))
			{
				$sql="UPDATE `g_user_fields_values` SET `VALUE`='".$arVariables['VALUE']."', `VALUE_NUM`='".((int)$arVariables['VALUE'])."' WHERE `USER_ID`='".$USER_ID."' AND `FIELD_ID`='".$FIELD_ID."'";
				$res=$DB->Query($sql);
				return $res==TRUE; 
			}
			else
			{
				$FLD[]=Array('USER_ID'=>$USER_ID,'FIELD_ID'=>$FIELD_ID,'VALUE'=>$arVariables['VALUE']);
				GTuserfields::AddFieldValue($FLD);
			}
			
		}
		return FALSE;
	}
	
	function DeleteFields($arVariables)
	{
	global $DB;
	foreach($arVariables as $key=>$val)
		{
		$sql="Delete FROM `g_user_fields` WHERE `ID`='".$val."'";
		$res=$DB->Query($sql);
			if($res==TRUE)
			{
			$sql2="Delete FROM `g_user_fields_values` WHERE `FIELD_ID`='".$val."'";
			$res2=$DB->Query($sql2);
			}
		}
		if($res==TRUE){return TRUE;}else{return FALSE;}
	}
	
	function DeleteFieldValue($arVariables)
	{
	global $DB;
	$sql2="Delete FROM `g_user_fields_values` WHERE `FIELD_ID`='".$arVariables['FIELD_ID']."' AND `USER_ID`='".$arVariables['USER_ID']."'";
	$res2=$DB->Query($sql2);
	}
	
	function Check($b=array())
	{
			global $DB;
			$a=array(
				'ID'=>array('int',FALSE),
				'NAME'=>array('string',TRUE),
				'TYPE'=>array('string',TRUE),
				'SORT'=>array('int',FALSE),
				'DEFAULT'=>array('string',FALSE),
				'ACTIVE'=>array('boolean',FALSE),
				'REQUIRED'=>array('boolean',FALSE),
				'VALUES'=>array('string',FALSE),
				'USER_EDITABLE'=>array('boolean',FALSE),
				'MULTIPLE'=>array('boolean',FALSE),
				'LENGTH'=>array('int',FALSE),
				'OPTIONS'=>array('string',FALSE),
				'USER_ID'=>array('int',TRUE),				
				'FIELD_ID'=>array('int',TRUE),				
				'VALUE'=>array('value',TRUE),				
				'VALUE_NUM'=>array('int',TRUE),				
				'FIELD_KEY'=>array('value',FALSE),				
				);
			
			$c=array();
			foreach($b as $key=>$val)
			{
			
			if(!isset($a[$key])) {$c[]=$key;}
			else{
					switch($a[$key][0])
					{
					case 'value':
						if(is_numeric($val) && $val!=0)
						{$b['VALUE_NUM']=(int)$val;}
					break;
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
						$val=trim($val);
						$val=(string)$val;
						if((string)$val!==$val){$c[]=$key;}else{$b[$key]=$val;}
						break;
					case 'date':
						if($val==' '){$c[]=$key;}else{$b[$key]=strtotime($val);}
						break;
					case 'md5':
						if($val==' '){$c[]=$key;}else{$b[$key]=md5($val);}
						break;
					case 'login':
						$val=(string)$val;
						$re1='((?:[a-z][a-z0-9_]*))';
						if ($preg=preg_match_all ("/".$re1."/is", $val, $matches))
						  {
							  $val=$matches[1][0];
						  }
						
						$login=$DB->Query("SELECT `ID` FROM `g_users` WHERE `LOGIN`='".$val."'");
						$lrow=$DB->fetchAssoc($login);
						if(!empty($lrow['ID'])){$c[]=$key;}else{$b[$key]=$val;}
						break;
					case 'email':
						$re1='((?:[a-z][a-z0-9_]*))(.)((?:[a-z][a-z]+))(.)((?:[a-z][a-z]+))';	# Word 2
						if ($preg=preg_match_all ("/".$re1."/is", $val, $matches))
						{
							$Email=$DB->Query("SELECT `ID` FROM `g_users` WHERE `EMAIL`='".$val."'");
							$Erow=$DB->fetchAssoc($Email);
							if(!empty($Erow['ID'])){$c[]=$key;}else{$b[$key]=$val;}
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
					if ($a[$val][1]==TRUE){$d[]=$val;}
					}
				}
				
				if(empty($d[0])){return $b;}else{return $d;}
		
	}
	function Get($t=FALSE,$arv=FALSE,$order=FALSE,$limit=FALSE, $FIVE=FALSE)
	{
		if($arv)
		{
			foreach($arv as $key=>$val)
			if(!is_array($val)){if($val=='' || $val==' '){unset($arv[$key]);}}
		}
		global $DB;
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
		
		$table['FIELD']=Array(
								'ID'=>'fld.`ID`',
								'NAME'=>'fld.`NAME`',
								'TYPE'=>'fld.`TYPE`',
								'SORT'=>'fld.`SORT`',
								'DEFAULT'=>'fld.`DEFAULT`',
								'ACTIVE'=>'fld.`ACTIVE`',
								'REQUIRED'=>'fld.`REQUIRED`',
								'VALUES'=>'fld.`VALUES`',
								'MULTIPLE'=>'fld.`MULTIPLE`',
								'USER_EDITABLE'=>'fld.`USER_EDITABLE`',
								'LENGTH'=>'fld.`LENGTH`',
								'OPTIONS'=>'fld.`OPTIONS`',
								'FIELD_KEY'=>'fld.`FIELD_KEY`');
		$table['FIELDS_VALUES']=Array(
								'USER_ID'=>'fv.`USER_ID`',
								'FIELD_ID'=>'fv.`FIELD_ID`',
								'VALUE'=>'fv.`VALUE`',
								'VALUE_NUM'=>'fv.`VALUE_NUM`',
								'LOGIN'=>'fv.`LOGIN`',
								'FV_FIELD_KEY'=>'fv.`FIELD_KEY`');
								
		if($order)
		{
			if($table['FIELD'][$order]){$ORD=' ORDER BY '.$table['FIELD'][$order].' ';}
			elseif($table['FIELDS_VALUES'][$order]){$ORD=' ORDER BY '.$table['FIELDS_VALUES'][$order].' ';}
		}
		if(is_array($t))
		{
		foreach ($t as $key=>$val)
			{
				foreach ($table as $KEY=>$VAL)
				{
					if($table[$KEY][$val])
					{
						$tab[$KEY][]=$table[$KEY][$val].'as '.$val;
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
							case 'FIELDS_VALUES':
							$BY='BY3';
							break;
							case 'FIELD':
							$BY='BY';
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
							case 'FIELDS_VALUES':
							$BY='BY3';
							break;
							case 'FIELD':
							$BY='BY';
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
					case 'FIELD':
					$tabs=implode(',',$tab[$key]); $TABLES[]=$tabs;
					break;
					case 'FIELDS_VALUES':
					$tabs=implode(',',$tab[$key]); $TABLES3[]=$tabs;
					break;
					}
				}
				else{$tab=$t;}
			}
		}
		
		if(isset($TABLES)){$all='fld.`ID`, '.implode(', ',$TABLES);}else{
		$all="	fld.`ID`,
				fld.`NAME`, 
				fld.`TYPE`, 
				fld.`SORT`, 
				fld.`DEFAULT`, 
				fld.`ACTIVE`, 
				fld.`REQUIRED`, 
				fld.`VALUES`, 
				fld.`MULTIPLE`, 
				fld.`USER_EDITABLE`, 
				fld.`LENGTH`, 
				fld.`OPTIONS`, 
				fld.`FIELD_KEY`";}
		if($sql['BY'])
		{
			$BYs="\nWHERE ".implode(' AND ',$sql['BY'])." ";
		}
		$from="`g_user_fields` fld";
		$SQL="SELECT ".$all." FROM ".$from." ".$BYs.$ORD.$LIMIT;	
		$res = $DB->Query($SQL);
		$ArrRes=array();
		while($row=$DB->fetchAssoc($res))
		{
			if(($F5['GROUP']) && $row[$F5['GROUP']]!='')
			{
				$ArrRes[$row[$F5['GROUP']]]=$row;
				$ArrMAP[$row['ID']]=$row[$F5['GROUP']];
				$ArrRes[$ArrMAP[$row['ID']]]['FIELDS_VALUE']=array();
			}
			else
			{
			$ArrRes[]=$row;
			$ArrMAP[$row['ID']]=count($ArrRes)-1;
			$ArrRes[$ArrMAP[$row['ID']]]['FIELDS_VALUE']=array();
			}
		}
		
		$user_id=array_keys($ArrMAP);
		if(isset($TABLES2)){$all2='user.`ID` as USER_ID, '.implode(', ',$TABLES);}else{
		$all2="	fv.`USER_ID`, 
				fv.`FIELD_ID`, 
				fv.`VALUE`, 
				fv.`VALUE_NUM`, 
				fv.`LOGIN`, 
				fv.`FIELD_KEY` as FV_FIELD_KEY";}
		if($sql['BY2'])
		{
			$BY2=" WHERE ".implode(' AND ',$sql['BY2'])." AND fv.`FIELD_ID` IN (".implode(' , ',$user_id).")";
		}
		else
		{
			$BY2=" WHERE fv.`FIELD_ID` IN (".implode(' , ',$user_id).")";
		}
		$from2="`g_user_fields_values` as fv";
		$SQL2="SELECT ".$all2." FROM ".$from2." ".$BY2;
		$res2 = $DB->Query($SQL2);
		while($row2=$DB->fetchAssoc($res2))
		{			
				if(($ArrMAP[$row2['FIELD_ID']]) || $ArrMAP[$row2['FIELD_ID']]===0)
				{
					if(($F5['USER']) && $row2[$F5['USER']]!='')
					{
					$ArrRes[$ArrMAP[$row2['USERGROUP_GROUP_ID']]]['USERS'][$row2[$F5['USER']]]=$row2;
					$A[$row2['USER_ID']]=$row2[$F5['USER']];
					$ArrRes[$ArrMAP[$row2['USERGROUP_GROUP_ID']]]['USERS'][$A[$row2['USER_ID']]]['FIELDS']=array();
					}
					else
					{
					$ArrRes[$ArrMAP[$row2['FIELD_ID']]]['FIELDS_VALUE'][]=$row2;	
					}
				}
		}
		if(!empty($ArrRes)){return $ArrRes;} else false;
	}
}
?>