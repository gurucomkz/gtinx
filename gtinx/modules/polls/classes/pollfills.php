<?
if(!defined('COUNT_DEFINED_VALUE')){define('COUNT_DEFINED_VALUE',rand());}
class GTpollfills
{
function Check($b=array())
	{
		global $DB;
		foreach ($b as $key=>$val)
		{
			
			if($key=='DESC')
			{ if($b['edswitch'.$key]=='ht')
				{
					$b[$key]=trim($b[$key]);
					if(preg_match('/[^\\\\]\'/',$b[$key]))
					{
						$b[$key]=addslashes($b[$key]);
					}
					$b[$key.'_TYPE']=$b['edswitch'.$key];
				}
				else
				{
					$b[$key.'_TYPE']=$b['edswitch'.$key];
					//$b[$key]=nl2br(htmlspecialchars(trim($b[$key])));
					$b[$key]=htmlspecialchars(trim($b[$key]));
					if(preg_match('/[^\\\\]\'/',$b[$key]))
					{
						$b[$key]=addslashes($b[$key]);
					}
				}
			}
			else{
					$b[$key]=stripslashes(trim($b[$key]));
				}
		}
		//foreach ($b as $key=>$val) if($val==''){unset($b[$key]);}
		
		$a=array(
		'ID'=>array('int',TRUE),
		'USER_ID'=>array('int',TRUE),
		'POLL_ID'=>array('int',FALSE),
		'IP'=>array('string',FALSE),
		'CREATED'=>array('date',FALSE),
		'STMT_ID'=>array('int',FALSE),
		'FILL_ID'=>array('int',FALSE),
		'VALUE'=>array('string',FALSE),
		'VALUE_NUM'=>array('int',FALSE)
		);
		

		foreach($b as $key=>$val)
		{
			//print_r($this->vara);
			if(!isset($a[$key])) {$c[]=$key;}
			else{
				switch($a[$key][0])
					{
					case 'key':
						$res='';
						$res=$DB->QResult("SELECT `ID` FROM `g_dblock` WHERE `DBLOCK_KEY`='".$val."'");
						if(empty($res))
						{
						$val=(string)$val;
						if((string)$val!==$val || $val==' '){$c[]=$key;}else{$b[$key]=$val;}
						}
						else
						{
						$c[]=$key;
						}
						break;
					case 'must_have':
						if(is_numeric($val))
						{
							$val=(int)$val;
							$res='';
							$res=$DB->QResult("SELECT `ID` FROM `g_dblock_types` WHERE `ID`='".$val."'");
							if(!empty($res))
							{
							$b[$key]=$val;
							}
							else
							{
							$c[]=$key;
							die('error TYPE_ID Not FINDED');
							}
						}
						else
							{
							$c[]=$key;
							die('error TYPE_ID Not FINDED');
							}
						break;
					case 'boolean':
						if($val!=1)
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
function Ready($arVar)
{
	//d($arVar);
	foreach($arVar as $key=>$val)
	{
		if(is_array($val))
		{
			
			foreach($val as $KEY=>$VAL)
			{
				if(is_array($VAL))
				{
					$FV=array();
					foreach($VAL as $TKEY=>$TVAL)
					{
						$FV[]=$TVAL;
					}
					$arV['STMT_ID'][$KEY]=implode('\n',$FV);
				}
				else
				{
				$arV['STMT_ID'][$KEY]=$VAL;
				}
			}
		
		}
	}
	$Arv['POLL_ID']=$arVar['POLL_ID'];
	GTpollfills::Add($Arv,$arV);
	return TRUE;
}
function Add($arVariables,$arVariables2)
{
	global $DB, $APP;
	foreach($arVariables2['STMT_ID'] as $key=>$val)
	{
		$r=GTpollsstms::Get('',Array('ID'=>$key));
		$r=$r[0];
		if($r['REQUIRED']!=0)
		{
			$val=trim($val);
			if(empty($val))
			{
				return FALSE;
			}
		}
	}
		$arVariables['USER_ID']=$APP->GetCurrentUserID();
		$arVariables['CREATED']=date('Y-m-d H:m:s');
		$arVariables['IP']=Get_User_Ip();
		$used=GTpollfills::Get('*',array('IP'=>$arVariables['IP'],'POLL_ID'=>$arVariables['POLL_ID'],'USER_ID'=>$arVariables['USER_ID']));
		if(empty($used))
		{ 
		$arVariables= GTpollfills::Check($arVariables);//d($arVariables);
	if(!isset($arVariables[0]) && isset($arVariables['POLL_ID']))
		{
			$R='';
			$R=$DB->QResult("SELECT `ID` FROM `g_polls` WHERE `ID`='".$arVariables['POLL_ID']."'");
			if(!empty($R))
			{
				$Value=$arVariables;
				$VKeys = array_keys($Value);
				$sql="INSERT INTO `g_poll_fills` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
				$res=$DB->Query($sql);
				$LASTID = $DB->insertId();
				foreach($arVariables2['STMT_ID'] as $key=>$val)
				{
					$R='';
					$R=$DB->QResult("SELECT `FILL_ID` FROM `g_poll_stmt_fills` WHERE `STMT_ID`='".$key."' AND `FILL_ID`='".$LASTID."'");
					if(empty($R))
					{
						$val=htmlspecialchars(trim($val));
						$valN=(int)$val;
						$Ins=array(
						'STMT_ID'=>$key,
						'FILL_ID'=>$LASTID,
						'VALUE'=>$val,
						'VALUE_NUM'=>$valN,
						);
						$Value=$Ins;
						$VKeys = array_keys($Value);
						$sql="INSERT INTO `g_poll_stmt_fills` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
						$res=$DB->Query($sql);
					}
					else
					{
						$val=htmlspecialchars(trim($val));
						$valN=(int)$val;
						$Ins=array(
						'STMT_ID'=>$key,
						'FILL_ID'=>$LASTID,
						'VALUE'=>$val,
						'VALUE_NUM'=>$valN,
						);
						$sql="UPDATE `g_poll_stmt_fills` SET `VALUE`=".$val.", `VALUE_NUM`=".$valN." WHERE `STMT_ID`='".$key."' AND `FILL_ID`='".$R."'";
						$res=$DB->Query($sql);
					}
				}
				return TRUE;
			}else{return FALSE;}
		}else{return FALSE;}
	}return 'Used IP';
}
function Update($arVariables,$arVariables2)
{
	global $DB, $APP;
	foreach($arVariables2['STMT_ID'] as $key=>$val)
	{
		$r=GTpollsstms::Get('',Array('ID'=>$key));
		$r=$r[0];
		if($r['REQUIRED']!=0)
		{
			$val=trim($val);
			if(empty($val))
			{
				return FALSE;
			}
		}
	}
	$R='';
	$R=$DB->QResult("SELECT `ID` FROM `g_polls` WHERE `ID`='".$arVariables['POLL_ID']."'");
	if(!empty($R))
	{
		$R='';
		$R=$DB->QResult("SELECT `ID` FROM `g_poll_fills` WHERE `ID`='".$arVariables['FILL_ID']."'");
		if(!empty($R))
		{		
			foreach($arVariables2['STMT_ID'] as $key=>$val)
			{
				$R='';
				$R=$DB->QResult("SELECT `FILL_ID` FROM `g_poll_stmt_fills` WHERE `STMT_ID`='".$key."' AND `FILL_ID`='".$arVariables['FILL_ID']."'");
				if(empty($R))
				{
					$val=htmlspecialchars(trim($val));
					$valN=(int)$val;
					$Ins=array(
					'STMT_ID'=>$key,
					'FILL_ID'=>$arVariables['FILL_ID'],
					'VALUE'=>$val,
					'VALUE_NUM'=>$valN,
					);
					$Value=$Ins;
					$VKeys = array_keys($Value);
					$sql="INSERT INTO `g_poll_stmt_fills` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
					//d($sql);
					$res=$DB->Query($sql);
				}
				else
				{
					$val=htmlspecialchars(trim($val));
					$valN=(int)$val;
					$Ins=array(
					'STMT_ID'=>$key,
					'FILL_ID'=>$arVariables['FILL_ID'],
					'VALUE'=>$val,
					'VALUE_NUM'=>$valN,
					);
					$sql="UPDATE `g_poll_stmt_fills` SET `VALUE`=".$val.", `VALUE_NUM`=".$valN." WHERE `STMT_ID`='".$key."' AND `FILL_ID`='".$R."'";
					//d($sql);
					$res=$DB->Query($sql);
				}
			}
			return TRUE;
		}else{return FALSE;}
	}else{return FALSE;}
}

function Delete($A)
{
	global $DB;
	$ID="('".implode('\',\'',$A)."')";
	$SQL="DELETE FROM `g_poll_fills` WHERE `POLL_ID` IN ".$ID."";
	$DB->Query($SQL);
	$SQL="DELETE FROM `g_poll_stmt_fills` WHERE `FILL_ID` IN ".$ID."";
	$DB->Query($SQL);
}

function DeleteA($A)
{
	global $DB;
	$ID="('".implode('\',\'',$A)."')";
	$SQL="DELETE FROM `g_poll_fills` WHERE `ID` IN ".$ID."";
	$DB->Query($SQL);
	$SQL="DELETE FROM `g_poll_stmt_fills` WHERE `FILL_ID` IN ".$ID."";
	$DB->Query($SQL);
}

function GetStmtFill($t=FALSE,$arv=FALSE,$order=FALSE,$limit=FALSE, $FIVE=FALSE,&$count=COUNT_DEFINED_VALUE)
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
	$table['STMS_FILLS']=Array(
		'STMT_ID'=>'stmsf.`STMT_ID`',
		'FILL_ID'=>'stmsf.`FILL_ID`',
		'VALUE'=>'stmsf.`VALUE`',
		'VALUE_NUM'=>'stmsf.`VALUE_NUM`'
		);
	if($order)
	{
		if($table['STMS_FILLS'][$order])
		{
			$ORD=' ORDER BY '.$table['STMS_FILLS'][$order].' ';
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
						case 'STMS_FILLS':
						$BY='BY';
						break;				
						}
					if($table[$KEY][$key])
						{
							foreach($val as $A=>$B)
							{
								$arg= substr($B,0,1);
								$arg1= substr($B,1);
								$D='';
								$a='';
								switch($arg)
								{
								case '=':
								$D=$table[$KEY][$key]."='".$arg1."'";
								break;
								case '<':
								$D=$table[$KEY][$key]."<'".$arg1."'";
								break;
								case '>':
								$D=$table[$KEY][$key].">'".$arg1."'";
								break;
								case '~':
								$D=$table[$KEY][$key]."  LIKE '".$arg1."'";
								break;
								case '!':
								$D=$table[$KEY][$key]."<>'".$arg1."'";
								break;
								case '*':
								$D=$table[$KEY][$key]."<='".$arg1."'";
								break;
								case '#':
								$D=$table[$KEY][$key].">='".$arg1."'";
								break;
								default:
								$a=$B;
								break;
								}
								
								if($D)
								{
								$sqlOR[$BY][]=$D;
								}
								elseif($a)
								{
								$sqlIN[$BY][$table[$KEY][$key]][]=$a;
								}
							}
							
							//$sql[$BY][]=$table[$KEY][$key]." IN (".implode(' , ',$val).")";
						}
						unset($BY);
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
						case 'STMS_FILLS':
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
						$notFoundInTables[$key]=$val;			
				}
			}
		}
		$sqlasd['IN']=array();
		$INER='';
		if(!empty($sqlIN['BY']))
		{
			foreach($sqlIN['BY'] as $key=>$val)
			{
			$sqlasd['IN'][]=$key." IN ('".implode('\' ,\'',$sqlIN['BY'][$key])."')";
			$INER=$key." IN ('".implode('\',\'',$sqlIN['BY'][$key])."')";
			}
		}
		if(!empty($sqlOR['BY']))
		{
			$sql['BY'][]=" (".implode(' OR ',array_merge($sqlasd['IN'],$sqlOR['BY'])).") ";
		}
		elseif(!empty($sqlasd['IN']))
		{
			$sql['BY'][]=$INER;
		}
	}
	foreach($table as $key=>$val)
	{
		if(isset($tab[$key]))
		{
			if(is_array($t))
			{
			$tabs=implode(',',$tab[$key]); $TABLES[]=$tabs;
			}
			else{$tab=$t;}
		}
	}
	
	if($sql['BY'])
	{
		$BYFIR=" WHERE ".implode(" AND \n",$sql['BY']);
	}
	if(isset($TABLES))
	{
		$tabs2='stmsf.`STMT_ID`, '. implode(', ',$TABLES);
	}
	else
	{
		$tabs2=implode(', ',$table['STMS_FILLS']);
	}
	$COUNT='';
		if($count!=COUNT_DEFINED_VALUE)
		{
			$COUNT=' SQL_CALC_FOUND_ROWS ';
		}
	$SQL="SELECT ".$COUNT.$tabs2." FROM `g_poll_stmt_fills` as stmsf ".$BYFIR.$ORD.$LIMIT;
	$res=$DB->Query($SQL);
	if(!empty($COUNT))
		{
			$count=$DB->QResult("SELECT FOUND_ROWS()");
		}
	$ArrRes=array();
	while($row=$DB->fetchAssoc($res))
	{
	$ArrRes[]=$row;
	}
	return $ArrRes;
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
	$table['POLL_FILLS']=Array(
		'ID'=>'pfills.`ID`',
		'POLL_ID'=>'pfills.`POLL_ID`',
		'USER_ID'=>'pfills.`USER_ID`',
		'IP'=>'pfills.`IP`',
		'CREATED'=>'pfills.`CREATED`'
		);
	$table['STMS_FILLS']=Array(
		'STMT_ID'=>'stmsf.`STMT_ID`',
		'FILL_ID'=>'stmsf.`FILL_ID`',
		'VALUE'=>'stmsf.`VALUE`',
		'VALUE_NUM'=>'stmsf.`VALUE_NUM`'
		);
	if($order)
	{
		if($table['POLL_FILLS'][$order])
		{
			$ORD=' ORDER BY '.$table['POLL_FILLS'][$order].' ';
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
						case 'POLL_FILLS':
						$BY='BY';
						break;				
						}
					if($table[$KEY][$key])
						{
							foreach($val as $A=>$B)
							{
								$arg= substr($B,0,1);
								$arg1= substr($B,1);
								$D='';
								$a='';
								switch($arg)
								{
								case '=':
								$D=$table[$KEY][$key]."='".$arg1."'";
								break;
								case '<':
								$D=$table[$KEY][$key]."<'".$arg1."'";
								break;
								case '>':
								$D=$table[$KEY][$key].">'".$arg1."'";
								break;
								case '~':
								$D=$table[$KEY][$key]."  LIKE '".$arg1."'";
								break;
								case '!':
								$D=$table[$KEY][$key]."<>'".$arg1."'";
								break;
								case '*':
								$D=$table[$KEY][$key]."<='".$arg1."'";
								break;
								case '#':
								$D=$table[$KEY][$key].">='".$arg1."'";
								break;
								default:
								$a=$B;
								break;
								}
								
								if($D)
								{
								$sqlOR[$BY][]=$D;
								}
								elseif($a)
								{
								$sqlIN[$BY][$table[$KEY][$key]][]=$a;
								}
							}
							
							//$sql[$BY][]=$table[$KEY][$key]." IN (".implode(' , ',$val).")";
						}
						unset($BY);
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
						case 'POLL_FILLS':
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
						$notFoundInTables[$key]=$val;			
				}
			}
		}
		$sqlasd['IN']=array();
		$INER='';
		if(!empty($sqlIN['BY']))
		{
			foreach($sqlIN['BY'] as $key=>$val)
			{
			$sqlasd['IN'][]=$key." IN ('".implode('\' ,\'',$sqlIN['BY'][$key])."')";
			$INER=$key." IN ('".implode('\',\'',$sqlIN['BY'][$key])."')";
			}
		}
		if(!empty($sqlOR['BY']))
		{
			$sql['BY'][]=" (".implode(' OR ',array_merge($sqlasd['IN'],$sqlOR['BY'])).") ";
		}
		elseif(!empty($sqlasd['IN']))
		{
			$sql['BY'][]=$INER;
		}
	}
	foreach($table as $key=>$val)
	{
		if(isset($tab[$key]))
		{
			if(is_array($t))
			{
			$tabs=implode(',',$tab[$key]); $TABLES[]=$tabs;
			}
			else{$tab=$t;}
		}
	}
	
	if($sql['BY'])
	{
		$BYFIR=" WHERE ".implode(" AND \n",$sql['BY']);
	}
	if(isset($TABLES))
	{
		$tabs2='pl.`ID`, '. implode(', ',$TABLES);
	}
	else
	{
		$tabs2=implode(', ',$table['POLL_FILLS']);
	}
	$SQL="SELECT ".$tabs2." FROM `g_poll_fills` as pfills ".$BYFIR.$ORD.$LIMIT;
	$res=$DB->Query($SQL);
	$ArrRes=array();
	while($row=$DB->fetchAssoc($res))
	{
		$ArrRes[]=$row;
		$ArrMAP[$row['ID']]=count($ArrRes)-1;
		$ArrRes[$ArrMAP[$row['ID']]]['SMTS_FILLS']=array();
	}
	if(empty($ArrMAP)) return array();
	$id=array_keys($ArrMAP);
	
	if($sql['BY2'])
		{
			$BY2=" WHERE ".implode(" AND \n\t\t",$sql['BY2'])."\n\t\t AND stmsf.`FILL_ID` IN (".implode(' , ',$db_id).")";
		}
		else
		{
			$BY2=" WHERE stmsf.`FILL_ID` IN (".implode(' , ',$id).")";
		}
		$tabs3=implode(', ',$table['STMS_FILLS']);
		
		$from="`g_poll_stmt_fills` as stmsf";
		$SQL2="SELECT ".$tabs3." FROM ".$from." ".$BY2;
		$res2 = $DB->Query($SQL2);
		while($row2=$DB->fetchAssoc($res2))
		{
			$ArrRes[$ArrMAP[$row2['FILL_ID']]]['SMTS_FILLS'][$row2['STMT_ID']]=$row2;
		}
	return $ArrRes;
}

}
?>
