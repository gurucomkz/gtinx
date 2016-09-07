<?
Class GTuserprivs
{
	function Check($b=array())
	{
			global $DB;
			$a=array(
				'GROUP_ID'=>array('int',TRUE),
				'ACTIVE'=>array('boolean',FALSE),
				'ACTIVE_FROM'=>array('date',FALSE),
				'ACTIVE_TO'=>array('date',FALSE),
				'NAME'=>array('string',TRUE),
				'SUBSYSTEM'=>array('string',TRUE)			
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
	
	function GetMode()
	{
	$dir=GTROOT.'/modules/';
	$scan = scandir($dir);
		foreach($scan as $key=>$val)
		{
			if($val=='.svn' || $val=='.' || $val=='..'){unset ($scan[$key]);}
			else
			{
			if (file_exists($dir.$val.'/.prop.php'))
				{
				include($dir.$val.'/.prop.php');
				if($arrModProperties['PRIVS'])
					{
					$module[$val]=Array($arrModProperties['PRIVS']);
					}
				}
			}
		}
	return $module;
	}
	
	function Get($GROUP_ID,$SUBSYSTEM)
	{ 
		global $DB;
		$SQL="SELECT * FROM `g_groups_privs` WHERE `GROUP_ID`='".$GROUP_ID."' AND `SUBSYSTEM`='".$SUBSYSTEM."'";
		$res=$DB->Query($SQL);
		$row=$DB->fetchAssoc($res);
		if($row['GROUP_ID']!='')
		{
		return $row;
		}
		else
		{ 
		return 2;
		}
	}
	
	function Add($arVar)
	{
	
	//ERROR_REPORTING(E_ALL);
	global $DB;
	foreach($arVar as $key=>$val)
		{
			$SQL="SELECT * FROM `g_groups_privs` WHERE `GROUP_ID`='".$arVar[$key]['GROUP_ID']."' AND `SUBSYSTEM`='".$arVar[$key]['SUBSYSTEM']."'";
			$res=$DB->Query($SQL);
			$row=$DB->fetchAssoc($res);
			if($row['GROUP_ID']=='')
			{
			$arVars[]=GTuserprivs::Check($arVar[$key]);
			}
			else
			{
			$arVarUp[]=GTuserprivs::Check($arVar[$key]);
			}
		}
	
		if($arVars)
		{ 
			foreach($arVars as $key=>$val)
			{
			$Ava[] = '(\''.implode('\',\'',$arVars[$key]).'\')';
			}
			$val = implode(', ',$Ava);
			$VKeys = array_keys($arVars[0]);
			$sql="INSERT INTO `g_groups_privs` (`".implode('`, `',$VKeys)."`) VALUES".$val;
			$res = $DB->Query($sql);
		}
		if($arVarUp)
		{
			foreach($arVarUp as $key=>$val)
			{
				foreach($val as $KEY=>$VAL)
				{
				if($VAL!=' ' && !empty($VAL) && $KEY!='SUBSYSTEM' && $KEY!='GROUP_ID')
					{$k1[] = "`".$KEY."`='".$VAL."'";}
					if($KEY=='SUBSYSTEM'){$SUBSYSTEM=$VAL;}
					if($KEY=='GROUP_ID'){$GROUPID=$VAL;}
				}
				if($k1)
				{
				$sql2="UPDATE `g_groups_privs` SET ".implode(', ',$k1)." WHERE `SUBSYSTEM`='".$SUBSYSTEM."' AND `GROUP_ID`='".$GROUPID."'";
				unset($k1);
				$res=$DB->Query($sql2);
				}
			}
		}
		cacheSetVars('usergroups'.$iUserId,false);
		cacheSetVars('userprivs'.$iUserId,false);
	}

}
?>