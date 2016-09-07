<?
Class ControllerDblocks {
       
	   function __construct() 
		{
			global $DB,$APP;
			if($_POST)
			{
				$POST = $_POST;
			}
			if($_GET)
			{
				$GET= $_GET;
			}
			include(GTROOT.'/modules/dblocks/.prop.php');
			foreach($arrModProperties["PRIVS"] as $key=>$val)
			{
			$access[$val]=$APP->UserAble($val,$arrModProperties["NAME_KEY"]);
			}
			//print_r($access);
			if(isset($GET))
			{
				foreach($GET as $key=>$val)
				{
				switch($key)
					{
						case 'act':
							switch($val)
							{
							case 'export':
								$this->Export();
								break;
							case 'statistics':
								$this->Statistics();
								break;
							case 'search_options':
								$this->SEARCH_OPTIONS($_GET);
								break;
							case 'import':
								$this->Import();
								break;
							case 'groups':
								$this->Groups();
								break;
							case 'group_filter':
								$this->Groups($POST);
								break;
							case 'group_edit':
								$this->Edit_Group($GET['id']);
								break;
							case 'new_group':
								$this->NewGroup();
								break;
							case 'types':
								$this->Types($GET['id']);
								break;
							case 'type_filter':
								$this->Types($GET['id'],$POST);
								break;
							case 'type_edit':
								$this->Edit_Types($GET['id']);
								break;
							case 'new_type':						
								$this->New_Types($GET['id']);
								break;
							case 'dblocks':
								$this->dblocks($GET['id'],'',$GET['page']);
								break;
							case 'dblock_filter':
								$this->dblocks($GET['id'],$POST,$GET['page']);
								break;
							case 'dblock_edit':		
								$this->Edit_dblock($GET['id']);
								break;
							case 'new_dblock':
								$this->New_dblock($GET['type'],$GET['subtype']);
								break;
							case 'get_subblock':
								$this->Get_subblock($GET['subtype'],$GET['type'],$GET['page']);
								break;
							case 'dblock_subfilter':
								$this->Get_subblock($GET['subtype'],$GET['type'],$GET['page'],$POST);
								break;
							case 'new_subtype':
								$this->New_subtype($_REQUEST['id'],$_REQUEST['parent']);
								break;
							case 'subtype_edit':
								$this->Edit_subtype($GET['id']);
								break;
							}
					case 'moded':
							switch ($val)
							{
							case 'del_group':
							
							$this->DeleteInGroup($POST['checkID']); 
							break;
							case 'del_type':
							$this->DeleteInTypes($POST['checkID']);
							break;
							case 'del_dblock':
							$this->DeleteInDblock($POST['checkID']);
							break;
							case 'del_ST':
							$this->DelST($POST['checkID']);
							break;
							}
							break;
					}
				}
			}
			
			if(isset($POST))
			{
				foreach($POST as $key=>$val)
				{
				
					switch($key)
					{
						case 'ADD':
							switch($val)
							{
							case 'NEW_GROUP':
							$this->AddInGroup($POST);
							break;
							case 'EDIT_GROUP':
							$this->UpdateInGroup($POST);
							break;
							case 'NEW_TYPE':
							$this->AddInTypes($POST);
							break;
							case 'EDIT_TYPE':
							$this->UpdateInTypes($POST);
							break;
							case 'NEW_DBLOCK':
							$this->AddInDblock($POST);
							break;
							case 'EDIT_DBLOCK':
							$this->UpdateInDblock($POST);
							break;
							case 'NEW_SUBTYPE':
							$this->AddInST($POST);
							break;
							case 'EDIT_SUBTYPE':
							$this->UpdateInST($POST);
							break;
							case 'NEW_EXPORT':
							$this->NEW_EXPORT($POST);
							break;
							case 'NEW_IMPORT':
							$this->NEW_IMPORT($POST);
							break;
							}
						break;
					}
				}
			}
			
        }
	function GetParams($arv)
	{
		switch($arv['TYPE'])
		{
			case 'DBLOCK':
				$iParams=array(array('TITLE','SORT','ACTIVE','TYPE_NAME','ID'),array('CREATED','ASC'));
			break;
			case 'TYPES':
				$iParams=array(array('NAME','ORDER','DESC','GROUP_NAME','ID'),array('CREATED','ASC'));
			break;
			case 'GROUP':
				$iParams=array(array('NAME','ORDER','DESC','ACTIVE','ID'),array('CREATED','ASC'));
			break;
		}
		return $iParams;
	}
	
	function Statistics()
	{
		
		$arAllGroups=GTdblock::STAT();
		//d($arAllGroups);
		echo '<h3>Статистические данные по записям</h3>
		<div id="ex1">
			<ul id="browser" class="filetree">';
				
			foreach($arAllGroups as $key=>$val)
			{
				echo'<li class="closed"><span class="folder">'.$val[0].'</span>';
				echo "<ul><li><span class='file'>Типов:".$val['TYPES']."</span></li>";
				if(!empty($val[1]))
				{
					//echo '<ul><li><span class="file">Item 1.1</span></li>';
					foreach($val[1] as $val1)
					{
						echo '<li class="closed"><span class="folder">'.$val1[0].'</span>';
						echo '<ul><li><span class="file">Папок:'.$val1['SUBTYPES'].'<br />Записей:'.$val1['DBLOCKS'].'<br />С преводом:'.$val1['wLANG'].'<br />Без перевода:'.$val1['oLANG'].'</span></li>';
						if(!empty($val1[1]))
						{
							foreach($val1[1] as $val2)
							{
								echo '<li class="closed"><span class="folder">'.$val2[0].'</span>';
								echo '<ul><li><span class="file">Записей:'.$val2['DBLOCKS'].'<br />С преводом:'.$val2['wLANG'].'<br />Без перевода:'.$val2['oLANG'].'</span></li>';
								echo '</ul></li>';
							}
						}
						echo '</ul></li>';
					}
					
				}
				echo '</ul>';
				echo '</li>';
			
			}
		
		echo '</ul>
		</div>

		<script type="text/javascript">
		$(document).ready(function(){
		// ---- TREE -----
		$("#browser").treeview();
		$("#navigation").treeview({
		  persist: "location",
		  collapsed: true,
		  unique: true
		});
		// ---- TREE -----
		});
		</script>';
	}
	function SEARCH_OPTIONS($arv=false)
	{
		global $DB;
		//d($arv);
		//unset($_GET);
		if(isset($arv['ELAST_CACHED']) || isset($arv['reset']))
		{
			if(isset($arv['ELAST_CACHED']))
			{
				$iRES=IndexData(array('LIMIT'=>50),'DBLOCK');
				if($arv['PAGE']!='0'){GTdblock::GetSerchablePages();}
			
				//echo "TRUE\n";
				$res=$DB->Query("SELECT SQL_CALC_FOUND_ROWS * FROM `search_cache_elements` WHERE `MODULE`='DBLOCK' ORDER BY `ELAST_CACHED` DESC");
				$row=$DB->fetchAssoc($res);
				$iRes2['DATE']=$row['ELAST_CACHED'];
				$iRes2['COUNT']=$DB->QResult("SELECT FOUND_ROWS()");
				//echo phpArray2JSONObject($iRes2);
				echo json_encode($iRes2);
				die();
			}
			elseif($arv['reset']==1)
			{
				$DB->Query("TRUNCATE TABLE  `search_results_items`");
				$DB->Query("TRUNCATE TABLE  `search_results`");
				$DB->Query("TRUNCATE TABLE  `search_cache_constructs`");
				$DB->Query("TRUNCATE TABLE  `search_cache_counts`");
				$DB->Query("TRUNCATE TABLE  `search_cache_elements`");
				$DB->Query("TRUNCATE TABLE  `search_cache_words`");
				$DB->Query("UPDATE `g_dblock` SET `ININDEX`='0' WHERE `ININDEX`='1' AND `INDEXED`='1'");
				header("Location: ./?mod=dblocks&act=search_options");
				die();
			} 
		}
		else
		{
			$res=$DB->Query("SELECT SQL_CALC_FOUND_ROWS * FROM `search_cache_elements` WHERE `MODULE`='DBLOCK' ORDER BY `ELAST_CACHED` DESC");
			$row=$DB->fetchAssoc($res);
			if(!empty($row)){
				$DateMD3=$row['ELAST_CACHED'];
				$row['ELAST_CACHED'] = date('Y-m-j h:i:s', $row['ELAST_CACHED']);
				$DateMD=$row['ELAST_CACHED'];	
			}
			else
			{$DateMD=''; $DateMD3=0;}
			$countAD=$DB->QResult("SELECT FOUND_ROWS()");
			
			$res2=$DB->Query("SELECT SQL_CALC_FOUND_ROWS * FROM `search_cache_elements` WHERE `MODULE`='TEXTPAGE' ORDER BY `ELAST_CACHED` DESC");
			$row2=$DB->fetchAssoc($res2);
			if(!empty($row2)){
				$row2['ELAST_CACHED'] = date('Y-m-j h:i:s', $row2['ELAST_CACHED']);
				$DateMD2=$row2['ELAST_CACHED'];
			}
			else
			{$DateMD2='';}
			$countAD2=$DB->QResult("SELECT FOUND_ROWS()");

			$res4=$DB->Query("SELECT `ID` FROM `g_dblock` WHERE `INDEXED`='1'");
			while($row=$DB->fetchAssoc($res4))
			{
				$id[]=$row['ID'];
			}
			$res3=$DB->Query("SELECT SQL_CALC_FOUND_ROWS * FROM `g_dblock_multy` WHERE `DBLOCK_ID` IN ('".implode("','",$id)."')");
			$countAD3=$DB->QResult("SELECT FOUND_ROWS()");
			echo "
			<form id='INDEX'>
			<table>
			<tr>
				<td>Дата послед. индексации записей:</td><td>$DateMD<input type='hidden' value='$DateMD3' id='DATE_DB'></td>
			</tr><tr>
				<td>Количество индексированных записей:</td><td><input type='hidden' value='$countAD' id='COUNT1'><i id='C1'>$countAD</i> из $countAD3<input type='hidden' value='$countAD3' id='COUNT2'></td>
			</tr><tr>
				<td>Дата послед. индексации страниц:</td><td>$DateMD2<input type='hidden' value='$DateMD2' id='DATE_TP'></td>
			</tr><tr>
				<td>Количество индексированных странниц:</td><td>$countAD2</td>
			</tr><tr>
			<td>Индексировать странницы:</td><td><input type=\"checkbox\" id='PAGE' value='1'></td>
			</tr><tr>
				<td></td><td><input type='submit' value='Начать индексацию'></td>
			</tr>
			</table>
			</form>
			<center>
			<div id='PRE' style='display:none;'><img src='/gtinx/templates/.general/images/index_progressbar.gif'><br />Идет индексация.<br />Это может занять некоторое время!</div>
			</center>
			<form action='/gtinx/admin/' method='get'>
			<input type='hidden' name='mod' value='dblocks'>
			<input type='hidden' name='act' value='search_options'>
			<input type='hidden' name='reset' value='1'>
			Обнулить результаты : <input type='submit' value='Обнулить'>
			</form>
			";
			echo "<script>

				function onGetData(data)
				{
					\$('#PRE').hide();
					if(data != null)
					{
						var DATE=data.DATE;
						\$('#DATE_DB').attr('value',DATE);
						var COUNT2 = document.getElementById('COUNT2').value;
						var COUNT=data.COUNT;
						
						\$('#C1').html(COUNT);
						INDEX_START(DATE);
						 
					}else
					{
						var DATE_DB = encodeURI(document.getElementById('DATE_DB').value);
						INDEX_START(DATE_DB);
					}
					
				}
				function INDEX_START(DATE_DB,PAGES)
				{
							\$('#PRE').show();
							if(DATE_DB=='')
							{
								DATE_DB=0;
							}
							if(PAGES=='')
							{
								PAGES=0;
							}
							var s = {
							url: \"/gtinx/admin/?mod=dblocks&act=search_options&ELAST_CACHED=\"+DATE_DB, 
							context: this, 
							cache: false, 
							success:onGetData,
							error:onGetData,
							dataType:'json'};

							\$.ajax(s);
				}
				
				
				
					\$(\"#INDEX\").submit(function() {
					var DATE_DB = encodeURI(document.getElementById('DATE_DB').value);
					var PAGES = \$('#PAGE').attr('checked')==''?0:1;
					
					INDEX_START(DATE_DB,PAGES);
					return false;
					});
				
				</script>";
		}
	}
	
	function NEW_EXPORT($arv)
	{
		unset($_POST);
		$iArv=array('FILENAME'=>$arv['FILENAME'],'PARAMS'=>array('TYPE_ID'=>$arv['TYPE'][0]));
		GTdblock::GetCSV($iArv);
		//header("Location: ./?mod=dblocks&act=groups");
		//die();
	}
	
	function NEW_IMPORT()
	{
		//error_reporting(E_ALL);
		//d($_FILES);
		$handle =file_get_contents($_FILES['FILE']['tmp_name']);
		$txt = explode('";"', $handle);
		$i=0;
		foreach($txt as $key=>$val)
		{
			if($i<=57)
			{
				$iTable[]=str_replace('"','',$val);
			}
			else
			{
				$iCol[]=$val;
			}
			$i++;
		}//d($iTable);
		$j=0;
		$f=0;
		foreach($iCol as $val)
		{
			if($j>57){$j=0; $f++;}
			if($j>38 && $j<57)
			{
				$iColK[$f]['PROPERTIES'][$iTable[$j]]=$val;
			}
			else{
				$iColK[$f][$iTable[$j]]=$val;
			}
			$j++;
		}GTdblock::CSVtoMYSQL($iColK);
	}
	
	function Import()
	{
		global $APP;
		$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
						Array("DESC" => GetMessage('FILE'), "NAME" => "FILE", "VIEW" => 1, "TYPE" => "file", "VALUE" =>''),
						Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'NEW_IMPORT')),
						);
				
			$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
			$APP->ModCreateBreadcumbs(GetMessage('IMPORT'));
			$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	function Export()
	{
		global $APP;
		$r=GTdblocktype::Get();
		$types=array();
		foreach($r as $val)
		{
			$types[$val['ID']]=$val['NAME'];
		}
		$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
						Array("DESC" => GetMessage('GIVE_FILENAME'), "NAME" => "FILENAME", "VIEW" => 1, "TYPE" => "string", "VALUE" =>''),
						Array("DESC" => GetMessage('TYPE'), "NAME" => "TYPE", "VIEW" => 1, "TYPE" => "select", "VALUE" =>$types),
						Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'NEW_EXPORT')),
						);
				
			$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
			$APP->ModCreateBreadcumbs(GetMessage('EXPORT'));
			$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	function Groups($PARAMS=FALSE)
	{
		global $DB, $APP,$arrModResult;
		if($PARAMS){

		if($PARAMS['NAME']!=FALSE)$PARAMS['NAME']='~'.$PARAMS['NAME'];
		if($PARAMS['DESC']!=FALSE)$PARAMS['DESC']='~'.$PARAMS['DESC'];
		if($PARAMS['ACTIVE'][0]!='3'){$PARAMS['ACTIVE']=$PARAMS['ACTIVE'][0];} else {unset($PARAMS['ACTIVE']);}
		}
		$iParams=$this->GetParams(array('TYPE'=>'GROUP'));
		if(empty($iParams))
		{
		$iParams=Array(Array('NAME','UPDATED','ID'),Array('TITLE','ASC'));
		}
		$D_lang=GToptionlang::Get('',Array('ENABLED'=>1,'DEFAULT'=>1));
		$D_lang=$D_lang[0];
		$row=GTdblockgroup::Get($iParams[0],$PARAMS,$iParams[1]);

		foreach($row as $key=>$val)
			{
			$val['DESC']=substr($val['DESC'],0,150);
			$iTbody=array();
				foreach($iParams[0] as $iKey=>$iVal)
				{
					
					$iTbody[$iVal]=$val[$iVal];
					
					if($iVal=='ACTIVE')
					{
						switch ($val[$iVal])
						{
							case '1':
							$val[$iVal]=GetMessage('YES');
							break;
							case '0':
							$val[$iVal]=GetMessage('NO');
						}
						$iTbody[$iVal]=$val[$iVal];
					}
					if($iVal=='CREATED' || $iVal=='DATESTART' || $iVal=='DATEEND' || $iVal=='UPDATED')
					{
						if($val[$iVal]!=0){$val[$iVal] = date($D_lang['DATE_TIME_FORMAT'],$val[$iVal]);}
						$iTbody[$iVal]=$val[$iVal];
					}
				}
				$arrModResult['modContent'][] =$iTbody;
			}
			$iThead=array();
			foreach($iParams[0] as $iKeys=>$iVals)
			{
				$iThead[$iVals]=GetMessage($iVals);
			}
			$arrModResult['modHeader'] = $iThead;
		//$APP->admModSetUrl('act=group_edit&id=', 'edit');
		$APP->admModSetUrl('act=types&id=', 'edit');
		$APP->admModSetUrl('act=new_group', 'add');
		$APP->admModSetUrl('moded=del_group', 'action');
		$APP->admModSetUrl('act=group_filter', 'filter');
		$APP->ModCreateBreadcumbs('Группы');
		$APP->admModDetemineActions(Array('AdD', 'LIST', 'EDIT', 'DELETE'));
		$APP->admModShowElements(
		$arrModResult['modHeader'],
		$arrModResult['modContent'],
		"list",
		Array('NAME', 'DESC', 'ID', 'ACTIVE'=>Array('3'=>' ',0=>GetMessage('NO'),1=>GetMessage('YES'))),
		$DHTML
		);
	}
	
	function Edit_Group($ID)
	{
		global $DB, $APP,$arrModResult;
		
		$res=GTdblockgroup::Get(array('ID','CREATED','UPDATED','NAME','ORDER','ACTIVE','DESC'),array('ID'=>'='.$ID));
		$row=$res[0];
		if($row['UPDATED']!=$row['CREATED'])
			{$row['UPDATED'] = date('Y-m-j h:i:s', $row['UPDATED']);}else{$row['UPDATED']='';}
		$row['CREATED'] = date('Y-m-j h:i:s', $row['CREATED']);
			foreach($row as $key=>$val)
				{
				$mass[]=Array("DESC" => "", "NAME" => "CHECK[".$key."]", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$val);
				}
					$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
						Array("DESC" => GetMessage('ID'), "NAME" => "SHOWID", "VIEW" => 1, "TYPE" => "text", "VALUE" => $row['ID']),
						Array("DESC" => GetMessage('NAME'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['NAME']),
						Array("DESC" => "", "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => $row['ID']),
						Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => 'EDIT_GROUP')),
						GetMessage("DESC")=> Array(Array("DESC" => '', "NAME" => "DESC", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $row['DESC']),),
						GetMessage("PROPERTIES")=> Array(
						Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio:".$row['ACTIVE'], "VALUE" => Array(1 =>GetMessage('YES'), 0=>GetMessage('NO'))),
						Array("DESC" => GetMessage('SORT'), "NAME" => "ORDER", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['ORDER']),
						Array("DESC" => GetMessage('CREATED'), "NAME" => "CREATED", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$row['CREATED']),
						Array("DESC" => GetMessage('UPDATED'), "NAME" => "UPDATED", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$row['UPDATED']),)
						);
			$arrModResult['modHeader'][GetMessage('MAIN')]=array_merge($arrModResult['modHeader'][GetMessage('MAIN')],$mass);
			
			$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
			$APP->ModCreateBreadcumbs($row['NAME']);
			$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	function NewGroup()
	{
		global $APP , $arrModResult;
		
					$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
						Array("DESC" => GetMessage('NAME'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" =>''),
						Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'NEW_GROUP')),
						GetMessage("DESC")=> Array(Array("DESC" => '', "NAME" => "DESC", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '')),
						GetMessage("PROPERTIES")=> Array(
						Array("DESC" => GetMessage('SORT'), "NAME" => "ORDER", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
						Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 0=>GetMessage('NO'))),)
						);
				
			$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
			$APP->ModCreateBreadcumbs(GetMessage('NEW_GROUP'));
			$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	function AddInGroup($arVar)
		{
		unset($_POST);
		
		$res = GTdblockgroup::Add(array(
		'NAME'=>$arVar['NAME'],
		'DESC'=>$arVar['DESC'],
		'edswitchDESC'=>$arVar['edswitchDESC'],
		'ORDER'=>$arVar['ORDER'],
		'ACTIVE'=>$arVar['ACTIVE']));
		
		if ($res===TRUE)
		{
			header("Location: ./?mod=dblocks&act=groups");
			die();
		}
		else { GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));}
		}
		
	function UpdateInGroup($arVar)
		{
			
			unset($_POST);
			
			if($arVar['ACTIVE']==''){$arVar['ACTIVE']=1;}
			$update=Array(	'ID'=>$arVar['ID'],
							'NAME'=>$arVar['NAME'],
							'DESC'=>$arVar['DESC'],
							'edswitchDESC'=>$arVar['edswitchDESC'],
							'ORDER'=>$arVar['ORDER'],
							'ACTIVE'=>$arVar['ACTIVE']);
			$noup=array();
			foreach($update as $key=>$val)
			{
				if($arVar['CHECK'][$key]==$val){$noup[]=$key;}
			}
			foreach($noup as $key=>$val)
			{
			unset ($update[$val]);
			}
			
			if(!empty($update))
			{
			$IDD=array('ID'=>$arVar['CHECK']['ID']);
			$update=array_merge($update,$IDD);
			$res = GTdblockgroup::Update($update);
			}else{$res=TRUE;}
			if ($res===TRUE)
			{
				header("Location: ./?mod=dblocks&act=groups");
				die();
				//echo "<html><head><meta    http-equiv='Refresh' content='0;    URL=?mod=dblocks&act=groups'></head></html>";
			}
			else {GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));}
		}
		
