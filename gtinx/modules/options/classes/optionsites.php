<?
class GToptionsites
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
			'site_id'=>array('string',TRUE,2),
			'site_dir'=>array('string',TRUE,255),
			'site_domains'=>array('string',TRUE,255),
			'site_template'=>array('string',TRUE,255),
			'site_lang'=>array('string',FALSE,4),
			'site_name'=>array('string',TRUE,255),
			'site_default'=>array('boolean',FALSE,1),
			'site_enabled'=>array('boolean',FALSE,1));
		
		
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
	
	function Update($arv)
	{
		global $DB;
		$arv=GToptionsites::Check($arv);
		if(!isset($arv[0]))
		{
			$id=$arv['site_id'];
			if($arv['site_default']==1)
			{
				$SQl="UPDATE `g_sites` SET `site_default`='0' WHERE `site_default`='1'";
				$DB->Query($SQl);
			}
			$r='';
			$r=$DB->QResult("SELECT 'site_id' FROM `g_sites` WHERE `site_id`='".$id."'");
			if(!empty($r))
			{
				foreach($arv as $keyss=>$arV)
				{
					if ($arV==''){$arV=' ';}
					$K1[]="`".$keyss."`='".$arV."'";
				}
				if(!empty($K1))
				{
					$sql="UPDATE `g_sites` SET ".implode(', ',$K1)." WHERE `site_id`='".$id."'";
					$res=$DB->Query($sql);
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	
	function templates($dir)
	{
		$dir=GTROOT.$dir;
		$scan = scandir($dir);//d($scan);
			foreach($scan as $key=>$val)
			{
				if($val=='.svn' || $val=='.' || $val=='..'){unset ($scan[$key]);}
				else
				{
					if (file_exists($dir.$val.'/description.php'))
					{
						include($dir.$val.'/description.php');
						if(!empty($arTemplate))
						{
							$tamplate[$val]=$arTemplate;
						}
					}
				}
			}
		return $tamplate;
	}
	
	function UpTemp($arv)
	{
		$arv['NAME']=trim($arv['NAME']);
		$arv['DESCRIPTION']=trim($arv['DESCRIPTION']);
		$file = fopen (GTROOT."/templates/".$arv['ID']."/description.php","w");
		$str = '<?$arTemplate = Array("NAME"=>"'.$arv['NAME'].'", "DESCRIPTION"=>"'.$arv['DESCRIPTION'].'");?>';
		if ( !$file )
		{
			return FALSE;
		}
		else
		{
			fputs ( $file, $str);
		}
		fclose ($file);
		return TRUE;
	}
	function Get($t=FALSE,$arv=FALSE,$order=FALSE,$limit=FALSE, $FIVE=FALSE)
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
		$table['SITES']=Array(
			'site_id'=>'w.`site_id`',
			'site_dir'=>'w.`site_dir`',
			'site_domains'=>'w.`site_domains`',
			'site_template'=>'w.`site_template`',
			'site_lang'=>'w.`site_lang`',
			'site_name'=>'w.`site_name`',
			'site_enabled'=>'w.`site_enabled`',
			'site_default'=>'w.`site_default`'
			);
		if($order)
		{
			if($table['SITES'][$order])
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
							case 'SITES':
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
							case 'SITES':
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
			$tabs2='w.`ID`, '. implode(', ',$TABLES);
		}
		else
		{
			$tabs2=implode(', ',$table['SITES']);
		}
		$SQL="SELECT ".$tabs2." FROM `g_sites` as w ".$BYFIR.$ORD.$LIMIT;
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