<?
class GTdblocksubtype
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
			$b[$key]=stripslashes(trim($b[$key]));
				}
		}
		foreach ($b as $key=>$val) if($val==''){unset($b[$key]);}
			global $DB;
			$a=array(
				'ID'=>array('int',TRUE),
				'NAME'=>array('string',TRUE),
				'DESC'=>array('string',FALSE),
				'SORT'=>array('int',FALSE),
				'TYPE_ID'=>array('int',TRUE),
				'PARENT'=>array('int',TRUE),
				'CREATED'=>array('date',FALSE),
				'UPDATED'=>array('date',FALSE));
			
			
			foreach($b as $key=>$val)
			{
			//print_r($this->vara);
			if(!isset($a[$key])) {$c[]=$key;}
			else{
					switch($a[$key][0])
					{
					case 'key':
						$res=$DB->Query("SELECT `ID` FROM `g_dblock_props_values` WHERE `DBLOCK_KEY`='".$val."'");
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
					case 'date':
						if($val==' '){$c[]=$key;}else{$b[$key]=strtotime($val);}
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
			$arVariables['CREATED']=date('Y-m-d H:m:s');
			$arVariables['UPDATED']=$arVariables['CREATED'];
			$arVariables= GTdblocksubtype::Check($arVariables);
			if(!isset($arVariables[0]) && isset($arVariables['NAME']))
			{
				$Value=$arVariables;
				$VKeys = array_keys($Value);
				$sql="INSERT INTO `g_dblock_subtypes` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
				$DB->Query($sql);
				$id=$DB->insertId();
				
				if (!empty($arVariables['PARENT']))
				{
					$SQL="INSERT INTO `g_dblock_subtypes_i` (`ID`,`PARENT`) VALUES ('".$id."','".$arVariables['PARENT']."')";
					$DB->Query($SQL);
				}
				if($id)
				{return $id;}else{return FALSE;}
			}else{return FALSE;}
	}
	
	function Update($arVariables)
	{
		global $DB;	
			$arVariables['UPDATED']=date('Y-m-d H:m:s');
			$arVariables= GTdblocksubtype::Check($arVariables);
			if(!isset($arVariables[0]) && isset($arVariables['ID']))
			{
			$ID=$arVariables['ID'];
			unset($arVariables['ID']);
				foreach($arVariables as $keyss=>$arV)
				{
				if ($arV==''){$arV=' ';}
				$K1[]="`".$keyss."`='".$arV."'";
				}
				$sql="UPDATE `g_dblock_subtypes` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'";
				$res=$DB->Query($sql);
				if($res==TRUE)
				{return TRUE;}else{return FALSE;}
			}else{return FALSE;}
	}
	
 
	function Delete($ID,$THEN=FALSE)
	{
		global $DB;
		if(is_numeric($ID))
		{
			$SQL="DELETE FROM `g_dblock_subtypes_multy` WHERE `ID`='".$ID."'";
			$DB->Query($SQL);
			$sql2="DELETE FROM `g_dblock_subtypes` WHERE `ID`='".$ID."'";
			$res2=$DB->Query($sql2);
			if($THEN=='Y')
			{
				$sql3="SELECT * FROM `g_dblock` WHERE `SUBTYPE`='".$ID."'";
				$res3=$DB->Query($sql3);
				while($row3=$DB->fetchAssoc($res3))
				{
					$Did[]=$row3['ID'];
				}
				if(!empty($Did))
				{
					$sql="DELETE FROM `g_dblock_props_values` WHERE `DBLOCK_ID` IN (".implode(' , ',$Did).")";
					$res=$DB->Query($sql);
					$sql="DELETE FROM `g_dblock_multy_values` WHERE `DBLOCK_ID` IN (".implode(' , ',$Did).")";
					$res=$DB->Query($sql);
				}
				$sql="DELETE FROM `g_dblock` WHERE `SUBTYPE`='".$ID."'";
				$res=$DB->Query($sql);
				$sql="DELETE FROM `g_dblock_multy` WHERE `SUBTYPE`='".$ID."'";
				$res=$DB->Query($sql);
			}
			else
			{
				$sql="UPDATE `g_dblock` SET `SUBTYPE`='0' WHERE `SUBTYPE`='$ID'";
				$res=$DB->Query($sql);
			}
			if($res2==TRUE){return true;}else{return false;}
		}
	}
	function Get_i($P)
	{
		global $DB;
		$sql="SELECT * FROM `g_dblock_subtypes_i` WHERE `PARENT`='".$P."'";
		$res=$DB->Query($sql);
		$arv=array();
		while($row=$DB->fetchAssoc($res))
		{
			$arv['ID']=$row['ID'];
		}
		if(!empty($arv))
		{
			$F=GTdblocksubtype::Get('',$arv);
		}
		return $F;
	}
	
	function Get($t=FALSE,$arv=FALSE,$order=FALSE,$limit=FALSE,$FIVE=FALSE)
	{
		global $DB;
		$MULTY=GTAPP::Conf('multilang_sync');
		if($MULTY==TRUE)
		{
			$OTHER=0;
			$LANG = GTAPP::SiteLang();
			//$LANG = 'kz';
			$Dlang=GToptionlang::Get('',Array('DEFAULT'=>1,'ENABLED'=>1));
			$Dlang=$Dlang[0]['ID'];
			if($Dlang!=$LANG)
			{
				$OTHER=1;
				//$sql['BY'][]= "\n`sub_C`.`ID`<>'0'";
				$sql['BY'][]= "\n`sub_c`.`LANG`='".$LANG."'";						
			}
				unset($arv['FORCE_LANG']);
		}
		if($arv)
		{
			foreach($arv as $key=>$val)
			if(!is_array($val)){if($val=='' || $val==' '){unset($arv[$key]);}}
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
		
		$table['DBLOCK']=Array(
								'DBLOCK_ID'=>'dblock.`ID`', 
								'DBLOCK_TITLE'=>'dblock.`TITLE`', 
								'DBLOCK_DATESTART'=>'dblock.`DATESTART`', 
								'DBLOCK_DATEEND'=>'dblock.`DATEEND`', 
								'DBLOCK_SHORTTEXT'=>'dblock.`SHORTTEXT`', 
								'DBLOCK_FULLTEXT'=>'dblock.`FULLTEXT`', 
								'DBLOCK_ACTIVE'=>'dblock.`ACTIVE`', 
								'DBLOCK_SORT'=>'dblock.`SORT`', 
								'DBLOCK_AUTHOR'=>'dblock.`AUTHOR`', 
								'DBLOCK_SHORTIMG'=>'dblock.`SHORTIMG`',
								'DBLOCK_FULLIMG'=>'dblock.`FULLIMG`',
								'DBLOCK_TAGS'=>'dblock.`TAGS`',
								'DBLOCK_TYPE_ID'=>'dblock.`TYPE_ID`',
								'DBLOCK_CREATED'=>'dblock.`CREATED`',
								'DBLOCK_UPDATED'=>'dblock.`UPDATED`',
								'DBLOCK_DBLOCK_KEY'=>'dblock.`DBLOCK_KEY`',
								'DBLOCK_SUBTYPE'=>'dblock.`SUBTYPE`');
		$table['SUBTYPE']=Array(
								'ID'=>'st.`ID`',
								'NAME'=>'st.`NAME`',
								'DESC'=>'st.`DESC`',
								'TYPE_ID'=>'st.`TYPE_ID`',
								'PARENT'=>'st.`PARENT`',
								'SORT'=>'st.`SORT`',
								'CREATED'=>'st.`CREATED`',
								'UPDATED'=>'st.`UPDATED`');
		$table['PROPS']=Array(
								'PROPS_ID'=>'props.`ID`',
								'PROPS_NAME'=>'props.`NAME`',
								'PROPS_TYPE_ID'=>'props.`TYPE_ID`',
								'PROPS_TYPE'=>'props.`TYPE`',
								'PROPS_ACTIVE'=>'props.`ACTIVE`',
								'PROPS_SORT'=>'props.`SORT`',
								'PROPS_DEFAULT'=>'props.`DEFAULT`',
								'PROPS_REQUIRED'=>'props.`REQUIRED`',
								'PROPS_VALUES'=>'props.`VALUES`',
								'PROPS_MULTIPLE'=>'props.`MULTIPLE`',
								'PROPS_LENGTH'=>'props.`LENGTH`',
								'PROPS_OPTIONS'=>'props.`OPTIONS`',
								'PROPS_PROP_KEY'=>'props.`PROP_KEY`'
								);
		$table['PROPS_VALUES']=Array(
								'PROPS_VALUES_DBLOCK_ID'=>'pv.`DBLOCK_ID`',
								'PROPS_VALUES_PROP_ID'=>'pv.`PROP_ID`',
								'PROPS_VALUES_VALUE'=>'pv.`VALUE`',
								'PROPS_VALUES_VALUE_NUM'=>'pv.`VALUE_NUM`');
								
		if($order)
		{
			if(is_array($order))
			{
				if($table['DBLOCK'][$order[0]]){$ORD=' ORDER BY '.$table['DBLOCK'][$order[0]].' '.$order[1].' ';}
				elseif($table['SUBTYPE'][$order[0]]){$ORD=' ORDER BY '.$table['SUBTYPE'][$order[0]].' '.$order[1].' ';}
				elseif($table['PROPS'][$order[0]]){$ORD=' ORDER BY '.$table['PROPS'][$order[0]].' '.$order[1].' ';}
				elseif($table['PROPS_VALUES'][$order[0]]){$ORD=' ORDER BY '.$table['PROPS_VALUES'][$order[0]].' '.$order[1].' ';}
			}
			else
			{
				if($table['DBLOCK'][$order]){$ORD=' ORDER BY '.$table['DBLOCK'][$order].' ';}
				elseif($table['SUBTYPE'][$order]){$ORD=' ORDER BY '.$table['SUBTYPE'][$order].' ';}
				elseif($table['PROPS'][$order]){$ORD=' ORDER BY '.$table['PROPS'][$order].' ';}
				elseif($table['PROPS_VALUES'][$order]){$ORD=' ORDER BY '.$table['PROPS_VALUES'][$order].' ';}
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
						if($KEY!='DBLOCK')
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
							case 'DBLOCK':
							$BY='BY4';
							break;
							case 'SUBTYPE':
							$BY='BY';
							break;
							case 'PROPS':
							$BY='BY2';
							break;
							case 'PROPS_VALUES':
							$BY='BY2';
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
							case 'DBLOCK':
							$BY='BY4';
							break;
							case 'SUBTYPE':
							$BY='BY';
							break;
							case 'PROPS':
							$BY='BY2';
							break;
							case 'PROPS_VALUES':
							$BY='BY2';
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
					switch($key)
					{
					case 'DBLOCK':
					$tabs=implode(',',$tab[$key]); $TABLES4[]=$tabs;
					break;
					case 'SUBTYPE':
					$tabs=implode(',',$tab[$key]); $TABLES[]=$tabs;
					break;
					case 'PROPS':
					$tabs=implode(',',$tab[$key]); $TABLES3[]=$tabs;
					break;
					case 'PROPS_VALUES':
					$tabs=implode(',',$tab[$key]); $TABLES3[]=$tabs;
					break;
					}
				}
				else{$tab=$t;}
			}
		}
		
		
		$fives['SUBTYPE']=Array('ID'=>'ID');
		$fives['DBLOCK']=Array('DBLOCK_ID'=>'DBLOCK_ID');
		if($FIVE)
		{	
			foreach($FIVE as $key=>$val)
			{
			if($fives['DBLOCK'][$val]){$F5['DBLOCK']=$fives['DBLOCK'][$val];}
			if($fives['SUBTYPE'][$val]){$F5['SUBTYPE']=$fives['SUBTYPE'][$val];}
			}
		}
		//d($sql['BY']);
		if($sql['BY'])
		{$BY=" WHERE ".implode(' AND ',$sql['BY']);}
		
		if(isset($TABLES)){$tabs2='st.`ID`, st.`TYPE_ID`, type.`NAME` as TYPE_NAME, '. implode(', ',$TABLES);}else
		{
			$tabs2='st.`ID`,
			st.`NAME`,
			st.`DESC`,
			st.`TYPE_ID`,
			st.`PARENT`,
			type.`NAME` as TYPE_NAME,
			sub_c.`TITLE` as OTHER,
			sub_c.`DSC` as DSC,
			st.`SORT`,
			st.`CREATED`,
			st.`UPDATED`';
		}
		
		$from2="\nLEFT JOIN `g_dblock_subtypes_multy` as sub_c ON (st.`ID`=sub_c.`ID`)";
		$from=" `g_dblock_subtypes` as st LEFT JOIN `g_dblock_types` as type ON(st.`TYPE_ID`=type.`ID`)";
		$SQL="SELECT ".$tabs2." FROM ".$from.$from2." ".$BY.$ORD.$LIMIT;
		//d($SQL);
		$res = $DB->Query($SQL);
		$ArrRes=array();
		while($row=$DB->fetchAssoc($res))
		{
			if($OTHER==1 && !empty($row['OTHER']))
			{
				$row['NAME']=$row['OTHER'];
				$row['DESC']=$row['DSC'];
			}
			if($F5['SUBTYPE'])
			{
				$ArrRes[$row[$F5['SUBTYPE']]]=$row;
				$ArrMAP[$row['PARENT']]=$row[$F5['SUBTYPE']];
				if($row['PARENT']!=0)
				{
					$DIM[]=$row['PARENT'];
				}
				//$ArrRes[$row[$F5['SUBTYPE']]]['DATABLOCKS']=array();
			}
			else
			{
				$ArrRes[]=$row;
				$ArrMAP[$row['PARENT']]=count($ArrRes)-1;
				if($row['PARENT']!=0)
				{
					$DIM[]=$row['PARENT'];
				}
				//$ArrRes[$ArrMAP[$row['ID']]]['DATABLOCKS']=array();
			}
		}
		if(isset($DIM))
		{
			$DIM=array_unique($DIM);
			$row3=GTdblocksubtype::Get(Array('NAME','ID','PARENT'),Array('ID'=>$DIM));
			foreach($row3 as $key=>$val)
			{
				$ArrRes[$ArrMAP[$row3[0]['ID']]]['PARENT']=$row3[0];
			}
		}
		//GTdblocksubtype::Get();
		if(empty($ArrMAP)) return array();
		//d($ArrRes);
		return $ArrRes;
	}
	function GetLsubtype($ID,$LANG)
	{
		global $DB;
		$SQL="SELECT * FROM `g_dblock_subtypes_multy` WHERE `ID`='$ID' AND `LANG`='$LANG'";
		//d($SQL);
		$res = $DB->Query($SQL);
		while($row=$DB->fetchAssoc($res))
		{
			$ArrRes[]=$row;
		}
		return $ArrRes;
	}
	function OtherLangST($arv)
	{
		global $DB;//d($arv);
		if(!empty($arv))
		{
			$values=array();
			foreach($arv as $key=>$val)
			{
				$val['TITLE']=stripslashes(trim($val['TITLE']));
				$val['DSC']=stripslashes(trim($val['DSC']));
				$values[]="('".$val['ID']."','".$val['TITLE']."','".$val['LANG']."','".$val['DSC']."')";
				$SQL="DELETE FROM `g_dblock_subtypes_multy` WHERE `ID`='".$val['ID']."'";
				$DB->Query($SQL);
			}
			if(!empty($values))
			{
				$SQL="INSERT INTO `g_dblock_subtypes_multy` (`ID`,`TITLE`,`LANG`,`DSC`) VALUES ".implode(',',$values);
				//d($SQL);
				$DB->Query($SQL);
			}
		}
	}
	function Breds($fr)
	{			
			$arv[]=Array('NAME'=>$fr['NAME'],'SUBTYPE'=>$fr['ID']);
			if(is_array($fr['PARENT']))
			{	
				$arv2 = GTdblocksubtype::Breds($fr['PARENT']);
				foreach($arv2 as $key=>$val)
				{
				$arv[]=$arv2[$key];
				}
			}
			return $arv;
	}
}

?>