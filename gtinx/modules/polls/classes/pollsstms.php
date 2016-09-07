<?
class GTpollsstms
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
		'TITLE'=>array('string',TRUE),
		'POLL_ID'=>array('int',FALSE),
		'SORT'=>array('int',FALSE),
		'TYPE'=>array('string',FALSE),
		'OPTIONS'=>array('string',FALSE),
		'ACTIVE'=>array('boolean',FALSE),
		'REQUIRED'=>array('boolean',FALSE),
		'MULTIPLE'=>array('boolean',FALSE),
		'VALUES'=>array('string',FALSE));

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
						if((string)$val!==$val || $val==' ' || $val==''){$c[]=$key;}else{$b[$key]=$val;}
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

function Add($arVariables,$ar2=FALSE)
{
	global $DB, $APP;		
		$arVariables= GTpollsstms::Check($arVariables);
	if(!isset($arVariables[0]) && isset($arVariables['TITLE']))
		{
			$R=$DB->QResult("SELECT `ID` FROM `g_polls` WHERE `ID`='".$arVariables['POLL_ID']."'");
			if(!empty($R))
			{
				$Value=$arVariables;
				$VKeys = array_keys($Value);
				$sql="INSERT INTO `g_poll_stms` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";//d($sql);
				$res=$DB->Query($sql);
				$id=$DB->insertId();
				if($ar2!=FALSE && !empty($id))
				{
					foreach($ar2 as $val)
					{
						//$val['STMS_ID']=$id;
						$Value=$val;
						$VKeys = array_keys($Value);
						$sql="INSERT INTO `g_poll_stms_multy` (`STMS_ID`,`".implode('`, `',$VKeys)."`) VALUES ('$id','".implode('\', \'',$Value)."')"; //d($sql);
						$res=$DB->Query($sql);
					}
				}
				
			}
		}else{return FALSE;}


}
function Update($arVariables)
{
	global $DB, $APP;
	//d($arVariables);
	
	$arVariables= GTpollsstms::Check($arVariables); //d($arVariables);
	if(isset($arVariables['ID']))
			{
				$R=$DB->QResult("SELECT `ID` FROM `g_polls` WHERE `ID`='".$arVariables['POLL_ID']."'");
				if(!empty($R))
				{
					$ID=$arVariables['ID'];
					unset($arVariables['ID']);
					foreach($arVariables as $keyss=>$arV)
					{
					if ($arV==''){$arV=' ';}
					$K1[]="`".$keyss."`='".$arV."'";
					}
					$sql="UPDATE `g_poll_stms` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'";
					$res=$DB->Query($sql);
				}
			}else{return FALSE;}
}

function UpdateMULTY($arVariables)
{
	global $DB, $APP;
	//d($arVariables);
	
	//$arVariables= GTpollsstms::Check($arVariables); //d($arVariables);
	if(isset($arVariables['STMS_ID']))
			{
				$R=$DB->QResult("SELECT `ID` FROM `g_polls` WHERE `ID`='".$arVariables['POLL_ID']."'");
				if(!empty($R))
				{
					$ID=$arVariables['STMS_ID'];
					$LANG=$arVariables['LANG'];
					unset($arVariables['STMS_ID']);
					unset($arVariables['POLL_ID']);
					unset($arVariables['LANG']);
					foreach($arVariables as $keyss=>$arV)
					{
					if ($arV==''){$arV=' ';}
					$K1[]="`".$keyss."`='".$arV."'";
					}
					$sql="UPDATE `g_poll_stms_multy` SET ".implode(', ',$K1)." WHERE `STMS_ID`='".$ID."' AND `LANG`='".$LANG."'";
					//d($sql);
					$res=$DB->Query($sql);
				}
			}else{return FALSE;}
}

