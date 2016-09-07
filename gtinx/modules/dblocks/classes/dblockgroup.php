<?
class GTdblockgroup
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
		foreach ($b as $key=>$val) if(empty($val)){unset($b[$key]);}
			$a=array(
				'ID'=>array('int',TRUE),
				'NAME'=>array('string',TRUE),
				'DESC'=>array('string',FALSE),
				'ORDER'=>array('int',FALSE),
				'ACTIVE'=>array('boolean',FALSE),
				'CREATED'=>array('date',FALSE),
				'UPDATED'=>array('date',FALSE));
			
			
			foreach($b as $key=>$val)
			{
			if(!isset($a[$key])) {$c[]=$key;}
			else{
					switch($a[$key][0])
					{
					case 'boolean':
						if($val!='1' || $val=='')
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
			$arVariables['CREATED']=date('Y-m-d H:m:s');
			$arVariables['UPDATED']=$arVariables['CREATED'];
			$arVariables= GTdblockgroup::Check($arVariables);
			if(!isset($arVariables[0]) && isset($arVariables['NAME']))
			{
				$Value=$arVariables;
				$VKeys = array_keys($Value);
				$sql="INSERT INTO `g_dblock_group` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
				$res=$DB->Query($sql);
				if($res==TRUE)
				{return TRUE;}else{return FALSE;}
			}else{return FALSE;}
		}

function Update($arVariables)
		{
		global $DB;	
			$arVariables['UPDATED']=date('Y-m-d H:m:s');
			$arVariables=GTdblockgroup::Check($arVariables);
			if(isset($arVariables['ID']))
			{
			$ID=$arVariables['ID'];
			unset($arVariables['ID']);
				foreach($arVariables as $keyss=>$arV)
				{
				if ($arV==''){$arV=' ';}
				$K1[]="`".$keyss."`='".$arV."'";
				}
				$sql="UPDATE `g_dblock_group` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'";
				$res=$DB->Query($sql);
				if($res==TRUE)
				{return TRUE;}else{return FALSE;}
			}else{return FALSE;}
		}

function Delete($ID)
		{
			global $DB;
			if (!empty($ID)&& is_numeric($ID))
			{
			$R=GTdblockgroup::Get('',Array('ID'=>$ID));
			$sql="Delete FROM `g_dblock_group` WHERE `ID`='".$ID."'";
			$res=$DB->Query($sql);
			foreach($R[0]['TYPES'] as $key=>$val)
				{
					GTdblocktype::Delete($val['TYPE_TYPE_ID']);
				}
			}else{return false;}
		}

/*function Clone($ID=FALSE)
		{
			if (!empty($ID) && is_numeric($ID))
			{
				$this->ID=$ID;
				global $DB;
				$sql="SELECT * FROM `g_dblock_group` WHERE `ID`='".$this->ID."'";
				$res=$DB->Query($sql);
				$row=$DB->fetchAssoc($res);
				if (!empty($row))
				{
					$row['CREATED']=time('Y-m-d H:m:s');
					$keys=array_keys($row);
					foreach ($keys as $key=>$val) if($val==''||$val==FALSE ||$val=='ID') {unset($keys[$key]);unset($row['ID']);}
					$sql="INSERT INTO `g_dblock_group` (`".implode('`, `',$keys)."`) VALUES ('".implode('\', \'',$row)."')";
					$res=$DB->Query($sql);
					if($res==TRUE)
					{
					
					$lastG=$DB->insertId();
					$type="SELECT * FROM `g_dblock_types` WHERE `GROUP_ID`='".$this->ID."'";
					$typeres=$DB->Query($type);
					while($typerow=$DB->fetchAssoc($typeres))
						{
						$this->type=$typerow['ID'];
						$keys=array_keys($typerow);
						foreach ($keys as $key=>$val) if($val==''||$val==FALSE ||$val=='ID') {unset($keys[$key]);unset($typerow['ID']); $typerow['GROUP_ID']=$lastG;}
						$sql="INSERT INTO `g_dblock_types` (`".implode('`, `',$keys)."`) VALUES ('".implode('\', \'',$typerow)."')";
						$res=$DB->Query($sql);
						$lastT=$DB->insertId();
					
						
						$dblock="SELECT * FROM `g_dblock_props` WHERE `TYPE_ID`='".$this->type."'";
						$dblockres=$DB->Query($dblock);
						while($dblockrow=$DB->fetchAssoc($dblockres))
							{
							$keys=array_keys($dblockrow);
							foreach ($keys as $key=>$val) if($val==''||$val==FALSE ||$val=='ID') {unset($keys[$key]);unset($dblockrow['ID']); $dblockrow['TYPE_ID']=$lastT;}
							$sql="INSERT INTO `g_dblock_props` (`".implode('`, `',$keys)."`) VALUES ('".implode('\', \'',$dblockrow)."')";
							$res=$DB->Query($sql);
							}
						}
					}
				}
				//fix me
			}else{return false;}
		}*/
		
function Get($t=FALSE,$arv=FALSE,$order=FALSE,$limit=FALSE,$FIVE=FALSE)
		{
		if($arv){
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
		global $DB;
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
		$table['GROUP']=Array(
								'ID'=>'gr.`ID`',
								'NAME'=>'gr.`NAME`',
								'DESC'=>'gr.`DESC`',
								'ORDER'=>'gr.`ORDER`',
								'ACTIVE'=>'gr.`ACTIVE`',
								'CREATED'=>'gr.`CREATED`',
								'UPDATED'=>'gr.`UPDATED`');
		$table['TYPE']=Array(
								
								'TYPE_GROUP_ID'=>'type.`GROUP_ID`',
								'TYPE_TYPE_ID'=>'type.`ID`',
								'TYPE_NAME'=>'type.`NAME`',
								'TYPE_DESC'=>'type.`DESC`',
								'TYPE_ORDER'=>'type.`ORDER`',
								'TYPE_AUTHOR'=>'type.`AUTHOR`',
								'TYPE_CREATED'=>'type.`CREATED`',
								'TYPE_KEY'=>'type.`TYPE_KEY`',
								'TYPE_UPDATED'=>'type.`UPDATED`');
		$table['SUBTYPE']=Array(
								'SUBTYPE_ID'=>'st.`ID`',
								'SUBTYPE_NAME'=>'st.`NAME`',
								'SUBTYPE_TYPE_ID'=>'st.`TYPE_ID`',
								'SUBTYPE_SORT'=>'st.`SORT`',
								'SUBTYPE_CREATED'=>'st.`CREATED`',
								'SUBTYPE_UPDATED'=>'st.`UPDATED`');
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
		
		if($order){if($table['GROUP'][$order]){$ORD=' ORDER BY '.$table['GROUP'][$order].' ';}}
		
		$fives['DBLOCK']=Array('DBLOCK_ID'=>'DBLOCK_ID');
		$fives['GROUP']=Array('ID'=>'ID');
		$fives['TYPE']=Array('TYPE_TYPE_ID'=>'TYPE_TYPE_ID');
		if($FIVE)
		{	
			foreach($FIVE as $key=>$val)
			{
			
			if($fives['GROUP'][$val]){$F5['GROUP']=$fives['GROUP'][$val];}
			if($fives['DBLOCK'][$val]){$F5['DBLOCK']=$fives['DBLOCK'][$val];}
			if($fives['TYPE'][$val]){$F5['TYPE']=$fives['TYPE'][$val];}
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
						if($KEY=='GROUP' || $KEY=='PROPS' || $KEY=='PROPS_VALUES')
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
							case 'GROUP':
							$BY='BY';
							break;
							case 'TYPE':
							$BY='BY3';
							break;
							case 'DBLOCK':
							$BY='BY4';
							break;
							case 'SUBTYPE':
							$BY='BY4';
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
							case 'GROUP':
							$BY='BY';
							break;
							case 'TYPE':
							$BY='BY3';
							break;
							case 'DBLOCK':
							$BY='BY4';
							break;
							case 'SUBTYPE':
							$BY='BY4';
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
					case 'GROUP':
					$tabs=implode(',',$tab[$key]); $TABLES[]=$tabs;
					break;
					case 'TYPE':
					$tabs=implode(',',$tab[$key]); $TABLES2[]=$tabs;
					break;
					case 'DBLOCK':
					$tabs=implode(',',$tab[$key]); $TABLES4[]=$tabs;
					break;
					case 'SUBTYPE':
					$tabs=implode(',',$tab[$key]); $TABLES4[]=$tabs;
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

	if($sql['BY'])
		{$BY=" WHERE ".implode(' AND ',$sql['BY']);}
		
		if(isset($TABLES)){$tabs2='gr.`ID`, '. implode(', ',$TABLES);}else
		{$tabs2='
		gr.`ID`,
		gr.`NAME`,
		gr.`DESC`,
		gr.`ORDER`,
		gr.`ACTIVE`,
		gr.`CREATED`,
		gr.`UPDATED`		
		';}
		

		$from="`g_dblock_group` as gr";
		$SQL="SELECT ".$tabs2." FROM ".$from." ".$BY.$ORD.$LIMIT;
		$res = $DB->Query($SQL);
		$ArrRes=array();
		while($row=$DB->fetchAssoc($res))
		{
			if($F5['GROUP'])
			{
			$ArrRes[$row[$F5['GROUP']]]=$row;
			$ArrMAP[$row['ID']]=$row[$F5['GROUP']];
			$ArrRes[$ArrMAP[$row['ID']]]['TYPES']=array();
			}else
			{
			$ArrRes[]=$row;
			$ArrMAP[$row['ID']]=count($ArrRes)-1;
			$ArrRes[$ArrMAP[$row['ID']]]['TYPES']=array();
			}
		}
		if(empty($ArrMAP)) return array();
		$dbg_id=array_keys($ArrMAP);
		
		if($sql['BY3'])
		{
			$BY3=" WHERE ".implode(' AND ',$sql['BY2'])." AND type.`GROUP_ID` IN (".implode(' , ',$dbg_id).")";
		}
		else
		{
			$BY3=" WHERE type.`GROUP_ID` IN (".implode(' , ',$dbg_id).")";
		}
		if(isset($TABLES2)){$tabs2='type.`ID` as TYPE_TYPE_ID, type.`GROUP_ID` as TYPE_GROUP_ID, '.implode(', ',$TABLES2);}
		else
		{
			$tabs2='type.`GROUP_ID` as TYPE_GROUP_ID,
			type.`ID` as TYPE_TYPE_ID,
			type.`NAME` as TYPE_NAME,
			type.`DESC` as TYPE_DESC,
			type.`ORDER` as TYPE_ORDER,
			type.`AUTHOR` as TYPE_AUTHOR,
			type.`CREATED` as TYPE_CREATED,
			type.`UPDATED` as TYPE_UPDATED';
		}
		$from=" `g_dblock_types` as type";
		$SQL2="SELECT ".$tabs2." FROM ".$from." ".$BY3;
		$res2 = $DB->Query($SQL2);
		while($row2=$DB->fetchAssoc($res2))
		{			
				if(isset($ArrMAP[$row2['TYPE_GROUP_ID']]))
				{
					if(($F5['TYPE']) && $row2[$F5['TYPE']]!='')
					{
						$ArrRes[$ArrMAP[$row2['TYPE_GROUP_ID']]]['TYPES'][$row2[$F5['TYPE']]]=$row2;
						$ArrMap2[$row2['TYPE_TYPE_ID']]=array('COUNT'=>$row2[$F5['TYPE']], 'GROUP'=>$ArrMAP[$row2['TYPE_GROUP_ID']],'TYPE'=>$row2['TYPE_TYPE_ID']);
						//$ArrRes[$ArrMAP[$row2['TYPE_GROUP_ID']]]['TYPES'][$ArrMap2[$row2['TYPE_TYPE_ID']]['COUNT']]['DATABLOCKS']=array();
					}else
					{
						$ArrRes[$ArrMAP[$row2['TYPE_GROUP_ID']]]['TYPES'][]=$row2;
						$count=count($ArrRes[$ArrMAP[$row2['TYPE_GROUP_ID']]]['TYPES'])-1;
						$ArrMap2[$row2['TYPE_TYPE_ID']]=array('COUNT'=>$count, 'GROUP'=>$ArrMAP[$row2['TYPE_GROUP_ID']],'TYPE'=>$row2['TYPE_TYPE_ID']);
						//$ArrRes[$ArrMAP[$row2['TYPE_GROUP_ID']]]['TYPES'][$ArrMap2[$row2['TYPE_TYPE_ID']]['COUNT']]['DATABLOCKS']=array();
					}
				}			
		}
		return $ArrRes;
		if(empty($ArrMap2)) return $ArrRes;
		$dbt_id=array_keys($ArrMap2);
		if($sql['BY4'])
		{$BY4=" WHERE ".implode(' AND ',$sql['BY4'])." AND dblock.`TYPE_ID` IN (".implode(' , ',$dbt_id).")";}
		else
		{
		$BY4=" WHERE dblock.`TYPE_ID` IN (".implode(' , ',$dbt_id).")";
		}
		if(isset($TABLES4)){$tabs4='dblock.`ID` as DBLOCK_ID,dblock.`TYPE_ID` as DBLOCK_TYPE_ID, '.implode(', ',$TABLES4);}
		else
		{
		$tabs4='	dblock.`ID` as DBLOCK_ID, 
						dblock.`TITLE` as DBLOCK_TITLE, 
						dblock.`DATESTART` as DBLOCK_DATESTART, 
						dblock.`DATEEND` as DBLOCK_DATEEND, 
						dblock.`SHORTTEXT` as DBLOCK_SHORTTEXT, 
						dblock.`FULLTEXT` as DBLOCK_FULLTEXT, 
						dblock.`ACTIVE` as DBLOCK_ACTIVE, 
						dblock.`SORT` as DBLOCK_SORT, 
						dblock.`AUTHOR` as DBLOCK_AUTHOR, 
						dblock.`SHORTIMG` as DBLOCK_SHORTIMG,
						dblock.`FULLIMG` as DBLOCK_FULLIMG,
						dblock.`TAGS` as DBLOCK_TAGS,
						dblock.`TYPE_ID` as DBLOCK_TYPE_ID,
						dblock.`CREATED` as DBLOCK_CREATED,
						dblock.`UPDATED` as DBLOCK_UPDATED,
						dblock.`SUBTYPE` as DBLOCK_SUBTYPE,
						dblock.`DBLOCK_KEY` as DBLOCK_DBLOCK_KEY,
						st.`ID` as SUBTYPE_ID,
						st.`NAME` as SUBTYPE_NAME,
						st.`TYPE_ID` as SUBTYPE_TYPE_ID,
						st.`CREATED` as SUBTYPE_CREATED,
						st.`UPDATED` as SUBTYPE_UPDATED';
		}
		$from=" `g_dblock` as dblock LEFT JOIN `g_dblock_subtypes` as st ON (st.`ID`=dblock.`SUBTYPE`)";
		$SQL4="SELECT ".$tabs4." FROM ".$from." ".$BY4;
		$res4 = $DB->Query($SQL4);
		while($row4=$DB->fetchAssoc($res4))
		{			
				if(isset($ArrMap2[$row4['DBLOCK_TYPE_ID']]))
				{
					if(($F5['DBLOCK']) && $row4[$F5['DBLOCK']]!='')
					{
					$ArrRes[$ArrMap2[$row4['DBLOCK_TYPE_ID']]['GROUP']]['TYPES'][$ArrMap2[$row4['DBLOCK_TYPE_ID']]['COUNT']]['DATABLOCKS'][$row4[$F5['DBLOCK']]]=$row4;
					$COUNT = $row4[$F5['DBLOCK']];
					$ArrRes[$ArrMap2[$row4['DBLOCK_TYPE_ID']]['GROUP']]['TYPES'][$ArrMap2[$row4['DBLOCK_TYPE_ID']]['COUNT']]['DATABLOCKS'][$COUNT]['PROPERTIES']=array();
					$ArrMap3[$row4['DBLOCK_ID']]=Array('COUNT'=>$COUNT,'GROUP'=>$ArrMap2[$row4['DBLOCK_TYPE_ID']]['GROUP'],'TYPE'=>$ArrMap2[$row4['DBLOCK_TYPE_ID']]['COUNT']);
					}else
					{
					$ArrRes[$ArrMap2[$row4['DBLOCK_TYPE_ID']]['GROUP']]['TYPES'][$ArrMap2[$row4['DBLOCK_TYPE_ID']]['COUNT']]['DATABLOCKS'][]=$row4;
					$COUNT = count($ArrRes[$ArrMap2[$row4['DBLOCK_TYPE_ID']]['GROUP']]['TYPES'][$ArrMap2[$row4['DBLOCK_TYPE_ID']]['COUNT']]['DATABLOCKS'])-1;
					$ArrRes[$ArrMap2[$row4['DBLOCK_TYPE_ID']]['GROUP']]['TYPES'][$ArrMap2[$row4['DBLOCK_TYPE_ID']]['COUNT']]['DATABLOCKS'][$COUNT]['PROPERTIES']=array();
					$ArrMap3[$row4['DBLOCK_ID']]=Array('COUNT'=>$COUNT,'GROUP'=>$ArrMap2[$row4['DBLOCK_TYPE_ID']]['GROUP'],'TYPE'=>$ArrMap2[$row4['DBLOCK_TYPE_ID']]['COUNT']);
					}
				}			
		}
		
		//if(empty($ArrMap3)) 
		
		$db_id=array_keys($ArrMap3);
		if($sql['BY2'])
		{$BY2=" WHERE ".implode(' AND ',$sql['BY2'])." AND `DBLOCK_ID` IN (".implode(' , ',$db_id).")";}
		else
		{
		$BY2=" WHERE `DBLOCK_ID` IN (".implode(' , ',$db_id).")";
		}
		if(isset($TABLES3)){$tabs3='pv.`DBLOCK_ID`, '.implode(', ',$TABLES3);}
		else
		{
		$tabs3='*';
		}
		$from=" `g_dblock_props_values` as pv LEFT JOIN `g_dblock_props` as props ON (pv.`PROP_ID`=props.`ID`)";
		$SQL2="SELECT ".$tabs3." FROM ".$from." ".$BY2;
		$res2 = $DB->Query($SQL2);
		while($row2=$DB->fetchAssoc($res2))
		{			
				if(isset($ArrMap3[$row2['DBLOCK_ID']]))
				{
					$ArrRes[$ArrMap3[$row2['DBLOCK_ID']]['GROUP']]['TYPES'][$ArrMap3[$row2['DBLOCK_ID']]['TYPE']]['DATABLOCKS'][$ArrMap3[$row2['DBLOCK_ID']]['COUNT']]['PROPERTIES'][]=$row2;
				}			
		}
		
		return $ArrRes;
	}
}
?>