<?
class GTpolls
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
					$b[$key]=nl2br(htmlspecialchars(trim($b[$key])));
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
		'DESC'=>array('string',FALSE),
		'ACTIVE'=>array('boolean',FALSE),
		'TAGS'=>array('string',FALSE),
		'CREATED'=>array('date',FALSE),
		'UPDATED'=>array('date',FALSE),
		'OWNER'=>array('int',FALSE),
		'AUTHOR'=>array('int',FALSE),
		'ACTIVE_TO'=>array('date',FALSE),
		'ACTIVE_FROM'=>array('date',FALSE));

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

function Add($arVariables)
{
	global $DB, $APP;			
		$arVariables['AUTHOR']=$APP->GetCurrentUserID();
		$arVariables['CREATED']=date('Y-m-d H:m:s');
		$arVariables['UPDATED']=$arVariables['CREATED'];
		$arVariables= GTpolls::Check($arVariables); d($arVariables);
	if(!isset($arVariables[0]) && isset($arVariables['TITLE']))
		{
			$Value=$arVariables;
			$VKeys = array_keys($Value);
			$sql="INSERT INTO `g_polls` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
			$res=$DB->Query($sql);
		}else{return FALSE;}
}
function Update($arVariables)
{
	global $DB, $APP;
	//d($arVariables);
	$arVariables['UPDATED']=date('Y-m-d H:m:s');
	$arVariables= GTpolls::Check($arVariables); //d($arVariables);
	if(isset($arVariables['ID']))
			{
			$ID=$arVariables['ID'];
			unset($arVariables['ID']);
				foreach($arVariables as $keyss=>$arV)
				{
				if ($arV==''){$arV=' ';}
				$K1[]="`".$keyss."`='".$arV."'";
				}
				$sql="UPDATE `g_polls` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'";
				$res=$DB->Query($sql);
			}else{return FALSE;}
}
function Delete($A)
{
	global $DB;
	$ID="('".implode('\',\'',$A)."')";
	$SQL="DELETE FROM `g_polls` WHERE `ID` IN ".$ID."";
	$DB->Query($SQL);
	$SQL2="SELECT `ID` FROM `g_poll_stms` WHERE `POLL_ID` IN ".$ID."";
	$res=$DB->Query($SQL2);
	while($row=$DB->fetchAssoc($res))
	{
		$Ar[]=$row['ID'];
	}
	$SID="('".implode('\',\'',$Ar)."')";
	$SQL3="DELETE FROM `g_poll_stmt_fills` WHERE `STMT_ID` IN ".$SID."";
	$DB->Query($SQL3);
	$SQL4="DELETE FROM `g_poll_stms` WHERE `POLL_ID` IN ".$ID."";
	$DB->Query($SQL4);
	$SQL5="DELETE FROM `g_poll_fills` WHERE `POLL_ID` IN ".$ID."";
	$DB->Query($SQL5);
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
	$table['POLLS']=Array(
		'ID'=>'pl.`ID`',
		'TITLE'=>'pl.`TITLE`',
		'DESC'=>'pl.`DESC`',
		'ACTIVE'=>'pl.`ACTIVE`',
		'TAGS'=>'pl.`TAGS`',
		'CREATED'=>'pl.`CREATED`',
		'UPDATED'=>'pl.`UPDATED`',
		'OWNER'=>'pl.`OWNER`',
		'AUTHOR'=>'pl.`AUTHOR`',
		'ACTIVE_TO'=>'pl.`ACTIVE_TO`',
		'ACTIVE_FROM'=>'pl.`ACTIVE_FROM`',
		);
	if($order)
	{
		if($table['POLLS'][$order])
		{
			$ORD=' ORDER BY '.$table['POLLS'][$order].' ';
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
						case 'POLLS':
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
						case 'POLLS':
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
		$tabs2=implode(', ',$table['POLLS']);
	}
	$SQL="SELECT ".$tabs2." FROM `g_polls` as pl ".$BYFIR.$ORD.$LIMIT;
	$res=$DB->Query($SQL);
	$ArrRes=array();
	while($row=$DB->fetchAssoc($res))
	{
	$ArrRes[]=$row;
	}
	return $ArrRes;
}
}
?>