function Delete($A)
{
	global $DB;
	$ID="('".implode('\',\'',$A)."')";
	$SQL="DELETE FROM `g_poll_stms` WHERE `ID` IN ".$ID."";
	$DB->Query($SQL);
	$SQL2="SELECT `FILL_ID` FROM `g_poll_stmt_fills` WHERE `STMT_ID` IN ".$ID."";
	$res=$DB->Query($SQL2);
	while($row=$DB->fetchAssoc($res))
	{
		$Ar[]=$row['FILL_ID'];
	}
	$SID="('".implode('\',\'',$Ar)."')";
	$SQL3="DELETE FROM `g_poll_stmt_fills` WHERE `STMT_ID` IN ".$ID."";
	$DB->Query($SQL3);
	$SQL5="DELETE FROM `g_poll_fills` WHERE `ID` IN ".$SID."";
	$DB->Query($SQL5);
	$MULTY=GTAPP::Conf('multilang_sync');
	if($MULTY==TRUE)
	{
		$SQL="DELETE FROM `g_poll_stms_multy` WHERE `STMS_ID` IN ".$ID."";
		$DB->Query($SQL);
	}
}

function Get($t=FALSE,$arv=FALSE,$order=FALSE,$limit=FALSE, $FIVE=FALSE)
{
	global $DB;
	$MULTY=GTAPP::Conf('multilang_sync');
	if($MULTY==TRUE)
		{
			if(empty($arv['LANG']))
			{
				$LANG = GTAPP::SiteLang();
				
			}
			else
			{
				$LANG=$arv['LANG'];
				//$arv['PROPS_VALUES_LANG']=$LANG;
				$sql['BY'][]= "\n`stms_multy`.`LANG`='".$LANG."'";
			}
			if($arv['FORCE_LANG'])
			{
				if(!empty($arv['FORCE_LANG']))
				{
					$sql['BY'][]= "\n`stms_multy`.`STMS_ID`<>'0'";
					$sql['BY'][]= "\n`stms_multy`.`LANG`='".$LANG."'";
				}
				unset($arv['FORCE_LANG']);
			}
		}
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
	$table['POLLS_STMS']=Array(
		'ID'=>'pstms.`ID`',
		'POLL_ID'=>'pstms.`POLL_ID`',
		'TITLE'=>'pstms.`TITLE`',
		'SORT'=>'pstms.`SORT`',
		'TYPE'=>'pstms.`TYPE`',
		'OPTIONS'=>'pstms.`OPTIONS`',
		'ACTIVE'=>'pstms.`ACTIVE`',
		'REQUIRED'=>'pstms.`REQUIRED`',
		'MULTIPLE'=>'pstms.`MULTIPLE`',
		'VALUES'=>'pstms.`VALUES`'
		);
	if($order)
	{
		if($table['POLLS_STMS'][$order])
		{
			$ORD=' ORDER BY '.$table['POLLS_STMS'][$order].' ';
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
						case 'POLLS_STMS':
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
						case 'POLLS_STMS':
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
		$tabs2=implode(', ',$table['POLLS_STMS']);
	}
	$tabs_C='';
	if($MULTY!=FALSE)
	{
	$tabs_C=
		',stms_multy.`STMS_ID`,
		stms_multy.`TITLE` as L_TITLE,
		stms_multy.`VALUES` as L_VALUES,
		stms_multy.`LANG`';
	}
	$from="`g_poll_stms` as pstms";
	if($MULTY)
			$from.="\n LEFT JOIN `g_poll_stms_multy` as stms_multy ON (`pstms`.`ID`=`stms_multy`.`STMS_ID`) ";
	$SQL="SELECT ".$tabs2." $tabs_C FROM $from ".$BYFIR.$ORD.$LIMIT; //d($SQL);
	$res=$DB->Query($SQL);
	$ArrRes=array();
	while($row=$DB->fetchAssoc($res))
	{		
		if($MULTY)
		{
			$row['TITLE']=$row['L_TITLE'];
			$row['VALUES']=$row['L_VALUES'];
			unset($row['L_TITLE']);
			unset($row['L_VALUES']);
			$ArrRes[$row['ID']][$row['LANG']]=$row;
			$ArrMAP[$row['ID']]=$row['ID'];
		}
		else{
			$ArrRes[]=$row;
			$ArrMAP[$row['ID']]=count($ArrRes)-1;
		}
		
	}
	if(empty($ArrMAP)) return array();
	$db_id=array_keys($ArrMAP);
	
	//d($ArrRes);
	return $ArrRes;
}
}
?>