/****For types*****For types******For types*****For types*/
	function Types($GROUP_ID=FALSE,$PARAMS=FALSE)
	{
		global $DB,$APP, $arrModResult;
		$iParams='';
		$iParams=$this->GetParams(array('TYPE'=>'TYPES'));
		$D_lang=GToptionlang::Get('',Array('ENABLED'=>1,'DEFAULT'=>1));
		$D_lang=$D_lang[0];
		if(empty($iParams))
		{
			$iParams=Array(Array('NAME','UPDATED','ID'),Array('TITLE','ASC'));
		}
		
		if($PARAMS)
		{
			if($PARAMS['NAME']!=FALSE)$PARAMS['NAME']='~%'.$PARAMS['NAME'].'%';
			if($PARAMS['DESC']!=FALSE)$PARAMS['DESC']='~%'.$PARAMS['DESC'].'%';
			$GR=GetMessage('FILTER');
		}
		else
		{
			$PARAMS['GROUP_ID']=$GROUP_ID;
			$GR=GTdblockgroup::Get(array('ID','NAME'),Array('ID'=>$PARAMS['GROUP_ID']));
			$GR=$GR[0]['NAME'];
		}
		
			
			
			if($GROUP_ID!=FALSE)
			{$row=GTdblocktype::Get($iParams[0],$PARAMS,$iParams[1],'',Array('ID'));}
			else{$row=GTdblocktype::Get($iParams[0],'',$iParams[1]);}
			//d($row);
			foreach($row as $key=>$val)
			{
			$val['DESC']=substr($val['DESC'],0,150);
			$iTbody=array();
				foreach($iParams[0] as $iKey=>$iVal)
				{
					
					$iTbody[$iVal]=$val[$iVal];
					
					if($iVal=='ACTIVE')
					{
						switch ($val[$iVal])
						{
							case '1':
							$val[$iVal]=GetMessage('YES');
							break;
							case '0':
							$val[$iVal]=GetMessage('NO');
						}
						$iTbody[$iVal]=$val[$iVal];
					}
					if($iVal=='CREATED' || $iVal=='DATESTART' || $iVal=='DATEEND' || $iVal=='UPDATED')
					{
						if($val[$iVal]!=0){$val[$iVal] = date($D_lang['DATE_TIME_FORMAT'],$val[$iVal]);}
						$iTbody[$iVal]=$val[$iVal];
					}
				}
				$arrModResult['modContent'][] =$iTbody;
			}
			$iThead=array();
			foreach($iParams[0] as $iKeys=>$iVals)
			{
				$iThead[$iVals]=GetMessage($iVals);
			}
			$arrModResult['modHeader'] = $iThead;
		$APP->admModSetUrl('act=dblocks&id=', 'edit');
    	$APP->admModSetUrl('act=new_type&id='.$PARAMS['GROUP_ID'], 'add');
		$APP->admModSetUrl('moded=del_type', 'action');
		$APP->admModSetUrl('act=type_filter&id='.$PARAMS['GROUP_ID'], 'filter');
		$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
		
		
		$APP->ModCreateBreadcumbs($GR.':'.GetMessage('TYPES'));
        $APP->admModDetemineActions(Array('ADD', 'LIST', 'EDIT', 'DELETE'));
        $APP->admModShowElements(
        	$arrModResult['modHeader'],
        	$arrModResult['modContent'],
        	"list",
        	Array('NAME', 'DESC', 'ID'),
        	$DHTML
        	);
	}
	
	function Edit_Types($ID)
	{
		global $DB, $APP, $arrModResult;
		$row=GTdblocktype::Get('',array('ID'=>$ID),'','',Array('ID'));	
		include(GTROOT.'/modules/dblocks/.prop.php');
		$roww=GTgroupsusers::Get('');
		foreach($roww as $key=>$val)
		{
			$R=GTuserprivs::Get($val['ID'],$arrModProperties['NAME_KEY'].':'.$ID);
			if($R==2)
			{
			foreach($arrModProperties['PRIVS'] as $KEY=>$VAL)
			{
			$select[]='<option value="'.$VAL.'">'.$VAL.'</option>';
			}
		
			$MD[]='<tr>
				<td><input type="checkbox" name="ID_PRIV['.$val['ID'].']" value="'.$val['ID'].'"></td>
						<td>'.$val['NAME'].'<input type="hidden" name="NAME_PRIV['.$val['ID'].']" value="'.$arrModProperties['NAME_KEY'].':'.$ID.'"></td>
						<td><input type="text" name="ACTIVE_FROM_PRIV['.$val['ID'].']" value=""></td>
						<td><input type="text" name="ACTIVE_TO_PRIV['.$val['ID'].']" value=""></td>
						<td><input type="radio" name="ACTIVE_PRIV['.$val['ID'].']" value="1">да</td>
						<td><select name="PRIV['.$val['ID'].']">'.implode('',$select).'</select></td>
						</tr>';
			unset($select);
			}
			else
			{
			$select[]='<option value="'.$R['NAME'].'">'.$R['NAME'].'</option>';
			foreach($arrModProperties['PRIVS'] as $KEY=>$VAL)
			{
			if($R['NAME']!=$VAL){$select[]='<option value="'.$VAL.'">'.$VAL.'</option>';}
			}
				if($R['ACTIVE_FROM']!=0){$R['ACTIVE_FROM'] = date('Y-m-j h:i:s', $R['ACTIVE_FROM']);}
				if($R['ACTIVE_TO']!=0){$R['ACTIVE_TO'] = date('Y-m-j h:i:s', $R['ACTIVE_TO']);}
				if($R['ACTIVE']==1){$R['ACTIVE'] ='checked';}else{$R['ACTIVE']='';}
			$MD[]='<tr>
				<td><input type="checkbox" name="ID_PRIV['.$val['ID'].']" value="'.$val['ID'].'" checked></td>
						<td>'.$val['NAME'].'<input type="hidden" name="NAME_PRIV['.$val['ID'].']" value="'.$arrModProperties['NAME_KEY'].':'.$ID.'"></td>
						<td><input type="text" name="ACTIVE_FROM_PRIV['.$val['ID'].']" value="'.$R['ACTIVE_FROM'].'"></td>
						<td><input type="text" name="ACTIVE_TO_PRIV['.$val['ID'].']" value="'.$R['ACTIVE_TO'].'"></td>
						<td><input type="radio" name="ACTIVE_PRIV['.$val['ID'].']" value="1" '.$R['ACTIVE'].'>да</td>
						<td><select name="PRIV['.$val['ID'].']">'.implode('',$select).'</select></td>
						</tr>';
			unset($select);
			}
		}
		$MD1='<center><table border="1" cellspacing="0" cellpadding="4" style="text-align:center;";>
				<thead style="background-color:#999; color:#FFF;"><tr><td>1</td><td>2</td><td>'.GetMessage('ACTIVE_FROM').'</td><td>'.GetMessage('ACTIVE_TO').'</td><td>'.GetMessage('ACTIVE').'</td>
				<td>'.GetMessage('PRIV').'</td></tr></thead>
				'.implode('',$MD).'</table></center>';
			$row=$row[$ID];
			$list='';
			$types=array('STRING','TEXT','FILE','PASS','IMAGE','DATE','NUMBER','URL','LINK','LIST','DBLOCK');
			foreach($row['PROPS'] as $key=>$val)
			{
			$table1='<input type="hidden" name="PROP_ID['.$key.']" value="'.$row['PROPS'][$key]['ID'].'">
						<tr><td>'.$row['PROPS'][$key]['ID'].'</td>
						<td><input type="text" name="TITLE['.$key.']" value="'.$row['PROPS'][$key]['NAME'].'"></td>';
						$table2= '<td><select name="TYPE['.$key.']"><option value="'.$row['PROPS'][$key]['TYPE'].'">'.$row['PROPS'][$key]['TYPE'].'</option>';				
							foreach($types as $val)
							{
								if(!strstr($row['PROPS'][$key]['TYPE'],$val))
								{
								$tab = '<option value="'.$val.'">'.$val.'</option>';
								$table2.=$tab;
								}
							}
						$table4= '</select></td>';
						if ($row['PROPS'][$key]['MULTIPLE']=='1')
						{$table5= '<td><input type="checkbox" name="MULTIPLE['.$key.']" value="1" CHECKED></td>';}
						else {$table5= '<td><input type="checkbox" name="MULTIPLE['.$key.']" value="1"></td>';}
						if ($row['PROPS'][$key]['REQUIRED']=='1')
						{$table6= '<td><input type="checkbox" name="REQUIRED['.$key.']" value="1" CHECKED></td>';}
						else {$table6= '<td><input type="checkbox" name="REQUIRED['.$key.']" value="1"></td>';}
						$table7= '<td><input type="text" name="SORT['.$key.']" value="'.$row['PROPS'][$key]['SORT'].'" size="5"></td>
						<td><textarea name="VALUES['.$key.']">'.$row['PROPS'][$key]['VALUES'].'</textarea></td>
						<td><input type="text" name="DEFAULT['.$key.']" value="'.$row['PROPS'][$key]['DEFAULT'].'" size="15"></td>
						<td><input type="text" name="LENGTH['.$key.']" value="'.$row['PROPS'][$key]['LENGTH'].'" size="5"></td>
						<td><input type="text" name="OPTIONS['.$key.']" value="'.$row['PROPS'][$key]['OPTIONS'].'" size="15"></td>
						<td><input type="text" name="PROP_KEY['.$key.']" value="'.$row['PROPS'][$key]['PROP_KEY'].'" size="15"></td>';
						if ($row['PROPS'][$key]['ACTIVE']=='1')
						{$table8 = '<td><input type="checkbox" name="ACTIVE['.$key.']" value="1" CHECKED></td>';}
						else {$table8 = '<td><input type="checkbox" name="ACTIVE['.$key.']" value="1"></td>';}
						$table9= "<td><input type='checkbox' name='DEL[]' value='".$row['PROPS'][$key]['ID']."'></td></tr>";
						$list.=$table1.$table2.$table4.$table5.$table6.$table7.$table8.$table9;
			}
			
			$lists='';
					$types=array('STRING','TEXT','FILE','PASS','IMAGE','DATE','NUMBER','URL','LINK','LIST','DBLOCK');
					$table2='';					
					foreach($types as $val)
							{
								$tab = '<option value="'.$val.'">'.$val.'</option>';
								$table2.=$tab;
							}
					for ($j=0;$j<5;$j++)
					{
						
						$table1='<tr><td> </td>
						<td><input type="text" name="TITLE[j'.$j.']" value=""></td>
						<td><select name="TYPE[j'.$j.']"><option>  </option>';
						$table3= '</select></td>
						<td><input type="checkbox" name="MULTIPLE[j'.$j.']" value="1"></td>
						<td><input type="checkbox" name="REQUIRED[j'.$j.']" value="1"></td>
						<td><input type="text" name="SORT[j'.$j.']" value="" size="5"></td>
						<td><textarea name="VALUES[j'.$j.']"></textarea></td>
						<td><input type="text" name="DEFAULT[j'.$j.']" value="" size="15"></td>
						<td><input type="text" name="LENGTH[j'.$j.']" value="" size="5"></td>
						<td><input type="text" name="OPTIONS[j'.$j.']" value="" size="15"></td>
						<td><input type="text" name="PROP_KEY[j'.$j.']" value="" size="15"></td>
						<td><input type="checkbox" name="ACTIVE[j'.$j.']" value="1"></td><td></td></tr>';
						$lists.=$table1.$table2.$table3;
					}
			$DHTML ='<center><table border="1" width="80%" cellspacing="0" cellpadding="4" style="text-align:center;";>
			<thead style="background-color:#2c6fd3; color:#FFF;"><tr><td>ID</td><td>'.GetMessage('NAME').'</td><td>'.GetMessage('TYPE').'</td><td>'.GetMessage('MULTIPLE').'</td><td>'.GetMessage('REQUIRED').'</td>
			<td>'.GetMessage('SORT').'</td><td>'.GetMessage('VALUES').'</td><td>'.GetMessage('DEFAULT').'</td>
			<td>'.GetMessage('LENGTH').'</td><td>'.GetMessage('OPTIONS').'</td><td>'.GetMessage('KEY').'</td><td>'.GetMessage('ACTIVE').'</td><td>'.GetMessage('DELETE').'</td></tr></thead><tbody>'.$list.$lists.'</tbody>
			</table></center>';
			$row3=GTdblockgroup::Get(Array('ID','NAME'));
			$GrID=array('0'=>'');
				foreach($row3 as $key=>$val)
				{
					$GrID2[]=array($row3[$key]['ID']=>$row3[$key]['NAME']);
				}
				foreach($GrID2 as $key=>$val)
				{
					foreach($val as $kkey=>$vval)
					{
					$GrID[$kkey]=$vval;
					}
				}

			if($row['UPDATED']!=$row['CREATED'])
			{$row['UPDATED'] = date('Y-m-j h:i:s', $row['UPDATED']);}else{$row['UPDATED']='';}
			$row['CREATED'] = date('Y-m-j h:i:s', $row['CREATED']); 
						$USER=$APP->GetCurrentUserID();
						
						//$mass[]=Array("DESC" => "CLONE", "NAME" => "CLONE", "VIEW" => 1, "TYPE" => "checkbox", "VALUE" => 'CLONE');
						
							$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
								Array("DESC" => GetMessage('ID'), "NAME" => "SHOW_ID", "VIEW" => 1, "TYPE" => "text", "VALUE" => $row['ID']),	
								Array("DESC" => "", "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => $row['ID']),
								Array("DESC" => GetMessage('NAME'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['NAME']),
								Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => 'EDIT_TYPE')),
								GetMessage("DESC")=> Array(Array("DESC" => '', "NAME" => "DESC", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $row['DESC'])),
								GetMessage("PROPERTIES")=> Array(
								Array("DESC" => GetMessage('CREATED'), "NAME" => "CREATED", "VIEW" => 1, "TYPE" => "text", "VALUE" => $row['CREATED']),
								Array("DESC" => GetMessage('UPDATED'), "NAME" => "UPDATED", "VIEW" => 1, "TYPE" => "text", "VALUE" => $row['UPDATED']),
								Array("DESC" => GetMessage('SORT'), "NAME" => "ORDER", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['ORDER']),
								Array("DESC" => GetMessage('KEY'), "NAME" => "TYPE_KEY", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['TYPE_KEY']),
								Array("DESC" => GetMessage('IN_GROUP'), "NAME" => "GROUP_ID", "VIEW" => 1, "TYPE" => "select:".$row['GROUP_ID'], "VALUE" => $GrID))
								);
					$arrModResult['modHeader'][GetMessage('PROPS')]=$DHTML;
					$arrModResult['modHeader'][GetMessage("PRIVS")]=$MD1;
					$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
					
					$row1=GTdblockgroup::Get(array('ID','NAME'),Array('ID'=>$row['GROUP_ID']));
					$APP->ModCreateBreadcumbs($row1[0]['NAME'],'?mod=dblocks&act=types&id='.$row['GROUP_ID']);
					$APP->ModCreateBreadcumbs($row['NAME']);
					$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
			
	}
	
	function New_Types($GROUP_ID)
	{
	global $DB,$APP , $arrModResult;
	
	include(GTROOT.'/modules/dblocks/.prop.php');
		$roww=GTgroupsusers::Get('');
		foreach($roww as $key=>$val)
		{
			foreach($arrModProperties['PRIVS'] as $KEY=>$VAL)
			{
			$select[]='<option value="'.$VAL.'">'.$VAL.'</option>';
			}
		
			$MD[]='<tr>
				<td><input type="checkbox" name="ID_PRIV['.$val['ID'].']" value="'.$val['ID'].'"></td>
						<td>'.$val['NAME'].'<input type="hidden" name="NAME_PRIV['.$val['ID'].']" value="'.$arrModProperties['NAME_KEY'].':"></td>
						<td><input type="text" name="ACTIVE_FROM_PRIV['.$val['ID'].']" value=""></td>
						<td><input type="text" name="ACTIVE_TO_PRIV['.$val['ID'].']" value=""></td>
						<td><input type="radio" name="ACTIVE_PRIV['.$val['ID'].']" value="1">да</td>
						<td><select name="PRIV['.$val['ID'].']">'.implode('',$select).'</select></td>
						</tr>';
			unset($select);
		}
		$MD1='<center><table border="1" cellspacing="0" cellpadding="4" style="text-align:center;";>
				<thead style="background-color:#999; color:#FFF;"><tr><td>1</td><td>2</td><td>'.GetMessage('ACTIVE_FROM').'</td><td>'.GetMessage('ACTIVE_TO').'</td><td>'.GetMessage('ACTIVE').'</td>
				<td>'.GetMessage('PRIV').'</td></tr></thead>
				'.implode('',$MD).'</table></center>';
			$row3=GTdblockgroup::Get(Array('GROUP_ID','GROUP_NAME'));
			$GrID=array('0'=>'');
			foreach($row3 as $key=>$val)
						{
						$GrID2[]=array($row3[$key]['ID']=>$row3[$key]['NAME']);
						}
						foreach($GrID2 as $key=>$val)
						{
							foreach($val as $kkey=>$vval)
							{
							$GrID[$kkey]=$vval;
							}
						}
						
					$list='';
					$types=array('STRING','TEXT','FILE','PASS','IMAGE','DATE','NUMBER','URL','LINK','LIST','DBLOCK');
					$table2='';					
					foreach($types as $val)
							{
								$tab = '<option value="'.$val.'">'.$val.'</option>';
								$table2.=$tab;
							}
					for ($j=0;$j<5;$j++)
					{
						
						$table1='<tr><td> </td>
						<td><input type="text" name="TITLE[j'.$j.']" value=""></td>
						<td><select name="TYPE[j'.$j.']"><option>  </option>';
						$table3= '</select></td>
						<td><input type="checkbox" name="MULTIPLE[j'.$j.']" value="1"></td>
						<td><input type="checkbox" name="REQUIRED[j'.$j.']" value="1"></td>
						<td><input type="text" name="SORT[j'.$j.']" value="" size="5"></td>
						<td><textarea name="VALUES[j'.$j.']"></textarea></td>
						<td><input type="text" name="DEFAULT[j'.$j.']" value="" size="15"></td>
						<td><input type="text" name="LENGTH[j'.$j.']" value="" size="5"></td>
						<td><input type="text" name="OPTIONS[j'.$j.']" value="" size="15"></td>
						<td><input type="text" name="PROP_KEY[j'.$j.']" value="" size="15"></td>
						<td><input type="checkbox" name="ACTIVE[j'.$j.']" value="1"></td></tr>';
						$list.=$table1.$table2.$table3;
					} 
					$DHTML ='<center><table border="1" width="80%" cellspacing="0" cellpadding="4" style="text-align:center;";>
							<thead style="background-color:#2c6fd3; color:#FFF;"><tr><td>ID</td><td>'.GetMessage('NAME').'</td><td>'.GetMessage('TYPE').'</td><td>'.GetMessage('MULTIPLE').'</td><td>'.GetMessage('REQUIRED').'</td>
						<td>'.GetMessage('SORT').'</td><td>'.GetMessage('VALUES').'</td><td>'.GetMessage('DEFAULT').'</td>
						<td>'.GetMessage('LENGTH').'</td><td>'.GetMessage('OPTIONS').'</td><td>'.GetMessage('KEY').'</td><td>'.GetMessage('ACTIVE').'</td></tr></thead><tbody>'.$list.'</tbody>
						</table></center>';
							$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
								Array("DESC" => GetMessage('NAME'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
								Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => 'NEW_TYPE')),
								GetMessage("DESC")=> Array(Array("DESC" => '', "NAME" => "DESC", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '')),
								GetMessage("PROPERTIES")=> Array(
								Array("DESC" => GetMessage('SORT'), "NAME" => "ORDER", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
								Array("DESC" => GetMessage('KEY'), "NAME" => "TYPE_KEY", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
								Array("DESC" => GetMessage('IN_GROUP'), "NAME" => "GROUP_ID", "VIEW" => 1, "TYPE" => "select:".$GROUP_ID, "VALUE" => $GrID))
								);
								$arrModResult['modHeader'][GetMessage('PROPS')]=$DHTML;
								$arrModResult['modHeader'][GetMessage("PRIVS")]=$MD1;
					$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
					
					$row1=GTdblockgroup::Get(array('ID','NAME'),Array('ID'=>$GROUP_ID));
					$APP->ModCreateBreadcumbs($row1[0]['NAME'],'?mod=dblocks&act=types&id='.$GROUP_ID);
					$APP->ModCreateBreadcumbs(GetMessage('NEW_TYPE'));
					$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	function AddInTypes($arVar)
		{
		
		unset($_POST);
		global $DB;
		
		foreach($arVar['TITLE'] as $key=>$val)
		{
			if(!empty($val))
			{
			$Props[]=Array(
			'NAME'=>$arVar['TITLE'][$key],
			'TYPE'=>$arVar['TYPE'][$key],
			'SORT'=>$arVar['SORT'][$key],
			'DEFAULT'=>$arVar['DEFAULT'][$key],
			'ACTIVE'=>$arVar['ACTIVE'][$key],
			'REQUIRED'=>$arVar['REQUIRED'][$key],
			'VALUES'=>$arVar['VALUES'][$key],
			'MULTIPLE'=>$arVar['MULTIPLE'][$key],
			'LENGTH'=>$arVar['LENGTH'][$key],
			'PROP_KEY'=>$arVar['PROP_KEY'][$key],
			'OPTIONS'=>$arVar['OPTIONS'][$key]);
			}
		}
		
		
		$TYPE=array(
		'GROUP_ID'=>$arVar['GROUP_ID'][0],
		'NAME'=>$arVar['NAME'],
		'DESC'=>$arVar['DESC'],
		'edswitchDESC'=>$arVar['edswitchDESC'],
		'TYPE_KEY'=>$arVar['TYPE_KEY'],
		'ORDER'=>$arVar['ORDER'],
		'AUTHOR'=>$arVar['AUTHOR']);
		$ID = GTdblocktype::Add($TYPE,$Props);		
		
		foreach($arVar['ID_PRIV'] as $key=>$val)
		{
			if($val!='')
			{
			$PRIV[]=Array("GROUP_ID"=>$val,
						"NAME"=>$arVar['PRIV'][$val],
						"ACTIVE"=>$arVar['ACTIVE_PRIV'][$val],
						"ACTIVE_FROM"=>$arVar['ACTIVE_FROM_PRIV'][$val],
						"ACTIVE_TO"=>$arVar['ACTIVE_TO_PRIV'][$val],
						"SUBSYSTEM"=>$arVar['NAME_PRIV'][$val].$ID);
			}
		}
		
		LoadClass('GTugpriv');
		GTuserprivs::Add($PRIV);
		
		
		if (!empty($ID))
			{
				header("Location: ./?mod=dblocks&act=types&id=".$arVar['GROUP_ID'][0]);
				die();
			}		
			else {GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));}
		
		}
		
	function UpdateInTypes($arVar)
		{
			unset($_POST);
			//d($arVar);
			/*if(!empty($arVar['CLONE'][0]))
			{
				GTdblocktype::Cloner($arVar);
				header("Location: ./?mod=dblocks&act=types&id=".$arVar['GROUP_ID'][0]);
				die();
			}
			else
			{*/
				foreach($arVar['ID_PRIV'] as $key=>$val)
				{
					if($val!='')
					{
					$PRIV[]=Array("GROUP_ID"=>$val,
								"NAME"=>$arVar['PRIV'][$val],
								"ACTIVE"=>$arVar['ACTIVE_PRIV'][$val],
								"ACTIVE_FROM"=>$arVar['ACTIVE_FROM_PRIV'][$val],
								"ACTIVE_TO"=>$arVar['ACTIVE_TO_PRIV'][$val],
								"SUBSYSTEM"=>$arVar['NAME_PRIV'][$val]);
					}
				}
				
				LoadClass('GTugpriv');
				if($PRIV){GTuserprivs::Add($PRIV);}
				
				foreach($arVar['DEL'] as $key =>$val)
				{
					foreach($arVar['PROP_ID'] as $pkey=>$pval)
					{
					if($pval==$val){$arVar['TITLE'][$pkey]='';}
					}
					GTdblockprop::Delete($val);
				}
				
				foreach($arVar['TITLE'] as $key=>$val)
				{
						if($arVar['TITLE'][$key]!='')
						{
						$FORMP[]=Array(
						'PROP_ID'=>$arVar['PROP_ID'][$key],
						'NAME'=>$arVar['TITLE'][$key],
						'TYPE'=>$arVar['TYPE'][$key],
						'SORT'=>$arVar['SORT'][$key],
						'DEFAULT'=>$arVar['DEFAULT'][$key],
						'ACTIVE'=>$arVar['ACTIVE'][$key],
						'REQUIRED'=>$arVar['REQUIRED'][$key],
						'VALUES'=>$arVar['VALUES'][$key],
						'MULTIPLE'=>$arVar['MULTIPLE'][$key],
						'LENGTH'=>$arVar['LENGTH'][$key],
						'PROP_KEY'=>$arVar['PROP_KEY'][$key],
						'OPTIONS'=>$arVar['OPTIONS'][$key]);
						}
				}
					$update=Array(	'ID'=>$arVar['ID'],
									'GROUP_ID'=>$arVar['GROUP_ID'][0],
									'NAME'=>$arVar['NAME'],
									'DESC'=>$arVar['DESC'],
									'TYPE_KEY'=>$arVar['TYPE_KEY'],
									'ORDER'=>$arVar['ORDER']);
										
					if(!empty($update))
					{	
						$res = GTdblocktype::Update($update);
					}else{$res=TRUE;}
					
				if(!empty($FORMP))
				{
					foreach($FORMP as $key=>$val)
					{
					
						if($FORMP[$key]['PROP_ID']!='')
						{
						
							$prop_res[] = GTdblockprop::Update(array(
															'ID'=>$FORMP[$key]['PROP_ID'],
															'TYPE_ID'=>$arVar['ID'],
															'NAME'=>$FORMP[$key]['NAME'],
															'TYPE'=>$FORMP[$key]['TYPE'],
															'SORT'=>$FORMP[$key]['SORT'],
															'DEFAULT'=>$FORMP[$key]['DEFAULT'],
															'ACTIVE'=>$FORMP[$key]['ACTIVE'],
															'REQUIRED'=>$FORMP[$key]['REQUIRED'],
															'VALUES'=>$FORMP[$key]['VALUES'],
															'MULTIPLE'=>$FORMP[$key]['MULTIPLE'],
															'LENGTH'=>$FORMP[$key]['LENGTH'],
															'PROP_KEY'=>$FORMP[$key]['PROP_KEY'],
															'OPTIONS'=>$FORMP[$key]['OPTIONS']));
						}
						else
						{	
							$prop_res[] = GTdblockprop::Add(array(
															'TYPE_ID'=>$arVar['ID'],
															'NAME'=>$FORMP[$key]['NAME'],
															'TYPE'=>$FORMP[$key]['TYPE'],
															'SORT'=>$FORMP[$key]['SORT'],
															'DEFAULT'=>$FORMP[$key]['DEFAULT'],
															'ACTIVE'=>$FORMP[$key]['ACTIVE'],
															'REQUIRED'=>$FORMP[$key]['REQUIRED'],
															'VALUES'=>$FORMP[$key]['VALUES'],
															'MULTIPLE'=>$FORMP[$key]['MULTIPLE'],
															'LENGTH'=>$FORMP[$key]['LENGTH'],
															'PROP_KEY'=>$FORMP[$key]['PROP_KEY'],
															'OPTIONS'=>$FORMP[$key]['OPTIONS']));
						}
						
					}
				}
				
				if ($res===TRUE)
				{
					header("Location: ./?mod=dblocks&act=types&id=".$arVar['GROUP_ID'][0]);
					die();
					
				}else{
				GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));}
			//}
		}
