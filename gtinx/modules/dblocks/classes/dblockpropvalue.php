<?
class GTdblockpropvalue
{

	function Check($b=array())
	{
		$to_NUM=trim(str_replace(' ','',$b['VALUE']));
		$b['VALUE_NUM']=(int)$to_NUM;
		foreach ($b as $key=>$val)
		{
			if($key=='VALUE' && isset($b['SWITCH']))
			{ 
				if($b['SWITCH']=='ht')
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
			
			$b[$key]=htmlspecialchars(trim($b[$key]));
				}
		}
			$a=array(
					'DBLOCK_ID'=>array('int',TRUE),
					'PROP_ID'=>array('int',TRUE),
					'VALUE'=>array('string',TRUE),
					'SWITCH'=>array('switch',TRUE),
					'LANG'=>array('string',TRUE),
					'VALUE_NUM'=>array('int',FALSE));
			
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
					case 'switch':
						if($val!='ht')
						{
						$b[$key]='tx';
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
					}
				
				}			
			}
				if($c[0]!='')
				{
					foreach($c as $key=>$val)
					{
					unset($b[$val]);
					if ($a[$val][1]==TRUE){$d=FALSE;}
					}
				}
				if(empty($d[0])){return $b;}else{return $d;}
		
	}
	
	function Add($arVariables)
		{
		global $DB;	
		foreach ($arVariables as $key=>$val) if($val==''||$val==FALSE && $val!=0) 
			{unset($arVariables[$key]);}else{$arVariables[$key]=htmlspecialchars(trim($arVariables[$key]));}
			$arVariables= GTdbpv::Check($arVariables);
			if(!isset($arVariables[0]))
			{
				$RES=$DB->Query("SELECT * FROM `g_dblock_props` WHERE `ID`='".$arVariables['PROP_ID']."'");
				$ROW=$DB->fetchAssoc($RES);
				if($ROW['TYPE']='DATE')
				{
				$arVariables['VALUE']=strtotime($arVariables['VALUE']);
				$arVariables['VALUE_NUM']=$arVariables['VALUE'];
				}
				$Value=$arVariables;
				$VKeys = array_keys($Value);
				$sql="INSERT INTO `g_dblock_props_values` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
				$res=$DB->Query($sql);
				if($res==TRUE)
				{return true;}else{echo "error";}
			}else{return false;}
			//fix me
			
		}
	function Get($t=FALSE,$PARAM=FALSE,$order=FALSE)
	{
		global $DB;	
		$table['PROPS_VALUES']=Array(
								'DBLOCK_ID'=>'`DBLOCK_ID`',
								'PROP_ID'=>'`PROP_ID`',
								'VALUE'=>'`VALUE`',
								'SWITCH'=>'`SWITCH`',
								'LANG'=>'`LANG`',
								'VALUE_NUM'=>'`VALUE_NUM`');
		if(!empty($order))
		{
			if(is_array($order))
			{
				if($table['PROPS_VALUES'][$order[0]]){$ORD=' ORDER BY '.$table['PROPS_VALUES'][$order[0]].' '.$order[1].' ';}
			}
			else
			{
				if($table['PROPS_VALUES'][$order]){$ORD=' ORDER BY '.$table['PROPS_VALUES'][$order].' ';}
			}
		}
		if(isset($t) && is_array($t))
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
		
		if($PARAM)
		{
			foreach($PARAM as $key=>$val)
			{
				$a=SWITCH_IT($val,$key);
				if($a!=FALSE)
				{
					$sql[]=$a;
				}					
			}
		}
		
		if(isset($tab['PROPS_VALUES']))
		{
			if(is_array($t))
			{
				$tabs=implode(',',$tab['PROPS_VALUES']); $TABLES[]=$tabs;
			}
			else{$tab=$t;}
		}
		if(isset($TABLES)){$tabs='`ID`, '. implode(', ',$TABLES);}else{$tabs='*';}
			if (isset($sql)){ $BY = " WHERE ".implode(' AND ',$sql);}
			$SQL = "SELECT * FROM `g_dblock_props_values`".$BY.$ORD;
			$res=$DB->Query($SQL);
			while($row=$DB->fetchAssoc($res))
				{	
					$ArrRes[]=$row;
				}
			return $ArrRes;
	}
		
	function Update($arVariables)
		{
		global $DB;		
			foreach($arVariables as $key=>$val)
			{
			$arVar[]= GTdbpv::Check($val);
			}
			
			if($arVar!=FALSE)
			{
				foreach($arVar as $key=>$val)
				{
				$sql="SELECT `PROP_ID` FROM `g_dblock_props_values` WHERE `DBLOCK_ID`='".$val['DBLOCK_ID']."' AND `PROP_ID`='".$val['PROP_ID']."'";
				$res=$DB->Query($sql);
				$row=$DB->fetchAssoc($res);
					if($row['PROP_ID']=='')
					{
					$k1=array_keys($val);
					$VALUES= "('".implode('\', \'',$val)."')";
					$sql="INSERT INTO `g_dblock_props_values` (`".implode('`, `',$k1)."`) VALUES ".$VALUES;
					$res=$DB->Query($sql);
					}
					else
					{
					foreach($val as $KEYS=>$VALS)
						{
						if(!empty($VALS) && $KEYS!='DBLOCK_ID' && $KEYS!='PROP_ID')
						{$k1[] = "`".$KEYS."`='".$VALS."'";}
							if($KEYS=='PROP_ID'){$PROP_ID=$VALS;}
							if($KEYS=='DBLOCK_ID'){$DBLOCK_ID=$VALS;}
						}
						if($k1)
						{
						$sql2="UPDATE `g_dblock_props_values` SET ".implode(', ',$k1)." WHERE `DBLOCK_ID`='".$DBLOCK_ID."' AND `PROP_ID`='".$PROP_ID."'";
						$res=$DB->Query($sql2);
						}
						unset ($k1);
					}
				}
			}
			
			
		}
}
?>