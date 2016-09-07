<?
if(!defined('COUNT_DEFINED_VALUE')){define('COUNT_DEFINED_VALUE',rand());}

class GTdblock
{
	function Check($b=array())
	{
		global $DB;
		foreach ($b as $key=>$val)
		{
			
			if($key=='SHORTTEXT' || $key=='FULLTEXT')
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
			else
			{
				if(preg_match('/[^\\\\]\'/',$b[$key]))
				{
					$b[$key]=addslashes($b[$key]);
				}
				$b[$key]=htmlspecialchars(trim($b[$key]));
			}
		}
		foreach ($b as $key=>$val) if($val==''){unset($b[$key]);}
		
		$a=array(
			'ID'=>array('int',TRUE),
			'TITLE'=>array('string',TRUE),
			'DATESTART'=>array('date',FALSE),
			'DATEEND'=>array('date',FALSE),
			'SHORTTEXT'=>array('string',FALSE),
			'SHORTTEXT_TYPE'=>array('string',FALSE),
			'FULLTEXT'=>array('string',FALSE),
			'FULLTEXT_TYPE'=>array('string',FALSE),
			'ACTIVE'=>array('boolean',FALSE),
			'SORT'=>array('int',FALSE),
			'AUTHOR'=>array('int',TRUE),
			'SHORTIMG'=>array('string',FALSE),
			'FULLIMG'=>array('string',FALSE),
			'TAGS'=>array('string',FALSE),
			'TYPE_ID'=>array('must_have',TRUE),
			'SUBTYPE'=>array('int',FALSE),
			'CREATED'=>array('date',TRUE),
			'DBLOCK_KEY'=>array('key',FALSE),
			'SEARCH'=>array('boolean',FALSE),
			'LANG'=>array('string',FALSE),
			'LINK'=>array('string',FALSE),
			'INDEXED'=>array('boolean',FALSE),
			'ININDEX'=>array('boolean',FALSE),
			'UPDATED'=>array('date',FALSE));

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
						$val=trim((string)$val);
						if((string)$val!==$val || empty($val)){$c[]=$key;}else{$b[$key]=$val;}
						break;
					case 'date':
						if($val==' ' && $val==''){$c[]=$key;}else{$b[$key]=strtotime($val);}
						break;
					}
				}
		}//d($c);
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

function Add($arVariables,$arVariables2=FALSE,$PM=False)
	{
		global $APP;
		$LASTID = false;
		$MULTY=GTAPP::Conf('multilang_sync');
		if($MULTY!=TRUE)
		{	
			global $DB, $APP;
			if($arVariables2!=FALSE && is_array($arVariables2))
			{
				foreach($arVariables2 as $key=>$val)
				{
					$returned=array();
					foreach($arVariables2 as $key=>$val)
					{
						$V2=array();
						$V2=$val;
						if($val['PROP_KEY'])
						{
							$RTYPE=$DB->QResult("SELECT `TYPE_ID` FROM `g_dblock` WHERE `ID`='".$V2['DBLOCK_ID']."'");
							$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$RTYPE));
							$val['PROP_ID']=$V2['PROP_ID']=$Props[0]['ID'];
							unset ($V2['PROP_KEY']);
						}

						$R='';
						$R=$DB->QResult("SELECT `REQUIRED` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."' ");
						$V2['VALUE']=trim($V2['VALUE']);
						if(!empty($R) && $R!=0 && empty($V2['VALUE']))
						{	
							$returned[]=$V2['PROP_ID'];
						}
					}
					if(!empty($returned))
					{	
						$iRes=$DB->Query("SELECT `ID`,`NAME` FROM `g_dblock_props` WHERE `ID` IN ('".implode("','",$returned)."')");
						while($iRow=$DB->fetchAssoc($iRes))
						{
							$aReq['REQUIRED'][]=$iRow;
						}
						//d($aReq);
						return $aReq;
					}
				}		
			}
			
				if(!$arVariables['ACTIVE']){$arVariables['ACTIVE']='0';}
				$arVariables['CREATED']=date('Y-m-d H:m:s');
				$arVariables['UPDATED']=$arVariables['CREATED'];
				$arVariables['ININDEX']='0';
				$arVariables['AUTHOR']=$APP->GetCurrentUserID();
				$arVariables= GTdblock::Check($arVariables);
			if(isset($arVariables['TITLE']))
			{
				$Value=$arVariables;
				$VKeys = array_keys($Value);
				$sql="INSERT INTO `g_dblock` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
				$res=$DB->Query($sql); 
				$LASTID = $DB->insertId();
				if(!$LASTID) return false;
				if($arVariables2!=FALSE)
				{
					$var=array();
					LoadClass('GTdblockprop');
					foreach($arVariables2 as $key=>$val)
					{
						$V2=array();
						$V2=$val;
						if($val['PROP_KEY'])
						{
						$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$arVariables['TYPE_ID']));
						$V2['PROP_ID']=$Props[0]['ID'];
						unset ($V2['PROP_KEY']);
						}
						$V2['DBLOCK_ID']=$LASTID;
						if($V2['DBLOCK_ID'] && $V2['PROP_ID'] && !empty($V2['VALUE']))
						{	
							$R='';
							$R=$DB->QResult("SELECT `ID` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."'");
							if(!empty($R))
							{
							$V2=GTdblockpropvalue::Check($V2);
							$var[]= "('".implode('\', \'',$V2)."')";
							}
						}
					}
					$VaKeys=Array();
					$VaKeys = array_keys($V2);
					if(!empty($VaKeys))
					{
						$sql="INSERT INTO `g_dblock_props_values` (`".implode('`, `',$VaKeys)."`) VALUES ".implode(',',$var)."";
						$res=$DB->Query($sql);
					}				
				}
				
				if($PM!=FALSE)
				{
					$var=array();
					LoadClass('GTdblockprop');
					foreach($PM as $key=>$val)
					{
						$V2=array();
						$V2=$val;
						if($val['PROP_KEY'])
						{
						$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$arVariables['TYPE_ID']));
						$V2['PROP_ID']=$Props[0]['ID'];
						unset ($V2['PROP_KEY']);
						}
						$V2['DBLOCK_ID']=$LASTID;
						if($V2['DBLOCK_ID'] && $V2['PROP_ID'] && !empty($V2['VALUE']))
						{	
							$R='';
							$R=$DB->QResult("SELECT `TYPE` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."'");
							if(!empty($R))
							{
								$V2=GTdblockpropvalue::Check($V2);
								$var[]= "('".implode('\', \'',$V2)."')";
							}
						}
					}
					$VaKeys=Array();
					$VaKeys = array_keys($V2);
					if(!empty($VaKeys))
					{
						$sql="INSERT INTO `g_dblock_multy_values` (`".implode('`, `',$VaKeys)."`) VALUES ".implode(',',$var)."";
						$res=$DB->Query($sql);
					}
				}
				
				$QiNdex="SELECT *,MAX(`ELAST_CACHED`) as `EC` FROM `search_cache_elements`";
				$MaxCacheRes=$DB->Query($QiNdex);
				$MaxCacheRow=$DB->fetchAssoc($MaxCacheRes); 
				if(!empty($MaxCacheRow))
				{
					$CREATED=strtotime(date('Y-m-d H:m:s'));
					$Index=$CREATED-$MaxCacheRow['ELAST_CACHED'];
					//d($Index);
					if($Index>277204)
					{
						IndexData(array('DATE'=>$MaxCacheRow['ELAST_CACHED']));
					}
				}
				else
				{
					IndexData();
				}
				return TRUE;
				
			}
			else
				return FALSE;
		}
		else
		{
			return GTdblock::AddMulty($arVariables,$arVariables2,$PM);
		}
	}