/**for dblocks****for dblocks*****for dblocks*******for dblocks********for dblocks*****for dblocks*/
	function dblocks($ID=FALSE,$PARAMS=FALSE,$iPage=False)
	{
		$iParams=array(array('TITLE','NAME','SORT','TYPE_ID','ID'),array('CREATED','ASC'));
		global $DB,$APP;	
			$row=GTdblocksubtype::Get('',Array('TYPE_ID'=>$ID,'PARENT'=>'0'));
			if(!empty($row) && $PARAMS==FALSE)
			{
			
				foreach($row as $key=>$val)
				{
					$arrModResult['modContent'][] =
					Array(
							"NAME" =>$val['NAME'],
							"SORT" => $val['SORT'],
							"TYPE_ID" => $val['TYPE_NAME'],
							"ID" => $val['ID']
						);
				}
					$arrModResult['modContent'][] =
					Array(
							"NAME" => GetMessage('ELEMENTS'),
							"SORT" => '',
							"TYPE_ID" => '',
							"ID" => '0'
						);
				$arrModResult['modHeader'] = Array(
										"NAME" => GetMessage('NAME'),
										"SORT" => GetMessage('SORT'),
										"TYPE_ID" => GetMessage('TYPE'),
										"ID" => "ID"
										);
				$APP->admModDetemineActions(Array('AdD','LIST','EDIT','DELETE'));
				$APP->admModSetUrl('act=get_subblock&type='.$ID.'&subtype=','edit');
				$APP->admModSetUrl('act=new_dblock&type='.$ID, 'add');
				$APP->addControlUserButton(GetMessage('CREATE_FOLDER'),'act=new_subtype&id='.$ID);
				$APP->admModSetUrl('moded=del_ST', 'action');
				$APP->admModSetUrl('act=dblock_filter&id='.$ID, 'filter');
				
				$row=GTdblocktype::Get('',array('ID'=>'='.$ID),'','',Array('ID'));
				$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
				$APP->ModCreateBreadcumbs($row[$ID]['GROUP_NAME'].':'.GetMessage('TYPES'),'?mod=dblocks&act=types&id='.$row[$ID]['GROUP_ID']);
				$APP->ModCreateBreadcumbs($row[$ID]['NAME'].':'.GetMessage('FOLDERS'));
				$APP->admCacheGetModuleFields();
				
				$APP->admModShowElements($arrModResult['modHeader'], $arrModResult['modContent'], "list",  Array('TITLE','DESC','ID', 'ACTIVE'=>Array('3'=>' ',0=>GetMessage('NO'),1=>GetMessage('YES'))));
			}
			else
			{
			global $DB,$APP , $arrModResult;
			$iParams='';
			$iParams=$this->GetParams(array('TYPE'=>'DBLOCK'));
			if(empty($iParams))
			{
				$iParams=Array(Array('TITLE','UPDATED','ID'),Array('TITLE','ASC'));
			}
			if($PARAMS)
			{
				if($PARAMS['TITLE']!=FALSE)$PARAMS['TITLE']='~%'.$PARAMS['TITLE'].'%';
				if($PARAMS['DESC']!=FALSE)$PARAMS['DESC']='~%'.$PARAMS['DESC'].'%';
				if($PARAMS['ACTIVE'][0]!='3'){$PARAMS['ACTIVE']=$PARAMS['ACTIVE'][0];} else {unset($PARAMS['ACTIVE']);}
				if($PARAMS['OUT_LANG'][0]!=''){$PARAMS2['LANG']=$PARAMS['OUT_LANG'][0]; $PARAMS2['FORCE_LANG']='1';} else {unset($PARAMS['OUT_LANG']);}
				$PARAMS['TYPE_ID']=$ID;
				$PARAMS2['TYPE_ID']=$ID;
			}
			else
			{
				$PARAMS['TYPE_ID']=$ID;
				$PARAMS['FORCE_LANG']=$ID;
			}
			
			$iStart=0;
			$iLimit=100;
			if($iPage!=False)
			{
				$iStart=($iPage*$iLimit)-$iLimit;
			}
			$row=GTdblock::Get($iParams[0],$PARAMS,$iParams[1],Array($iStart,$iLimit),'',$counts);
			$D_lang=GToptionlang::Get('',Array('ENABLED'=>1,'DEFAULT'=>1));
			$D_lang=$D_lang[0];			
			$iType=$ID;
			if($counts>$iLimit)
			{
				$Pages=round($counts/$iLimit);
				echo '<br />';
				echo GetMessage('PAGES');
				$Pend=round($Pages/50);
				if($iPage>=50)
				{
					echo '&nbsp<a href="?mod=dblocks&act=dblocks&id='.$iType.'&page=1" style="font-size:14px;"><начало</a>&nbsp';
				}
				$j=1;
				$p=array();
				for($i=1;$i<=$Pages;$i++)
				{
					if(empty($iPage)){$iPage=1;}
					$iPage=(int)$iPage;
					$mode=($i%50);
					if($mode==0)
					{
						$j++;
					}
					$p[$j][$i]=$i;
				}
				foreach($p as $iPk=>$iPv)
				{
					if(in_array($iPage,$iPv))
					{
						$inArray=$iPk;
					}
				}
				foreach($p[$inArray] as $iK=>$iV)
				{
					if($iPage==$iV)
					{
						echo '&nbsp<a href="?mod=dblocks&act=dblocks&id='.$iType.'&page='.$iV.'" style="font-size:16px;">'.$iV.'</a>&nbsp';
					}
					else
					{
						echo '&nbsp<a href="?mod=dblocks&act=dblocks&id='.$iType.'&page='.$iV.'" style="font-size:12px;">'.$iV.'</a>&nbsp';
					}
					$last=$iV+1;
				}
				if(isset($last) && $last<=$Pages)
				{
					echo '&nbsp<a href="?mod=dblocks&act=dblocks&id='.$iType.'&page='.$last.'" style="font-size:12px;">'.$last.'</a>&nbsp';
				}
				if($iPage<$Pages)
				{
					echo '&nbsp<a href="?mod=dblocks&act=dblocks&id='.$iType.'&page='.$Pages.'" style="font-size:14px;">последни></a>&nbsp';
				}
				
			}
			if($PARAMS2)
			{
				$lans=GTdblock::Get('*',$PARAMS2); //d($lans);
				$another=array();
			}
			foreach($row as $key=>$val)
			{
				if(!empty($lans))
				{
					foreach($lans as $lk=>$lv)
					{
						if($val['ID']==$lv['ID'])
						{
							$another[$val['ID']]=$val['ID'];
						}
					}
				}
				if(!isset($another[$val['ID']]))
				{
					foreach($iParams[0] as $ikey=>$ival)
					{					
						$iTables[$ival]=$val[$ival];
						if($ival=='SHORTTEXT' || $ival=='FULLTEXT')
						{
							$val[$ival]=substr($val[$ival],0,100);
							$iTables[$ival]=$val[$ival];
						}
						if($ival=='ACTIVE')
						{
							switch ($val[$ival])
							{
								case '1':
								$val[$ival]=GetMessage('YES');
								break;
								case '0':
								$val[$ival]=GetMessage('NO');
							}
							$iTables[$ival]=$val[$ival];
						}
						if($ival=='CREATED' || $ival=='DATESTART' || $ival=='DATEEND' || $ival=='UPDATED')
						{
							if($val[$ival]!=0){$val[$ival] = date($D_lang['DATE_TIME_FORMAT'],$val[$ival]);}
							$iTables[$ival]=$val[$ival];
						}					
					}
				
					$arrModResult['modContent'][]=$iTables;
				}
			}
			$iThead=array();
			foreach($iParams[0] as $ikey=>$ival)
				{
					$iThead[$ival]=GetMessage($ival);
				}
					$arrModResult['modHeader'] = $iThead;
					$APP->admModDetemineActions(Array('AdD','LIST','EDIT','DELETE'));
					$APP->admModSetUrl('act=dblock_edit&id=','edit');
					$APP->admModSetUrl('act=new_dblock&type='.$ID, 'add');
					$APP->addControlUserButton(GetMessage('CREATE_FOLDER'),'act=new_subtype&id='.$ID);
					$APP->admModSetUrl('moded=del_dblock', 'action');
					$APP->admModSetUrl('act=dblock_filter&id='.$ID, 'filter');
					$row=GTdblocktype::Get('',array('ID'=>'='.$ID),'','',Array('ID'));
					$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
					$APP->ModCreateBreadcumbs($row[$ID]['GROUP_NAME'].':'.GetMessage('TYPES'),'?mod=dblocks&act=types&id='.$row[$ID]['GROUP_ID']);
					$APP->ModCreateBreadcumbs($row[$ID]['NAME'].':'.GetMessage('ELEMENTS'));
					$APP->admCacheGetModuleFields();
					
					$APP->admModShowElements($arrModResult['modHeader'], $arrModResult['modContent'], "list", Array('TITLE','DESC','OUT_LANG'=>Array('3'=>'','ru'=>'рус.','kz'=>'каз.'), 'ACTIVE'=>Array('3'=>' ',0=>GetMessage('NO'),1=>GetMessage('YES'))));}

	}
