<?
class GTdblockprop
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
			$b[$key]=htmlspecialchars(trim($b[$key]));
				}
		}
		//foreach ($b as $key=>$val) if($val==''){$l[]=$key;}
		//foreach ($l as $key=>$val){unset($b[$val]);}
		
		global $DB;
		$a=array(
		'ID'=>array('int',TRUE),
		'TYPE_ID'=>array('int',TRUE),
		'NAME'=>array('string',TRUE),
		'TYPE'=>array('string',TRUE),
		'SORT'=>array('int',FALSE),
		'DEFAULT'=>array('string',FALSE),
		'ACTIVE'=>array('boolean',FALSE),
		'REQUIRED'=>array('boolean',FALSE),
		'VALUES'=>array('string',FALSE),
		'MULTIPLE'=>array('boolean',FALSE),
		'LENGTH'=>array('int',FALSE),
		'PROP_KEY'=>array('key',FALSE),
		'OPTIONS'=>array('string',FALSE));
			
		foreach($b as $key=>$val)
		{
		if(!isset($a[$key])) {$c[]=$key;}
		else{
				switch($a[$key][0])
				{
				case 'key':
					$sql="SELECT `ID` FROM `g_dblock_props` WHERE `PROP_KEY`='".$val."' AND `TYPE_ID`='".$b['TYPE_ID']."'";
					$res=$DB->Query($sql);
					$row=$DB->fetchAssoc($res);
					if($row['ID']==NULL)
					{
						$val=(string)$val;
						if((string)$val!==$val || $val==' '){$c[]=$key;}else{$b[$key]=$val;}
					}else{$c[]=$key;}
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
				if ($a[$val][1]==TRUE){$d[]=$val;}
			}
		}
		if(empty($d[0])){return $b;}else{return $d;}		
	}
	
function Add($arVariables)
	{		
		global $DB; 
		$arVariables= GTdblockprop::Check($arVariables);			
		if(!isset($arVariables[0]) && isset($arVariables['NAME']))
		{
			$Value=$arVariables;
			$VKeys = array_keys($Value);
			$sql="INSERT INTO `g_dblock_props` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
			$res=$DB->Query($sql);
			$id=$DB->insertId();
			if(!empty($id))
			{return TRUE;}else{return FALSE;}
		}
		else
		{
		return false;
		}	
	}
function GetMP($t=FALSE,$PARAM=FALSE)
{
	global $DB;	
	$table['PROPS']=Array(
							'DBLOCK_ID'=>'`DBLOCK_ID`',
							'PROP_ID'=>'`PROP_ID`',
							'VALUE'=>'`VALUE`',
							'LANG'=>'`LANG`',
							'VALUE_NUM'=>'`VALUE_NUM`'
							);
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
	
	if(isset($tab['PROPS']))
	{
		if(is_array($t))
		{
			$tabs=implode(',',$tab['PROPS']); $TABLES[]=$tabs;
		}
		else{$tab=$t;}
	}
	if(isset($TABLES)){$tabs='`ID`, '. implode(', ',$TABLES);}else{$tabs='*';}
	if (isset($sql)){ $BY = " WHERE ".implode(' AND ',$sql);}
	$SQL = "SELECT ".$tabs." FROM `g_dblock_multy_values`".$BY;
	$res=$DB->Query($SQL);
	while($row=$DB->fetchAssoc($res))
		{	
			$ArrRes[]=$row;
		}
	return $ArrRes;
}
function Get($t=FALSE,$PARAM=FALSE,$order=FALSE)
	{
		global $DB;	
		$table['PROPS']=Array(
								'ID'=>'`ID`',
								'NAME'=>'`NAME`',
								'TYPE_ID'=>'`TYPE_ID`',
								'TYPE'=>'`TYPE`',
								'ACTIVE'=>'`ACTIVE`',
								'SORT'=>'`SORT`',
								'DEFAULT'=>'`DEFAULT`',
								'REQUIRED'=>'`REQUIRED`',
								'VALUES'=>'`VALUES`',
								'MULTIPLE'=>'`MULTIPLE`',
								'LENGTH'=>'`LENGTH`',
								'OPTIONS'=>'`OPTIONS`',
								'PROP_KEY'=>'`PROP_KEY`'
								);
		if(!empty($order))
		{
			//d($order);
			if(is_array($order))
			{
				if($table['PROPS'][$order[0]]){$ORD=' ORDER BY '.$table['PROPS'][$order[0]].' '.$order[1].' ';}
			}
			else
			{
				if($table['PROPS'][$order]){$ORD=' ORDER BY '.$table['PROPS'][$order].' ';}
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
		
		if(isset($tab['PROPS']))
		{
			if(is_array($t))
			{
				$tabs=implode(',',$tab['PROPS']); $TABLES[]=$tabs;
			}
			else{$tab=$t;}
		}
		if(isset($TABLES)){$tabs='`ID`, '. implode(', ',$TABLES);}else{$tabs='*';}
			if (isset($sql)){ $BY = " WHERE ".implode(' AND ',$sql);}
			$SQL = "SELECT ".$tabs." FROM `g_dblock_props`".$BY.$ORD;
			//d($SQL);
			$res=$DB->Query($SQL);
			while($row=$DB->fetchAssoc($res))
				{	
					$ArrRes[]=$row;
				}
			return $ArrRes;
	}

function Delete($ID=FALSE)
		{
			global $DB;
			if (!empty($ID)&& is_numeric($ID))
			{
			$sql="Delete FROM `g_dblock_props` WHERE `ID`='".$ID."'";
			$res=$DB->Query($sql);
			if($res==TRUE)
				{
				$DB->Query("Delete FROM `g_dblock_props_value` WHERE `PROPS_ID`='".$ID."'");
				}
			}else{return false;}
		}
	
/*function CloneProps($ID=FALSE)
		{
			if (!empty($ID) && is_numeric($ID))
			{
				$this->ID=$ID;
				global $DB;
				$sql="SELECT * FROM `g_dblock_props` WHERE `ID`='".$this->ID."'";
				$res=$DB->Query($sql);
				$row=$DB->fetchAssoc($res);
				if (!empty($row))
				{
					$row['CREATED']=time('Y-m-d H:m:s');
					$keys=array_keys($row);
					foreach ($keys as $key=>$val) if($val==''||$val==FALSE ||$val=='ID') {unset($keys[$key]);unset($row['ID']);}
					$sql="INSERT INTO `g_dblock_props` (`".implode('`, `',$keys)."`) VALUES ('".implode('\', \'',$row)."')";
					$res=$DB->Query($sql);
					if($res==TRUE)
					{
					return true;
					}
				}
				//fix me
			}else{return false;}
		}*/
		
function Update($arVariables)
		{
		global $DB;
		//print_r($arVariables);
		foreach ($arVariables as $key=>$val)
		{
		if($key!='VALUES'){$arVariables[$key]=htmlspecialchars(trim($arVariables[$key]));};
		}
			$arVariables= GTdblockprop::Check($arVariables);
			if(!isset($arVariables[0]) && isset($arVariables['ID']))
			{
			
			$ID=$arVariables['ID'];
			unset($arVariables['ID']);
				foreach($arVariables as $keyss=>$arV)
				{
				$K1[]="`".$keyss."`='".$arV."'";
				}
				$sql="UPDATE `g_dblock_props` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'";
				$res=$DB->Query($sql);
				if($res==TRUE)
				{return true;}else{echo "error";}
			}else{return false;}			
		}
}
?>