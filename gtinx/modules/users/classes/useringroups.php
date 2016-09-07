<?
//USRE=GROUP
class GTuseringroups
{
	function Check($b=array())
	{
			$a=array(
				'ID'=>array('int',TRUE),
				'USER_ID'=>array('int',TRUE),
				'GROUP_ID'=>array('int',TRUE),
				'ACTIVE'=>array('boolean',FALSE),
				'ACTIVE_FROM'=>array('date',FALSE),
				'ACTIVE_TO'=>array('date',FALSE),
				'CREATED'=>array('date',TRUE),
				'CREATOR'=>array('int',TRUE));
			
			
			foreach($b as $key=>$val)
			{
			
			if(!isset($a[$key])) {$c[]=$key;}
			else{
					switch($a[$key][0])
					{
					case 'boolean':
						if($val!='1')
						{
						$b[$key]=0;
						}
						break;
					case 'int':
						if(is_array($val))
						{
							$b[$key]=$val;
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
						$val=(string)$val;
						if((string)$val!==$val || $val==' '){$c[]=$key;}else{$b[$key]=$val;}
						break;
					case 'date':
						if($val==' ' || $val=='' || $val==FALSE){$c[]=$key;}else{$b[$key]=strtotime($val);}
						break;
					case 'md5':
						if($val==' '){$c[]=$key;}else{$b[$key]=md5($val);}
						break;
					}
				
				}			
			}
				if($c[0]!='')
				{
					foreach($c as $key=>$val)
					{
					unset($b[$val]);
					}
				}
				return $b;
		
	}
	
	
	
	function AddUser($arVariables)
	{
		global $DB, $APP;
		
		$sql="Delete FROM `g_usergroups` WHERE `USER_ID`='".$arVariables['USER_ID']."'";
		$DB->Query($sql);

		$arVariables['CREATED']=date('Y-m-d H:m:s');
		foreach($arVariables['GROUP_ID'] as $key=>$val)
		{	
			$SQL="SELECT `ID` FROM `g_groups` WHERE `ID`='".$val."'";
			$USE=$DB->QResult($SQL);
			if(!empty($USE))
			{
			$arVar=Array("USER_ID"=>$arVariables['USER_ID'],
						"GROUP_ID"=>$val,
						"ACTIVE_FROM"=>$arVariables['ACTIVE_FROM'][$key],
						"CREATED"=>$arVariables['CREATED'],
						"ACTIVE_TO"=>$arVariables['ACTIVE_TO'][$key]);
			$arVars[]= GTuseringroups::Check($arVar);
			}
		}
		
		foreach($arVars as $key=>$val)
		{
		$Ava[] = '('.implode(', ',$arVars[$key]).')';
		}
		$val = implode(', ',$Ava);
		if(!empty($arVar)){
			$VKeys = array_keys($arVar);
			$sql="INSERT INTO `g_usergroups` (`".implode('`, `',$VKeys)."`) VALUES".$val;
			$res = $DB->Query($sql);
			return $res;
		}
		return true;
	}
	
	function UpdateUser($arVariables)
	{
		global $DB, $APP;
		if(!empty($arVariables['GROUP_ID']))
		{
		$DEL=" AND `GROUP_ID` NOT IN (".implode(' , ',$arVariables['GROUP_ID']).")";
		}
		$sql="Delete FROM `g_usergroups` WHERE `USER_ID`='".$arVariables['USER_ID']."' ".$DEL;
		$DB->Query($sql);
		$arVariables['CREATED']=date('Y-m-d H:m:s');
		foreach($arVariables['GROUP_ID'] as $key=>$val)
		{	
			$SQL="SELECT `ID` FROM `g_groups` WHERE `ID`='".$val."'";
			$USE=$DB->QResult($SQL);
			if(!empty($USE))
			{
			$chSql="SELECT 'USER_ID' FROM `g_usergroups` WHERE `USER_ID`='".$arVariables['USER_ID']."' AND `GROUP_ID`='".$val."'";
			$res=$DB->QResult($chSql);
			if(empty($res))
			{
			$arVar=Array("USER_ID"=>$arVariables['USER_ID'],
						"GROUP_ID"=>$val,
						"ACTIVE_FROM"=>$arVariables['ACTIVE_FROM'][$key],
						"CREATED"=>$arVariables['CREATED'],
						"ACTIVE_TO"=>$arVariables['ACTIVE_TO'][$key]);
			$arVars[]= GTuseringroups::Check($arVar);
			}
			else
			{
				$arVarUp=Array("USER_ID"=>$arVariables['USER_ID'],
						"GROUP_ID"=>$val,
						"ACTIVE_FROM"=>$arVariables['ACTIVE_FROM'][$key],
						
						"ACTIVE_TO"=>$arVariables['ACTIVE_TO'][$key]);
			$arVarsUp[]= GTuseringroups::Check($arVarUp);
			}
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
			$sql="INSERT INTO `g_usergroups` (`".implode('`, `',$VKeys)."`) VALUES".$val;
			$res = $DB->Query($sql);
		}
		if($arVarsUp)
		{
			foreach($arVarsUp as $key=>$val)
			{
				foreach($val as $KEY=>$VAL)
				{
				$k1=array();
				if($VAL!=' ' && !empty($VAL))
					{$k1[] = "`".$KEY."`='".$VAL."'";}
					if($KEY=='USER_ID'){$USERID=$VAL;}
					if($KEY=='GROUP_ID'){$GROUPID=$VAL;}
				}
				if(!empty($k1))
				{
				$sql2="UPDATE `g_usergroups` SET ".implode(', ',$k1)." WHERE `USER_ID`='".$USERID."' AND `GROUP_ID`='".$GROUPID."'";
				$res=$DB->Query($sql2);
				}
			}
		}
	}
	
	function AddGroup($arVariables)
	{
		global $DB, $APP;
		
		$sql="Delete FROM `g_usergroups` WHERE `GROUP_ID`='".$arVariables['GROUP_ID']."'";
		$DB->Query($sql);

		$arVariables['CREATED']=date('Y-m-d H:m:s');
		foreach($arVariables['USER_ID'] as $key=>$val)
		{	
		$SQL="SELECT `ID` FROM `g_users` WHERE `ID`='".$val."'";
		$USE=$DB->QResult($SQL);
		if(!empty($USE))
			{
			$arVar=Array("USER_ID"=>$val,
						"GROUP_ID"=>$arVariables['GROUP_ID'],
						"ACTIVE_FROM"=>$arVariables['ACTIVE_FROM'][$key],
						"CREATED"=>$arVariables['CREATED'],
						"ACTIVE_TO"=>$arVariables['ACTIVE_TO'][$key]);
			$arVars[]= GTuseringroups::Check($arVar);
			}
		}
		
		foreach($arVars as $key=>$val)
		{
		$Ava[] = '('.implode(', ',$arVars[$key]).')';
		}
		$val = implode(', ',$Ava);
		$VKeys = array_keys($arVars[0]);
		echo $sql="INSERT INTO `g_usergroups` (`".implode('`, `',$VKeys)."`) VALUES".$val;
					$res = $DB->Query($sql);
					if($res==TRUE){return TRUE;}else{return FALSE;}
	}
	
	function UpdateGroup($arVariables)
	{
		global $DB, $APP;
		if(!empty($arVariables['USER_ID']))
		{
		$DEL=" AND `USER_ID` NOT IN (".implode(' , ',$arVariables['USER_ID']).")";
		}
		$sql="Delete FROM `g_usergroups` WHERE `GROUP_ID`='".$arVariables['GROUP_ID']."' ".$DEL;
		$DB->Query($sql);
		$arVariables['CREATED']=date('Y-m-d H:m:s');
		foreach($arVariables['USER_ID'] as $key=>$val)
		{	
			$SQL="SELECT `ID` FROM `g_users` WHERE `ID`='".$val."'";
			$USE=$DB->QResult($SQL);
			if(!empty($USE))
			{
			$chSql="SELECT * FROM `g_usergroups` WHERE `USER_ID`='".$val."' AND `GROUP_ID`='".$arVariables['GROUP_ID']."'";
			$res=$DB->QResult($chSql);
			if(empty($res))
			{
			$arVar=Array("USER_ID"=>$val,
						"GROUP_ID"=>$arVariables['GROUP_ID'],
						"ACTIVE_FROM"=>$arVariables['ACTIVE_FROM'][$key],
						"CREATED"=>$arVariables['CREATED'],
						"ACTIVE_TO"=>$arVariables['ACTIVE_TO'][$key]);
			$arVars[]= GTuseringroups::Check($arVar);
			}
			else
			{
				$arVarUp=Array("USER_ID"=>$val,
						"GROUP_ID"=>$arVariables['GROUP_ID'],
						"ACTIVE_FROM"=>$arVariables['ACTIVE_FROM'][$key],
						
						"ACTIVE_TO"=>$arVariables['ACTIVE_TO'][$key]);
			$arVarsUp[]= GTuseringroups::Check($arVarUp);
			}
			}			
		}
		
		if(!empty($arVars))
		{
			foreach($arVars as $key=>$val)
			{
			$Ava[] = '(\''.implode('\',\'',$arVars[$key]).'\')';
			}
			$val = implode(', ',$Ava);
			$VKeys = array_keys($arVars[0]);
			$sql="INSERT INTO `g_usergroups` (`".implode('`, `',$VKeys)."`) VALUES".$val;
			$res = $DB->Query($sql);
		}
		if(!empty($arVarsUp))
		{
			foreach($arVarsUp as $key=>$val)
			{
				foreach($val as $KEY=>$VAL)
				{
				$k1=array();
				if($VAL!=' ' && !empty($VAL))
					{$k1[] = "`".$KEY."`='".$VAL."'";}
					if($KEY=='USER_ID'){$USERID=$VAL;}
					if($KEY=='GROUP_ID'){$GROUPID=$VAL;}
				}
				if(!empty($k1))
				{
				$sql2="UPDATE `g_usergroups` SET ".implode(', ',$k1)." WHERE `USER_ID`='".$USERID."' AND `GROUP_ID`='".$GROUPID."'";
				//unset($k1);
				$res=$DB->Query($sql2);
				}
			}
		}
	}
	
}
?>