function Edit_dblock($ID)
	{
		global $DB,$APP; 
		$MULTY=GTAPP::Conf('multilang_sync');
		if($MULTY!=FALSE)
		{
			$this->EditDblockInMultyLang($ID);
		}
		else
		{
			$this->EditDblockNotInMultyLang($ID);
		}
	}
function EditDblockNotInMultyLang($ID)
	{
		global $DB,$APP; 
		$MULTY=GTAPP::Conf('multilang_sync');
		$this->ID=$ID;
		$row = GTdblock::Get('*',array('ID'=>$ID),'','',array('ID')); 
		$row=$row[$ID];
		
		$res4=GTdblocksubtype::Get('',array('TYPE_ID'=>$row['TYPE_ID']));
		$directory[]=array(''=>GetMessage('UPER_LEVEL'));
		foreach($res4 as $key=>$val)
		{
			$directory[]=array($val['ID']=>$val['NAME']);	
		}
		foreach($directory as $key=>$val)
		{
			foreach($val as $kkey=>$wal)
			{
				$directorys[$kkey]=$wal;
			}
		}
					
		
		if($row['DATESTART']!=0){$row['DATESTART'] = date('Y-m-j h:i:s', $row['DATESTART']);}
		if($row['DATEEND']!=0){$row['DATEEND'] = date('Y-m-j h:i:s', $row['DATEEND']);}
		if($row['CREATED']!=0){$row['CREATED'] = date('Y-m-j h:i:s', $row['CREATED']);}
		if($row['UPDATED']!=0){$row['UPDATED'] = date('Y-m-j h:i:s', $row['UPDATED']);}
		$TITLE=$row['TITLE']; $TYPE=$row['TYPE_ID'];
		if(!empty($row['SHORTIMG']))
		{
			$SH=GTFile::Get('*',array('ID'=>$row['SHORTIMG']));
			if(!empty($SH))
			{
				$SHIM='<input type="hidden" name="SHORTIMG_ID" value="'.$SH[0]['ID'].'"><img src="'.$SH[0]['URL'].'" title="'.$SH[0]['TITLE'].'" alt="'.$SH[0]['TITLE'].'" width="100px">
				<br /><input type="checkbox" name="DeleteF[]" value="'.$SH[0]['ID'].'">';
			}
		}
		if(!empty($row['FULLIMG']))
		{
			$FL=GTFile::Get('*',array('ID'=>$row['FULLIMG']));
			if(!empty($FL))
			{
				$FLIM='<input type="hidden" name="FULLIMG_ID" value="'.$FL[0]['ID'].'"><img src="'.$FL[0]['URL'].'" title="'.$FL[0]['TITLE'].'" alt="'.$FL[0]['TITLE'].'" width="250px">
				<br /><input type="checkbox" name="DeleteF[]" value="'.$FL[0]['ID'].'">';
			}
		}
		$arrModResult['modHeader']=Array(GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('ID'), "NAME" => "SHOWID", "VIEW" => 1, "TYPE" => "text", "VALUE" => $row['ID']),
		Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['TITLE']),
		Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => 'EDIT_DBLOCK'),
		Array("DESC" => "", "NAME" => "TYPE_ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => $row['TYPE_ID']),
		Array("DESC" => "", "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => $row['ID'])),
		GetMessage("DESC")=> Array(
		Array("DESC" => '', "NAME" => "SHORTIMG", "VIEW" => 1, "TYPE" => "raw", "VALUE" => $SHIM),
		Array("DESC" => GetMessage('SHORTIMG'), "NAME" => "SHORTIMG", "VIEW" => 1, "TYPE" => "file", "VALUE" => $row['SHORTIMG']),
		Array("DESC" => '', "NAME" => "SHORTTEXT", "VIEW" => 1, "TYPE" => "textarea:".$row['SHORTTEXT_TYPE']."", "VALUE" => $row['SHORTTEXT'])
		),
		GetMessage("FULL_DESC")=> Array(	
		Array("DESC" => '', "NAME" => "SHORTIMG", "VIEW" => 1, "TYPE" => "raw", "VALUE" => $FLIM),
		Array("DESC" => GetMessage('FULLIMG'), "NAME" => "FULLIMG", "VIEW" => 1, "TYPE" => "file", "VALUE" => $row['FULLIMG']),			
		Array("DESC" => '', "NAME" => "FULLTEXT", "VIEW" => 1, "TYPE" => "textarea:".$row['FULLTEXT_TYPE']."", "VALUE" => $row['FULLTEXT'])
		)
		);
		
		$Props2=GTdblock::Props($row['TYPE_ID'],$ID,"EDIT");	
		$Props2[]=Array("DESC" => GetMessage('CREATED'), "NAME" => "CREATED", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$row['CREATED']);
		$Props2[]=Array("DESC" => GetMessage('UPDATED'), "NAME" => "UPDATED", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$row['UPDATED']);
		$Props2[]=Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['SORT']);
		$Props2[]=Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio:".$row['ACTIVE'], "VALUE" => Array(1 =>GetMessage('YES'), 0=>GetMessage('NO')));
		$Props2[]=Array("DESC" => GetMessage('DATESTART'), "NAME" => "DATESTART", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row['DATESTART']);
		$Props2[]=Array("DESC" => GetMessage('DATEEND'), "NAME" => "DATEEND", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row['DATEEND']);
		$Props2[]=Array("DESC" => GetMessage('TAGS'), "NAME" => "TAGS", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['TAGS']);
		$Props2[]=Array("DESC" => GetMessage('DIRECTORY'), "NAME" => "SUBTYPE", "TYPE" => "select:".$row['SUBTYPE_ID'], "VALUE" => $directorys);
		$Props2[]=Array("DESC" => GetMessage('KEY'), "NAME" => "DBLOCK_KEY", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row['DBLOCK_KEY']);
				
		$row=GTdblocktype::Get('',array('ID'=>'='.$TYPE),'','',Array('ID'));
		$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
		$APP->ModCreateBreadcumbs($row[$TYPE]['GROUP_NAME'],'?mod=dblocks&act=types&id='.$row[$TYPE]['GROUP_ID']);
		$APP->ModCreateBreadcumbs($row[$TYPE]['NAME'].':'.GetMessage('ELEMENTS'),'?mod=dblocks&act=dblocks&id='.$TYPE);
		$APP->ModCreateBreadcumbs($TITLE);
		if(isset($Props2)){$arrModResult['modHeader'][GetMessage('PROPERTIES')]=array_merge($arrModResult['modHeader'],$Props2);}
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
function EditDblockInMultyLang($ID)
	{
		global $DB,$APP; 
		$MULTY=GTAPP::Conf('multilang_sync');
		$D_lang=GToptionlang::Get('',Array('ENABLED'=>1,'DEFAULT'=>1));
		$D_lang=$D_lang[0];
		$row = GTdblock::Get('*',array('ID'=>$ID,'LANG'=>$D_lang['ID']),'','',array('ID')); 
		$row=$row[$ID];
		//d($row);
		$res4=GTdblocksubtype::Get('',array('TYPE_ID'=>$row['TYPE_ID']));
		$directory[]=array(''=>GetMessage('UPER_LEVEL'));
		foreach($res4 as $key=>$val)
		{
			$directory[]=array($val['ID']=>$val['NAME']);	
		}
		foreach($directory as $key=>$val)
		{
			foreach($val as $kkey=>$wal)
			{
				$directorys[$kkey]=$wal;
			}
		}
			
		if($row['DATESTART']!=0){$row['DATESTART'] = date($D_lang['DATE_TIME_FORMAT'], $row['DATESTART']);}
		if($row['DATEEND']!=0){$row['DATEEND'] = date($D_lang['DATE_TIME_FORMAT'], $row['DATEEND']);}
		if($row['CREATED']!=0){$row['CREATED'] = date($D_lang['DATE_TIME_FORMAT'], $row['CREATED']);}
		if($row['UPDATED']!=0){$row['UPDATED'] = date($D_lang['DATE_TIME_FORMAT'], $row['UPDATED']);}
		$TITLE=$row['TITLE']; $TYPE=$row['TYPE_ID'];
		if(!empty($row['SHORTIMG']))
		{
			$SH=GTFile::Get('*',array('ID'=>$row['SHORTIMG']));
			if(!empty($SH))
			{
				$SHIM='<input type="hidden" name="SHORTIMG_ID" value="'.$SH[0]['ID'].'"><img src="'.$SH[0]['URL'].'" title="'.$SH[0]['TITLE'].'" alt="'.$SH[0]['TITLE'].'" width="100px">
				<br /><input type="checkbox" name="DeleteF[]" value="'.$SH[0]['ID'].'">';
			}
		}
		if(!empty($row['FULLIMG']))
		{
			$FL=GTFile::Get('*',array('ID'=>$row['FULLIMG']));
			if(!empty($FL))
			{
				$FLIM='<input type="hidden" name="FULLIMG_ID" value="'.$FL[0]['ID'].'"><img src="'.$FL[0]['URL'].'" title="'.$FL[0]['TITLE'].'" alt="'.$FL[0]['TITLE'].'" width="250px">
				<br /><input type="checkbox" name="DeleteF[]" value="'.$FL[0]['ID'].'">';
			}
		}
		$arrModResult['modHeader']=Array(GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('ID'), "NAME" => "SHOWID", "VIEW" => 1, "TYPE" => "text", "VALUE" => $row['ID']),
		Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE[ru]", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['TITLE']),
		Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => 'EDIT_DBLOCK'),
		Array("DESC" => "", "NAME" => "TYPE_ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => $row['TYPE_ID']),
		Array("DESC" => "", "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => $row['ID'])),
		GetMessage("DESC")=> Array(
		Array("DESC" => '', "NAME" => "SHORTIMG", "VIEW" => 1, "TYPE" => "raw", "VALUE" => $SHIM),
		Array("DESC" => GetMessage('SHORTIMG'), "NAME" => "SHORTIMG", "VIEW" => 1, "TYPE" => "file", "VALUE" => $row['SHORTIMG']),
		Array("DESC" => '', "NAME" => "SHORTTEXT[ru]", "VIEW" => 1, "TYPE" => "textarea:".$row['SHORTTEXT_TYPE']."", "VALUE" => $row['SHORTTEXT'])
		),
		GetMessage("FULL_DESC")=> Array(	
		Array("DESC" => '', "NAME" => "SHORTIMG", "VIEW" => 1, "TYPE" => "raw", "VALUE" => $FLIM),
		Array("DESC" => GetMessage('FULLIMG'), "NAME" => "FULLIMG", "VIEW" => 1, "TYPE" => "file", "VALUE" => $row['FULLIMG']),			
		Array("DESC" => '', "NAME" => "FULLTEXT[ru]", "VIEW" => 1, "TYPE" => "textarea:".$row['FULLTEXT_TYPE']."", "VALUE" => $row['FULLTEXT'])
		)
		);
		$lang=GToptionlang::Get('',Array('ENABLED'=>1),'','','',$count); //d($lang);
		foreach($lang as $key=>$val)
		{
			if($val['DEFAULT']!=0)
			{
				$Props2['MAIN']=GTdblock::Props($TYPE,$ID,"EDIT",$val['ID']);
				$MainLang=$val['ID'];
			}
			else
			{
				$langR=array();
				$langR = GTdblock::Get('*',array('ID'=>$ID,'LANG'=>'kz','FORCE_LANG'=>'1'));
				
					$langR=$langR[0];
					$Props2[$val['ID']]['TITLE']=Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => $langR ['TITLE']);
					$Props2[$val['ID']]['SHORTTEXT']=Array("DESC" => GetMessage('SHORTTEXT'), "NAME" => "SHORTTEXT[".$val['ID']."]", "VIEW" => 1, "TYPE" => "textarea:".$langR['SHORTTEXT_TYPE'], "VALUE" => $langR ['SHORTTEXT']);
					$Props2[$val['ID']]['FULLTEXT']=Array("DESC" => GetMessage('FULLTEXT'), "NAME" => "FULLTEXT[".$val['ID']."]", "VIEW" => 1, "TYPE" => "textarea:".$langR['FULLTEXT_TYPE'], "VALUE" => $langR ['FULLTEXT']);
					$Propss2[$val['ID']]=array();
					$Propss2[$val['ID']]=GTdblock::Props($TYPE,$ID,"EDIT",$val['ID']);
					if(!empty($Propss2[$val['ID']]))
					{
						$Props2[$val['ID']]=array_merge($Props2[$val['ID']],$Propss2[$val['ID']]);
					}
									
			}
		}
		//$Props2=GTdblock::Props($row['TYPE_ID'],$ID,"EDIT");
		$Props2['MAIN'][]=Array("DESC" => GetMessage('CREATED'), "NAME" => "CREATED", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$row['CREATED']);
		$Props2['MAIN'][]=Array("DESC" => GetMessage('UPDATED'), "NAME" => "UPDATED", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$row['UPDATED']);
		$Props2['MAIN'][]=Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['SORT']);
		$Props2['MAIN'][]=Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio:".$row['ACTIVE'], "VALUE" => Array(1 =>GetMessage('YES'), 0=>GetMessage('NO')));
		$Props2['MAIN'][]=Array("DESC" => GetMessage('LINK'), "NAME" => "LINK", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['LINK']);
		$Props2['MAIN'][]=Array("DESC" => GetMessage('INDEXED'), "NAME" => "INDEXED", "VIEW" => 1, "TYPE" => "radio:".$row['INDEXED'], "VALUE" => Array(1 =>GetMessage('YES'), 0=>GetMessage('NO')));
		$Props2['MAIN'][]=Array("DESC" => GetMessage('DATESTART'), "NAME" => "DATESTART", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row['DATESTART']);
		$Props2['MAIN'][]=Array("DESC" => GetMessage('DATEEND'), "NAME" => "DATEEND", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row['DATEEND']);
		$Props2['MAIN'][]=Array("DESC" => GetMessage('TAGS'), "NAME" => "TAGS", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['TAGS']);
		$Props2['MAIN'][]=Array("DESC" => GetMessage('DIRECTORY'), "NAME" => "SUBTYPE", "TYPE" => "select:".$row['SUBTYPE_ID'], "VALUE" => $directorys);
		$Props2['MAIN'][]=Array("DESC" => GetMessage('KEY'), "NAME" => "DBLOCK_KEY", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row['DBLOCK_KEY']);
				
		$row=GTdblocktype::Get('',array('ID'=>'='.$TYPE),'','',Array('ID'));
		$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
		$APP->ModCreateBreadcumbs($row[$TYPE]['GROUP_NAME'],'?mod=dblocks&act=types&id='.$row[$TYPE]['GROUP_ID']);
		$APP->ModCreateBreadcumbs($row[$TYPE]['NAME'].':'.GetMessage('ELEMENTS'),'?mod=dblocks&act=dblocks&id='.$TYPE);
		$APP->ModCreateBreadcumbs($TITLE);
		if(isset($Props2['MAIN'])){$arrModResult['modHeader'][GetMessage('PROPERTIES')]=array_merge($arrModResult['modHeader'],$Props2['MAIN']);}
		foreach($lang as $key=>$val)
		{
			if($val['DEFAULT']!=1)
			{
				if(isset($Props2[$val['ID']]))
				{
					
					$arrModResult['modHeader'][GetMessage($val['TITLE'])]=array_merge($arrModResult['modHeader'],$Props2[$val['ID']]);
				}
			}
		}
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
function New_dblock($TYPE,$SUBTYPE=FALSE)
	{ 
		global $DB, $APP , $arrModResult;
		$MULTY=GTAPP::Conf('multilang_sync');
		if($MULTY!=FALSE)
		{
			$this->NewDblockInMultyLang($TYPE,$SUBTYPE);
		}
		else
		{
			$this->NewDblockNotMulttLang($TYPE,$SUBTYPE);
		}
	}
