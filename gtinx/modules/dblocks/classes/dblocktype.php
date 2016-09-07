<?
class GTdblocktype
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
				'GROUP_ID'=>array('must_have',TRUE),
				'NAME'=>array('string',TRUE),
				'DESC'=>array('string',FALSE),
				'ORDER'=>array('int',FALSE),
				'AUTHOR'=>array('int',TRUE),
				'CREATED'=>array('date',FALSE),
				'TYPE_KEY'=>array('key',FALSE),
				'UPDATED'=>array('date',FALSE));
			foreach($b as $key=>$val)
			{
			
			if(!isset($a[$key])) {$c[]=$key;}
			else{
					switch($a[$key][0])
					{
					case 'must_have':
						if(is_numeric($val))
						{
							$val=(int)$val;
							//$res='';
							$res=$DB->QResult("SELECT `ID` FROM `g_dblock_group` WHERE `ID`='".$val."'");
							if(!empty($res))
							{
							echo $key.'=>'.$val;
							$b[$key]=$res;
							}
							else
							{
							$c[]=$key;
							die('error GROUP_ID Not FINDED');
							}
						}
						else
							{
							$c[]=$key;
							die('error GROUP_ID Not FINDED');
							}
						break;
					case 'boolean':
						if($val!='1')
						{
						$b[$key]='0';
						}
						break;
					case 'key':
						$res=$DB->Query("SELECT `ID` FROM `g_dblock_types` WHERE `TYPE_KEY`='".$val."'");
						$row=$DB->fetchAssoc($res);
						if($row['ID']==NULL)
						{
						$val=(string)$val;
						if((string)$val!==$val || $val==' '){$c[]=$key;}else{$b[$key]=$val;}
						}else{$c[]=$key;}
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
	
	
function Add($arVariables,$arVariables2=FALSE)
		{
			global $DB, $APP;	
			$arVariables['CREATED']=date('Y-m-d H:m:s');
			$arVariables['UPDATED']=$arVariables['CREATED'];
			$arVariables['AUTHOR']=$APP->GetCurrentUserID();
			$arVariables= GTdblocktype::Check($arVariables); 
			$IG=$DB->QResult("SELECT `ID` FROM `g_dblock_group` WHERE `ID`='".$arVariables['GROUP_ID']."'");
			if(!empty($IG))
			{
				if(!isset($arVariables[0]) && isset($arVariables['NAME']) && isset($arVariables['GROUP_ID']))
				{
					$VKeys = array_keys($arVariables);
					$sql="INSERT INTO `g_dblock_types` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$arVariables)."')";
					$res=$DB->Query($sql);
					$ID=$DB->insertId();
				}
			
				if(!empty($ID) && !empty($arVariables2))
				{	
					foreach($arVariables2 as $key=>$val)
					{
						$val['TYPE_ID']=$ID;
						$val= GTdblockprop::Check($val);			
						if(!isset($val[0]) && isset($val['NAME']))
						{
							$Value=$val;
							$VKeys = array_keys($Value);
							$sql="INSERT INTO `g_dblock_props` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
							$res=$DB->Query($sql);
							$id=$DB->insertId();
						}
						/*if($val['TYPE']=='DBLOCK' && $val['MULTIPLE']==1)
						{
							$VV = explode("\n", $val['VALUES']);
							foreach($VV as $Vkey=>$Vval)
							{
								$Vn=(int)$Vval;
								$sql="INSERT INTO `g_dblock_multy_values` (`TYPE_ID`,`PROP_ID`,`VALUE`,`VALUE_NUM`) VALUES ('".$val['TYPE_ID']."','".$id."','".$Vval."','".$Vn."')";
								$res=$DB->Query($sql);
							}
						}*/
					}
				}
			}
			
			if(!empty($ID))
			{return $ID;}else{return FALSE;}
		}
		
function Delete($ID=FALSE)
		{
			global $DB;
			if (!empty($ID)&& is_numeric($ID))
			{
				$sql="Delete FROM `g_dblock_types` WHERE `ID`='".$ID."'";
				$res=$DB->Query($sql);
				//d($R);
				$res=$DB->Query("SELECT `ID` FROM `g_dblock` WHERE `TYPE_ID`='".$ID."'");
				while($row=$DB->fetchAssoc($res))
				{
					$Did[]=$row['ID'];
				}
				$DB->Query("Delete FROM `g_dblock` WHERE `TYPE_ID`='".$ID."'");
				$DB->Query("Delete FROM `g_dblock_multy` WHERE `TYPE_ID`='".$ID."'");
				if(!empty($Did))
				{
					$DB->Query("Delete FROM `g_dblock_props_values` WHERE `DBLOCK_ID` IN (".implode(' , ',$Did).")");
					$DB->Query("Delete FROM `g_dblock_multy_values` WHERE `DBLOCK_ID` IN (".implode(' , ',$Did).")");
				}
				$DB->Query("Delete FROM `g_dblock_props` WHERE `TYPE_ID`='".$ID."'");
			}else{return false;}
		}
function Cloner($arVar=FALSE)
		{
			global $DB;
			$SQL="INSERT `g_dblock_types` (`GROUP_ID`,`NAME`,`DESC`,`ORDER`,`AUTHOR`,`CREATED`,`UPDATED`) 
			SELECT '".$arVar['GROUP_ID'][0]."','".$arVar['NAME']."',`DESC`,`ORDER`,`AUTHOR`,`CREATED`,`UPDATED` FROM `g_dblock_types` WHERE `ID`='".$arVar['ID']."'";
			$DB->Query($SQL);
			$TYPE_ID=$DB->insertId();
			
			$res=$DB->Query("SELECT `ID` FROM `g_dblock_props` WHERE `TYPE_ID`='".$arVar['ID']."'");
			while($row=$DB->fetchAssoc($res))
			{	
				$PROP[]=$row;
			}
			//d($PROP);
			if(!empty($PROP))
			{
				$PROPS=array();
				foreach($PROP as $key=>$val)
				{
					$SQL2="INSERT `g_dblock_props` (`TYPE_ID`,`NAME`,`TYPE`,`SORT`,`DEFAULT`,`ACTIVE`,`REQUIRED`,`VALUES`,`MULTIPLE`,`LENGTH`,`OPTIONS`)
					SELECT '".$TYPE_ID."',`NAME`,`TYPE`,`SORT`,`DEFAULT`,`ACTIVE`,`REQUIRED`,`VALUES`,`MULTIPLE`,`LENGTH`,`OPTIONS` FROM `g_dblock_props` 
					WHERE `TYPE_ID`='".$arVar['ID']."' AND ID='".$val['ID']."'";
					$DB->Query($SQL2);
					$PROP_ID=$DB->insertId();
					//$PROP_ID=1;
					$PROPS[$val['ID']]=Array('PROP_ID'=>$PROP_ID);
				}
			}
			//d($PROPS);
			//$DBLOCK=GTdblock::Get(Array('ID'),Array('TYPE_ID'=>$arVar['ID']));
			//d($DBLOCK);
			$res=$DB->Query("SELECT `ID` FROM `g_dblock` WHERE `TYPE_ID`='".$arVar['ID']."'");
			while($row=$DB->fetchAssoc($res))
			{	
				$DBLOCK[]=$row;
			}
			if(!empty($DBLOCK))
			{
				$PR_V=array();
				foreach($DBLOCK as $key=>$val)
				{
					$SQL3="INSERT `g_dblock` 
					(`TITLE`,`DATESTART`,`DATEEND`,`SHORTTEXT`,`FULLTEXT`,`SHORTTEXT_TYPE`,`FULLTEXT_TYPE`,`ACTIVE`,`SORT`,`AUTHOR`,`SHORTIMG`,`FULLIMG`,`TAGS`,`CREATED`,`SUBTYPE`,`TYPE_ID`)
					SELECT
					 `TITLE`,`DATESTART`,`DATEEND`,`SHORTTEXT`,`FULLTEXT`,`SHORTTEXT_TYPE`,`FULLTEXT_TYPE`,`ACTIVE`,`SORT`,`AUTHOR`,`SHORTIMG`,`FULLIMG`,`TAGS`,`CREATED`,`SUBTYPE`,'".$TYPE_ID."'
					FROM `g_dblock` 
					WHERE ID='".$val['ID']."'";
					$DB->Query($SQL3);
					$DBLOCK_ID=$DB->insertId();
					//$DBLOCK_ID=1;
					
					if(!empty($PROPS))
					{
						foreach($PROPS as $PK=>$PV)
						{
							$YS='';
							$YS=$DB->QResult("SELECT `PROP_ID` FROM `g_dblock_props_values` WHERE `DBLOCK_ID`='".$val['ID']."' AND `PROP_ID`='".$PK."'");
							if(!empty($YS))
							{
								$PR_V[]=Array('PROP_ID'=>$PV['PROP_ID'],'DBLOCK_ID'=>$DBLOCK_ID,'DBL'=>$val['ID'],'PRP'=>$PK);
							}
						}
					}
				}
			}
			//d($PR_V);
			if($PR_V)
			{
				foreach($PR_V as $key=>$val)
				{
					$SQL4="INSERT `g_dblock_props_values` (`DBLOCK_ID`,`PROP_ID`,`VALUE`,`VALUE_NUM`) 
					SELECT '".$val['DBLOCK_ID']."','".$val['PROP_ID']."',`VALUE`,`VALUE_NUM`
					FROM `g_dblock_props_values` 
					WHERE `DBLOCK_ID`='".$val['DBL']."' AND `PROP_ID`='".$val['PRP']."'";
					$DB->Query($SQL4);
				}
			}
		}
		
function Update($arVariables)
		{
		global $DB, $APP;	
			$arVariables['AUTHOR']=$APP->GetCurrentUserID();
			$arVariables['UPDATED']=date('Y-m-d H:m:s');
			$arVariables= GTdblocktype::Check($arVariables);
			$IS=$DB->QResult("SELECT `GROUP_ID` FROM `g_dblock_types` WHERE `ID`='".$arVariables['ID']."'");
			if(!empty($IS))
			{
				if(isset($arVariables['ID']))
				{
					$ID=$arVariables['ID'];
					$r=$DB->Query("SELECT * FROM `g_dblock_types` WHERE `ID`='".$ID."'");
					$Row=$DB->fetchAssoc($r);
					foreach($row as $key=>$val)
					{
						if($arVariables[$key]==$val)
						{
							unset($arVariables[$key]);
						}
					}
					foreach($arVariables as $keyss=>$arV)
					{
						if ($arV==''){$arV=' ';}
						$K1[]="`".$keyss."`='".$arV."'";
					}
					$sql="UPDATE `g_dblock_types` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'";
					$res=$DB->Query($sql);
					if($res==TRUE)
					{
						return TRUE;
					}
					else
					{
						return FALSE;
					}
				}else{return FALSE;}
			}else{return FALSE;}
		}
		
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
		$table['TYPE']=Array(
								
								'GROUP_ID'=>'type.`GROUP_ID`',
								'GROUP_NAME'=>'gr.`NAME` as `GROUP_NAME`',
								'ID'=>'type.`ID`',
								'NAME'=>'type.`NAME`',
								'DESC'=>'type.`DESC`',
								'ORDER'=>'type.`ORDER`',
								'AUTHOR'=>'type.`AUTHOR`',
								'CREATED'=>'type.`CREATED`',
								'TYPE_KEY'=>'type.`TYPE_KEY`',
								'UPDATED'=>'type.`UPDATED`');
		$table['SUBTYPE']=Array(
								'SUBTYPE_ID'=>'st.`ID`',
								'SUBTYPE_NAME'=>'st.`NAME`',
								'SUBTYPE_TYPE_ID'=>'st.`TYPE_ID',
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
		
		//if($order){if($table['TYPE'][$order]){$ORD=' ORDER BY '.$table['TYPE'][$order].' ';}}
		if($order)
		{
			if(is_array($order))
			{
				if($table['TYPE'][$order[0]]){$ORD=' ORDER BY '.$table['TYPE'][$order[0]].' '.$order[1].' ';}
				elseif($table['SUBTYPE'][$order[0]]){$ORD=' ORDER BY '.$table['SUBTYPE'][$order[0]].' '.$order[1].' ';}
				elseif($table['DBLOCK'][$order[0]]){$ORD=' ORDER BY dblock.`'.$order[0].'` '.$order[1].' ';}
			}
			else
			{
				if($table['TYPE'][$order]){$ORD=' ORDER BY '.$table['TYPE'][$order].' ';}
				elseif($table['SUBTYPE'][$order]){$ORD=' ORDER BY '.$table['SUBTYPE'][$order].' ';}
				elseif($table['DBLOCK'][$order]){$ORD=' ORDER BY dblock.`'.$order.'` ';}
			}
		}
		$fives['TYPE']=Array('ID'=>'ID');
		$fives['DBLOCK']=Array('DBLOCK_ID'=>'DBLOCK_ID');
		if($FIVE)
		{	
			foreach($FIVE as $key=>$val)
			{
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
						if($KEY=='TYPE' || $KEY=='PROPS' || $KEY=='PROPS_VALUES')
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
							case 'TYPE':
							$BY='BY';
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
							case 'TYPE':
							$BY='BY';
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
	//d($tab);
		foreach($table as $key=>$val)
		{
			if(isset($tab[$key]))
			{
				if(is_array($t))
				{
					switch($key)
					{
					case 'TYPE':
					$tabs=implode(',',$tab[$key]); $TABLES[]=$tabs;
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
		
		if(isset($TABLES)){$tabs2='type.`ID`, type.`GROUP_ID`, '. implode(', ',$TABLES);}else
		{$tabs2='
		type.`GROUP_ID`,
		gr.`NAME` as GROUP_NAME,
		type.`ID`,
		type.`NAME`,
		type.`DESC`,
		type.`ORDER`,
		type.`AUTHOR`,
		type.`CREATED`,
		type.`TYPE_KEY`,
		type.`UPDATED`		
		';}
		
		
		$from=" `g_dblock_types` as type LEFT JOIN `g_dblock_group` as gr ON (type.`GROUP_ID`=gr.`ID`)";
		$SQL="SELECT ".$tabs2." FROM ".$from." ".$BY.$ORD.$LIMIT;
		$res = $DB->Query($SQL);
		$ArrRes=array();
		while($row=$DB->fetchAssoc($res))
		{
			if($F5['TYPE'])
			{
			$ArrRes[$row[$F5['TYPE']]]=$row;
			$ArrMAP[$row['ID']]=$row[$F5['TYPE']];
			//$ArrRes[$row[$F5['TYPE']]]['DATABLOCKS']=array();
			$ArrRes[$row[$F5['TYPE']]]['PROPS']=array();
			}
			else
			{
			$ArrRes[]=$row;
			$ArrMAP[$row['ID']]=count($ArrRes)-1;
			//$ArrRes[$ArrMAP[$row['ID']]]['DATABLOCKS']=array();
			$ArrRes[$ArrMAP[$row['ID']]]['PROPS']=array();
			}
		}
		if(empty($ArrMAP)) return array();
		$dbt_id=array_keys($ArrMAP);
		if(!empty($dbt_id))
		{
			/*if($sql['BY4'])
			{$BY4=" WHERE ".implode(' AND ',$sql['BY4'])." AND dblock.`TYPE_ID` IN (".implode(' , ',$dbt_id).")";}
			else
			{
			$BY4=" WHERE dblock.`TYPE_ID` IN (".implode(' , ',$dbt_id).")";
			}
			if(isset($TABLES4)){$tabs4='dblock.`ID` as DBLOCK_ID,dblock.`TYPE_ID` as DBLOCK_TYPE_ID, '.implode(', ',$TABLES4);}
			else
			{
			$tabs2='	dblock.`ID` as DBLOCK_ID, 
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
			$SQL4="SELECT ".$tabs2." FROM ".$from." ".$BY4;
			$res4 = $DB->Query($SQL4);
			while($row2=$DB->fetchAssoc($res4))
			{			
					if(isset($ArrMAP[$row2['DBLOCK_TYPE_ID']]))
					{
						if($F5['DBLOCK'] && $row2[$F5['DBLOCK']]!=NULL)
						{
							$ArrRes[$ArrMAP[$row2['DBLOCK_TYPE_ID']]]['DATABLOCKS'][$row2[$F5['DBLOCK']]]=$row2;
							$ArrMap2[$row2['DBLOCK_ID']]=array('COUNT'=>$row2[$F5['DBLOCK']], 'TYPE'=>$ArrMAP[$row2['DBLOCK_TYPE_ID']]);
							$ArrRes[$ArrMAP[$row2['DBLOCK_TYPE_ID']]]['DATABLOCKS'][$row2[$F5['DBLOCK']]]['PROPERTIES']=array();
						}
						else
						{
						$ArrRes[$ArrMAP[$row2['DBLOCK_TYPE_ID']]]['DATABLOCKS'][]=$row2;
						$count=count($ArrRes[$ArrMAP[$row2['DBLOCK_TYPE_ID']]]['DATABLOCKS'])-1;
						$ArrMap2[$row2['DBLOCK_ID']]=array('COUNT'=>$count, 'TYPE'=>$ArrMAP[$row2['DBLOCK_TYPE_ID']]);
						$ArrRes[$ArrMAP[$row2['DBLOCK_TYPE_ID']]]['DATABLOCKS'][$count]['PROPERTIES']=array();
						}
					}			
			}*/

			$from=" `g_dblock_props` as props";
			$SQL4="SELECT * FROM ".$from." WHERE props.`TYPE_ID` IN (".implode(' , ',$dbt_id).")";
			$res4 = $DB->Query($SQL4);
			while($row2=$DB->fetchAssoc($res4))
			{			
					if(isset($ArrMAP[$row2['TYPE_ID']]))
					{
						$ArrRes[$ArrMAP[$row2['TYPE_ID']]]['PROPS'][]=$row2;
					}			
			}
		
		}
		return $ArrRes;
	}
}
?>