function AddMulty($arVariables,$arVariables2=FALSE,$PM=False)
	{ 
		global $DB, $APP;
		$LASTID = false;
		if($arVariables2!=FALSE && is_array($arVariables2))
		{
			foreach($arVariables2 as $key=>$val)
			{
				$returned=array();
				foreach($arVariables2 as $key=>$val)
				{
					$V2=array();
					$V2=$val;
					if($val['PROP_KEY'])
					{
						$RTYPE=$DB->QResult("SELECT `TYPE_ID` FROM `g_dblock` WHERE `ID`='".$V2['DBLOCK_ID']."'");
						$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$RTYPE));
						$val['PROP_ID']=$V2['PROP_ID']=$Props[0]['ID'];
						unset ($V2['PROP_KEY']);
					}

					$R='';
					$R=$DB->QResult("SELECT `REQUIRED` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."' ");
					$V2['VALUE']=trim($V2['VALUE']);
					if(!empty($R) && $R!=0 && empty($V2['VALUE']))
					{	
						$returned[]=$V2['PROP_ID'];
					}
				}
				if(!empty($returned))
				{	
					$iRes=$DB->Query("SELECT `ID`,`NAME` FROM `g_dblock_props` WHERE `ID` IN ('".implode("','",$returned)."')");
					while($iRow=$DB->fetchAssoc($iRes))
					{
						$aReq['REQUIRED'][]=$iRow;
					}
					//d($aReq);
					return $aReq;
				}
			}		
		}
		
		
		if(!isset($arVariables['TYPE_ID']))
		{
			if(!$arVariables['MAIN']['ACTIVE']){$arVariables['MAIN']['ACTIVE']='0';}
				$arVariables['MAIN']['CREATED']=date('Y-m-d H:m:s');
				$arVariables['MAIN']['UPDATED']=$arVariables['MAIN']['CREATED'];
				$arVariables['MAIN']['ININDEX']='0';
				$arVariables ['MAIN']['AUTHOR']=$APP->GetCurrentUserID(); 
			foreach($arVariables as $key=>$val)
			{
				$arVariables[$key]= GTdblock::Check($arVariables[$key]);
				$LANG[$key]=$key;
			}
			unset($LANG['MAIN']);
			
			
			$Value=$arVariables['MAIN'];
			if(!empty($Value['TITLE']))
			{
				$VKeys = array_keys($Value);
				$sql="INSERT INTO `g_dblock` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
				$res=$DB->Query($sql); 
				$LASTID = $DB->insertId();
			}
			else
			{
				return false;
			}
			
			if(!empty($LASTID))
			{
				foreach($LANG as $val)
				{
					$arVariables[$val]['DBLOCK_ID']=$LASTID;
					$Value=$arVariables[$val];
					$VKeys = array_keys($Value);
					$sql="INSERT INTO `g_dblock_multy` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
					$res=$DB->Query($sql); 
				}
			}
		}
		else
		{
			if(!$arVariables['ACTIVE']){$arVa['MAIN']['ACTIVE']='0';}
				$arVa['MAIN']['CREATED']=date('Y-m-d H:m:s');
				$arVa['MAIN']['UPDATED']=$arVa['MAIN']['CREATED'];
				$arVa ['MAIN']['AUTHOR']=$APP->GetCurrentUserID();
				
				$arVa['MAIN'] = array(
				'TITLE'=>$arVariables['TITLE'],
				'DATESTART'=>$arVariables['DATESTART'],
				'DATEEND'=>$arVariables['DATEEND'],
				'ACTIVE'=>$arVariables['ACTIVE'],
				'SHORTIMG'=>$arVariables['SHORTIMG'],
				'FULLIMG'=>$arVariables['FULLIMG'],
				'SORT'=>$arVariables['SORT'],
				'TAGS'=>$arVariables['TAGS'],
				'ININDEX'=>'0',
				'SUBTYPE'=>$arVariables['SUBTYPE'][0],
				'DBLOCK_KEY'=>$arVariables['DBLOCK_KEY'],
				'TYPE_ID'=>$arVariables['TYPE_ID']);
				
				$arrVars['ML'] = array(
				'TITLE'=>$arVariables['TITLE'],
				'SHORTTEXT'=>$arVariables['SHORTTEXT'],
				'SHORTTEXT_TYPE'=>$arVariables['SHORTTEXT_TYPE'],
				'FULLTEXT_TYPE'=>$arVariables['FULLTEXT_TYPE'],
				'FULLTEXT'=>$arVariables['FULLTEXT'],
				'edswitchSHORTTEXT'=>$arVariables['edswitchSHORTTEXT'],
				'edswitchFULLTEXT'=>$arVariables['edswitchFULLTEXT'],
				'SUBTYPE'=>$arVariables['SUBTYPE'][0],
				'DBLOCK_KEY'=>$arVariables['DBLOCK_KEY'],
				'LANG'=>'ru',
				'TYPE_ID'=>$arVariables['TYPE_ID']);
				$TYPE_ID=$arVariables['TYPE_ID'];
				$arVa['MAIN']= GTdblock::Check($arVa['MAIN']);
				$Value=$arVa['MAIN'];
				$VKeys = array_keys($Value);
				$sql="INSERT INTO `g_dblock` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
				
				$res=$DB->Query($sql); 
				$LASTID = $DB->insertId();
				
				if(!$LASTID) return;
				
				$arrVars['ML']= GTdblock::Check($arrVars['ML']);
				$arrVars['ML']['DBLOCK_ID']=$LASTID;
				$Value=$arrVars['ML'];
				if(empty($Value['TITLE'])){return false;}
				$VKeys = array_keys($Value);
				$sql="INSERT INTO `g_dblock_multy` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
				$res=$DB->Query($sql); 
				
		}
		
	
			if($arVariables2!=FALSE && !empty($LASTID))
			{
				$var=array();
				LoadClass('GTdblockprop');
				foreach($arVariables2 as $key=>$val)
				{
					$V2=array();
					if(!isset($val['SWITCH']))
					{
						$val['SWITCH']='tx';
					}
					$V2=$val;
					
					if($val['PROP_KEY'])
					{
						$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$val['PROP_KEY'],'TYPE_ID'=>$TYPE_ID)); 
						$V2['PROP_ID']=$Props[0]['ID'];
						unset ($V2['PROP_KEY']);
					}
					if(!isset($val['LANG']))
					{
						$V2['LANG']=GTAPP::getDeafaultLang();
					}
					$V2['DBLOCK_ID']=$LASTID;
					
					if($V2['DBLOCK_ID'] && $V2['PROP_ID'] && !empty($V2['VALUE']))
					{	
						$R='';
						$R=$DB->QResult("SELECT `ID` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."'");
						if(!empty($R))
						{
						$V2=GTdblockpropvalue::Check($V2);
						$var[]= "('".implode('\', \'',$V2)."')";
						}
					}
				}
				$VaKeys=Array();
				$VaKeys = array_keys($V2);
				if(!empty($VaKeys))
				{
					$sql="INSERT INTO `g_dblock_props_values` (`".implode('`, `',$VaKeys)."`) VALUES ".implode(',',$var)."";
					$res=$DB->Query($sql);
				}				
			}
			
			if($PM!=FALSE && !empty($LASTID))
			{
				$var=array();
				LoadClass('GTdblockprop');
				foreach($PM as $key=>$val)
				{
					$V2=array();
					$V2=$val;
					if($val['PROP_KEY'])
					{
					$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$arVariables['MAIN']['TYPE_ID']));
					$V2['PROP_ID']=$Props[0]['ID'];
					unset ($V2['PROP_KEY']);
					}
					$V2['DBLOCK_ID']=$LASTID;
					if($V2['DBLOCK_ID'] && $V2['PROP_ID'] && !empty($V2['VALUE']))
					{	
						$R='';
						$R=$DB->QResult("SELECT `ID` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."'");
						if(!empty($R))
						{
							$V2=GTdblockpropvalue::Check($V2);
							$var[]= "('".implode('\', \'',$V2)."')";
						}
					}
				}
				$VaKeys=Array();
				$VaKeys = array_keys($V2);
				if(!empty($VaKeys))
				{
					$sql="INSERT INTO `g_dblock_multy_values` (`".implode('`, `',$VaKeys)."`) VALUES ".implode(',',$var).""; 
					$res=$DB->Query($sql);
				}
			}
		
			$QiNdex="SELECT *,MAX(`ELAST_CACHED`) as `EC` FROM `search_cache_elements`";
			$MaxCacheRes=$DB->Query($QiNdex);
			$MaxCacheRow=$DB->fetchAssoc($MaxCacheRes); 
			if(!empty($MaxCacheRow))
			{
				$CREATED=strtotime(date('Y-m-d H:m:s'));
				$Index=$CREATED-$MaxCacheRow['ELAST_CACHED'];
				//d($Index);
				if($Index>277204)
				{
					IndexData(array('DATE'=>$MaxCacheRow['ELAST_CACHED']));
				}
			}
			else
			{
				IndexData();
			}
		if(!empty($LASTID))
		{
			return TRUE;
		}
		return FALSE;
	}	


	function Update($arVariables=FALSE,$arVariables2=FALSE,$PM=False,$PM_DELL=FALSE,$PM_DELL2=FALSE)
	{	
		global $DB, $APP;
		$MULTY=GTAPP::Conf('multilang_sync');
		//MULTY_LANG TRUE
		if($MULTY!=FALSE)
		{
			if($arVariables2!=FALSE && is_array($arVariables2))
			{
				$returned=array();
				foreach($arVariables2 as $key=>$val)
				{
					$V2=array();
					$V2=$val;
					if($val['PROP_KEY'])
					{
						$RTYPE=$DB->QResult("SELECT `TYPE_ID` FROM `g_dblock` WHERE `ID`='".$V2['DBLOCK_ID']."'");
						$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$RTYPE));
						$val['PROP_ID']=$V2['PROP_ID']=$Props[0]['ID'];
						unset ($V2['PROP_KEY']);
					}

					$R='';
					$R=$DB->QResult("SELECT `REQUIRED` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."' ");
					$V2['VALUE']=trim($V2['VALUE']);
					if(!empty($R) && $R!=0 && empty($V2['VALUE']))
					{	
						$returned[]=$V2['PROP_ID'];
					}
				}
				if(!empty($returned))
				{	
					$iRes=$DB->Query("SELECT `ID`,`NAME` FROM `g_dblock_props` WHERE `ID` IN ('".implode("','",$returned)."')");
					while($iRow=$DB->fetchAssoc($iRes))
					{
						$aReq['REQUIRED'][]=$iRow;
					}
					//d($aReq);
					return $aReq;
				}
			}
			if($PM_DELL!=FALSE && is_array($PM_DELL))
			{
				 foreach($PM_DELL as $key=>$val)
				 {
					$DB->Query("Delete FROM `g_dblock_multy_values` WHERE `PROP_ID`='".$val['PROP_ID']."' AND `DBLOCK_ID`='".$val['DBLOCK_ID']."' AND VALUE='".$val['VALUE']."' AND `LANG`='".$val['LANG']."'");
					$MP = GTdblockprop::GetMP('',array('DBLOCK_ID'=>$val['DBLOCK_ID'],'PROP_ID'=>$val['PROP_ID'],'LANG'=>$val['LANG']));
					if(empty($MP))
					{
						$DB->Query("Delete FROM `g_dblock_props_values` WHERE `PROP_ID`='".$val['PROP_ID']."' AND `DBLOCK_ID`='".$val['DBLOCK_ID']."'  AND `LANG`='".$val['LANG']."'");
					}
				 }
			}
			
			if($PM_DELL2!=FALSE && is_array($PM_DELL2))
			{
				 foreach($PM_DELL2 as $key=>$val)
				 {
					$DB->Query("Delete FROM `g_dblock_props_values` WHERE `PROP_ID`='".$val['PROP_ID']."' AND `DBLOCK_ID`='".$val['DBLOCK_ID']."' AND VALUE='".$val['VALUE']."' AND `LANG`='".$val['LANG']."'");
				 }
			}
			if(!isset($arVariables['TYPE_ID']))
			{
				if($arVariables['MAIN']!=FALSE && is_array($arVariables['MAIN']))
				{
					$arVariables['MAIN']['AUTHOR']=$APP->GetCurrentUserID();
					$arVariables['MAIN']['UPDATED']=date('Y-m-d H:m:s');
					$arVariables['MAIN']['ININDEX']='0';
					$arVariables['MAIN']= GTdblock::Check($arVariables['MAIN']);

					if(!isset($arVariables['MAIN'][0]) && isset($arVariables['MAIN']['ID']))
					{
						$ID=$arVariables['MAIN']['ID'];
						unset($arVariables['MAIN']['ID']);
						$K1=array();
						foreach($arVariables['MAIN'] as $keyss=>$arV)
						{
							if ($arV!=''){
							$K1[]="`".$keyss."`='".$arV."'";}
						}
						$sql="UPDATE `g_dblock` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'"; 
						$res=$DB->Query($sql);	
					}
				}
				
				$lang=GToptionlang::Get('',Array('ENABLED'=>1),'','','',$count);
				foreach($lang as $Lval)
				{
					if($arVariables[$Lval['ID']]!=FALSE && is_array($arVariables[$Lval['ID']]))
					{
						$arVariables[$Lval['ID']]= GTdblock::Check($arVariables[$Lval['ID']]);

						if(!isset($arVariables[$Lval['ID']][0]) && isset($arVariables[$Lval['ID']]['ID']) && isset($arVariables[$Lval['ID']]['TITLE']))
						{
							$ID=$arVariables[$Lval['ID']]['ID'];
							unset($arVariables[$Lval['ID']]['ID']);
							$K1=array();
							foreach($arVariables[$Lval['ID']] as $keyss=>$arV)
							{
								if ($arV!=''){
								$K1[]="`".$keyss."`='".$arV."'";}
							}
							$arVariables[$Lval['ID']]['DBLOCK_ID']=$ID;
							$Value='';
							$VKeys=array();
							$Value=$arVariables[$Lval['ID']];
							$VKeys = array_keys($Value);
							$SSQL="INSERT INTO `g_dblock_multy` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."') ON DUPLICATE KEY 
							UPDATE  ".implode(', ',$K1)." /*WHERE `DBLOCK_ID`='".$ID."' AND `LANG`='".$Lval['ID']."'*/"; //d($SSQL);
							$res=$DB->Query($SSQL);
						}
					}
				}
			}
			else
			{				
				$arVa['MAIN'] = array(
				'ID'=>$arVariables['ID'],
				'DATESTART'=>$arVariables['DATESTART'],
				'DATEEND'=>$arVariables['DATEEND'],
				'ACTIVE'=>$arVariables['ACTIVE'],
				'SHORTIMG'=>$arVariables['SHORTIMG'],
				'FULLIMG'=>$arVariables['FULLIMG'],
				'SORT'=>$arVariables['SORT'],
				'TAGS'=>$arVariables['TAGS'],
				'SUBTYPE'=>$arVariables['SUBTYPE'][0],
				'DBLOCK_KEY'=>$arVariables['DBLOCK_KEY'],
				'LINK'=>$arVariables['LINK'],
				'INDEXED'=>$arVariables['INDEXED'],
				'ININDEX'=>'0',
				'TYPE_ID'=>$arVariables['TYPE_ID']);
				if(!$arVariables['ACTIVE']){$arVa['MAIN']['ACTIVE']='0';}
				$arVa['MAIN']['CREATED']=date('Y-m-d H:m:s');
				$arVa['MAIN']['UPDATED']=$arVa['MAIN']['CREATED'];
				$arVa ['MAIN']['AUTHOR']=$APP->GetCurrentUserID();
				
				$arrVars['ML'] = array(
				'ID'=>$arVariables['ID'],
				'TITLE'=>$arVariables['TITLE'],
				'SHORTTEXT'=>$arVariables['SHORTTEXT'],
				'SHORTTEXT_TYPE'=>$arVariables['SHORTTEXT_TYPE'],
				'FULLTEXT_TYPE'=>$arVariables['FULLTEXT_TYPE'],
				'FULLTEXT'=>$arVariables['FULLTEXT'],
				'edswitchSHORTTEXT'=>$arVariables['edswitchSHORTTEXT'],
				'edswitchFULLTEXT'=>$arVariables['edswitchFULLTEXT'],
				'SUBTYPE'=>$arVariables['SUBTYPE'][0],
				'DBLOCK_KEY'=>$arVariables['DBLOCK_KEY'],
				'LANG'=>'ru',
				'TYPE_ID'=>$arVariables['TYPE_ID']);
				$arVariables['MAIN']= GTdblock::Check($arVariables['MAIN']);
				if(!isset($arVariables['MAIN'][0]) && isset($arVariables['MAIN']['ID']))
				{
					$ID=$arVariables['MAIN']['ID'];
					unset($arVariables['MAIN']['ID']);
					$K1=array();
					foreach($arVariables['MAIN'] as $keyss=>$arV)
					{
						if ($arV!=''){
						$K1[]="`".$keyss."`='".$arV."'";}
					}
					$sql="UPDATE `g_dblock` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'";
					$res=$DB->Query($sql);	
				}
				
				$arVariables['ML']= GTdblock::Check($arVariables['ML']);

				if(!isset($arVariables['ML'][0]) && isset($arVariables['ML']['ID']))
				{
					$ID=$arVariables['ML']['ID'];
					unset($arVariables['ML']['ID']);
					$K1=array();
					foreach($arVariables['ML'] as $keyss=>$arV)
					{
						if ($arV!=''){
						$K1[]="`".$keyss."`='".$arV."'";}
					}
					$sql="UPDATE `g_dblock_multy` SET ".implode(', ',$K1)." WHERE `DBLOCK_ID`='".$ID."'";
					$res=$DB->Query($sql);	
				}
			}
			if($arVariables2!=FALSE && is_array($arVariables2))
			{
				$var=array();
				LoadClass('GTdblockprop');
				foreach($arVariables2 as $key=>$val)
				{
					$V2=array();
					$V2=$val; 
					if($val['PROP_KEY'])
					{
						$RTYPE=$DB->QResult("SELECT `TYPE_ID` FROM `g_dblock` WHERE `ID`='".$V2['DBLOCK_ID']."'");
						$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$RTYPE,'LANG'=>$V2['LANG']));
						$val['PROP_ID']=$V2['PROP_ID']=$Props[0]['ID'];
						unset ($V2['PROP_KEY']);
					}
					
					$R='';
					$R=$DB->QResult("SELECT `ID` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."' ");
					if(!empty($R))
					{
						$PCh='';
						$PCh=$DB->QResult("SELECT `PROP_ID` FROM `g_dblock_props_values` WHERE `PROP_ID`='".$V2['PROP_ID']."' AND `DBLOCK_ID`='".$V2['DBLOCK_ID']."' AND `LANG`='".$V2['LANG']."'"); 
						if(empty($PCh))
						{	$var=array();
							if(!empty($V2['VALUE']))
							{
								
								$V2=GTdblockpropvalue::Check($V2);
								$var[]= "('".implode('\', \'',$V2)."')";
							}
							if(!empty($var))
							{
								$VaKeys=Array();
								$VaKeys = array_keys($V2);
								$sql="INSERT INTO `g_dblock_props_values` (`".implode('`, `',$VaKeys)."`) VALUES ".implode(',',$var).""; 
								$DB->Query($sql);
							}
						}
						else
						{
							$arVar=array();
							$arVar[]= GTdblockpropvalue::Check($val);
							if($arVar!=FALSE)
							{
								foreach($arVar as $Arkey=>$Arval)
								{
									foreach($Arval as $KEYS=>$VALS)
										{
											
											if($KEYS!='DBLOCK_ID' && $KEYS!='PROP_ID' && $KEYS!='LANG')
											{
												$k1[] = "`".$KEYS."`='".$VALS."'";
											}
											if($KEYS=='PROP_ID'){$PROP_ID=$VALS;}
											if($KEYS=='DBLOCK_ID'){$DBLOCK_ID=$VALS;}
											if($KEYS=='LANG'){$LANG_ID=$VALS;}
										}
										if($k1)
										{
											$sql2="UPDATE `g_dblock_props_values` SET ".implode(', ',$k1)." WHERE `DBLOCK_ID`='".$DBLOCK_ID."' AND `PROP_ID`='".$PROP_ID."' AND `LANG`='".$LANG_ID."'";
											$DB->Query($sql2);
										}
										unset ($k1);
								}
							}
						}
					}
				}			
			}
			
			if($PM!=FALSE && is_array($PM))
			{
				$var=array();
				LoadClass('GTdblockprop');
				foreach($PM as $key=>$val)
				{
					$V2=array();
					$V2=$val;
					$R='';
					$R=$DB->QResult("SELECT `ID` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."'");
					if(!empty($R))
					{
						$PCh='';
						$PCh=$DB->QResult("SELECT `PROP_ID` FROM `g_dblock_multy_values` WHERE `PROP_ID`='".$V2['PROP_ID']."' AND `DBLOCK_ID`='".$V2['DBLOCK_ID']."' AND `VALUE`='".$V2['VALUE']."' AND `LANG`='".$V2['LANG']."'");
						if(empty($PCh))
						{
							$var=array();
							if($val['PROP_KEY'])
							{
							$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$arVariables['MAIN']['TYPE_ID'],'LANG'=>$V2['LANG']));
							$V2['PROP_ID']=$Props[0]['ID'];
							unset ($V2['PROP_KEY']);
							}
							if($V2['DBLOCK_ID'] && $V2['PROP_ID'] && !empty($V2['VALUE']))
							{	
								
								$V2=GTdblockpropvalue::Check($V2); 
								$var[]= "('".implode('\', \'',$V2)."')";
							}
							
							$VaKeys=Array();
							$VaKeys = array_keys($V2);
							if(!empty($VaKeys))
							{
								$sql="INSERT INTO `g_dblock_multy_values` (`".implode('`, `',$VaKeys)."`) VALUES ".implode(',',$var)."";
								$DB->Query($sql);
							}
						}
						else
						{
							$arVar=array();
							$arVar[]= GTdblockpropvalue::Check($val);
							if($arVar!=FALSE)
							{
								foreach($arVar as $Arkey=>$Arval)
								{
									foreach($Arval as $KEYS=>$VALS)
										{
											if($KEYS!='DBLOCK_ID' && $KEYS!='PROP_ID' && $KEYS!='LANG')
											{
												$k1[] = "`".$KEYS."`='".$VALS."'";
											}
											if($KEYS=='PROP_ID'){$PROP_ID=$VALS;}
											if($KEYS=='LANG'){$LANG=$VALS;}
											if($KEYS=='DBLOCK_ID'){$DBLOCK_ID=$VALS;}
										}
										if($k1)
										{
											
											$sql2="UPDATE `g_dblock_multy_values` SET ".implode(', ',$k1)." WHERE `DBLOCK_ID`='".$DBLOCK_ID."' AND `PROP_ID`='".$PROP_ID."' AND `LANG`='".$LANG."'";
											$DB->Query($sql2);
										}
										unset ($k1);
								}
							}
						}
					}
				}
			}			
		}
		else
		{
			if($arVariables2!=FALSE && is_array($arVariables2))
			{
				foreach($arVariables2 as $key=>$val)
				{
					$V2=array();
					$V2=$val;
					if($val['PROP_KEY'])
					{
						$RTYPE=$DB->QResult("SELECT `TYPE_ID` FROM `g_dblock` WHERE `ID`='".$V2['DBLOCK_ID']."'");
						$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$RTYPE));
						$val['PROP_ID']=$V2['PROP_ID']=$Props[0]['ID'];
						unset ($V2['PROP_KEY']);
					}

					$R='';
					$R=$DB->QResult("SELECT `REQUIRED` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."' ");
					$V2['VALUE']=trim($V2['VALUE']);
					if(!empty($R) && $R!=0 && empty($V2['VALUE']))
					{	
						$returned[]=$V2['PROP_ID'];						
					}
				}
				if(!empty($returned))
				{	
					$iRes=$DB->Query("SELECT `ID`,`NAME` FROM `g_dblock_props` WHERE `ID` IN ('".implode("','",$returned)."')");
					while($iRow=$DB->fetchAssoc($iRes))
					{
						$aReq['REQUIRED'][]=$iRow;
					}
					//d($aReq);
					return $aReq;
				}				
			}
			if($PM_DELL!=FALSE && is_array($PM_DELL))
			{
			 foreach($PM_DELL as $key=>$val)
			 {
				$DB->Query("Delete FROM `g_dblock_multy_values` WHERE `PROP_ID`='".$val['PROP_ID']."' AND `DBLOCK_ID`='".$val['DBLOCK_ID']."' AND VALUE='".$val['VALUE']."'");
			 }
			}
			if($arVariables!=FALSE && is_array($arVariables))
			{
				$arVariables['AUTHOR']=$APP->GetCurrentUserID();
				$arVariables['UPDATED']=date('Y-m-d H:m:s');
				$arVariables= GTdblock::Check($arVariables);

				if(!isset($arVariables[0]) && isset($arVariables['ID']))
				{
					$ID=$arVariables['ID'];
					unset($arVariables['ID']);
					$K1=array();
					foreach($arVariables as $keyss=>$arV)
					{
						if ($arV==''){$arV=' ';}
						$K1[]="`".$keyss."`='".$arV."'";
					}
					$Value=$arVariables;
					$sql="UPDATE `g_dblock` SET ".implode(', ',$K1)." WHERE `ID`='".$ID."'";
					$res=$DB->Query($sql);	
				}
			}
			if($arVariables2!=FALSE && is_array($arVariables2))
			{
				$var=array();
				LoadClass('GTdblockprop');
				foreach($arVariables2 as $key=>$val)
				{
					$V2=array();
					$V2=$val;
					if($val['PROP_KEY'])
					{
						$RTYPE=$DB->QResult("SELECT `TYPE_ID` FROM `g_dblock` WHERE `ID`='".$V2['DBLOCK_ID']."'");
						$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$RTYPE));
						$val['PROP_ID']=$V2['PROP_ID']=$Props[0]['ID'];
						unset ($V2['PROP_KEY']);
					}
					
					$R='';
					$R=$DB->QResult("SELECT `ID` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."' ");
					if(!empty($R))
					{
						$PCh='';
						$PCh=$DB->QResult("SELECT `PROP_ID` FROM `g_dblock_props_values` WHERE `PROP_ID`='".$V2['PROP_ID']."' AND `DBLOCK_ID`='".$V2['DBLOCK_ID']."'");
						if(empty($PCh))
						{;
							if($V2['DBLOCK_ID'] && $V2['PROP_ID'] && !empty($V2['VALUE']))
							{
								$var=array();
								$V2=GTdblockpropvalue::Check($V2);
								$var[]= "('".implode('\', \'',$V2)."')";
							}
							if(!empty($var))
							{
								$VaKeys=Array();
								$VaKeys = array_keys($V2);
								$sql="INSERT INTO `g_dblock_props_values` (`".implode('`, `',$VaKeys)."`) VALUES ".implode(',',$var)."";
								$DB->Query($sql);
							}
						}
						else
						{
							$arVar=array();
							$arVar[]= GTdblockpropvalue::Check($val);
							if($arVar!=FALSE)
							{
								foreach($arVar as $Arkey=>$Arval)
								{
									foreach($Arval as $KEYS=>$VALS)
										{
											if($KEYS!='DBLOCK_ID' && $KEYS!='PROP_ID')
											{
												$k1[] = "`".$KEYS."`='".$VALS."'";
											}
											if($KEYS=='PROP_ID'){$PROP_ID=$VALS;}
											if($KEYS=='DBLOCK_ID'){$DBLOCK_ID=$VALS;}
										}
										if($k1)
										{
											$sql2="UPDATE `g_dblock_props_values` SET ".implode(', ',$k1)." WHERE `DBLOCK_ID`='".$DBLOCK_ID."' AND `PROP_ID`='".$PROP_ID."'";
											$DB->Query($sql2);
										}
										unset ($k1);
								}
							}
						}
					}
				}			
			}
			
			if($PM!=FALSE && is_array($PM))
			{
				$var=array();
				LoadClass('GTdblockprop');
				foreach($PM as $key=>$val)
				{
					$V2=array();
					$V2=$val;
					$R='';
					$R=$DB->QResult("SELECT `ID` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."'");
					if(!empty($R))
					{
						$PCh='';
						$PCh=$DB->QResult("SELECT `PROP_ID` FROM `g_dblock_multy_values` WHERE `PROP_ID`='".$V2['PROP_ID']."' AND `DBLOCK_ID`='".$V2['DBLOCK_ID']."' AND `VALUE`='".$V2['VALUE']."' ");
						if(empty($PCh))
						{
							if($val['PROP_KEY'])
							{
							$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$arVariables['TYPE_ID']));
							$V2['PROP_ID']=$Props[0]['ID'];
							unset ($V2['PROP_KEY']);
							}
							if($V2['DBLOCK_ID'] && $V2['PROP_ID'] && !empty($V2['VALUE']))
							{	
								$var=array();
								$V2=GTdblockpropvalue::Check($V2);
								$var[]= "('".implode('\', \'',$V2)."')";
							}
							
							$VaKeys=Array();
							$VaKeys = array_keys($V2);
							if(!empty($VaKeys))
							{
								$sql="INSERT INTO `g_dblock_multy_values` (`".implode('`, `',$VaKeys)."`) VALUES ".implode(',',$var)."";
								$DB->Query($sql);
							}
						}
						else
						{
							$arVar=array();
							$arVar[]= GTdbpv::Check($val);
							if($arVar!=FALSE)
							{
								foreach($arVar as $Arkey=>$Arval)
								{
									foreach($Arval as $KEYS=>$VALS)
										{
											if($KEYS!='DBLOCK_ID' && $KEYS!='PROP_ID')
											{
												$k1[] = "`".$KEYS."`='".$VALS."'";
											}
											if($KEYS=='PROP_ID'){$PROP_ID=$VALS;}
											if($KEYS=='DBLOCK_ID'){$DBLOCK_ID=$VALS;}
										}
										if($k1)
										{
											
											$sql2="UPDATE `g_dblock_multy_values` SET ".implode(', ',$k1)." WHERE `DBLOCK_ID`='".$DBLOCK_ID."' AND `PROP_ID`='".$PROP_ID."'";
											$DB->Query($sql2);
										}
										unset ($k1);
								}
							}
						}
					}
				}
			}
		}
		if($res==TRUE){
			$QiNdex="SELECT *,MAX(`ELAST_CACHED`) as `EC` FROM `search_cache_elements`";
			$MaxCacheRes=$DB->Query($QiNdex);
			$MaxCacheRow=$DB->fetchAssoc($MaxCacheRes);
			if(!empty($MaxCacheRow))
			{
				$CREATED=strtotime(date('Y-m-d H:m:s'));
				$Index=$CREATED-$MaxCacheRow['ELAST_CACHED'];
				
				if($Index>277204)
				{
					IndexData(array('DATE'=>$CREATED));
				}
			}
			else
			{
				IndexData();
			}
		return TRUE;}else{return False;}
	}

	function Delete($ID)
	{
		global $DB;
		if(is_numeric($ID))
		{
			$R=GTdblock::Get('',Array('ID'=>$ID));
			$sql="Delete FROM `g_dblock` WHERE `ID`='".$ID."'";
			$res=$DB->Query($sql);
			
			foreach($R[0]['PROPERTIES'] as $key=>$val)
			{
				if($val['TYPE']=='IMAGE' || $val['TYPE']=='FILE')
				{
					$sql="SELECT `ID` FROM `g_files` WHERE `ID`='".$val['VALUE']."' OR `TITLE`='".$val['VALUE']."'";
					$res=$DB->Query($sql);
					while($row=$DB->fetchAssoc($res))
					{
						GTfile::Delete($row['ID'],TRUE);
					}
				}
			}
			$sql="Delete FROM `g_dblock_props_values` WHERE `DBLOCK_ID`='".$ID."'";
			$res=$DB->Query($sql);
			$sql="Delete FROM `g_dblock_multy` WHERE `DBLOCK_ID`='".$ID."'";
			$res=$DB->Query($sql);
			$sql="Delete FROM `g_dblock_multy_values` WHERE `DBLOCK_ID`='".$ID."'";
			$res=$DB->Query($sql);			
		}
	}
	
		
	
	function Get($t=FALSE,$arv=FALSE,$order=FALSE,$limit=FALSE,$FIVE=FALSE,&$countAD=COUNT_DEFINED_VALUE)
	{
		$MULTY=GTAPP::Conf('multilang_sync');
				
		global $DB, $APP;
		if($arv['ID'])
		{
			if(!is_array($arv['ID']) || count($arv['ID'])==1)
			{
				$gt=strstr($APP->GetCurPage(),'gtinx');
				$rl="http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				if(empty($gt)){
				$idlink=$arv['ID'];
					$Links="UPDATE  `g_dblock` SET  `LINK` = '$rl' WHERE  `ID`='$idlink';"; //d($Links); die();
					$DB->Query($Links);
				}
			}
		}
		if($MULTY==TRUE)
		{
			if(empty($arv['LANG']))
			{
				$LANG = GTAPP::SiteLang();
				$arv['PROPS_VALUES_LANG']=$LANG;
			}
			else
			{
				$LANG=$arv['LANG'];
				$arv['PROPS_VALUES_LANG']=$LANG;
			}
			if($arv['FORCE_LANG'])
			{
				if(!empty($arv['FORCE_LANG']))
				{
					$sql['BY'][]= "\n`dblock_C`.`DBLOCK_ID`<>'0'";
					$sql['BY'][]= "\n`dblock_C`.`LANG`='".$LANG."'";
				}
				unset($arv['FORCE_LANG']);
			}
		}
	//d($arv);	
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
			$LIMIT=' LIMIT 0,'.$limit;
		}
	}
		
		
		
		$table['DBLOCK']=Array(
						'ID'=>'dblock.`ID`',
						'TITLE'=>'dblock.`TITLE`',
						'DATESTART'=>'dblock.`DATESTART`',
						'DATEEND'=>'dblock.`DATEEND`',
						'SHORTTEXT'=>'dblock.`SHORTTEXT`',
						'SHORTTEXT_TYPE'=>'dblock.`SHORTTEXT_TYPE`',
						'FULLTEXT'=>'dblock.`FULLTEXT`',
						'FULLTEXT_TYPE'=>'dblock.`FULLTEXT_TYPE`',
						'ACTIVE'=>'dblock.`ACTIVE`',
						'SORT'=>'dblock.`SORT`',
						'AUTHOR'=>'dblock.`AUTHOR`',
						'SHORTIMG'=>'dblock.`SHORTIMG`',
						'FULLIMG'=>'dblock.`FULLIMG`',
						'TAGS'=>'dblock.`TAGS`',
						'TYPE_ID'=>'dblock.`TYPE_ID`',
						'CREATED'=>'dblock.`CREATED`',
						'UPDATED'=>'dblock.`UPDATED`',
						'DBLOCK_KEY'=>'dblock.`DBLOCK_KEY`',
						'LINK'=>'dblock.`LINK`',
						'INDEXED'=>'dblock.`INDEXED`',
						'ININDEX'=>'dblock.`ININDEX`',
						'SUBTYPE'=>'dblock.`SUBTYPE`');
		if($MULTY!=FALSE)
		{
		$table['DBLOCK_C']=Array(
						'ID'=>'dblock_C.`DBLOCK_ID`',
						'TITLE'=>'dblock_C.`TITLE`',
						'SHORTTEXT'=>'dblock_C.`SHORTTEXT`',
						'SHORTTEXT_TYPE'=>'dblock_C.`SHORTTEXT_TYPE`',
						'FULLTEXT'=>'dblock_C.`FULLTEXT`',
						'FULLTEXT_TYPE'=>'dblock_C.`FULLTEXT_TYPE`',
						'LANG'=>'dblock_C.`LANG`',
						'TYPE_ID'=>'dblock_C.`TYPE_ID`',
						'DBLOCK_KEY'=>'dblock_C.`DBLOCK_KEY`',
						'LEVEL'=>'dblock_C.`LEVEL`',
						'SUBTYPE'=>'dblock_C.`SUBTYPE`');
		}
		$table['GROUP']=Array(
								'GROUP_ID'=>'gr.`ID`',
								'GROUP_NAME'=>'gr.`NAME`',
								'GROUP_DESC'=>'gr.`DESC`',
								'GROUP_ORDER'=>'gr.`ORDER`',
								'GROUP_ACTIVE'=>'gr.`ACTIVE`',
								'GROUP_CREATED'=>'gr.`CREATED`',
								'GROUP_UPDATED'=>'gr.`UPDATED`');
		$table['TYPE']=Array(

								'TYPE_GROUP_ID'=>'type.`GROUP_ID`',
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
								'SUBTYPE_PARENT'=>'st.`PARENT`',
								'SUBTYPE_DESC'=>'st.`DESC`',
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
								'PROPS_VALUES_LANG'=>'pv.`LANG`',
								'PROPS_VALUES_SWITCH'=>'pv.`SWITCH`',
								'PROPS_VALUES_VALUE_NUM'=>'pv.`VALUE_NUM`');
		$table['MULTY_VALUES']=Array(
								'MV_DBLOCK_ID'=>'pmv.`DBLOCK_ID`',
								'MV_PROP_ID'=>'pmv.`PROP_ID`',
								'MV_VALUE'=>'pmv.`VALUE`',
								'MV_VALUE_NUM'=>'pmv.`VALUE_NUM`');						

		if($order)
		{
			if(is_array($order))
			{
				if($table['GROUP'][$order[0]]){$ORD=' ORDER BY '.$table['GROUP'][$order[0]].' '.$order[1].' ';}
				elseif($table['TYPE'][$order[0]]){$ORD=' ORDER BY '.$table['TYPE'][$order[0]].' '.$order[1].' ';}
				elseif($table['SUBTYPE'][$order[0]]){$ORD=' ORDER BY '.$table['SUBTYPE'][$order[0]].' '.$order[1].' ';}
				elseif($table['DBLOCK'][$order[0]]){$ORD=' ORDER BY dblock.`'.$order[0].'` '.$order[1].' ';}
			}
			else
			{
				if($table['GROUP'][$order]){$ORD=' ORDER BY '.$table['GROUP'][$order].' ';}
				elseif($table['TYPE'][$order]){$ORD=' ORDER BY '.$table['TYPE'][$order].' ';}
				elseif($table['SUBTYPE'][$order]){$ORD=' ORDER BY '.$table['SUBTYPE'][$order].' ';}
				elseif($table['DBLOCK'][$order]){$ORD=' ORDER BY dblock.`'.$order.'` ';}
			}
		}
	//d($ORD);
		$fives['DBLOCK']=Array('ID'=>'ID','DBLOCK_KEY'=>'DBLOCK_KEY');
		$fives['PROPS']=Array('PROP_KEY'=>'PROP_KEY');
		if($FIVE)
		{
			foreach($FIVE as $key=>$val)
			{
				if($fives['DBLOCK'][$val]){$F5['DBLOCK']=$fives['DBLOCK'][$val];}
				elseif($fives['PROPS'][$val]){$F5['PROPS']=$fives['PROPS'][$val];}
				else{$F5['LINK']=$val;}
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
						if($KEY=='DBLOCK' || $KEY=='PROPS_VALUES' || $KEY=='PROPS')
						{$tab[$KEY][]=$table[$KEY][$val];}
						else
						{$tab[$KEY][]=$table[$KEY][$val].'as '.$val;}
					}
				}
			}
		}//d($tab);

		$notFoundInTables = array();
		if(($arv) && is_array($arv))
		{
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
							$BY='BY';
							break;
							case 'DBLOCK':
							$BY='BY';
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
							$searchKey = $table[$KEY][$key];
							if($MULTY && $table['DBLOCK_C'][$key])
								$searchKey = $table['DBLOCK_C'][$key];
							$r=SWITCH_IT($val,$searchKey); 
							$sql[$BY][]=$r[0];
						}
						$notFoundInTables[$key]=$val;
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
							case 'GROUP':
							$BY='BY';
							break;
							case 'TYPE':
							$BY='BY';
							break;
							case 'DBLOCK':
							$BY='BY';
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
							$searchKey = $table[$KEY][$key];
							if($MULTY && $table['DBLOCK_C'][$key])
								$searchKey = $table['DBLOCK_C'][$key];
							$a= SWITCH_IT($val,$searchKey);
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
			$continue='';
			$PROPC = 0;
			$UPROP = array();
			foreach($notFoundInTables as $key => $VAL)
			{
				if(false!==($nfp_ = strpos($key,'_'))){
					$nfpp = substr($key,0,$nfp_);
					$VAL2=substr($key,$nfp_+1);
					//d($VAL2);
					switch($nfpp){
						case 'AUTHOR':
						case 'OWNER':
							$ufXX = $nfpp;
							if(false!==($nfp_ = strpos($VAL2,'_'))){
								$nfpp = substr($VAL2,0,$nfp_);
								$VAL2 = substr($VAL2,$nfp_+1);
								//d($nfpp);
								switch($nfpp){
									case 'FIELD':
										if(!is_numeric($VAL2) && $VAL2!=''){
											$sql['BY'][]= "\n`fields$PROPC`.`FIELD_KEY`='".$VAL2."' AND ".SWITCH_IT($VAL,"`fv$PROPC`.`VALUE`");
											$PROP .="
											LEFT JOIN `g_user_fields_values` as `fv$PROPC` ON (`fv$PROPC`.`USER_ID`=`dblock`.`$ufXX`) 
											LEFT JOIN `g_user_fields` as `fields$PROPC` ON (`fv$PROPC`.`FIELD_ID`=`fields$PROPC`.`ID`)\n";
											$PROPC++;
										}
										break;
									default:
										$UPROP[$ufXX] = " LEFT JOIN `g_users` as `u$PROPC` ON (`u$PROPC`.`USER_ID`=`dblock`.`$ufXX`) ";
										$uf_ = $nfpp .'_'. $VAL2;
										//FIXME!!! UNSAFE FIELD EVALUATION
										$sql['BY'][]= SWITCH_IT($VAL,"`u$PROPC`.`$uf_`"); //"\n`u$PROPC`.`$uf_`='".$VAL."'";
										$PROPC++;
								}
							}
							break;
						case 'PROPERTYNUM':
						case 'PROPERTY':
							$searchV = $nfpp=='PROPERTY'?'VALUE':'VALUE_NUM';
							if(is_numeric($VAL2))
							{
								$sql['BY'][]= "props.`ID`='".$VAL2."'";
							}
							elseif($VAL2!='')
							{
								if(is_array($VAL) && isset($arv['TYPE_ID']) && is_numeric($arv['TYPE_ID']))
								{	
									
									foreach($VAL as $Akey=>$Aval)
									{
										if(is_array($Aval))
										{
											foreach($Aval as $Bkey=>$Bval)
											{
												$r='';
												$r=SWITCH_IT($Bval,"`$searchV`");
												$TYPE_ID=$DB->QResult("SELECT `ID` FROM `g_dblock_props` WHERE `PROP_KEY`='".$Bkey."' AND `TYPE_ID`='".$arv['TYPE_ID']."'");
												$D2[]="\n dblock.ID IN ( SELECT DISTINCT DBLOCK_ID FROM g_dblock_props_values WHERE `PROP_ID`='$TYPE_ID' AND $r )";
											}
											$sql['BY'][]=" (".implode(' OR ',$D2).") ";
											$continue='';											
										}
										else
										{
											$continue=1;
										}
									}
									if(!empty($continue))
									{
										$r='';
										$r=SWITCH_IT($VAL,"`$searchV`");
										$TYPE_ID=$DB->QResult("SELECT `ID` FROM `g_dblock_props` WHERE `PROP_KEY`='".$VAL2."' AND `TYPE_ID`='".$arv['TYPE_ID']."'");
										$sql['BY'][]="dblock.ID IN ( SELECT DISTINCT DBLOCK_ID FROM g_dblock_props_values WHERE `PROP_ID`='$TYPE_ID' AND $r[0] )";
									}
								}
								else
								{
								$sql['BY'][]= "\n`props$PROPC`.`PROP_KEY`='".$VAL2."'";
								$sql['BY'][]= SWITCH_IT($VAL,"`pv$PROPC`.`$searchV`"); //"\n`pv$PROPC`.`VALUE`='".$VAL."'";
								$PROP.="
								LEFT JOIN `g_dblock_props_values` as `pv$PROPC` ON (`dblock`.`ID`=`pv$PROPC`.`DBLOCK_ID`) 
								LEFT JOIN `g_dblock_props` as `props$PROPC` ON (`pv$PROPC`.`PROP_ID`=`props$PROPC`.`ID`)\n";
								$PROPC++;
								}
								
							}
							break;
					}
				}
			}
		}
		if($tab)
		{
			foreach($tab as $key=>$val)
			{
				if($key!='PROPS' && $key!='PROPS_VALUES' && $key!='DBLOCK_C')
				{
					$tabs=implode(',',$val); $TABLES[]=$tabs;
				}
				elseif($key=='PROPS' || $key=='PROPS_VALUES')
				{
					$tabs=implode(',',$val); $TABLES3[]=$tabs;
				}
			}
		}
		
		if($sql['BY'])
		{
			$BYFIR=" WHERE ".implode(" AND \n",$sql['BY']);
		}
		if(isset($TABLES)){
			$tabs2='dblock.`ID`, '. implode(', ',$TABLES);
		}else{
			
			$tabs2=' DISTINCT(dblock.`ID`),
			dblock.`TITLE`,
			dblock.`DATESTART`,
			dblock.`DATEEND`,
			dblock.`SHORTTEXT`,
			dblock.`SHORTTEXT_TYPE`,
			dblock.`FULLTEXT`,
			dblock.`FULLTEXT_TYPE`,
			dblock.`ACTIVE`,
			dblock.`SORT`,
			dblock.`AUTHOR`,
			dblock.`SHORTIMG`,
			dblock.`FULLIMG`,
			dblock.`TAGS`,
			dblock.`TYPE_ID`,
			dblock.`CREATED`,
			dblock.`UPDATED`,
			dblock.`SUBTYPE`,
			dblock.`DBLOCK_KEY`,
			dblock.`LINK`,
			dblock.`INDEXED`,
			dblock.`ININDEX`,
			type.`GROUP_ID` as TYPE_GROUP_ID,
			type.`NAME` as TYPE_NAME,
			type.`DESC` as TYPE_DESC,
			type.`ORDER` as TYPE_ORDER,
			type.`AUTHOR` as TYPE_AUTHOR,
			type.`CREATED` as TYPE_CREATED,
			type.`UPDATED` as TYPE_UPDATED,
			st.`ID` as SUBTYPE_ID,
			st.`NAME` as SUBTYPE_NAME,
			st.`TYPE_ID` as SUBTYPE_TYPE_ID,
			st.`CREATED` as SUBTYPE_CREATED,
			st.`PARENT` as SUBTYPE_PARENT,
			st.`DESC` as SUBTYPE_DESC,
			st.`UPDATED` as SUBTYPE_UPDATED,
			gr.`ID` as GROUP_ID,
			gr.`NAME` as GROUP_NAME,
			gr.`DESC` as GROUP_DESC,
			gr.`ORDER` as GROUP_ORDER,
			gr.`ACTIVE` as GROUP_ACTIVE,
			gr.`CREATED` as GROUP_CREATED,
			gr.`UPDATED` as GROUP_UPDATED
			';
		}
		
		$COUNT='';
		if($count!=COUNT_DEFINED_VALUE)
		{
			$COUNT=' SQL_CALC_FOUND_ROWS ';
		}
		
		$from="`g_dblock` as dblock 
		LEFT JOIN `g_dblock_types` as type ON (dblock.`TYPE_ID`=type.`ID`) 
		LEFT JOIN `g_dblock_subtypes` as st ON (st.`ID`=dblock.`SUBTYPE`) 
		LEFT JOIN `g_dblock_group` as gr ON (type.GROUP_ID=gr.ID) \n".$PROP.implode(' ',$UPROP);
		if($MULTY)
			$from.=" LEFT JOIN `g_dblock_multy` as `dblock_C` ON (`dblock`.`ID`=`dblock_C`.`DBLOCK_ID`) ";
			
		$SQL="SELECT ".$COUNT.$tabs2." FROM ".$from." ".$BYFIR.$ORD.$LIMIT;
		//d($SQL);
		$res = $DB->Query($SQL);
		if(!empty($COUNT))
		{
			$countAD=$DB->QResult("SELECT FOUND_ROWS()");
			//d($count);
		}
		$ArrRes=array();
		while($row=$DB->fetchAssoc($res))
		{
			if(($F5['DBLOCK']) && $row[$F5['DBLOCK']]!='')
			{
				$ArrRes[$row[$F5['DBLOCK']]]=$row;
				$ArrMAP[$row['ID']]=$row[$F5['DBLOCK']];
				$ArrRes[$ArrMAP[$row['ID']]]['PROPERTIES']=array();
			}
			else
			{
				$ArrRes[]=$row;
				$ArrMAP[$row['ID']]=count($ArrRes)-1;
				$ArrRes[$ArrMAP[$row['ID']]]['PROPERTIES']=array();
			}
		}
		
		if(empty($ArrMAP)) return array();
		$db_id=array_keys($ArrMAP);
		if($MULTY!=FALSE)
		{
			$tabs_C=
			'dblock_C.`DBLOCK_ID`,
			dblock_C.`TITLE`,
			dblock_C.`SHORTTEXT`,
			dblock_C.`SHORTTEXT_TYPE`,
			dblock_C.`FULLTEXT`,
			dblock_C.`FULLTEXT_TYPE`,
			dblock_C.`LANG`,
			dblock_C.`TYPE_ID`,
			dblock_C.`SUBTYPE`,
			dblock_C.`DBLOCK_KEY`';
			
			$from_C=" `g_dblock_multy` as dblock_C ";
			
			$SQLg="SELECT ".$tabs_C." FROM ".$from_C." WHERE dblock_C.`DBLOCK_ID` IN (".implode(' , ',$db_id).") AND dblock_C.LANG='".$LANG."'";
			
			$resl = $DB->Query($SQLg);
			while($rowl=$DB->fetchAssoc($resl))
			{
				//	d($rowl);
				if(!empty($rowl['TITLE']) && isset($ArrMAP[$rowl['DBLOCK_ID']]))
				{
					$ArrRes[$ArrMAP[$rowl['DBLOCK_ID']]]['TITLE']=$rowl['TITLE'];
					$ArrRes[$ArrMAP[$rowl['DBLOCK_ID']]]['SHORTTEXT']=$rowl['SHORTTEXT'];
					$ArrRes[$ArrMAP[$rowl['DBLOCK_ID']]]['SHORTTEXT_TYPE']=$rowl['SHORTTEXT_TYPE'];
					$ArrRes[$ArrMAP[$rowl['DBLOCK_ID']]]['FULLTEXT']=$rowl['FULLTEXT'];
					$ArrRes[$ArrMAP[$rowl['DBLOCK_ID']]]['FULLTEXT_TYPE']=$rowl['FULLTEXT_TYPE'];
					$ArrRes[$ArrMAP[$rowl['DBLOCK_ID']]]['LANG']=$rowl['LANG'];
				}
			}
		}
		
		if($sql['BY2'])
		{
			$BY2=" WHERE ".implode(" AND \n\t\t",$sql['BY2'])."\n\t\t AND pv.`DBLOCK_ID` IN (".implode(' , ',$db_id).")";
		}
		else
		{
			$BY2=" WHERE pv.`DBLOCK_ID` IN (".implode(' , ',$db_id).")";
		}
		if(isset($TABLES3)){$tabs3='pv.`DBLOCK_ID`, '.implode(', ',$TABLES3);}
		else
		{
			$tabs3=implode(', ',$table['PROPS']).','.implode(', ',$table['PROPS_VALUES']);
		}
		$from=" `g_dblock_props_values` as pv LEFT JOIN `g_dblock_props` as props ON (pv.`PROP_ID`=props.`ID`)";
		$SQL2="SELECT ".$tabs3." FROM ".$from." ".$BY2; //d($SQL2);
		$res2 = $DB->Query($SQL2);
		while($row2=$DB->fetchAssoc($res2))
		{//d($row2);
			if($row2['MULTIPLE']!=0)
			{
			$PMV=GTdblockprop::GetMP('',Array('DBLOCK_ID'=>$row2['DBLOCK_ID'],'PROP_ID'=>$row2['ID']));
			unset ($PMV['DBLOCK_ID']);
			unset ($PMV['PROP_ID']);
			$row2['VALUE']=$PMV;
			}
				if(($F5['PROPS']) && $row2[$F5['PROPS']]!='')
				{
					$ArrRes[$ArrMAP[$row2['DBLOCK_ID']]]['PROPERTIES'][$row2[$F5['PROPS']]]=$row2;
					if($row2['TYPE']=='DBLOCK')
					{
						if($row2['MULTIPLE']!=0)
						{
							foreach($PMV as $KKEY=>$VVAL)
							{
								if(!empty($VVAL['VALUE'])){$DIM[]=$VVAL['VALUE'];}
								$arMap[$VVAL['VALUE']][]=Array('DBLOCK'=>$ArrMAP[$row2['DBLOCK_ID']],'PROP'=>$row2[$F5['PROPS']],'KEY'=>$KKEY);
							}
						}
						else
						{
							if(!empty($row2['VALUE'])){$DIM[]=$row2['VALUE'];}
							$arMap[$row2['VALUE']][]=Array('DBLOCK'=>$ArrMAP[$row2['DBLOCK_ID']],'PROP'=>$row2[$F5['PROPS']]);;
						}
					}
				}
				else
				{
					$ArrRes[$ArrMAP[$row2['DBLOCK_ID']]]['PROPERTIES'][$row2['ID']]=$row2;
					$ARMV[$row2['ID']]=$row2['ID'];
					$count=count($ArrRes[$ArrMAP[$row2['DBLOCK_ID']]]['PROPERTIES'])-1;
					if($row2['TYPE']=='DBLOCK')
					{
						if($row2['MULTIPLE']!=0)
						{
							foreach($PMV as $KKEY=>$VVAL)
							{
								if(!empty($VVAL['VALUE'])){$DIM[]=$VVAL['VALUE'];}
								$arMap[$VVAL['VALUE']][]=Array('DBLOCK'=>$ArrMAP[$row2['DBLOCK_ID']],'PROP'=>$row2['ID'],'KEY'=>$KKEY);
							}
						}
						else
						{
							if(!empty($row2['VALUE'])){$DIM[]=$row2['VALUE'];}
							$arMap[$row2['VALUE']][]=Array('DBLOCK'=>$ArrMAP[$row2['DBLOCK_ID']],'PROP'=>$row2['ID']);;
						}						
					}
				}
		}
		
			if(isset($DIM)){
			$DIM=array_unique($DIM);
			$row3=GTdblock::Get('TITLE',Array('ID'=>$DIM));
			foreach($row3 as $key=>$val)
			{
				if(!empty($F5['LINK']))
				{
					foreach($arMap[$val['ID']] as $KEY=>$VAL)
					{
						if(isset($VAL['KEY']))
						{
						$K=(string)$VAL['KEY'];
						$ArrRes[$VAL['DBLOCK']]['PROPERTIES'][$VAL['PROP']]['VALUE'][$VAL['KEY']]=$val;
						}
						elseif(!isset($VAL['KEY']))
						{
						//d($VAL['PROP']);
						$ArrRes[$VAL['DBLOCK']]['PROPERTIES'][$VAL['PROP']]['VALUE']=$val;
						$ArrRes[$VAL['DBLOCK']]['PROP_'.$F5['LINK']]=$val;
						}
					}
				}
				else
				{
					foreach($arMap[$val['ID']] as $KEY=>$VAL)
					{
						if(isset($VAL['KEY']))
						{
						$K=(string)$VAL['KEY'];
						$ArrRes[$VAL['DBLOCK']]['PROPERTIES'][$VAL['PROP']]['VALUE'][$VAL['KEY']]=$val;
						}
						elseif(!isset($VAL['KEY']))
						{
						$ArrRes[$VAL['DBLOCK']]['PROPERTIES'][$VAL['PROP']]['VALUE']=$val;
						$ArrRes[$VAL['DBLOCK']][]=$val;
						}
					}
				}
			}

		}
		
		return $ArrRes;

	}
	
	function AddA($arVariables,$arVariables2=FALSE,$PM=False)
	{
		$MULTY=GTAPP::Conf('multilang_sync');
		if($MULTY!=FALSE)
		{
			GTdblock::Add($arVariables,$arVariables2,$PM);
			return TRUE;
		}
		else
		{		
			global $DB, $APP;
				if(!$arVariables['ACTIVE']){$arVariables['ACTIVE']='0';}
				$arVariables['CREATED']=date('Y-m-d H:m:s');
				$arVariables['UPDATED']=$arVariables['CREATED'];
				$arVariables['AUTHOR']=$APP->GetCurrentUserID();
				$arVariables= GTdblock::Check($arVariables);
			if(isset($arVariables['TITLE']))
			{
				$Value=$arVariables;
				$VKeys = array_keys($Value);
				$sql="INSERT INTO `g_dblock` (`".implode('`, `',$VKeys)."`) VALUES ('".implode('\', \'',$Value)."')";
				$res=$DB->Query($sql); 
				$LASTID = $DB->insertId();
				if($arVariables2!=FALSE)
				{
					$var=array();
					LoadClass('GTdblockprop');
					foreach($arVariables2 as $key=>$val)
					{
						$V2=array();
						$V2=$val;
						if($val['PROP_KEY'])
						{
						$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$arVariables['TYPE_ID']));
						$V2['PROP_ID']=$Props[0]['ID'];
						unset ($V2['PROP_KEY']);
						}
						$V2['DBLOCK_ID']=$LASTID;
						if($V2['DBLOCK_ID'] && $V2['PROP_ID'] && !empty($V2['VALUE']))
						{	
							$R='';
							$R=$DB->QResult("SELECT `ID` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."'");
							if(!empty($R))
							{
							$V2=GTdblockpropvalue::Check($V2);
							$var[]= "('".implode('\', \'',$V2)."')";
							}
						}
					}
					$VaKeys=Array();
					$VaKeys = array_keys($V2);
					if(!empty($VaKeys))
					{
						$sql="INSERT INTO `g_dblock_props_values` (`".implode('`, `',$VaKeys)."`) VALUES ".implode(',',$var)."";
						$res=$DB->Query($sql);
					}				
				}
				
				if($PM!=FALSE)
				{
					$var=array();
					LoadClass('GTdblockprop');
					foreach($PM as $key=>$val)
					{
						$V2=array();
						$V2=$val;
						if($val['PROP_KEY'])
						{
						$Props=GTdblockprop::Get('',Array('PROP_KEY'=>$V2['PROP_KEY'],'TYPE_ID'=>$arVariables['TYPE_ID']));
						$V2['PROP_ID']=$Props[0]['ID'];
						unset ($V2['PROP_KEY']);
						}
						$V2['DBLOCK_ID']=$LASTID;
						if($V2['DBLOCK_ID'] && $V2['PROP_ID'] && !empty($V2['VALUE']))
						{	
							$R='';
							$R=$DB->QResult("SELECT `ID` FROM `g_dblock_props` WHERE `ID`='".$V2['PROP_ID']."'");
							if(!empty($R))
							{
								$V2=GTdblockpropvalue::Check($V2);
								$var[]= "('".implode('\', \'',$V2)."')";
							}
						}
					}
					$VaKeys=Array();
					$VaKeys = array_keys($V2);
					if(!empty($VaKeys))
					{
						$sql="INSERT INTO `g_dblock_multy_values` (`".implode('`, `',$VaKeys)."`) VALUES ".implode(',',$var)."";
						$res=$DB->Query($sql);
					}
				}
				$R='';
				$R=$DB->QResult("SELECT `ID` FROM `g_dblock` WHERE `ID`='".$LASTID."'");
				if(!empty($R))
				{
					return TRUE;
				}
			}
			else
				return FALSE;
		}
	}