function NewDblockInMultyLang($TYPE,$SUBTYPE=FALSE)
	{
		global $DB, $APP , $arrModResult;
		$MULTY=GTAPP::Conf('multilang_sync');
		$res=GTdblocksubtype::Get('',array('TYPE_ID'=>'='.$TYPE));
		$directory[]=array(''=>GetMessage('UPER_LEVEL'));
		foreach($res as $key => $val)
		{
			$directory[]=array($val['ID']=>$val['NAME']);	
		}
		foreach($directory as $key=>$val)
		{
			foreach($val as $kkey=>$wal)
			{
			$directorys[$kkey]=$wal;
			}
		}
		$lang=GToptionlang::Get('',Array('ENABLED'=>1));
		foreach($lang as $key=>$val)
		{
			if($val['DEFAULT']!=0)
			{
				$Props2['MAIN']=GTdblock::Props($TYPE,'',"NEW",$val['ID']);
				$MainLang=$val['ID'];
			}
			else
			{
				$Props2[$val['ID']]['TITLE']=Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
				$Props2[$val['ID']]['SHORTTEXT']=Array("DESC" => GetMessage('SHORTTEXT'), "NAME" => "SHORTTEXT[".$val['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '');
				$Props2[$val['ID']]['FULLTEXT']=Array("DESC" => GetMessage('FULLTEXT'), "NAME" => "FULLTEXT[".$val['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '');
				$Propss2[$val['ID']]=array();
				$Propss2[$val['ID']]=GTdblock::Props($TYPE,'',"NEW",$val['ID']);
				if(!empty($Propss2[$val['ID']]))
				{
					$Props2[$val['ID']]=array_merge($Props2[$val['ID']],$Propss2[$val['ID']]);
				}
				
				
			}
		}
		
		$Props2['MAIN'][]=Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
		$Props2['MAIN'][]=Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO')));
		$Props2['MAIN'][]=Array("DESC" => GetMessage('LINK'), "NAME" => "LINK", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
		$Props2['MAIN'][]=Array("DESC" => GetMessage('INDEXED'), "NAME" => "INDEXED", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array(1 =>GetMessage('YES'), 0=>GetMessage('NO')));
		$Props2['MAIN'][]=Array("DESC" => GetMessage('DATESTART'), "NAME" => "DATESTART", "VIEW" => 1, "TYPE" => "string", "VALUE" =>'');
		$Props2['MAIN'][]=Array("DESC" => GetMessage('DATEEND'), "NAME" => "DATEEND", "VIEW" => 1, "TYPE" => "string", "VALUE" =>'');
		$Props2['MAIN'][]=Array("DESC" => GetMessage('TAGS'), "NAME" => "TAGS", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
		$Props2['MAIN'][]=Array("DESC" => GetMessage('DIRECTORY'), "NAME" => "SUBTYPE", "TYPE" => "select:".$SUBTYPE, "VALUE" => $directorys);
		$Props2['MAIN'][]=Array("DESC" => GetMessage('KEY'), "NAME" => "DBLOCK_KEY", "VIEW" => 1, "TYPE" => "string", "VALUE" =>'');

		$arrModResult['modHeader']=Array(
			GetMessage("MAIN")=> Array(
			Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE[".$MainLang."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
			Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => 'NEW_DBLOCK'),
			Array("DESC" => "", "NAME" => "TYPE_ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => $TYPE)
			),
			GetMessage("DESC")=> Array(
			Array("DESC" => GetMessage('SHORTIMG'), "NAME" => "SHORTIMG", "VIEW" => 1, "TYPE" => "file", "VALUE" => $row['SHORTIMG']),
			Array("DESC" => '', "NAME" =>"SHORTTEXT[".$MainLang."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '')
			),
			GetMessage("FULL_DESC")=> Array(
			Array("DESC" => GetMessage('FULLIMG'), "NAME" => "FULLIMG", "VIEW" => 1, "TYPE" => "file", "VALUE" => $row['FULLIMG']),
			Array("DESC" => '', "NAME" => "FULLTEXT[".$MainLang."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $row['FULLTEXT']))
			);
		
		$row=GTdblocktype::Get('',array('ID'=>'='.$TYPE),'','',Array('ID'));
		$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
		$APP->ModCreateBreadcumbs($row[$TYPE]['GROUP_NAME'],'?mod=dblocks&act=types&id='.$row[$TYPE]['GROUP_ID']);
		$APP->ModCreateBreadcumbs($row[$TYPE]['NAME'].':'.GetMessage('ELEMENTS'),'?mod=dblocks&act=dblocks&id='.$TYPE);
		$APP->ModCreateBreadcumbs(GetMessage('NEW_ELEMENT'));
		if(isset($Props2['MAIN'])){$arrModResult['modHeader'][GetMessage('PROPERTIES')]=array_merge($arrModResult['modHeader'],$Props2['MAIN']);}
		foreach($lang as $key=>$val)
		{
			if($val['DEFAULT']!=1)
			{
				if(isset($Props2[$val['ID']]))
				{
					
					$arrModResult['modHeader'][GetMessage($val['TITLE'])]=array_merge($arrModResult['modHeader'],$Props2[$val['ID']]);
				}
			}
		}
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}

function NewDblockNotMulttLang($TYPE,$SUBTYPE=FALSE)
	{
		global $DB, $APP , $arrModResult;
		$MULTY=GTAPP::Conf('multilang_sync');
		$res=GTdblocksubtype::Get('',array('TYPE_ID'=>'='.$TYPE));
		$directory[]=array(''=>GetMessage('UPER_LEVEL'));
		foreach($res as $key => $val)
		{
			$directory[]=array($val['ID']=>$val['NAME']);	
		}
		foreach($directory as $key=>$val)
		{
			foreach($val as $kkey=>$wal)
			{
			$directorys[$kkey]=$wal;
			}
		}
	
		$Props2=GTdblock::Props($TYPE,'',"NEW");
		$Props2[]=Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
		$Props2[]=Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO')));
		$Props2[]=Array("DESC" => GetMessage('DATESTART'), "NAME" => "DATESTART", "VIEW" => 1, "TYPE" => "string", "VALUE" =>'');
		$Props2[]=Array("DESC" => GetMessage('DATEEND'), "NAME" => "DATEEND", "VIEW" => 1, "TYPE" => "string", "VALUE" =>'');
		$Props2[]=Array("DESC" => GetMessage('TAGS'), "NAME" => "TAGS", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
		$Props2[]=Array("DESC" => GetMessage('DIRECTORY'), "NAME" => "SUBTYPE", "TYPE" => "select:".$SUBTYPE, "VALUE" => $directorys);
		$Props2[]=Array("DESC" => GetMessage('KEY'), "NAME" => "DBLOCK_KEY", "VIEW" => 1, "TYPE" => "string", "VALUE" =>'');

		$arrModResult['modHeader']=Array(
			GetMessage("MAIN")=> Array(
			Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
			Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => 'NEW_DBLOCK'),
			Array("DESC" => "", "NAME" => "TYPE_ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" => $TYPE)
			),
			GetMessage("DESC")=> Array(
			Array("DESC" => GetMessage('SHORTIMG'), "NAME" => "SHORTIMG", "VIEW" => 1, "TYPE" => "file", "VALUE" => $row['SHORTIMG']),
			Array("DESC" => '', "NAME" => "SHORTTEXT", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '')
			),
			GetMessage("FULL_DESC")=> Array(
			Array("DESC" => GetMessage('FULLIMG'), "NAME" => "FULLIMG", "VIEW" => 1, "TYPE" => "file", "VALUE" => $row['FULLIMG']),
			Array("DESC" => '', "NAME" => "FULLTEXT", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $row['FULLTEXT']))
			);
		
		$row=GTdblocktype::Get('',array('ID'=>'='.$TYPE),'','',Array('ID'));
		$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
		$APP->ModCreateBreadcumbs($row[$TYPE]['GROUP_NAME'],'?mod=dblocks&act=types&id='.$row[$TYPE]['GROUP_ID']);
		$APP->ModCreateBreadcumbs($row[$TYPE]['NAME'].':'.GetMessage('ELEMENTS'),'?mod=dblocks&act=dblocks&id='.$TYPE);
		$APP->ModCreateBreadcumbs(GetMessage('NEW_ELEMENT'));
		if(isset($Props2)){$arrModResult['modHeader'][GetMessage('PROPERTIES')]=array_merge($arrModResult['modHeader'],$Props2);}
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
function AddInDblock($arVar)
	{
		global $APP;
		$MULTY=GTAPP::Conf('multilang_sync');
		if($MULTY!=FALSE)
		{
			$lang=GToptionlang::Get('',Array('ENABLED'=>1));
			foreach($lang as $Lval)
			{
				if(!empty($arVar['LIST'][$Lval['ID']]))
				{
					$VALUE1=array();
					foreach($arVar['LIST'][$Lval['ID']] as $Key=>$Val)
					{
						if(is_array($Val))
						{
							$VALUE=array();
							foreach($Val as $key=>$val)
							{
							$val_n=(int)$val;
							$VALUE[]=trim($val);
							}
							if(!empty($VALUE))
							{
								$VALUES=implode(',',$VALUE);
								$VALUES=trim($VALUES);
								$PV[]=array(
								'PROP_ID'=>$Key,
								'VALUE'=>$VALUES,
								'LANG'=>$Lval['ID'],
								'VALUE_NUM'=>$val_n);
							}
							
						}
						else
						{
							$Val_n=(int)$Val;
							$VALUE1[]=trim($Val);
						}
					}
					if(!empty($VALUE1))
					{
						$VALUES1=implode(',',$VALUE1);
						$VALUES1=trim($VALUES1);
						$PV[]=array(
						'PROP_ID'=>$Key,
						'VALUE'=>$VALUES1,
						'LANG'=>$Lval['ID'],
						'VALUE_NUM'=>$Val_n);
					}
				}
			if(!empty($arVar['PROPS'][$Lval['ID']]))
			{
				foreach($arVar['PROPS'][$Lval['ID']] as $Key=>$Val)
				{
					if(is_array($Val))
					{
						foreach($Val as $key=>$val)
						{
							if($arVar['edswitchPROPS'.$Lval['ID'].$Key])
							{
								$val_n=(int)$val;
								$PV[]=array(
								'PROP_ID'=>$Key,
								'VALUE'=>$val,
								'LANG'=>$Lval['ID'],
								'VALUE_NUM'=>$val_n,
								'SWITCH'=>$arVar['edswitchPROPS'.$Lval['ID'].$Key]);
							}
							else
							{
								$val_n=(int)$val;
								$PV[]=array(
								'PROP_ID'=>$Key,
								'VALUE'=>$val,
								'LANG'=>$Lval['ID'],
								'VALUE_NUM'=>$val_n);
							}
						}
					}
					else
					{
						if($arVar['edswitchPROPS'.$Lval['ID'].$Key])
						{
							$Val_n=(int)$Val;
							$PV[]=array(
							'PROP_ID'=>$Key,
							'VALUE'=>$Val,
							'LANG'=>$Lval['ID'],
							'VALUE_NUM'=>$Val_n,
							'SWITCH'=>$arVar['edswitchPROPS'.$Lval['ID'].$Key]);
						}
						else
						{
							$Val_n=(int)$Val;
							$PV[]=array(
							'PROP_ID'=>$Key,
							'VALUE'=>$Val,
							'LANG'=>$Lval['ID'],
							'VALUE_NUM'=>$Val_n);
						}					
					}
				}		
			}
			
				if(!empty($arVar['MDBLOCK']))
				{
					foreach($arVar['MDBLOCK'][$Lval['ID']] as $Key=>$Val)
					{
						if(is_array($Val))
						{
							foreach($Val as $key=>$val)
							{
							$val_n=(int)$val;
							$PM[]=array(
							'PROP_ID'=>$Key,
							'VALUE'=>$val,
							'LANG'=>$Lval['ID'],
							'VALUE_NUM'=>$val_n);
							}
							$PV[$Key.'MULTIPLE'.$Lval['ID']]=array(
								'DBLOCK_ID'=>$arVar['ID'],
								'PROP_ID'=>$Key,
								'VALUE'=>'MULTIPLE',
								'LANG'=>$Lval['ID'],
								'VALUE_NUM'=>'0');
						}
						else
						{
							$Val_n=(int)$Val;
							$PM[]=array(
							'PROP_ID'=>$Key,
							'VALUE'=>$Val,
							'LANG'=>$Lval['ID'],
							'VALUE_NUM'=>$Val_n);
							
							$PV[$Key.'MULTIPLE'.$Lval['ID']]=array(
								'DBLOCK_ID'=>$arVar['ID'],
								'PROP_ID'=>$Key,
								'VALUE'=>'MULTIPLE',
								'LANG'=>$Lval['ID'],
								'VALUE_NUM'=>'0');
						}
					}
				}
				$arrVars[$Lval['ID']] = array(
					'TITLE'=>$arVar['TITLE'][$Lval['ID']],
					'SHORTTEXT'=>$arVar['SHORTTEXT'][$Lval['ID']],
					'SHORTTEXT_TYPE'=>$arVar['SHORTTEXT_TYPE'],
					'FULLTEXT_TYPE'=>$arVar['FULLTEXT_TYPE'],
					'FULLTEXT'=>$arVar['FULLTEXT'][$Lval['ID']],
					'edswitchSHORTTEXT'=>$arVar['edswitchSHORTTEXT'.$Lval['ID']],
					'edswitchFULLTEXT'=>$arVar['edswitchFULLTEXT'.$Lval['ID']],
					'SUBTYPE'=>$arVar['SUBTYPE'][0],
					'DBLOCK_KEY'=>$arVar['DBLOCK_KEY'],
					'LANG'=>$Lval['ID'],
					'TYPE_ID'=>$arVar['TYPE_ID']);
					
				$IMdate=date('Y-m-d H:m:s');
				$IMdate=strtotime($IMdate);
				$IM=array();
				$IM=GTfile::AddPostedFileGroup('IMAGES'.$Lval['ID'], array('SUBSYSTEM'=>'DBL::'.$LASTID,'TITLE'=>'IMG_'.$IMdate));
				if(!empty($IM))
				{
					foreach($IM as $key=>$val)
					{
						$F='';
						$F= GTDOCROOT.'/medialibrary/images/SMALL_'.$val['ID']._.$val['LOCAL_NAME'];
						mkThumbMS(GTDOCROOT.$val['URL'],$F,100);
						$F='';
						$F= GTDOCROOT.'/medialibrary/images/MEDIUM_'.$val['ID']._.$val['LOCAL_NAME'];
						mkThumbMS(GTDOCROOT.$val['URL'],$F,150);
						$val_n=$val['ID'];
						$PV[]=array(
							'PROP_ID'=>$key,
							'VALUE'=>$val['ID'],
							'LANG'=>$Lval['ID'],
							'VALUE_NUM'=>$val_n);
					}
				}
				$IM=array();
				$IM=GTfile::AddPostedFileGroup('MULTYIMAGES'.$Lval['ID'], array('SUBSYSTEM'=>'DBL::'.$LASTID,'TITLE'=>'MIMG_'.$IMdate));
				
				if(!empty($IM))
				{
					foreach($IM as $key=>$val)
					{
						$F='';
						$F= GTDOCROOT.'/medialibrary/images/SMALL_'.$val['ID']._.$val['LOCAL_NAME'];
						mkThumbMS(GTDOCROOT.$val['URL'],$F,100);
						$F='';
						$F= GTDOCROOT.'/medialibrary/images/MEDIUM_'.$val['ID']._.$val['LOCAL_NAME'];
						mkThumbMS(GTDOCROOT.$val['URL'],$F,150);
						$val_n=$val['ID'];
						$PM[]=array(
						'PROP_ID'=>$key,
						'VALUE'=>$val['ID'],
						'LANG'=>$Lval['ID'],
						'VALUE_NUM'=>$val_n);
						$Key=$key;
					}
					$PV[]=array(
					'PROP_ID'=>$Key,
					'VALUE'=>'MULTYIMAGES',
					'LANG'=>$Lval['ID'],
					'VALUE_NUM'=>'0');
				}
				$IM=array();
				$IM=GTfile::AddPostedFileGroup('FILES'.$Lval['ID'], array('SUBSYSTEM'=>'DBL::'.$LASTID,'TITLE'=>'FILE_'.$IMdate));
				if(!empty($IM))
				{
					foreach($IM as $key=>$val)
					{
						$val_n=$val['ID'];
						$PV[]=array(
						'PROP_ID'=>$key,
						'VALUE'=>$val['ID'],
						'LANG'=>$Lval['ID'],
						'VALUE_NUM'=>$val_n);
					}
				}
				$IM=array();
				$IM=GTfile::AddPostedFileGroup('MULTYFILES'.$Lval['ID'], array('SUBSYSTEM'=>'DBL::'.$LASTID,'TITLE'=>'MFILE_'.$IMdate));
				if(!empty($IM))
				{
					foreach($IM as $key=>$val)
					{
						$val_n=$val['ID'];
						$PM[]=array(
						'PROP_ID'=>$key,
						'VALUE'=>$val['ID'],
						'LANG'=>$Lval['ID'],
						'VALUE_NUM'=>$val_n);
						$Key=$key;
					}
					$PV[]=array(
					'PROP_ID'=>$Key,
					'VALUE'=>'MULTYFILES',
					'LANG'=>$Lval['ID'],
					'VALUE_NUM'=>'0');
				}
			}
			$arrVars['MAIN'] = array(
				'TITLE'=>$arVar['TITLE']['ru'],
				'DATESTART'=>$arVar['DATESTART'],
				'DATEEND'=>$arVar['DATEEND'],
				'ACTIVE'=>$arVar['ACTIVE'],
				'SHORTIMG'=>$arVar['SHORTIMG'],
				'FULLIMG'=>$arVar['FULLIMG'],
				'SORT'=>$arVar['SORT'],
				'TAGS'=>$arVar['TAGS'],
				'SUBTYPE'=>$arVar['SUBTYPE'][0],
				'DBLOCK_KEY'=>$arVar['DBLOCK_KEY'],
				'LINK'=>$arVar['LINK'],
				'INDEXED'=>$arVar['INDEXED'],
				'TYPE_ID'=>$arVar['TYPE_ID']);
				
			
			
			$SHIMG=GTfile::AddPostedFile('SHORTIMG', array('SUBSYSTEM'=>'DBLOCK','TITLE'=>'SHORTIMG'.$arVar['NAME']));
			$FULLIMG=GTfile::AddPostedFile('FULLIMG', array('SUBSYSTEM'=>'DBLOCK','TITLE'=>'FULLIMG'.$arVar['NAME']));
			if($SHIMG['TYPE']!='IMAGE')
			{
				GTfile::Delete($SHIMG['ID'],TRUE);
			}
			else
			{
				$arVar['SHORTIMG']=$SHIMG['ID'];
			}
			if($FULLIMG['TYPE']!='IMAGE')
			{
				GTfile::Delete($FULLIMG['ID'],TRUE);
			}
			else
			{
				$arVar['FULLIMG']=$FULLIMG['ID'];
			}

			$res = GTdblock::Add($arrVars,$PV,$PM);
			if($res['REQUIRED'])
				{	
					$pole=array();
					foreach($res['REQUIRED'] as $key=>$val)
					{
						$pole[]=$val['NAME'];
					}
					$poles="\nПоля: <strong>".implode(", ",$pole)."</strong> не заполнены";
					GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS').$poles);
				}
			elseif ($res===TRUE)
			{
				if($arVar['SUBTYPE'][0]!='')
				{
					$TO='&act=get_subblock&type='.$arVar['TYPE_ID'].'&subtype='.$arVar['SUBTYPE'][0];
				}
				else
				{
					$TO="&act=dblocks&id=".$arVar['TYPE_ID'];
				}
				header("Location: ./?mod=dblocks".$TO);
				die();				
			}		
			else 
			{
				GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
			}
		}
		else
		{
		
		if(!empty($arVar['LIST']))
		{
			$VALUE1=array();
			foreach($arVar['LIST'] as $Key=>$Val)
			{
				if(is_array($Val))
				{
					$VALUE=array();
					foreach($Val as $key=>$val)
					{
					$val_n=(int)$val;
					$VALUE[]=trim($val);
					}
					if(!empty($VALUE))
					{
						$VALUES=implode(',',$VALUE);
						$VALUES=trim($VALUES);
						$PV[]=array(
						'PROP_ID'=>$Key,
						'VALUE'=>$VALUES,
						'VALUE_NUM'=>$val_n);
					}
					
				}
				else
				{
					$Val_n=(int)$Val;
					$VALUE1[]=trim($Val);
				}
			}
			if(!empty($VALUE1))
			{
				$VALUES1=implode(',',$VALUE1);
				$VALUES1=trim($VALUES1);
				$PV[]=array(
				'PROP_ID'=>$Key,
				'VALUE'=>$VALUES1,
				'VALUE_NUM'=>$Val_n);
			}
		}
		
		$SHIMG=GTfile::AddPostedFile('SHORTIMG', array('SUBSYSTEM'=>'DBLOCK','TITLE'=>'SHORTIMG'.$arVar['NAME']));
		$FULLIMG=GTfile::AddPostedFile('FULLIMG', array('SUBSYSTEM'=>'DBLOCK','TITLE'=>'FULLIMG'.$arVar['NAME']));
		if($SHIMG['TYPE']!='IMAGE')
		{
			GTfile::Delete($SHIMG['ID'],TRUE);
		}
		else
		{
			$arVar['SHORTIMG']=$SHIMG['ID'];
		}
		if($FULLIMG['TYPE']!='IMAGE')
		{
			GTfile::Delete($FULLIMG['ID'],TRUE);
		}
		else
		{
			$arVar['FULLIMG']=$FULLIMG['ID'];
		}
		global $DB;
		//unset($_POST);
		$arrVars = array(
			'TITLE'=>$arVar['TITLE'],
			'DATESTART'=>$arVar['DATESTART'],
			'DATEEND'=>$arVar['DATEEND'],
			'SHORTTEXT'=>$arVar['SHORTTEXT'],
			'SHORTTEXT_TYPE'=>$arVar['SHORTTEXT_TYPE'],
			'FULLTEXT_TYPE'=>$arVar['FULLTEXT_TYPE'],
			'FULLTEXT'=>$arVar['FULLTEXT'],
			'edswitchSHORTTEXT'=>$arVar['edswitchSHORTTEXT'],
			'edswitchFULLTEXT'=>$arVar['edswitchFULLTEXT'],
			'ACTIVE'=>$arVar['ACTIVE'],
			'SHORTIMG'=>$arVar['SHORTIMG'],
			'FULLIMG'=>$arVar['FULLIMG'],
			'SORT'=>$arVar['SORT'],
			'TAGS'=>$arVar['TAGS'],
			'SUBTYPE'=>$arVar['SUBTYPE'][0],
			'DBLOCK_KEY'=>$arVar['DBLOCK_KEY'],
			'LINK'=>$arVar['LINK'],
			'INDEXED'=>$arVar['INDEXED'],
			'TYPE_ID'=>$arVar['TYPE_ID']);
		if(!empty($arVar['PROPS']))
		{
			foreach($arVar['PROPS'] as $Key=>$Val)
			{
				if(is_array($Val))
				{
					foreach($Val as $key=>$val)
					{
						if($arVar['edswitchPROPS'.$Key])
						{
							$val_n=(int)$val;
							$PV[]=array(
							'PROP_ID'=>$Key,
							'VALUE'=>$val,
							'SWITCH'=>$arVar['edswitchPROPS'.$Key],
							'VALUE_NUM'=>$val_n);
						}
						else
						{
							$val_n=(int)$val;
							$PV[]=array(
							'PROP_ID'=>$Key,
							'VALUE'=>$val,
							'VALUE_NUM'=>$val_n);
						}
					}
				}
				else
				{
					if($arVar['edswitchPROPS'.$Key])
					{
						$Val_n=(int)$Val;
						$PV[]=array(
						'PROP_ID'=>$Key,
						'VALUE'=>$Val,
						'SWITCH'=>$arVar['edswitchPROPS'.$Key],
						'VALUE_NUM'=>$Val_n);
					}
					else
					{
						$Val_n=(int)$Val;
						$PV[]=array(
						'PROP_ID'=>$Key,
						'VALUE'=>$Val,
						'VALUE_NUM'=>$Val_n);
					}					
				}
			}		
		}

		if(!empty($arVar['MDBLOCK']))
		{
			foreach($arVar['MDBLOCK'] as $Key=>$Val)
			{
				if(is_array($Val))
				{
					foreach($Val as $key=>$val)
					{
					$val_n=(int)$val;
					$PM[]=array(
					'PROP_ID'=>$Key,
					'VALUE'=>$val,
					'VALUE_NUM'=>$val_n);
					}
					$PV[$Key.'MULTIPLE']=array(
						'DBLOCK_ID'=>$arVar['ID'],
						'PROP_ID'=>$Key,
						'VALUE'=>'MULTIPLE',
						'VALUE_NUM'=>'0');
				}
				else
				{
					$Val_n=(int)$Val;
					$PM[]=array(
					'PROP_ID'=>$Key,
					'VALUE'=>$Val,
					'VALUE_NUM'=>$Val_n);
					
					$PV[$Key.'MULTIPLE']=array(
						'DBLOCK_ID'=>$arVar['ID'],
						'PROP_ID'=>$Key,
						'VALUE'=>'MULTIPLE',
						'VALUE_NUM'=>'0');
				}
			}
		}
		$IMdate=date('Y-m-d H:m:s');
		$IMdate=strtotime($IMdate);
		$IM=array();
		$IM=GTfile::AddPostedFileGroup('IMAGES', array('SUBSYSTEM'=>'DBL::'.$LASTID,'TITLE'=>'IMG_'.$IMdate));
		if(!empty($IM))
		{
			foreach($IM as $key=>$val)
			{
				$F='';
				$F= GTDOCROOT.'/medialibrary/images/SMALL_'.$val['ID']._.$val['LOCAL_NAME'];
				mkThumbMS(GTDOCROOT.$val['URL'],$F,100);
				$F='';
				$F= GTDOCROOT.'/medialibrary/images/MEDIUM_'.$val['ID']._.$val['LOCAL_NAME'];
				mkThumbMS(GTDOCROOT.$val['URL'],$F,150);
				$val_n=$val['ID'];
				$PV[]=array(
					'PROP_ID'=>$key,
					'VALUE'=>$val['ID'],
					'VALUE_NUM'=>$val_n);
			}
		}
		$IM=array();
		$IM=GTfile::AddPostedFileGroup('MULTYIMAGES', array('SUBSYSTEM'=>'DBL::'.$LASTID,'TITLE'=>'MIMG_'.$IMdate));
		if(!empty($IM))
		{
			foreach($IM as $key=>$val)
			{
				$F='';
				$F= GTDOCROOT.'/medialibrary/images/SMALL_'.$val['ID']._.$val['LOCAL_NAME'];
				mkThumbMS(GTDOCROOT.$val['URL'],$F,100);
				$F='';
				$F= GTDOCROOT.'/medialibrary/images/MEDIUM_'.$val['ID']._.$val['LOCAL_NAME'];
				mkThumbMS(GTDOCROOT.$val['URL'],$F,150);
				$val_n=$val['ID'];
				$PM[]=array(
				'PROP_ID'=>$key,
				'VALUE'=>$val['ID'],
				'VALUE_NUM'=>$val_n);
				$Key=$key;
			}
			$PV[]=array(
			'PROP_ID'=>$Key,
			'VALUE'=>'MULTYIMAGES',
			'VALUE_NUM'=>'0');
		}
		$IM=array();
		$IM=GTfile::AddPostedFileGroup('FILES', array('SUBSYSTEM'=>'DBL::'.$LASTID,'TITLE'=>'FILE_'.$IMdate));
		if(!empty($IM))
		{
			foreach($IM as $key=>$val)
			{
				$val_n=$val['ID'];
				$PV[]=array(
				'PROP_ID'=>$key,
				'VALUE'=>$val['ID'],
				'VALUE_NUM'=>$val_n);
			}
		}
		$IM=array();
		$IM=GTfile::AddPostedFileGroup('MULTYFILES', array('SUBSYSTEM'=>'DBL::'.$LASTID,'TITLE'=>'MFILE_'.$IMdate));
		if(!empty($IM))
		{
			foreach($IM as $key=>$val)
			{
				$val_n=$val['ID'];
				$PM[]=array(
				'PROP_ID'=>$key,
				'VALUE'=>$val['ID'],
				'VALUE_NUM'=>$val_n);
				$Key=$key;
			}
			$PV[]=array(
			'PROP_ID'=>$Key,
			'VALUE'=>'MULTYFILES',
			'VALUE_NUM'=>'0');
		}
	
		$LASTID = GTdblock::Add($arrVars,$PV,$PM);
		if(!empty($LASTID)){$res=TRUE;}
			if ($res===TRUE)
			{
				if($arVar['SUBTYPE'][0]!='')
				{
					$TO='&act=get_subblock&type='.$arVar['TYPE_ID'].'&subtype='.$arVar['SUBTYPE'][0];
				}
				else
				{
					$TO="&act=dblocks&id=".$arVar['TYPE_ID'];
				}
				header("Location: ./?mod=dblocks".$TO);
				die();
				
			}		
			else 
			{
				GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
			}
		}
	}
		
function UpdateInDblock($arVar)
	{
		global $APP;
		$MULTY=GTAPP::Conf('multilang_sync');
		if($MULTY!=FALSE)
		{
			$Deflang=GToptionlang::Get('',Array('DEFAULT'=>1,'ENABLED'=>1));
			$Deflang=$Deflang[0]['ID'];
			$lang=GToptionlang::Get('',Array('ENABLED'=>1),'','','',$count);
			foreach($lang as $Lval)
			{
				if(!empty($arVar['LIST'][$Lval['ID']]))
				{
					$VALUE1=array();
					foreach($arVar['LIST'][$Lval['ID']] as $Key=>$Val)
					{
						if(is_array($Val))
						{
							$VALUE=array();
							foreach($Val as $key=>$val)
							{
							$val_n=(int)$val;
							$VALUE[]=trim($val);
							}
							if(!empty($VALUE))
							{
								$VALUES=implode(',',$VALUE);
								$VALUES=trim($VALUES);
								$PV[]=array(
								'DBLOCK_ID'=>$arVar['ID'],
								'PROP_ID'=>$Key,
								'VALUE'=>$VALUES,
								'LANG'=>$Lval['ID'],
								'VALUE_NUM'=>$val_n);
							}
							
						}
						else
						{
							$Val_n=(int)$Val;
							$VALUE1[]=trim($Val);
						}
					}
					if(!empty($VALUE1))
					{
						$VALUES1=implode(',',$VALUE1);
						$VALUES1=trim($VALUES1);
						$PV[]=array(
						'DBLOCK_ID'=>$arVar['ID'],
						'PROP_ID'=>$Key,
						'VALUE'=>$VALUES1,
						'LANG'=>$Lval['ID'],
						'VALUE_NUM'=>$Val_n);
					}
				}
				if(!empty($arVar['PROPS']))
				{
					foreach($arVar['PROPS'][$Lval['ID']] as $Key=>$Val)
					{
						if(is_array($Val))
						{
							foreach($Val as $key=>$val)
							{
								if($arVar['edswitchPROPS'.$Lval['ID'].$Key])
								{
									$val_n=(int)$val;
									$PV[]=array(
									'DBLOCK_ID'=>$arVar['ID'],
									'PROP_ID'=>$Key,
									'VALUE'=>$val,
									'LANG'=>$Lval['ID'],
									'SWITCH'=>$arVar['edswitchPROPS'.$Lval['ID'].$Key],
									'VALUE_NUM'=>$val_n);
								}
								else
								{
									$val_n=(int)$val;
									$PV[]=array(
									'DBLOCK_ID'=>$arVar['ID'],
									'PROP_ID'=>$Key,
									'VALUE'=>$val,
									'LANG'=>$Lval['ID'],
									'VALUE_NUM'=>$val_n);
								}
							}
						}
						else
						{
							if($arVar['edswitchPROPS'.$Lval['ID'].$Key])
							{
								$Val_n=(int)$Val;
								$PV[]=array(
								'DBLOCK_ID'=>$arVar['ID'],
								'PROP_ID'=>$Key,
								'VALUE'=>$Val,
								'LANG'=>$Lval['ID'],
								'SWITCH'=>$arVar['edswitchPROPS'.$Lval['ID'].$Key],
								'VALUE_NUM'=>$Val_n);
							}
							else
							{
								$Val_n=(int)$Val;
								$PV[]=array(
								'DBLOCK_ID'=>$arVar['ID'],
								'PROP_ID'=>$Key,
								'VALUE'=>$Val,
								'LANG'=>$Lval['ID'],
								'VALUE_NUM'=>$Val_n);
							}
						}
					}		
				}
				
				if(!empty($arVar['MDBLOCK']))
				{
					foreach($arVar['MDBLOCK'][$Lval['ID']] as $Key=>$Val)
					{
						if(is_array($Val))
						{
							
							$CVal[$Lval['ID']]=array_flip($Val); 
							if(!empty($arVar['MDCHECK'][$Lval['ID']]))
							{	
								$to_del=array();
								foreach($arVar['MDCHECK'][$Lval['ID']][$Key] as $key=>$val)
								{	
									if(!isset($CVal[$Lval['ID']][$val]))
									{
										$to_del[]=array(
										'DBLOCK_ID'=>$arVar['ID'],
										'PROP_ID'=>$Key,
										'LANG'=>$Lval['ID'],
										'VALUE'=>$val);
									}
								}					
							}
							foreach($Val as $key=>$val)
							{
							$val_n=(int)$val;
							$PM[]=array(
							'DBLOCK_ID'=>$arVar['ID'],
							'PROP_ID'=>$Key,
							'VALUE'=>$val,
							'LANG'=>$Lval['ID'],
							'VALUE_NUM'=>$val_n);
							
							$PV[$Key.'MULTIPLE'.$Lval['ID']]=array(
							'DBLOCK_ID'=>$arVar['ID'],
							'PROP_ID'=>$Key,
							'VALUE'=>'MULTIPLE',
							'LANG'=>$Lval['ID'],
							'VALUE_NUM'=>'0');
							}
						}
						else
						{
							$Val_n=(int)$Val;
							$PM[]=array(
							'DBLOCK_ID'=>$arVar['ID'],
							'PROP_ID'=>$Key,
							'VALUE'=>$Val,
							'LANG'=>$Lval['ID'],
							'VALUE_NUM'=>$Val_n);
							
							$PV[$Key.'MULTIPLE'.$Lval['ID']]=array(
							'DBLOCK_ID'=>$arVar['ID'],
							'PROP_ID'=>$Key,
							'VALUE'=>'MULTIPLE',
							'LANG'=>$Lval['ID'],
							'VALUE_NUM'=>'0');
						}
					}
				}
				$arrVars[$Lval['ID']] = array(
					'ID'=>$arVar['ID'],
					'TITLE'=>$arVar['TITLE'][$Lval['ID']],
					'SHORTTEXT'=>$arVar['SHORTTEXT'][$Lval['ID']],
					'SHORTTEXT_TYPE'=>$arVar['SHORTTEXT_TYPE'],
					'FULLTEXT_TYPE'=>$arVar['FULLTEXT_TYPE'],
					'FULLTEXT'=>$arVar['FULLTEXT'][$Lval['ID']],
					'edswitchSHORTTEXT'=>$arVar['edswitchSHORTTEXT'.$Lval['ID']],
					'edswitchFULLTEXT'=>$arVar['edswitchFULLTEXT'.$Lval['ID']],
					'SUBTYPE'=>$arVar['SUBTYPE'][0],
					'DBLOCK_KEY'=>$arVar['DBLOCK_KEY'],
					'LANG'=>$Lval['ID'],
					'TYPE_ID'=>$arVar['TYPE_ID']);
					
				$IMdate=date('Y-m-d H:m:s');
				$I_File=array('IMAGES'.$Lval['ID']=>'IMG_','MULTYIMAGES'.$Lval['ID']=>'MIMG_','FILES'.$Lval['ID']=>'FILE_','MULTYFILES'.$Lval['ID']=>'MFILE_');
				foreach($I_File as $FNAME=>$FVALUE)
				{
					$IM=array();
					$IM=GTfile::AddPostedFileGroup($FNAME, array('SUBSYSTEM'=>'DBL::'.$arVar['ID'],'TITLE'=>$FVALUE.$IMdate));
					//d($IM);
					if(!empty($IM))
					{
						foreach($IM as $key=>$val)
						{
							if($FNAME=='MULTYIMAGES'.$Lval['ID'] || $FNAME=='MULTYFILES'.$Lval['ID'])
							{
								if($FNAME=='MULTYIMAGES'.$Lval['ID'])
								{
									$F='';
									$F= GTDOCROOT.'/medialibrary/images/SMALL_'.$val['ID']._.$val['LOCAL_NAME'];
									mkThumbMS(GTDOCROOT.$val['URL'],$F,100);
									$F='';
									$F= GTDOCROOT.'/medialibrary/images/MEDIUM_'.$val['ID']._.$val['LOCAL_NAME'];
									mkThumbMS(GTDOCROOT.$val['URL'],$F,150);
								}
								$PM[]=array(
								'DBLOCK_ID'=>$arVar['ID'],
								'PROP_ID'=>$key,
								'VALUE'=>$val['ID'],
								'LANG'=>$Lval['ID'],
								'VALUE_NUM'=>$val['ID']);
							}
							if($FNAME=='FILES'.$Lval['ID'] || $FNAME=='IMAGES'.$Lval['ID'])
							{
								if($FNAME=='IMAGES'.$Lval['ID'])
								{
									$F='';
									$F= GTDOCROOT.'/medialibrary/images/SMALL_'.$val['ID']._.$val['LOCAL_NAME'];
									mkThumbMS(GTDOCROOT.$val['URL'],$F,100);
									$F='';
									$F= GTDOCROOT.'/medialibrary/images/MEDIUM_'.$val['ID']._.$val['LOCAL_NAME'];
									mkThumbMS(GTDOCROOT.$val['URL'],$F,150);
								}
								$PV[]=array(
								'DBLOCK_ID'=>$arVar['ID'],
								'PROP_ID'=>$key,
								'VALUE'=>$val['ID'],
								'LANG'=>$Lval['ID'],
								'VALUE_NUM'=>$val['ID']);
							}
						}
					}
				}
				
				foreach($arVar['IMG_ID'] as $key=>$val)
				{
					if(!empty($_FILES['IMAGES'.$Lval['ID']]['name'][$key]))
					{
					GTfile::Delete($val,TRUE);
					}
				}
				foreach($arVar['FILE_ID'] as $key=>$val)
				{
					if(!empty($_FILES['FILES'.$Lval['ID']]['name'][$key]))
					{
					GTfile::Delete($val,TRUE);
					}
				}
			}
			$arrVars['MAIN'] = array(
				'ID'=>$arVar['ID'],
				'TITLE'=>$arVar['TITLE'][$Deflang],
				'DATESTART'=>$arVar['DATESTART'],
				'DATEEND'=>$arVar['DATEEND'],
				'ACTIVE'=>$arVar['ACTIVE'],
				'SHORTIMG'=>$arVar['SHORTIMG'],
				'FULLIMG'=>$arVar['FULLIMG'],
				'SORT'=>$arVar['SORT'],
				'TAGS'=>$arVar['TAGS'],
				'SUBTYPE'=>$arVar['SUBTYPE'][0],
				'DBLOCK_KEY'=>$arVar['DBLOCK_KEY'],
				'LINK'=>$arVar['LINK'],
				'INDEXED'=>$arVar['INDEXED'],
				'TYPE_ID'=>$arVar['TYPE_ID']);
			
				if(!($_FILES['SHORTIMG']) && !empty($arVar['SHORTIMG_ID']))
				{
					GTfile::Delete($arVar['SHORTIMG_ID'],TRUE);
				}
				if(!($_FILES['FULLIMG']) && !empty($arVar['FULLIMG_ID']))
				{
					GTfile::Delete($arVar['FULLIMG_ID'],TRUE);
				}
				if(!empty($arVar['SHORTIMG_ID'])){$arVar['SHORTIMG']=$arVar['SHORTIMG_ID'];}
				if(!empty($arVar['FULLIMG_ID'])){$arVar['FULLIMG']=$arVar['FULLIMG_ID'];}
				$SHIMG=GTfile::AddPostedFile('SHORTIMG', array('SUBSYSTEM'=>'DBLOCK','TITLE'=>'SHORTIMG'.$arVar['NAME']));
				$FULLIMG=GTfile::AddPostedFile('FULLIMG', array('SUBSYSTEM'=>'DBLOCK','TITLE'=>'FULLIMG'.$arVar['NAME']));
				if($SHIMG['TYPE']!='IMAGE')
				{
					GTfile::Delete($SHIMG['ID'],TRUE);
				}
				else
				{
					$arVar['SHORTIMG']=$SHIMG['ID'];
				}
				if($FULLIMG['TYPE']!='IMAGE')
				{
					GTfile::Delete($FULLIMG['ID'],TRUE);
				}
				else
				{
					$arVar['FULLIMG']=$FULLIMG['ID'];
				}
				if(!empty($arVar['DeleteF']))
				{
					foreach($arVar['DeleteF'] as $key=>$val)
					{
						foreach($val as $Kkey=>$Vval)
						{
							GTfile::Delete($Vval,TRUE);
							$to_del[]=array(
									'DBLOCK_ID'=>$arVar['ID'],
									'PROP_ID'=>$Kkey,
									'LANG'=>$key,
									'VALUE'=>$Vval);
						}
					}
				}
				if(!empty($arVar['DeleteFS']))
				{
					foreach($arVar['DeleteFS'] as $key=>$val)
					{
						foreach($val as $Kkey=>$Vval)
						{
							GTfile::Delete($Vval,TRUE);
							$to_del2[]=array(
									'DBLOCK_ID'=>$arVar['ID'],
									'PROP_ID'=>$Kkey,
									'LANG'=>$key,
									'VALUE'=>$Vval);
						}
					}
				}
				
				$res=GTdblock::Update($arrVars,$PV,$PM,$to_del);
				if($res['REQUIRED'])
				{	
					$pole=array();
					foreach($res['REQUIRED'] as $key=>$val)
					{
						$pole[]=$val['NAME'];
					}
					$poles="\nПоля: <strong>".implode(", ",$pole)."</strong> не заполнены";
					GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS').$poles);
				}
				elseif ($res===TRUE)
				{
						$row = GTdblock::Get(Array('TYPE_ID','SUBTYPE'),array('ID'=>$arVar['ID']));
						if($row[0]['SUBTYPE']!='')
						{
							$TO='&act=get_subblock&type='.$row[0]['TYPE_ID'].'&subtype='.$row[0]['SUBTYPE'];
						}
						else
						{
							$TO="&act=dblocks&id=".$arVar['TYPE_ID'];
						}
						header("Location: ./?mod=dblocks".$TO);
						die();
				}		
				else 
				{
					GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
				}
		}
		else
		{
		if(!($_FILES['SHORTIMG']) && !empty($arVar['SHORTIMG_ID']))
		{
			GTfile::Delete($arVar['SHORTIMG_ID'],TRUE);
		}
		if(!($_FILES['FULLIMG']) && !empty($arVar['FULLIMG_ID']))
		{
			GTfile::Delete($arVar['FULLIMG_ID'],TRUE);
		}
		if(!empty($arVar['SHORTIMG_ID'])){$arVar['SHORTIMG']=$arVar['SHORTIMG_ID'];}
		if(!empty($arVar['FULLIMG_ID'])){$arVar['FULLIMG']=$arVar['FULLIMG_ID'];}
		$SHIMG=GTfile::AddPostedFile('SHORTIMG', array('SUBSYSTEM'=>'DBLOCK','TITLE'=>'SHORTIMG'.$arVar['NAME']));
		$FULLIMG=GTfile::AddPostedFile('FULLIMG', array('SUBSYSTEM'=>'DBLOCK','TITLE'=>'FULLIMG'.$arVar['NAME']));
		if($SHIMG['TYPE']!='IMAGE')
		{
			GTfile::Delete($SHIMG['ID'],TRUE);
		}
		else
		{
			$arVar['SHORTIMG']=$SHIMG['ID'];
		}
		if($FULLIMG['TYPE']!='IMAGE')
		{
			GTfile::Delete($FULLIMG['ID'],TRUE);
		}
		else
		{
			$arVar['FULLIMG']=$FULLIMG['ID'];
		}
		if(!empty($arVar['DeleteF']))
		{
			foreach($arVar['DeleteF'] as $key=>$val)
			{
			GTfile::Delete($val,TRUE);
			}
		}
		foreach($arVar['IMG_ID'] as $key=>$val)
		{
			if(!empty($_FILES['IMAGES']['name'][$key]))
			{
			GTfile::Delete($val,TRUE);
			}
		}
		foreach($arVar['FILE_ID'] as $key=>$val)
		{
			if(!empty($_FILES['FILES']['name'][$key]))
			{
			GTfile::Delete($val,TRUE);
			}
		}
		$IMdate=date('Y-m-d H:m:s');
		$I_File=array('IMAGES'=>'IMG_','MULTYIMAGES'=>'MIMG_','FILES'=>'FILE_','MULTYFILES'=>'MFILE_');
		foreach($I_File as $FNAME=>$FVALUE)
		{
			$IM=array();
			$IM=GTfile::AddPostedFileGroup($FNAME, array('SUBSYSTEM'=>'DBL::'.$arVar['ID'],'TITLE'=>$FVALUE.$IMdate));
			//d($IM);
			if(!empty($IM))
			{
				foreach($IM as $key=>$val)
				{
					if($FNAME=='MULTYIMAGES' || $FNAME=='MULTYFILES')
					{
						if($FNAME=='MULTYIMAGES'.$Lval['ID'])
						{
							$F='';
							$F= GTDOCROOT.'/medialibrary/images/SMALL_'.$val['ID']._.$val['LOCAL_NAME'];
							mkThumbMS(GTDOCROOT.$val['URL'],$F,100);
							$F='';
							$F= GTDOCROOT.'/medialibrary/images/MEDIUM_'.$val['ID']._.$val['LOCAL_NAME'];
							mkThumbMS(GTDOCROOT.$val['URL'],$F,150);
						}
						$PM[]=array(
						'DBLOCK_ID'=>$arVar['ID'],
						'PROP_ID'=>$key,
						'VALUE'=>$val['ID'],
						'VALUE_NUM'=>$val['ID']);
					}
					if($FNAME=='FILES' || $FNAME=='IMAGES')
					{
						if($FNAME=='IMAGES')
						{
							$F='';
							$F= GTDOCROOT.'/medialibrary/images/SMALL_'.$val['ID']._.$val['LOCAL_NAME'];
							mkThumbMS(GTDOCROOT.$val['URL'],$F,100);
							$F='';
							$F= GTDOCROOT.'/medialibrary/images/MEDIUM_'.$val['ID']._.$val['LOCAL_NAME'];
							mkThumbMS(GTDOCROOT.$val['URL'],$F,150);
						}
						$PV[]=array(
						'DBLOCK_ID'=>$arVar['ID'],
						'PROP_ID'=>$key,
						'VALUE'=>$val['ID'],
						'VALUE_NUM'=>$val['ID']);
					}
				}
			}
		}
		
		unset($_POST);
		$arrVar1 = array(
		'ID'=>$arVar['ID'],
		'TITLE'=>$arVar['TITLE'],
		'DATESTART'=>$arVar['DATESTART'],
		'DATEEND'=>$arVar['DATEEND'],
		'SHORTTEXT'=>$arVar['SHORTTEXT'],
		'FULLTEXT'=>$arVar['FULLTEXT'],
		'edswitchSHORTTEXT'=>$arVar['edswitchSHORTTEXT'],
		'edswitchFULLTEXT'=>$arVar['edswitchFULLTEXT'],
		'ACTIVE'=>$arVar['ACTIVE'],
		'SORT'=>$arVar['SORT'],
		'SHORTIMG'=>$arVar['SHORTIMG'],
		'FULLIMG'=>$arVar['FULLIMG'],
		'TAGS'=>$arVar['TAGS'],
		'SUBTYPE'=>$arVar['SUBTYPE'][0],
		'DBLOCK_KEY'=>$arVar['DBLOCK_KEY'],
		'LINK'=>$arVar['LINK'],
		'INDEXED'=>$arVar['INDEXED'],
		'TYPE_ID'=>$arVar['TYPE_ID']);
		
		if(!empty($arVar['MDBLOCK']))
		{
			foreach($arVar['MDBLOCK'] as $Key=>$Val)
			{
				if(is_array($Val))
				{
					$CVal=array_flip($Val);
					if(!empty($arVar['MDCHECK']))
					{	
						$to_del=array();
						foreach($arVar['MDCHECK'][$Key] as $key=>$val)
						{
							if(!isset($CVal[$val]))
							{
							$to_del[$val]=array(
							'DBLOCK_ID'=>$arVar['ID'],
							'PROP_ID'=>$Key,
							'VALUE'=>$val);
							}
						}					
					}
					foreach($Val as $key=>$val)
					{
					$val_n=(int)$val;
					$PM[]=array(
					'DBLOCK_ID'=>$arVar['ID'],
					'PROP_ID'=>$Key,
					'VALUE'=>$val,
					'VALUE_NUM'=>$val_n);
					
					$PV[$Key.'MULTIPLE']=array(
					'DBLOCK_ID'=>$arVar['ID'],
					'PROP_ID'=>$Key,
					'VALUE'=>'MULTIPLE',
					'VALUE_NUM'=>'0');
					}
				}
				else
				{
					$Val_n=(int)$Val;
					$PM[]=array(
					'DBLOCK_ID'=>$arVar['ID'],
					'PROP_ID'=>$Key,
					'VALUE'=>$Val,
					'VALUE_NUM'=>$Val_n);
					
					$PV[$Key.'MULTIPLE']=array(
					'DBLOCK_ID'=>$arVar['ID'],
					'PROP_ID'=>$Key,
					'VALUE'=>'MULTIPLE',
					'VALUE_NUM'=>'0');
				}
			}
		}
		
		
		if(!empty($arVar['PROPS']))
		{
			foreach($arVar['PROPS'] as $Key=>$Val)
			{
				if(is_array($Val))
				{
					foreach($Val as $key=>$val)
					{
						if($arVar['edswitchPROPS'.$Key])
						{
							$val_n=(int)$val;
							$PV[]=array(
							'DBLOCK_ID'=>$arVar['ID'],
							'PROP_ID'=>$Key,
							'VALUE'=>$val,
							'SWITCH'=>$arVar['edswitchPROPS'.$Key],
							'VALUE_NUM'=>$val_n);
						}
						else
						{
							$val_n=(int)$val;
							$PV[]=array(
							'DBLOCK_ID'=>$arVar['ID'],
							'PROP_ID'=>$Key,
							'VALUE'=>$val,
							'VALUE_NUM'=>$val_n);
						}
					}
				}
				else
				{
					if($arVar['edswitchPROPS'.$Key])
					{
						$Val_n=(int)$Val;
						$PV[]=array(
						'DBLOCK_ID'=>$arVar['ID'],
						'PROP_ID'=>$Key,
						'VALUE'=>$Val,
						'SWITCH'=>$arVar['edswitchPROPS'.$Key],
						'VALUE_NUM'=>$Val_n);
					}
					else
					{
						$Val_n=(int)$Val;
						$PV[]=array(
						'DBLOCK_ID'=>$arVar['ID'],
						'PROP_ID'=>$Key,
						'VALUE'=>$Val,
						'VALUE_NUM'=>$Val_n);
					}
				}
			}		
		}
		
		$res=GTdblock::Update($arrVar1,$PV,$PM,$to_del);
		if ($res===TRUE)
		{
			$row = GTdblock::Get(Array('TYPE_ID','SUBTYPE'),array('ID'=>$arVar['ID']));
			if($row[0]['SUBTYPE']!='')
			{
				$TO='&act=get_subblock&type='.$row[0]['TYPE_ID'].'&subtype='.$row[0]['SUBTYPE'];
			}
			else
			{
				$TO="&act=dblocks&id=".$arVar['TYPE_ID'];
			}
			header("Location: ./?mod=dblocks".$TO);
			die();
		}		
		else 
		{
			GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
		}
		}
	}
	
	

function Get_subblock($SUBTYPE=FALSE,$TYPE=FALSE,$iPage=False,$PARAMS=FALSE)
	{
		global $DB, $APP;
		if(!strstr($SUBTYPE,'EL'))
		{
			$RS=GTdblocksubtype::Get_i($SUBTYPE);
		}
		if(!empty($RS))
		{
			foreach ($RS as $key=>$val)
			{
				$arrModResult['modContent'][] =
						Array(	"NAME" =>'Папка: '.$val['NAME'],
								"SORT" => $val['SORT'],
								"TYPE_ID" => $val['TYPE_NAME'],
								"ID" => $val['ID']);
			}
			
			$arrModResult['modContent'][] =
					Array(	"NAME" => GetMessage('ELEMENTS'),
							"SORT" => '',
							"TYPE_ID" => '',
							"ID" => 'EL'.$SUBTYPE);
				$arrModResult['modHeader'] = Array(
					"NAME" => GetMessage('NAME'),
					"SORT" => GetMessage('SORT'),
					"TYPE_ID" => GetMessage('TYPE'),
					"ID" => "ID");
				
			$APP->admModSetUrl('act=get_subblock&type='.$TYPE.'&subtype=','edit');
			$row=GTdblocktype::Get('',array('ID'=>$TYPE),'','',Array('ID'));
			$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
			$APP->ModCreateBreadcumbs($row[$TYPE]['GROUP_NAME'],'?mod=dblocks&act=types&id='.$row[$TYPE]['GROUP_ID']);
			$APP->ModCreateBreadcumbs($row[$TYPE]['NAME'].':'.GetMessage('FOLDERS'),'?mod=dblocks&act=dblocks&id='.$TYPE);
			$arv=GTdblocksubtype::Breds($RS[0]['PARENT']['PARENT']);
			$arv= array_reverse($arv); 
			foreach($arv as $key=>$val)
			{
				$APP->ModCreateBreadcumbs($val['NAME'],'?mod=dblocks&act=get_subblock&type='.$TYPE.'&subtype='.$val['SUBTYPE'].'');
			}
			$APP->ModCreateBreadcumbs('Папка '.$RS[0]['PARENT']['NAME']);
			$APP->admModSetUrl('moded=del_ST', 'action');
		}
		else
		{
			if($PARAMS)
			{
				if($PARAMS['TITLE']!=FALSE)$PARAMS['TITLE']='~%'.$PARAMS['TITLE'].'%';
				if($PARAMS['DESC']!=FALSE)$PARAMS['DESC']='~%'.$PARAMS['DESC'].'%';
				if($PARAMS['ACTIVE'][0]!='3'){$PARAMS['ACTIVE']=$PARAMS['ACTIVE'][0];} else {unset($PARAMS['ACTIVE']);}
				if($PARAMS['OUT_LANG'][0]!=''){$PARAMS2['LANG']=$PARAMS['OUT_LANG'][0]; $PARAMS2['FORCE_LANG']='1';} else {unset($PARAMS['OUT_LANG']);}
				$PARAMS['TYPE_ID']=$TYPE;
				$PARAMS['SUBTYPE']=$SUBTYPE;
				$PARAMS2['SUBTYPE']=$SUBTYPE;
				$PARAMS2['TYPE_ID']=$TYPE;
			}
			else
			{
				$PARAMS['TYPE_ID']=$TYPE;
				$PARAMS['FORCE_LANG']=$ID;
				$PARAMS['SUBTYPE']=$SUBTYPE;
			}
			if(!$SUBTYPE){$SUBTYPE='0';}
			if(strstr($SUBTYPE,'EL'))
			{
				$SUBTYPE=substr($SUBTYPE,2);
			}
			if($SUBTYPE!=0)
			{
				$RS=GTdblocksubtype::Get('',array('ID'=>$SUBTYPE));
			}
			$counts = GTdblock::Count('',array('TYPE_ID'=>$TYPE,'SUBTYPE'=>$SUBTYPE),Array('TITLE','ASC'));
			$iStart=0;
			$iLimit=100;
			if($iPage!=False && ($counts>$iLimit))
			{
				$iStart=($iPage*$iLimit)-$iLimit;
			}
			
			$row = GTdblock::Get('',$PARAMS,Array('TITLE','ASC'),Array($iStart,$iLimit));
			
			if($counts>$iLimit)
			{
				$Pages=round($counts/$iLimit);
				echo '<br />';
				echo GetMessage('PAGES');
				$Pend=round($Pages/50);
				if($iPage>=50)
				{
					echo '&nbsp<a href="?mod=dblocks&act=dblocks&id='.$iType.'&page=1" style="font-size:14px;"><начало</a>&nbsp';
				}
				$j=1;
				$p=array();
				for($i=1;$i<=$Pages;$i++)
				{
					if(empty($iPage)){$iPage=1;}
					$iPage=(int)$iPage;
					$mode=($i%50);
					if($mode==0)
					{
						$j++;
					}
					$p[$j][$i]=$i;
				}
				foreach($p as $iPk=>$iPv)
				{
					if(in_array($iPage,$iPv))
					{
						$inArray=$iPk;
					}
				}
				foreach($p[$inArray] as $iK=>$iV)
				{
					if($iPage==$iV)
					{
						echo '&nbsp<a href="?mod=dblocks&act=dblocks&id='.$iType.'&page='.$iV.'" style="font-size:16px;">'.$iV.'</a>&nbsp';
					}
					else
					{
						echo '&nbsp<a href="?mod=dblocks&act=dblocks&id='.$iType.'&page='.$iV.'" style="font-size:12px;">'.$iV.'</a>&nbsp';
					}
					$last=$iV+1;
				}
				if(isset($last) && $last<=$Pages)
				{
					echo '&nbsp<a href="?mod=dblocks&act=dblocks&id='.$iType.'&page='.$last.'" style="font-size:12px;">'.$last.'</a>&nbsp';
				}
				if($iPage<$Pages)
				{
					echo '&nbsp<a href="?mod=dblocks&act=dblocks&id='.$iType.'&page='.$Pages.'" style="font-size:14px;">последни></a>&nbsp';
				}
				
			}
			
			if($PARAMS2)
			{
				$lans=GTdblock::Get('*',$PARAMS2); //d($lans);
				$another=array();
			}
			foreach($row as $key=>$val)
			{
				if(!empty($lans))
				{
					foreach($lans as $lk=>$lv)
					{
						if($val['ID']==$lv['ID'])
						{
							$another[$val['ID']]=$val['ID'];
						}
					}
				}
				if(!isset($another[$val['ID']]))
				{
					switch ($row[$key]['ACTIVE'])
					{
						case '1':
						$row[$key]['ACTIVE']=GetMessage('YES');
						break;
						case '0':
						$row[$key]['ACTIVE']=GetMessage('NO');
					}
							
					$arrModResult['modContent'][] =Array(
										"TITLE" => $val['TITLE'],
										"SORT" => $val['SORT'],								
										"ACTIVE" => $val['ACTIVE'],
										"TRANSLATION" => $iTables['TRANSLATION'],
										"TYPE_ID" => $val['TYPE_NAME'],
										"ID" => $val['ID']
									);
				}
			}
						
			$arrModResult['modHeader'] = Array(
							"TITLE" => GetMessage('TITLE'),
							"SORT" => GetMessage('SORT'),								
							"ACTIVE" => GetMessage('ACTIVE'),
							"TRANSLATION" => GetMessage('TRANSLATION'),
							"TYPE_ID" => GetMessage('TYPE'),
							"ID" => GetMessage('ID'));
			$APP->admModSetUrl('act=dblock_edit&id=','edit');
			$APP->admModSetUrl('act=dblock_subfilter&type='.$TYPE.'&subtype='.$SUBTYPE, 'filter');
			$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
			$row=GTdblocktype::Get('',array('ID'=>'='.$TYPE),'','',Array('ID'));
			$APP->ModCreateBreadcumbs($row[$TYPE]['GROUP_NAME'],'?mod=dblocks&act=types&id='.$row[$TYPE]['GROUP_ID']);
			$APP->ModCreateBreadcumbs($row[$TYPE]['NAME'].':'.GetMessage('FOLDERS'),'?mod=dblocks&act=dblocks&id='.$TYPE);
			$arv=GTdblocksubtype::Breds($RS[0]['PARENT']);
			$arv= array_reverse($arv);
			foreach($arv as $key=>$val)
			{
				$APP->ModCreateBreadcumbs($val['NAME'],'?mod=dblocks&act=get_subblock&type='.$TYPE.'&subtype='.$val['SUBTYPE'].'');
			}
			$APP->ModCreateBreadcumbs('Папка '.$RS[0]['NAME'].' : '.GetMessage('ELEMENTS'));
			$APP->admModSetUrl('moded=del_dblock', 'action');
		}
						
		$APP->admModDetemineActions(Array('AdD','LIST','EDIT','DELETE'));
		
		$APP->admModSetUrl('act=new_dblock&type='.$TYPE.'&subtype='.$SUBTYPE, 'add');
		
		
		$APP->addControlUserButton(GetMessage('CREATE_FOLDER'),'act=new_subtype&parent='.$SUBTYPE.'&id='.$TYPE);
		
		
		
		$APP->admCacheGetModuleFields();		
		$APP->admModShowElements($arrModResult['modHeader'], $arrModResult['modContent'], "list", Array('TITLE','OUT_LANG'=>Array('3'=>'','ru'=>'рус.','kz'=>'каз.')));
	}
/*******************************************/	
	function New_subtype($TYPE,$SUBTYPE=0)
	{
		global $DB,$APP , $arrModResult;
		$MULTY=GTAPP::Conf('multilang_sync');
		if(!$SUBTYPE){
			$row=GTdblocktype::Get();
			$directory[]=array(''=>GetMessage('UPER_LEVEL'));
			foreach($row as $key=>$val)
			{
				$directory[]=array($row[$key]['ID']=>$row[$key]['NAME']);	
			}
			foreach($directory as $key=>$val)
			{
				foreach($val as $kkey=>$wal)
				{
					$directorys[$kkey]=$wal;
				}
			}
		}
		$arrModResult['modHeader'] = Array(
			GetMessage("MAIN")=> Array(
				Array("DESC" => GetMessage('TITLE'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" =>''),
				Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
				Array("DESC" => GetMessage('TYPE'), "NAME" => "TYPE_ID", "TYPE" => $SUBTYPE?'hidden':"select:".$TYPE, "VALUE" => $SUBTYPE?$TYPE:$directorys),
				Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'NEW_SUBTYPE'),
				Array("DESC" => "", "NAME" => "PARENT", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$SUBTYPE)
			),
			GetMessage("DESC")=> Array(
				Array("DESC" => '', "NAME" => "DESC", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '')
			)
		);
		
		if($MULTY!=FALSE)
		{
			$Dlang=GToptionlang::Get('',Array('DEFAULT'=>1,'ENABLED'=>1));
			$Dlang=$Dlang[0];
			$lang=GToptionlang::Get('',Array('ID'=>'!'.$Dlang['ID'],'ENABLED'=>1));
			//d($Dlang);
			//d($lang);
			$Props2=array();
			foreach($lang as $val)
			{
				$Props2[$val['ID']][]=Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
				$Props2[$val['ID']][]=Array("DESC" => GetMessage('DESC'), "NAME" => "DSC[".$val['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '');
				if(!empty($Props2))
				{
					$arrModResult['modHeader'][GetMessage($val['TITLE'])]=array_merge($arrModResult['modHeader'],$Props2[$val['ID']]);
				}
			}
		}
		$row=GTdblocktype::Get('',array('ID'=>'='.$TYPE),'','',Array('ID'));
		$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
		$APP->ModCreateBreadcumbs($row[$TYPE]['GROUP_NAME'],'?mod=dblocks&act=types&id='.$row[$TYPE]['GROUP_ID']);
		$APP->ModCreateBreadcumbs($row[$TYPE]['NAME'].':'.GetMessage('FOLDERS'),'?mod=dblocks&act=dblocks&id='.$TYPE);
		if($SUBTYPE){
			$row=GTdblocksubtype::Get('',array('ID'=>$SUBTYPE),'','',array('ID'));
			$APP->ModCreateBreadcumbs($row[$SUBTYPE]['NAME'].':'.GetMessage('FOLDERS'),'?mod=dblocks&act=dblocks&id='.$TYPE.'&subtype='.$SUBTYPE);
		}
		$APP->ModCreateBreadcumbs(GetMessage('CREATE_FOLDER'));
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	function Edit_subtype($ID)
	{
		global $DB;
		$MULTY=GTAPP::Conf('multilang_sync');
		$row=GTdblocksubtype::Get('',array('ID'=>$ID),'','',array('ID'));
		$row=$row[$ID];
		$TYPE=$row['TYPE_ID'];
		$row2=GTdblocktype::Get('',array('ID'=>$TYPE),'','',Array('ID'));
		$row2=$row2[$row['TYPE_ID']];
		if($row['UPDATED']!=$row['CREATED'])
		{$row['UPDATED'] = date('Y-m-j h:i:s', $row['UPDATED']);}else{$row['UPDATED']='';}
		$row['CREATED']=date('Y-m-j h:i:s', $row['CREATED']);
		foreach($row as $key=>$val)
			{
			$mass[]=Array("DESC" => "", "NAME" => "CHECK[".$key."]", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$val);
			}
		global $APP , $arrModResult;
		$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('TITLE'), "NAME" => "NAME", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row['NAME']),
		Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['SORT']),
		Array("DESC" => GetMessage('CREATED'), "NAME" => "CREATED", "VIEW" => 1, "TYPE" => "text", "VALUE" => $row['CREATED']),
		Array("DESC" => GetMessage('UPDATED'), "NAME" => "UPDATED", "VIEW" => 1, "TYPE" => "text", "VALUE" => $row['UPDATED']),
		Array("DESC" => GetMessage('TYPE'), "NAME" => "TYPE", "TYPE" => "text", "VALUE" => $row2['NAME']),
		Array("DESC" => GetMessage('TYPE'), "NAME" => "TYPE_ID", "TYPE" => "hidden", "VALUE" => $row['TYPE_ID']),
		Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'EDIT_SUBTYPE')),
		GetMessage("DESC")=> Array(
		Array("DESC" => '', "NAME" => "DESC", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $row['DESC'])));
		$arrModResult['modHeader'][GetMessage('MAIN')]=array_merge($arrModResult['modHeader'][GetMessage('MAIN')],$mass);
		
		if($MULTY!=FALSE)
		{
			$Dlang=GToptionlang::Get('',Array('DEFAULT'=>1,'ENABLED'=>1));
			$Dlang=$Dlang[0];
			$lang=GToptionlang::Get('',Array('ID'=>'!'.$Dlang['ID'],'ENABLED'=>1));
			
			//d($Dlang);
			//d($lang);
			$Props2=array();
			foreach($lang as $val)
			{
				$OTHER=GTdblocksubtype::GetLsubtype($ID,$val['ID']);
				$OTHER=$OTHER[0];
				//d($OTHER);
				$Props2[$val['ID']][]=Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => $OTHER['TITLE']);
				$Props2[$val['ID']][]=Array("DESC" => GetMessage('DESC'), "NAME" => "DSC[".$val['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $OTHER['DSC']);
				if(!empty($Props2))
				{
					$arrModResult['modHeader'][GetMessage($val['TITLE'])]=array_merge($arrModResult['modHeader'],$Props2[$val['ID']]);
				}
			}
		}
		$row1=GTdblocktype::Get('',array('ID'=>'='.$TYPE),'','',Array('ID'));
		$APP->ModCreateBreadcumbs(GetMessage('GROUPS'),'?mod=dblocks&act=groups');
		$APP->ModCreateBreadcumbs($row1[$TYPE]['GROUP_NAME'],'?mod=dblocks&act=types&id='.$row1[$TYPE]['GROUP_ID']);
		$APP->ModCreateBreadcumbs($row1[$TYPE]['NAME'].':'.GetMessage('FOLDERS'),'?mod=dblocks&act=dblocks&id='.$TYPE);
		$APP->ModCreateBreadcumbs($row['NAME']);
		$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
	}
	
	/******ALL OPERATIONS******ALL OPERATIONS*********ALL OPERATIONS************ALL OPERATIONS**********/
		
		
		
		function AddInST($arVar)
		{
			unset($_POST);
			//d($arVar); die();
			$arVar3['TITLE']=$arVar['TITLE'];
			$arVar3['DSC']=$arVar['DSC'];
			unset($arVar['TITLE']);
			foreach($arVar as $key=>$val)
			{
				if(is_array($val))
				{
					foreach($val as $k=>$v)
					{
					$arVar[$key]=$v;
					}
				}
			}
			$res = GTdblocksubtype::Add(array(
			'NAME'=>$arVar['NAME'],
			'DESC'=>$arVar['DESC'],
			'edswitchDESC'=>$arVar['edswitchDESC'],
			'TYPE_ID'=>$arVar['TYPE_ID'],
			'PARENT'=>$arVar['PARENT'],
			'SORT'=>$arVar['SORT']));
			if ($res!=FALSE)
			{
				if(!empty($arVar3['TITLE'])){
					$OTHER=array();
					foreach($arVar3['TITLE'] as $key=>$val)
					{
						$OTHER[$key]=array('TITLE'=>$val,'ID'=>$res,'LANG'=>$key,'DSC'=>$arVar3['DSC'][$key]);
					}
					if(!empty($OTHER))
					{
						GTdblocksubtype::OtherLangST($OTHER);
					}
				}
				//unset($arVar['TITLE']);
				header("Location: ./?mod=dblocks&act=dblocks&id=".$arVar['TYPE_ID']);
			}
			else 
			{
				GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
			}
		}
		
		function UpdateInST($arVar)
		{
			unset($_POST);//d($arVar); die();
			if(!empty($arVar['TITLE'])){
				$OTHER=array();
				foreach($arVar['TITLE'] as $key=>$val)
				{
					$OTHER[$key]=array('TITLE'=>$val,'ID'=>$arVar['CHECK']['ID'],'LANG'=>$key,'DSC'=>$arVar['DSC'][$key]);
				}
				if(!empty($OTHER))
				{
					GTdblocksubtype::OtherLangST($OTHER);
				}
			}
			unset($arVar['TITLE']);
			unset($arVar['DSC']);
			
			$update=Array(
			"ID"=>$arVar['CHECK']['ID'],
			"NAME"=>$arVar['NAME'],
			"DESC"=>$arVar['DESC'],
			"edswitchDESC"=>$arVar['edswitchDESC'],
			"SORT"=>$arVar['SORT']);
			$noup=array();
			foreach($update as $key=>$val)
			{
				if($arVar['CHECK'][$key]==$val){$noup[]=$key;}
			}
			foreach($noup as $key=>$val)
			{
				unset ($update[$val]);
			}
			
			if(!empty($update))
			{
				$IDD=array('ID'=>$arVar['CHECK']['ID']);
				$update=array_merge($update,$IDD);
				$res = GTdblocksubtype::Update($update);
			}
			
			if ($res===TRUE)
			{
				header("Location: ./?mod=dblocks&act=dblocks&id=".$arVar['TYPE_ID']);
				die();
			}
			else 
			{
				GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
			}
		}
	/*** operation to delete ****/
		function DeleteInGroup($arVar)
		{
			unset($_POST);
			foreach($arVar as $key=>$val)
			{
				if(is_numeric($val))
				{
					$res=GTdblockgroup::Delete($val);
				}
			}
			header("Location: ./?mod=dblocks&act=groups");
			die();
		}
		function DeleteInTypes($arVar)
		{
			unset($_POST);
			$row=GTdblocktype::Get('',array('ID'=>$arVar[0]));
			foreach($arVar as $key=>$val)
			{
				if(is_numeric($val))
				{
					$res=GTdblocktype::Delete($val);
				}
			}
			header("Location: ./?mod=dblocks&act=types&id=".$row[0]['GROUP_ID']);
			die();
		}
		function DeleteInDblock($arVar)
		{
			unset($_POST);
			$row = GTdblock::Get(Array('TYPE_ID','SUBTYPE'),array('ID'=>$arVar[0]));
			foreach($arVar as $key=>$val)
			{
				if(is_numeric($val))
				{
					$res=GTdblock::Delete($val);
				}
			}
			if(!empty($row[0]['SUBTYPE']))
			{
				$TO='&act=get_subblock&type='.$row[0]['TYPE_ID'].'&subtype='.$row[0]['SUBTYPE'];
			}
			else
			{
				$TO="&act=dblocks&id=".$row[0]['TYPE_ID'];
			}
			header("Location: ./?mod=dblocks$TO");
			die();			
		}
		function DeleteInProps($ID)
		{
			unset($_POST);
			$this->ID=$ID;
			$res=GTdblockpropvalue::Delete($this->ID);
			unset($_GET['deleteprops']);	
		}
		
		function DelST($arVar)
		{
			unset($_POST);
			d($arVar);
			$ID=GTdblocksubtype::Get('',array('ID'=>$arVar[0]));
			foreach($arVar as $key=>$val)
			{
				if(is_numeric($val))
				{
					$res=GTdblocksubtype::Delete($val);
				}
			}
			header("Location: ./?mod=dblocks&act=dblocks&id=".$ID[0]['TYPE_ID']);
			die();
		}
	
	
} 
?>