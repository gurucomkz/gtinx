<?
if(!defined('COUNT_DEFINED_VALUE')){define('COUNT_DEFINED_VALUE',rand());}
class GToptionlang
{
	function Check($b=array())
	{
		foreach ($b as $key=>$val)
		{
			if($b['edswitch'.$key]=='ht')
				{
					$b[$key]=trim($b[$key]);
					//$b[$key.'_TYPE']=$b['edswitch'.$key];
				}
				elseif($b['edswitch'.$key]=='tx')
				{
					//$b[$key.'_TYPE']=$b['edswitch'.$key];
					$b[$key]=nl2br(htmlspecialchars(trim($b[$key])));
				}
			else
				{
					$b[$key]=htmlspecialchars(trim($b[$key]));
				}
		}
		
		
		$a=array(
			'ID'=>array('string',TRUE,4),
			'TITLE'=>array('string',TRUE,255),
			'DEFAULT'=>array('boolean',TRUE,1),
			'SORT'=>array('int',FALSE,32),
			'DATE_FORMAT'=>array('string',FALSE,10),
			'DATE_TIME_FORMAT'=>array('string',TRUE,20),
			'CODE'=>array('string',FALSE,20),
			'TEXT_DIRECTION'=>array('boolstring',FALSE,3),
			'ENABLED'=>array('boolean',FALSE,1));
		
		
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
				case 'boolstring':
					if($val!='RTL')
					{
					$b[$key]='LTR';
					}
					break;
				case 'int':
					if(empty($val))
					{
						$c[]=$key;
					}
					else
					{
						if(is_numeric($val))
						{
						$val=(int)$val;
						}
						if((int)$val!==$val){$c[]=$key;}else{$b[$key]=$val;}
					}
					break;
				case 'string':
					if(empty($val) || strlen($val)>(int)$a[$key][2])
					{
						$c[]=$key;
					}
					else
					{ 
						$val=(string)$val;
						if((string)$val!==$val){$c[]=$key;}else{$b[$key]=$val;}
					}
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
	
	function Add($arv)
	{
		global $DB;
		$arv=GToptionlang::Check($arv);
		if(!isset($arv[0]))
		{
			$id=$arv['ID'];
			if($arv['DEFAULT']==1)
			{
				echo $SQl="UPDATE `g_lang` SET `DEFAULT`='0' WHERE `DEFAULT`='1'";
				$DB->Query($SQl);
			}
			$r='';
			$r=$DB->QResult("SELECT 'ID' FROM `g_lang` WHERE `ID`='".$id."'");
			if(empty($r))
			{
				$Value=$arv;
				$VKeys = array_keys($Value);
				$sql="INSERT INTO `g_lang` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
				$res=$DB->Query($sql);
				$Q='';
				$Q=$DB->QResult("SELECT 'ID' FROM `g_lang` WHERE `ID`='".$id."'");
				if(!empty($Q))
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}
		}
		return FALSE;
	}
	
	function Update($arv)
	{
		global $DB;
		$arv=GToptionlang::Check($arv); 
		if(!isset($arv[0]))
		{
			$id=$arv['ID'];
			if($arv['DEFAULT']==1)
			{
				$SQl="UPDATE `g_lang` SET `DEFAULT`='0' WHERE `DEFAULT`='1'";
				$DB->Query($SQl);
			}
			$r='';
			$r=$DB->QResult("SELECT 'ID' FROM `g_lang` WHERE `ID`='".$id."'");
			if(!empty($r))
			{
				
				foreach($arv as $keyss=>$arV)
				{
					if ($arV==''){$arV=' ';}
					$K1[]="`".$keyss."`='".$arV."'";
				}
				if(!empty($K1))
				{
					$sql="UPDATE `g_lang` SET ".implode(', ',$K1)." WHERE `ID`='".$id."'";
					$res=$DB->Query($sql);
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	
	function Get($t=FALSE,$arv=FALSE,$order=FALSE,$limit=FALSE, $FIVE=FALSE,&$count=COUNT_DEFINED_VALUE)
	{	//die('ss');
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
		$table['LANG']=Array(
			'ID'=>'lg.`ID`',
			'TITLE'=>'lg.`TITLE`',
			'DEFAULT'=>'lg.`DEFAULT`',
			'SORT'=>'lg.`SORT`',
			'DATE_FORMAT'=>'lg.`DATE_FORMAT`',
			'DATE_TIME_FORMAT'=>'lg.`DATE_TIME_FORMAT`',
			'CODE'=>'lg.`CODE`',
			'TEXT_DIRECTION'=>'lg.`TEXT_DIRECTION`',
			'ENABLED'=>'lg.`ENABLED`',
			);
		if($order)
		{
			if($table['LANG'][$order])
			{
				$ORD=' ORDER BY '.$table['SITES'][$order].' ';
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
						$tab[$KEY][]=$table[$KEY][$val];
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
							case 'LANG':
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
							case 'LANG':
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
			$tabs2='lg.`ID`, '. implode(', ',$TABLES);
		}
		else
		{
			$tabs2=implode(', ',$table['LANG']);
		}
		$COUNT='';
		if($count!=COUNT_DEFINED_VALUE)
		{
			$COUNT=' SQL_CALC_FOUND_ROWS ';
		}
		$SQL="SELECT ".$COUNT.$tabs2." FROM `g_lang` as lg ".$BYFIR.$ORD.$LIMIT;
		$res=$DB->Query($SQL);
		$res = $DB->Query($SQL);
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
}
?>