function LINK($TXT)
{
	$txt=(string)$TXT;
	$txt = explode("\n", $txt);
	$re1='((?:[a-z][a-z0-9_]*)).*?(\\d+)';	# Integer Number 1
	$r=array('ACTIVE'=>1);
	foreach($txt as $Dkey=>$DVal)
	{
		if ($c=preg_match_all ("/".$re1."/is", $DVal, $matches))
		{
		  $var1=$matches[1][0];
		  $int1=$matches[2][0];
		 $r=array_merge($r,array($var1=>$int1));
		}
	}
	if(!empty($r))
	{
		$R=GTdblock::GET(Array('ID','TITLE'),$r,'TITLE','',Array('ID'));
		$S=array(GetMessage('NOT_CHOOSEN')=>'0');
		foreach($R as $RKey=>$RVal)
		{
		$S=array_merge($S,array($RVal['TITLE']=>$RVal['ID']));
		}
		$S=array_flip($S); 
	}
	return $S;
}

function Props($TYPE_ID,$ID=FALSE,$ACT="NEW",$LANG="ru")
	{
		global $APP;
		$ArrRes = GTdblockprop::Get('',array('TYPE_ID'=>$TYPE_ID),'SORT'); 
		$MULTY=GTAPP::Conf('multilang_sync');
		if($MULTY!=FALSE)
		{
			if($ACT=="NEW")
			{ 
				if(!empty($ArrRes))
				{
					foreach($ArrRes as $key=>$val)
					{ 
						
						if ($val['REQUIRED']==1){$REQUIRED='*';}else{$REQUIRED='';}
						switch($val['TYPE'])
						{
							case 'LIST':
									$NAME=$val['NAME'];
									if($val['MULTIPLE']!=1)
									{
										$Sellect=array();
										$Sel=array(0=>GetMessage('NOT_CHOOSEN'));
										$Sellect = explode("\n", $val['VALUES']);
										foreach($Sellect as $Key=>$Val)
										{
											$RT=trim($Val);
											$Sel[$Val]=$RT;
										}
										$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select", "VALUE" =>$Sel);
									}
									else
									{
										$Sellect=array();
										$Sel=array(0=>GetMessage('NOT_CHOOSEN'));
										$Sellect = explode("\n", $val['VALUES']);
										foreach($Sellect as $Key=>$Val)
										{
											$RT=trim($Val);
											$Sel[$Val]=$RT;
										}
										$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'LIST['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select:multiple", "VALUE" =>$Sel);
									}
								break;
							case 'TEXT':
								$Props2[]=Array("DESC" =>$val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "textarea", "VALUE" =>'');
								break;
							case 'STRING':
								$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>'');
								break;
							case 'DBLOCK':
								if($val['MULTIPLE']!=1)
								{
									$S='';
									if(!empty($val['VALUES']))
									{
										$S=GTdblock::LINK($val['VALUES']);
										$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select", "VALUE" =>$S);
									}
								}
								else
								{
									/*$DBL = explode("\n", $val['VALUES']);
									foreach($DBL as $MPK=>$MPV)
									{*/
										$S=GTdblock::LINK($val['VALUES']);
										$MDB=array();
										$CH='';
										foreach($S as $Sk=>$Sv)
										{
											$MDB[]='<label><input type="checkbox" name="MDBLOCK['.$LANG.']['.$val['ID'].'][]" value="'.$Sk.'"> '.$Sv.'</label>';
										}
										$CH=implode('<br />',$MDB);
										$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'MDBLOCK['.$LANG.']['.$val['ID'].'][]', "VIEW" => 1, "TYPE" => "raw", "VALUE" =>$CH);
									//}
								}
								break;
							case 'URL':
								$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row4['VALUE']);
								break;
							case 'FILE':
									if($val['MULTIPLE']!=1)
									{
									$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'FILES'.$LANG.'['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
									}
								else{
									$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'MULTYFILES'.$LANG.'['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
									}
								break;
							case 'IMAGE':
								if($val['MULTIPLE']!=1)
									{
								$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'IMAGES['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
									}
								else{
								$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'MULTYIMAGES['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
									}
								break;
								default:
								$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row4['VALUE']);
								break;
						}
					}
					return $Props2;
				}
			}
			elseif($ACT=="EDIT")
			{
				if(!empty($ArrRes))
				{
					foreach($ArrRes as $key=>$val)
					{ 
						$res4=GTdblockpropvalue::Get('',Array('DBLOCK_ID'=>$ID,'PROP_ID'=>$val['ID'],'LANG'=>$LANG));
						$row4=$res4[0];
						
						if ($val['REQUIRED']==1){$REQUIRED='*';}else{$REQUIRED='';}
						switch($val['TYPE'])
						{
						case 'LIST':
							$NAME=$val['NAME'];
							if($val['MULTIPLE']!=1)
							{
								$Sellect=array();
								$Sel=array(0=>GetMessage('NOT_CHOOSEN'));
								$Sellect = explode("\n", $val['VALUES']);
								foreach($Sellect as $KEY=>$VAL)
								{
									$Sel[$VAL]=$VAL;
								}
								$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select:".$row4['VALUE'], "VALUE" =>$Sel);
							}
							else
							{
								$Sellect=array();
								$Sel=array(0=>GetMessage('NOT_CHOOSEN'));
								$Sellect = explode("\n", $val['VALUES']);
								foreach($Sellect as $Key=>$Val)
								{
									$Sel[$Val]=$Val;
								}
								$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'LIST['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select:multiple:".$row4['VALUE'], "VALUE" =>$Sel);
							}
						break;
						case 'TEXT':
						$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "textarea:".$row4['SWITCH'], "VALUE" =>$row4['VALUE']);
						break;
						case 'STRING':
						$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row4['VALUE']);
						break;
						case 'DBLOCK':
						if($val['MULTIPLE']!=1)
						{
							$S=GTdblock::LINK($val['VALUES']);
							$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select:".$row4['VALUE'], "VALUE" =>$S);
						}
						else
						{
							$S=GTdblock::LINK($val['VALUES']);
							$MP = GTdblockprop::GetMP('',array('DBLOCK_ID'=>$ID,'PROP_ID'=>$val['ID'],'LANG'=>$LANG));
							$MDB=array();
							$CH='';
							unset($S[0]);
							foreach($S as $Sk=>$Sv)
							{
								$jump='';
								foreach($MP as $MK=>$MV)
								{
									if($Sk==$MV['VALUE'])
									{
										$jump='checked';
										$Props2[]=Array("DESC" => "", "NAME" => "MDCHECK[$LANG][".$val['ID']."][]", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$Sk);
									}
								}								
								$MDB[]='<label><input type="checkbox" name="MDBLOCK['.$LANG.']['.$val['ID'].'][]" value="'.$Sk.'" '.$jump.'> '.$Sv.'</label>';
								
							}							
							$CH=implode('<br />',$MDB);
							$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'MDBLOCK['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "raw", "VALUE" =>$CH);
						}
						break;
						case 'URL':
						$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row4['VALUE']);
						break;
						case 'FILE': $NAME=$val['NAME'];
						if($val['MULTIPLE']!=1)
							{
								$FIL=array();
								$FIL=GTFile::Get('*',array('ID'=>$row4['VALUE']));//d($FIL);
								if(!empty($FIL))
								{
									$Props2[]=Array("DESC" =>'', "NAME" => '', "VIEW" => 1, "TYPE" => "RAW", "VALUE" =>'<a href="'.$FIL[0]['URL'].'">'.$FIL[0]['LOCAL_NAME'].'</a>
									<input type="checkbox" name="DeleteFS['.$LANG.']['.$val['ID'].']" value="'.$FIL[0]['ID'].'"> удалить');
									$Props2[]=Array("DESC" =>'', "NAME" => 'FILE_ID['.$val['ID'].']', "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$FIL[0]['ID']);
								}
								$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'FILES'.$LANG.'['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
							}
						else
							{
								$F=array();
								$F=GTdblockprop::GetMP('',Array('PROP_ID'=>$val['ID'],'DBLOCK_ID'=>$ID,'LANG'=>$LANG)); 
								foreach($F as $Key=>$Val)
								{
									$FIL=array();
									$FIL=GTFile::Get('*',array('ID'=>$Val['VALUE']));
									$FIL=$FIL[0];
									if(!empty($FIL))
									{
										$Props2[]=Array("DESC" =>'', "NAME" => '', "VIEW" => 1, "TYPE" => "RAW", "VALUE" =>'<a href="'.$FIL['URL'].'">'.$FIL['LOCAL_NAME'].'</a>
										<input type="checkbox" name="DeleteF['.$LANG.']['.$val['ID'].']" value="'.$FIL['ID'].'"> удалить');
									}
								}
								$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'MULTYFILES'.$LANG.'['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
							}
						break;
						case 'IMAGE': $NAME=$val['NAME'];
						if($val['MULTIPLE']!=1)
							{
								$IMG=array();
								$IMG=GTFile::Get('*',array('ID'=>$row4['VALUE']));//d($IMG);
								if(!empty($IMG))
								{
									$Props2[]=Array("DESC" =>'', "NAME" => '', "VIEW" => 1, "TYPE" => "RAW", "VALUE" =>'<img src="'.$IMG[0]['URL'].'" width="150px" alt="gtinx" title="gtinx"><br />
									<input type="checkbox" name="DeleteFS['.$LANG.']['.$val['ID'].']" value="'.$IMG[0]['ID'].'"> удалить');
									$Props2[]=Array("DESC" =>'', "NAME" => 'IMG_ID['.$val['ID'].']', "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$IMG[0]['ID']);
								}
								$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'IMAGES'.$LANG.'['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
							}
						else
							{
								$F=array();
								$F=GTdblockprop::GetMP('',Array('PROP_ID'=>$val['ID'],'DBLOCK_ID'=>$ID,'LANG'=>$LANG)); 
								foreach($F as $Key=>$Val)
								{
									$FIL=array();
									$FIL=GTFile::Get('*',array('ID'=>$Val['VALUE']));//d($FIL);
									$FIL=$FIL[0];
									if(!empty($FIL))
									{
										$Props2[]=Array("DESC" =>'', "NAME" => '', "VIEW" => 1, "TYPE" => "RAW", "VALUE" =>'<img src="'.$FIL['URL'].'" width="150px" alt="'.$FIL['LOCAL_NAME'].'" title="'.$FIL['LOCAL_NAME'].'"><br />
										<input type="checkbox" name="DeleteF['.$LANG.']['.$val['ID'].']" value="'.$FIL['ID'].'"> удалить');
									}
								}
								$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'MULTYIMAGES'.$LANG.'['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
							}
						break;
						default:
						$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$LANG.']['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row4['VALUE']);
						break;
						}
					}
					return $Props2;
				}
			}
		}
		else
		{ 
			if($ACT=="NEW")
			{ 
				foreach($ArrRes as $key=>$val)
				{ 
					
					if ($ArrRes[$key]['REQUIRED']==1){$REQUIRED='*';}else{$REQUIRED='';}
					switch($ArrRes[$key]['TYPE'])
					{
					case 'LIST':
						$NAME=$val['NAME'];
						if($val['MULTIPLE']!=1)
						{
							$Sellect=array();
							$Sel=array(0=>GetMessage('NOT_CHOOSEN'));
							$Sellect = explode("\n", $val['VALUES']);
							foreach($Sellect as $Key=>$Val)
							{
								$RT=trim($Val);
								$Sel[$Val]=$RT;
							}
							$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select", "VALUE" =>$Sel);
						}
						else
						{
							$Sellect=array();
							$Sel=array(0=>GetMessage('NOT_CHOOSEN'));
							$Sellect = explode("\n", $val['VALUES']);
							foreach($Sellect as $Key=>$Val)
							{
								$RT=trim($Val);
								$Sel[$Val]=$RT;
							}
							$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'LIST['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select:multiple", "VALUE" =>$Sel);
						}
					break;
					case 'TEXT':
					$Props2[]=Array("DESC" =>$val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "textarea", "VALUE" =>'');
					break;
					case 'STRING':
					$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>'');
					break;
					case 'DBLOCK':
					if($val['MULTIPLE']!=1)
					{
						$S='';
						if(!empty($val['VALUES']))
						{
							$S=GTdblock::LINK($val['VALUES']);
							$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select", "VALUE" =>$S);
						}
					}
					else
					{
						/*$DBL = explode("\n", $val['VALUES']);
						foreach($DBL as $MPK=>$MPV)
						{*/
							$S=GTdblock::LINK($val['VALUES']);
							$MDB=array();
							$CH='';
							foreach($S as $Sk=>$Sv)
							{
								$MDB[]='<label><input type="checkbox" name="MDBLOCK['.$val['ID'].'][]" value="'.$Sk.'"> '.$Sv.'</label>';
							}
							$CH=implode('<br />',$MDB);
							$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'MDBLOCK_'.$val['ID'].'', "VIEW" => 1, "TYPE" => "raw", "VALUE" =>$CH);
						//}
					}
					break;
					case 'URL':
					$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row4['VALUE']);
					break;
					case 'FILE':
						if($val['MULTIPLE']!=1)
						{
						$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'FILES['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
						}
					else{
						$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'MULTYFILES['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
						}
					break;
					case 'IMAGE':
					if($val['MULTIPLE']!=1)
						{
					$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'IMAGES['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
						}
					else{
					$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'MULTYIMAGES['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
						}
					break;
					default:
					$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row4['VALUE']);
					break;
					}
				}
				return $Props2;
			}
			else
			{
				foreach($ArrRes as $key=>$val)
					{ 
						$res4=GTdblockpropvalue::Get('',Array('DBLOCK_ID'=>$ID,'PROP_ID'=>$val['ID']));
						$row4=$res4[0];
						//d($row4);
						if ($val['REQUIRED']==1){$REQUIRED='*';}else{$REQUIRED='';}
						switch($val['TYPE'])
						{
						case 'LIST':
							$NAME=$val['NAME'];
							if($val['MULTIPLE']!=1)
							{
								$Sellect=array();
								$Sel=array(0=>GetMessage('NOT_CHOOSEN'));
								$Sellect = explode("\n", $val['VALUES']);
								foreach($Sellect as $KEY=>$VAL)
								{
									$Sel[$VAL]=$VAL;
								}
								$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select:".$row4['VALUE'], "VALUE" =>$Sel);
							}
							else
							{
								$Sellect=array();
								$Sel=array(0=>GetMessage('NOT_CHOOSEN'));
								$Sellect = explode("\n", $val['VALUES']);
								foreach($Sellect as $Key=>$Val)
								{
									$Sel[$Val]=$Val;
								}
								$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'LIST['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select:multiple:".$row4['VALUE'], "VALUE" =>$Sel);
							}
						break;
						case 'TEXT':
						$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "textarea:".$row4['SWITCH'], "VALUE" =>$row4['VALUE']);
						break;
						case 'STRING':
						$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row4['VALUE']);
						break;
						case 'DBLOCK':
						if($val['MULTIPLE']!=1)
						{
							$S=GTdblock::LINK($val['VALUES']);
							$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select:".$row4['VALUE'], "VALUE" =>$S);
						}
						else
						{
							$S=GTdblock::LINK($val['VALUES']);
							$MP = GTdblockprop::GetMP('',array('DBLOCK_ID'=>$ID,'PROP_ID'=>$val['ID']));
							$MDB=array();
							$CH='';
							unset($S[0]);
							foreach($S as $Sk=>$Sv)
							{
								$jump='';
								foreach($MP as $MK=>$MV)
								{
									if($Sk==$MV['VALUE'])
									{
										$jump='checked';
										$Props2[]=Array("DESC" => "", "NAME" => "MDCHECK[".$val['ID']."][]", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$Sk);
									}
								}								
								$MDB[]='<label><input type="checkbox" name="MDBLOCK['.$val['ID'].'][]" value="'.$Sk.'" '.$jump.'> '.$Sv.'</label>';
								
							}							
							$CH=implode('<br />',$MDB);
							$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'MDBLOCK['.$val['ID'].']', "VIEW" => 1, "TYPE" => "raw", "VALUE" =>$CH);
						}
						break;
						case 'URL':
						$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row4['VALUE']);
						break;
					case 'FILE': $NAME=$val['NAME'];
						if($val['MULTIPLE']!=1)
							{
							$FIL=array();
							$FIL=GTFile::Get('*',array('ID'=>$row4['VALUE']));//d($FIL);
							if(!empty($FIL))
							{
							$Props2[]=Array("DESC" =>'', "NAME" => '', "VIEW" => 1, "TYPE" => "RAW", "VALUE" =>'<a href="'.$FIL[0]['URL'].'">'.$FIL[0]['LOCAL_NAME'].'</a><input type="checkbox" name="DeleteF[]" value="'.$FIL[0]['ID'].'"> удалить');
							$Props2[]=Array("DESC" =>'', "NAME" => 'FILE_ID['.$val['ID'].']', "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$FIL[0]['ID']);
							}
							$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'FILES['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
							}
						else
							{
								$F=array();
								$F=GTdblockprop::GetMP('',Array('PROP_ID'=>$val['ID'],'DBLOCK_ID'=>$ID)); 
								foreach($F as $Key=>$Val)
								{
									$FIL=array();
									$FIL=GTFile::Get('*',array('ID'=>$Val['VALUE']));
									$FIL=$FIL[0];
									if(!empty($FIL))
									{
										$Props2[]=Array("DESC" =>'', "NAME" => '', "VIEW" => 1, "TYPE" => "RAW", "VALUE" =>'<a href="'.$FIL['URL'].'">'.$FIL['LOCAL_NAME'].'</a><input type="checkbox" name="DeleteF[]" value="'.$FIL['ID'].'"> удалить');
									}
								}
								$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'MULTYFILES['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
							}
						break;
					case 'IMAGE': $NAME=$val['NAME'];
						if($val['MULTIPLE']!=1)
							{
								$IMG=array();
								$IMG=GTFile::Get('*',array('ID'=>$row4['VALUE']));//d($IMG);
								if(!empty($IMG))
								{
									$Props2[]=Array("DESC" =>'', "NAME" => '', "VIEW" => 1, "TYPE" => "RAW", "VALUE" =>'<img src="'.$IMG[0]['URL'].'" width="150px" alt="gtinx" title="gtinx"><br /><input type="checkbox" name="DeleteF[]" value="'.$IMG[0]['ID'].'"> удалить');
									$Props2[]=Array("DESC" =>'', "NAME" => 'IMG_ID['.$val['ID'].']', "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$IMG[0]['ID']);
								}
								$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'IMAGES['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
							}
						else
							{
								$F=array();
								$F=GTdblockprop::GetMP('',Array('PROP_ID'=>$val['ID'],'DBLOCK_ID'=>$ID)); //d($F);
								foreach($F as $Key=>$Val)
								{
									$FIL=array();
									$FIL=GTFile::Get('*',array('ID'=>$Val['VALUE']));//d($FIL);
									$FIL=$FIL[0];
									if(!empty($FIL))
									{
										$Props2[]=Array("DESC" =>'', "NAME" => '', "VIEW" => 1, "TYPE" => "RAW", "VALUE" =>'<img src="'.$FIL['URL'].'" width="150px" alt="'.$FIL['LOCAL_NAME'].'" title="'.$FIL['LOCAL_NAME'].'"><br /><input type="checkbox" name="DeleteF[]" value="'.$FIL['ID'].'"> удалить');
									}
								}
								$Props2[]=Array("DESC" => $NAME.$REQUIRED, "NAME" => 'MULTYIMAGES['.$val['ID'].']', "VIEW" => 1, "TYPE" => "file", "VALUE" =>'');
							}
						break;
						default:
						$Props2[]=Array("DESC" => $val['NAME'].$REQUIRED, "NAME" => 'PROPS['.$val['ID'].']', "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row4['VALUE']);
						break;
						}
					}return $Props2;
			}
		}
	}
	
	function Count($t=FALSE,$arv=FALSE,$order=FALSE,$limit=FALSE, $FIVE=FALSE)
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
		$table['DBLOCK']=Array(
						'ID'=>'dblock.`ID`',
						'TITLE'=>'dblock.`TITLE`',
						'DATESTART'=>'dblock.`DATESTART`',
						'DATEEND'=>'dblock.`DATEEND`',
						'SHORTTEXT'=>'dblock.`SHORTTEXT`',
						'SHORTTEXT_TYPE'=>'dblock.`SHORTTEXT_TYPE`',
						'FULLTEXT'=>'dblock.`FULLTEXT`',
						'FULLTEXT_TYPE'=>'dblock.`FULLTEXT_TYPE`',
						'ACTIVE'=>'dblock.`ACTIVE`',
						'SORT'=>'dblock.`SORT`',
						'AUTHOR'=>'dblock.`AUTHOR`',
						'SHORTIMG'=>'dblock.`SHORTIMG`',
						'FULLIMG'=>'dblock.`FULLIMG`',
						'TAGS'=>'dblock.`TAGS`',
						'TYPE_ID'=>'dblock.`TYPE_ID`',
						'CREATED'=>'dblock.`CREATED`',
						'UPDATED'=>'dblock.`UPDATED`',
						'DBLOCK_KEY'=>'dblock.`DBLOCK_KEY`',
						'LINK'=>'dblock.`LINK`',
						'INDEXED'=>'dblock.`INDEXED`',
						'SUBTYPE'=>'dblock.`SUBTYPE`');
		if($order)
		{
			if($table['DBLOCK'][$order])
			{
				$ORD=' ORDER BY '.$table['DBLOCK'][$order].' ';
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
							case 'DBLOCK':
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
							case 'DBLOCK':
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
			$tabs2='dblock.`ID`, '. implode(', ',$TABLES);
		}
		else
		{
			$tabs2=implode(', ',$table['DBLOCK']);
		}
		$SQL="SELECT COUNT(`ID`) FROM `g_dblock` as dblock ".$BYFIR.$ORD.$LIMIT;
		$res=$DB->Query($SQL);
		$row=$DB->fetchAssoc($res);
		return $row['COUNT(`ID`)'];
	}
	
	function GetCSV($iArv)
	{
		global $DB;
		$res=GTdblock::Get('',$iArv['PARAMS']); 
		$header='';
		foreach($res as $Skey=>$Sval)
		{	$colums=Array();
			$colums2=array();
			foreach($Sval as $key=>$val)
			{
				if(!is_array($val))
				{
					$colums[]=$key;
					$colums2[]=addcslashes($val,"\\\'\"&\n\r\t");
				}
				else
				{
					//$colums[]=$key;
					foreach($val as $key2 =>$val2)
					if(is_array($val2))
					{
						foreach($val2 as $key3=>$val3)
						{
							$colums[]=$key3;
							$colums2[]=addcslashes($val3,"\\\'\"&\n\r\t");
						}
					}
				}
			}$header2[]='"'.implode('";"',$colums2).'";';
		}
		$Head='"'.implode('";"',$colums).'";';
		$header="".implode("",$header2);
	
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=".$iArv['FILENAME'].".CSV");
		header("Pragma: no-cache");
		header("Expires: 0");
		
		print $Head.$header;
		die();
	}
	
	function CSVtoMYSQL($arv)//fix me!!!
	{
		d($arv);
		$table['DBLOCK']=Array(
			'ID'=>'`ID`',
			'TITLE'=>'`TITLE`',
			'DATESTART'=>'`DATESTART`',
			'DATEEND'=>'`SHORTTEXT`',
			'SHORTTEXT_TYPE'=>'`SHORTTEXT_TYPE`',
			'FULLTEXT'=>'`FULLTEXT`',
			'FULLTEXT_TYPE'=>'`FULLTEXT_TYPE`',
			'ACTIVE'=>'`ACTIVE`',
			'SORT'=>'`SORT`',
			'AUTHOR'=>'`AUTHOR`',
			'SHORTIMG'=>'`SHORTIMG`',
			'FULLIMG'=>'`FULLIMG`',
			'TAGS'=>'`TAGS`',
			'TYPE_ID'=>'`TYPE_ID`',
			'CREATED'=>'`CREATED`',
			'UPDATED'=>'`UPDATED`',
			'DBLOCK_KEY'=>'`DBLOCK_KEY`',
			'LINK'=>'`LINK`',
			'INDEXED'=>'`INDEXED`',
			'SUBTYPE'=>'`SUBTYPE`');
		
		$table['PROPS_VALUES']=Array(
				'DBLOCK_ID'=>'`DBLOCK_ID`',
				'PROP_ID'=>'`PROP_ID`',
				'VALUE'=>'`VALUE`',
				'SWITCH'=>'`SWITCH`',
				'VALUE_NUM'=>'`VALUE_NUM`');
		
	}
	
	function GetSearchableContent($arrParams=FALSE)
	{
		
		if(!empty($arrParams['DATE']))
		{
			$Search['UPDATED']='<'.$arrParams['DATE'];
			
		}
		if(!empty($arrParams['ID']) && is_numeric($arrParams['ID']))
		{
			$Search['ID']='>'.$arrParams['ID'];
			 
			
		}
		if(empty($Search))
		{
			$Search='';
			 
		}
		if($arrParams['LIMIT'])
		{
			$iLimit=Array(0,$arrParams['LIMIT']);
		}
		else
		{
			$iLimit=Array(0,100);
		}
		$Search['INDEXED']='1';
		$Search['ININDEX']='0';
		//$iRes=GTdblock::Get('',$Search,Array('ID','ASC'),$iLimit); //d($iRes);
		$MULTY=GTAPP::Conf('multilang_sync');
		global $DB;
		if($MULTY!=FALSE)
		{
			$SH=array();
			foreach($Search as $key=>$val)
			{
				$SH[]="\n`$key`='$val'";
			}
			$LIM="\nLIMIT ".$iLimit[0].",".$iLimit[1];
			$sql="SELECT * FROM `g_dblock` WHERE ".implode(" AND ",$SH).$LIM;
			$iRez=$DB->Query($sql);
			$iID=array();
			while($iRow=$DB->fetchAssoc($iRez))
			{
				if(!empty($iRow)) 
				{
					$iID[]=$iRow['ID'];
					$iLang[$iRow['ID']]=$iRow['LINK'];
				}
			}
			$iRes=array();
			if(!empty($iID))
			{
				$IDs="\n('".implode("','",$iID)."')";
				$sqlM="SELECT * FROM `g_dblock_multy` WHERE `DBLOCK_ID` IN $IDs"; //d($sqlM);
				$iRezmulty=$DB->Query($sqlM);
				while ($IRowMulty=$DB->fetchAssoc($iRezmulty))
				{
					if($iLang[$IRowMulty['DBLOCK_ID']])
					{	
						
						$IRowMulty['LINK']='/'.$IRowMulty['LANG'].$iLang[$IRowMulty['DBLOCK_ID']];
					
					}
					$IRowMulty['ID']=$IRowMulty['DBLOCK_ID'];
					unset($IRowMulty['DBLOCK_ID']);
					$iRes[]=$IRowMulty;
				}
			}
		}
		else
		{
			$SH=array();
			foreach($Search as $key=>$val)
			{
				$SH[]="\n`$key`='$val'";
			}
			$iRez=$DB->Query("SELECT * FROM `g_dblock` WHERE ".implode(" AND ",$SH));
			$iRes=array();
			while($iRow=$DB->fetchAssoc($iRez))
			{
				$iRes[]=$iRow['ID'];
			}
		}//d($iRes);
		if(empty($iRes))
		{
			return FALSE;
		}
		$iEl=array();//d($iRes);
		foreach($iRes as $iVal)
		{
			$iEls=array();
			$iEl2=array();
			foreach($iVal['PROPERTIES'] as $key=>$val)
			{
				$iEl2['VALUE'.$key]=$val['VALUE'];
			}
			if(!empty($iVal['UPDATED'])){$iVal['UPDATED']=$iVal['CREATED'];}
			$iEls=array(
				'ID'=>$iVal['ID'],
				'TITLE'=>$iVal['TITLE'],
				'SHORTTEXT'=>$iVal['SHORTTEXT'],
				'FULLTEXT'=>$iVal['FULLTEXT'],
				'DATE'=>$iVal['UPDATED'],
				'LANG'=>$iVal['LANG'],
				'LINK'=>$iVal['LINK'],
				'TAGS'=>$iVal['TAGS']);
			$iEl[]=array_merge($iEls,$iEl2);
		}
		$c=array();
		foreach($iEl as $key=>$val)
		{
			foreach($val as $ikey=>$ival)
			{
				$ival=trim($ival);
				if(empty($ival))
				{
					$c[][$key]=$ikey;
				}
			}
		}
		
		foreach($c as $val)
		{
			foreach($val as $key=>$ival)
			{
				unset($iEl[$key][$ival]);
			}
		}
		//d($iEl);
		//d($iEl);
		return $iEl;
	}
	
	function GetSerchablePages()
	{
		global $DB;
		$dir=GTDOCROOT;
		$scan = scandir($dir);
		$noscan=array('..','.htaccess','.svn','gtinx',
													'.menu.bottom.kz.php',
													'.menu.bottom.php',
													'.menu.left.kz.php',
													'.menu.left.php',
													'.menu.top.kz.php',
													'.menu.top.php');
													
		foreach($scan as $key=>$val)
		{
			if($val{0}!='.' && $val!='gtinx' && $val!='medialibrary')
			{
				
				if (is_dir($dir.'/'.$val))
				{	
					$iscan = scandir($dir.'/'.$val);
					foreach($iscan as $ifile)
					{
						if($ifile{0}!='.' && $ifile{0}!='-')
						{
							if(is_file($dir.'/'.$val.'/'.$ifile) && file_exists($dir.'/'.$val.'/'.$ifile))
							{
								$di[$val][$ifile]='/'.$val.'/'.$ifile;
								$file=$dir.'/'.$val.'/'.$ifile;
								$open=file_get_contents($file);
								$handle=phpFileParse($open);
								$re1='(SetPageTitle).*?';	
								$re2='(\\\'.*?\\\')';

								if ($c=preg_match_all ("/".$re1.$re2."/is", $handle['HEAD'], $matches))
								{
									$string1=$matches[2][0];
									$string1=str_replace("'","",$string1);
									$h[$val][$ifile]=$string1;
									if(!$h2[$val][$ifile])
									{
										$h2[$val][$ifile]=explode(" ",$string1);
									}
								}
								
								$re1='(SetPageTitle).*?';	
								$re2='(".*?")';	

								if ($c=preg_match_all ("/".$re1.$re2."/is", $handle['HEAD'], $matches))
								{
									$string1=$matches[2][0];
									$string1=str_replace('"',"",$string1);
									$h[$val][$ifile]=$string1;
									$h2[$val][$ifile]=explode(" ",$string1);
								}
								
								$string2=$handle['CONTENT'];
								
								$string2=preg_replace('/\<\?.*\?\>/ms','',$string2);
								$string2=preg_replace('/\<script(.*)\/script\>/ims','',$string2);
								$string2=preg_replace('/[+\.,_\(\)\\:;\'"-]/imsu','',$string2);
								$string2=preg_replace('/[[:digit:]]/imsu','',$string2);
								$string2=str_replace("\n"," ",$string2);
								$string2=strip_tags($string2);
								$tt=array();
								$tt=explode(" ",$string2);
								foreach($tt as $str)
								{
									$str=trim($str);
									if(strlen($str)>2 && !empty($str))
									{
										$h2[$val][$ifile][]=$str;
									}
								}
							}
						}
					}
				}
			}
		}

		if(!empty($h2))
		{
			//$LINKS="('".implode('\',\'',$di)."')";
			$DB->Query("DELETE FROM `search_cache_elements` WHERE `MODULE`='TEXTPAGE'");
			foreach($h as $key=>$val)
			{
				foreach($val as $iK=>$iV)
				{
				$TITLE=$iV;
				$MODULE="TEXTPAGE";
				$CREATED=strtotime(date('Y-m-d H:m:s'));
				$LINK=$di[$key][$iK];
				$SQL="INSERT IGNORE INTO `search_cache_elements` (`ENAME`,`ELAST_CACHED`,`MODULE`,`LINK`) VALUES ('$TITLE','$CREATED','$MODULE','$LINK')"; //d($SQL);
				$DB->Query($SQL);
				$CEID=$DB->insertId();
					if($CEID!='0')
					{
						$Map[$key][$iK]=$CEID;
						$Mapse[]=$CEID;
					}
				}
			}
			
			if(!empty($Map))
			{
				$Maps="('".implode('\',\'',$Mapse)."')";
				$DB->Query("DELETE FROM `search_cache_constructs` WHERE `CEID` IN $Maps");
				$DB->Query("DELETE FROM `search_cache_counts` WHERE `CEID` IN $Maps");
				$words=$h2;
				foreach($words as $key=>$val)
				{
					foreach($val as $iK=>$iV)
					{
						foreach($iV as $poth=>$word)
						{
							$words3[]=$word;
						}
					}				
				}
				
				$words3=array_unique($words3);
				$ins="('".implode("'),('",$words3)."')";// d($ins);
				$IN="('".implode("','",$words3)."')";// d($IN);
				$sql="INSERT IGNORE INTO `search_cache_words` (`W_TEXT`) VALUES ".$ins."";
				$DB->Query($sql);
				$sql2="SELECT * FROM `search_cache_words` WHERE `W_TEXT` IN ".$IN;
				$res=$DB->Query($sql2);
				$iLasr=array();
				$COUNT=array();
				while($row=$DB->fetchAssoc($res))
				{
					foreach($words as $key=>$val)
					{
						foreach($val as $iK=>$iV)
						{
							foreach($iV as $poth=>$word)
							{
								if($row['W_TEXT']==$word)
								{
									$iLasr[]=array('WID'=>$row['W_ID'],'CEID'=>$Map[$key][$iK],'POSITION'=>$poth);
									$COUNT[$row['W_ID']][$Map[$key][$iK]]++;
								}
							}
						}				
					}
				}
			}
			
			if(!empty($COUNT))
			{
				foreach($COUNT as $key => $val)
				{
					foreach($val as $ikey => $ival)
					{
						$iCNT[]="('$key','$ikey','$ival')";
					}
				}
				$iCMP=implode(',',$iCNT);
				if(!empty($iCMP))
				{
					$sql3="INSERT INTO `search_cache_counts` (`WID`,`CEID`,`COUNT`) VALUES ".$iCMP;
					$DB->Query($sql3);
				}
			}
			if(!empty($iLasr))
			{
				$imp='';
				foreach($iLasr as $val)
				{
					$iLasv[]="('".implode("','",$val)."')";
				}
				$imp=implode(',',$iLasv);
				if(!empty($imp))
				{
					$sql3="INSERT INTO `search_cache_constructs` (`WID`,`CEID`,`POSITION`) VALUES ".$imp;
					$DB->Query($sql3);
				}
				
			}
		}
	}
	
	function Statistic($arv)
	{
		global $DB;
		//d($arv);
		$table['GROUP']=array('GROUP'=>'`GROUP_ID`','BASE'=>'`g_dblock_types`');
		$table['TYPE']=array('TYPE'=>'`TYPE_ID`','BASE'=>'`g_dblock_subtypes`');
		$table['SUBTYPE']=array('SUBTYPE'=>'`SUBTYPE`','BASE'=>'`g_dblock`');
		$table['DBLOCK']=array('TYPE_ID'=>'`TYPE_ID`','BASE'=>'`g_dblock`');
		$table['cDBLOCK']=array('F_ID'=>'`TYPE_ID`','LANG'=>'`LANG`','BASE'=>'`g_dblock_multy`');
		$table['cSUBTYPE']=array('F_ST'=>'`SUBTYPE`','LANG'=>'`LANG`','BASE'=>'`g_dblock_multy`');
		foreach($arv as $key=>$val)
		{
			foreach($table as $tk=>$tv)
			{
				if($table[$tk][$key])
				{
					$sql[]="".$table[$tk][$key]."='$val'";
					$from=$tv['BASE'];
				}
			}
		}
		$SQL="SELECT SQL_CALC_FOUND_ROWS  * FROM $from WHERE ".implode(' AND ',$sql);
		//d($SQL);
		$DB->Query($SQL);
		$countAD=$DB->QResult("SELECT FOUND_ROWS()");
		return $countAD;
		
	}
	function STAT()
	{
		global $DB;
		$arAllGroups=array();
		$sql="SELECT `ID`,`NAME`,`DESC` FROM `g_dblock_group`";
		$res=$DB->Query($sql);
		while ($row=$DB->FetchArray($res))
		{
		$arUGroups=array();
			$sql2="SELECT `ID`,`NAME`,`DESC` FROM `g_dblock_types` WHERE `GROUP_ID`='".$row['ID']."'";
			$res2=$DB->Query($sql2);
			
			while($row2=$DB->FetchArray($res2))
			{
				$arST=array();
				$sql3="SELECT `ID`,`NAME` FROM `g_dblock_subtypes` WHERE `TYPE_ID`='".$row2['ID']."' AND `PARENT`='0'";
				$res3=$DB->Query($sql3);
				while($row3=$DB->FetchArray($res3))
				{
					$countS=GTdblock::Statistic(array('SUBTYPE'=>$row3['ID']));
					$wLangS=GTdblock::Statistic(array('F_ST'=>$row3['ID'],'LANG'=>'kz'));
					$oLangS=$countS-$wLang;
					$arST[]=array($row3['NAME'],'DBLOCKS'=>$countS,'wLANG'=>$wLangS,'oLANG'=>$oLangS);
				}
				$countT=GTdblock::Statistic(array('TYPE'=>$row2['ID']));
				$BLang=GTdblock::Statistic(array('TYPE_ID'=>$row2['ID']));
				$wLang=GTdblock::Statistic(array('F_ID'=>$row2['ID'],'LANG'=>'kz'));
				$oLang=$BLang-$wLang;
				$arUGroups[]=array($row2['NAME'],'SUBTYPES'=>$countT,'DBLOCKS'=>$BLang,'wLANG'=>$wLang,'oLANG'=>$oLang,$arST);
			}
			$count='';
			$count=GTdblock::Statistic(array('GROUP'=>$row['ID']));
			$arAllGroups[] = array(
				$row['NAME'],
				'TYPES'=>$count,
				$arUGroups
			);
		}
		return $arAllGroups;
	}
	
	function UpLink($TXT,$ID)
	{
		global $DB;
		$sql="UPDATE `g_dblock` SET `LINK`='$TXT' WHERE `ID`='".$ID."'";
		$DB->Query($sql);
	}
}
?>