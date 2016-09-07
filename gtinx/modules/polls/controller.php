<?
class ControllerPolls
{
	function __construct()
	{
		if($_POST)
		{
			$POST = $_POST;
		}
		if($_GET)
		{
			$GET= $_GET;
		}
		if(isset($GET))
			{
				foreach($GET as $key=>$val)
				{
				switch($key)
					{
						case 'act':
							switch($val)
							{
							case 'polls':
								$this->ListPolls();
								break;
							case 'new_polls':
								$this->NewPolls();
								break;
							case 'edit_polls':
								$this->EditPolls($GET['id']);
								break;
							case 'polls_quest':
								$this->ListPollsSTMS();
								break;
							case 'new_pquest':
								$this->NewPQuest();
								break;
							case 'edit_pquest':
								$this->EditPQuest($GET['id']);
								break;
							case 'polls_answer':
								$this->ListPollsFill();
								break;
							case 'polls_answers':
								$this->ListPollsFills($GET['id']);
								break;
							case 'new_answer':
								$this->NewPFill($GET['id']);
								break;
							case 'edit_answer':
								$this->EditPFill($GET['id']);
								break;
							case 'del_polls':
								$this->del_polls($POST['checkID']);
								break;
							case 'del_pquest':
								$this->del_pquest($POST['checkID']);
								break;
							case 'del_pollsfill':
								$this->del_pollsfill($POST['checkID']);
								break;
							case 'del_panw':
								$this->del_panw($POST['checkID']);
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
							case 'NEW_POLLS':
								$this->AddPolls($POST);
								break;
							case 'EDIT_POLLS':
								$this->UpdatePolls($POST);
								break;
							case 'NEW_PQUEST':
								$this->AddPQuest($POST);
								break;
							case 'EDIT_PQUEST':
								$this->UpdatePQuest($POST);
								break;
							case 'ADD_ANS':
								$this->AddAns($POST);
								break;
							case 'EDIT_PFill':
								$this->EditAns($POST);
								break;							
							}
						break;
						
					}
				}
			}
	}
function del_polls($Arv)
{
	GTpolls::Delete($Arv);
	header("Location: ./?mod=polls&act=polls");
	die();
}
function del_pquest($Arv)
{
	GTpollsstms::Delete($Arv);
	header("Location: ./?mod=polls&act=polls_quest");
	die();
}
function del_pollsfill($Arv)
{
	GTpollfills::Delete($Arv);
	header("Location: ./?mod=polls&act=polls_answer");
	die();
}
function del_panw($Arv)
{
	GTpollfills::DeleteA($Arv);
	header("Location: ./?mod=polls&act=polls_answer");
	die();
}

function ListPolls()
{
	global $DB, $APP;
	$row=GTpolls::Get();
	foreach($row as $key=>$val)
	{
		if($row[$key]['ACTIVE']!=0)
		{$row[$key]['ACTIVE'] = GetMessage('YES');}else{$row[$key]['ACTIVE'] = GetMessage('NO');}
		 $arrModResult['modContent'][] =Array(
										"TITLE" => $row[$key]['TITLE'],
										"DESC" => $row[$key]['DESC'],
										"ACTIVE" => $row[$key]['ACTIVE'],
										"ID" => $row[$key]['ID']);
	}
		$arrModResult['modHeader'] = Array(
										"TITLE" =>GetMessage('TITLE'),
										"DESC" => GetMessage('DESC'),
										"ACTIVE" => GetMessage('ACTIVE'),
										"ID" => GetMessage('ID')
										);
		$APP->admModSetUrl('act=edit_polls&id=', 'edit');
		$APP->admModSetUrl('act=new_polls', 'add');
		$APP->admModSetUrl('act=del_polls', 'action');
		$APP->admModSetUrl('act=polls_filter', 'polls');
		$APP->ModCreateBreadcumbs(GetMessage('POLLS'),'?mod=polls&act=polls');
		$APP->ModCreateBreadcumbs(GetMessage('ALL_POLLS'));
			$APP->admModDetemineActions(Array('AdD', 'LIST', 'EDIT', 'DELETE'));
			$APP->admModShowElements($arrModResult['modHeader'],$arrModResult['modContent'],"list",
			Array('TILE','ID','ACTIVE'=>Array('3'=>' ',0=>GetMessage('NO'),1=>GetMessage('YES'))));
}
function NewPolls()
{
	global $APP , $arrModResult;
	$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE", "VIEW" => 1, "TYPE" => "string", "VALUE" =>''),
		Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'NEW_POLLS')),
		GetMessage("DESC")=> Array(Array("DESC" => '', "NAME" => "DESC", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '')),
		GetMessage("PROPERTIES")=> Array(
		Array("DESC" => GetMessage('TAGS'), "NAME" => "TAGS", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('ACTIVE_FROM'), "NAME" => "ACTIVE_FROM", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('ACTIVE_TO'), "NAME" => "ACTIVE_TO", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
		Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO'))),)
	);

	$APP->ModCreateBreadcumbs(GetMessage('POLLS'),'?mod=polls&act=polls');
	$APP->ModCreateBreadcumbs(GetMessage('NEW_POLLS'));
	$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
}
function EditPolls($ID)
{
	global $DB, $APP;
	$R=GTpolls::Get('',Array('ID'=>$ID));
	$R=$R[0];
	$R['CREATED'] = date('Y-m-j h:i:s', $R['CREATED']);
	$R['ACTIVE_FROM'] = date('Y-m-j h:i:s', $R['ACTIVE_FROM']);
	$R['ACTIVE_TO'] = date('Y-m-j h:i:s', $R['ACTIVE_TO']);
	$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('ID'), "NAME" => "ID", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$R['ID']),
		Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$R['TITLE']),
		Array("DESC" => '', "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$R['ID']),
		Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'EDIT_POLLS')),
		GetMessage("DESC")=> Array(Array("DESC" => '', "NAME" => "DESC", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $R['DESC'])),
		GetMessage("PROPERTIES")=> Array(
		Array("DESC" => GetMessage('TAGS'), "NAME" => "TAGS", "VIEW" => 1, "TYPE" => "string", "VALUE" => $R['TAGS']),
		Array("DESC" => GetMessage('ACTIVE_FROM'), "NAME" => "ACTIVE_FROM", "VIEW" => 1, "TYPE" => "string", "VALUE" => $R['ACTIVE_FROM']),
		Array("DESC" => GetMessage('ACTIVE_TO'), "NAME" => "ACTIVE_TO", "VIEW" => 1, "TYPE" => "string", "VALUE" => $R['ACTIVE_TO']),
		Array("DESC" => GetMessage('CREATED'), "NAME" => "CREATED", "VIEW" => 1, "TYPE" => "text", "VALUE" => $R['CREATED']),
		Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO'))),)
	);
	$APP->ModCreateBreadcumbs(GetMessage('POLLS'),'?mod=polls&act=polls');
	$APP->ModCreateBreadcumbs($R['TITLE']);
	$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
}
function AddPolls($arVar)
{
	GTpolls::Add($arVar);
	header("Location: ./?mod=polls&act=polls");
	die();
}
function UpdatePolls($arVar)
{
	GTpolls::Update($arVar);
	header("Location: ./?mod=polls&act=polls");
	die();
}

/**/
function ListPollsSTMS()
{
	global $DB, $APP;
	$row=GTpollsstms::Get('',array('FORCE_LANG'=>1),'SORT');//d($row);
	$MULTY=GTAPP::Conf('multilang_sync');
	foreach($row as $key=>$val2)
	{
		if($MULTY){ $D_lang=GToptionlang::Get('',Array('ENABLED'=>1,'DEFAULT'=>1)); $D_lang=$D_lang[0];	$val=$val2[$D_lang['ID']];}
		if($val['ACTIVE']!=0)
		{$val['ACTIVE'] = GetMessage('YES');}else{$val['ACTIVE'] = GetMessage('NO');}
		 $arrModResult['modContent'][] =Array(
										"TITLE" => $val['TITLE'],
										"TYPE" => $val['TYPE'],
										"SORT" => $val['SORT'],
										"ACTIVE" => $val['ACTIVE'],
										"ID" => $val['ID']);
	}
		$arrModResult['modHeader'] = Array(
										"TITLE" =>GetMessage('TITLE'),
										"SORT" => GetMessage('SORT'),
										"TYPE" => GetMessage('TYPE'),
										"ACTIVE" => GetMessage('ACTIVE'),
										"ID" => GetMessage('ID')
										);
		$APP->admModSetUrl('act=edit_pquest&id=', 'edit');
		$APP->admModSetUrl('act=new_pquest', 'add');
		$APP->admModSetUrl('act=del_pquest', 'action');
		$APP->admModSetUrl('act=pquest_filter', 'pquest');
		$APP->ModCreateBreadcumbs(GetMessage('POLLS_QUEST'),'?mod=polls&act=polls_quest');
		$APP->ModCreateBreadcumbs(GetMessage('ALL_POLLS'));
			$APP->admModDetemineActions(Array('AdD', 'LIST', 'EDIT', 'DELETE'));
			$APP->admModShowElements($arrModResult['modHeader'],$arrModResult['modContent'],"list",
			Array('TILE','ID','ACTIVE'=>Array('3'=>' ',0=>GetMessage('NO'),1=>GetMessage('YES'))));
}
function NewPQuest()
{
	global $APP , $arrModResult;
	$R=GTpolls::Get();
	$polls=array(GetMessage('NOT_CHOOSEN')=>'0');
	foreach($R as $RKey=>$RVal)
	{
		$polls=array_merge($polls,array($RVal['TITLE']=>$RVal['ID']));
	}
	$polls=array_flip($polls);
	$type=array('DBLOCK'=>'DBLOCK','LIST'=>'LIST','STRING'=>'STRING','TEXT'=>'TEXT','DATE'=>'DATE','URL'=>'URL','RADIO'=>'RADIO');
	$MULTY=GTAPP::Conf('multilang_sync');
	if($MULTY==TRUE)
	{
		$D_lang=GToptionlang::Get('',Array('ENABLED'=>1,'DEFAULT'=>1));
		$D_lang=$D_lang[0];
		$O_lang=GToptionlang::Get('',Array('ENABLED'=>1,'ID'=>'!'.$D_lang['ID']));
		$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
			Array("DESC" => GetMessage('POLLS'), "NAME" => "POLL_ID", "VIEW" => 1, "TYPE" => "select", "VALUE" =>$polls),
			Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE[".$D_lang['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" =>''),
			Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'NEW_PQUEST'),
			Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
			Array("DESC" => GetMessage('TYPE'), "NAME" => "TYPE", "VIEW" => 1, "TYPE" => "select", "VALUE" => $type),
			Array("DESC" => GetMessage('REQUIRED'), "NAME" => "REQUIRED", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO'))),
			Array("DESC" => GetMessage('MULTIPLE'), "NAME" => "MULTIPLE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO'))),
			Array("DESC" => GetMessage('OPTIONS'), "NAME" => "OPTIONS", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => ''),
			Array("DESC" => GetMessage('VALUES'), "NAME" => "VALUES[".$D_lang['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => ''),
			Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO')))
		));
		foreach($O_lang as $val)
		{
			$FLD[$val['TITLE']][]=Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" =>'');
			$FLD[$val['TITLE']][]=Array("DESC" => GetMessage('VALUES'), "NAME" => "VALUES[".$val['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '');
			
		}
		if($FLD){$arrModResult['modHeader']=array_merge($arrModResult['modHeader'],$FLD);}
	}
	else
	{
		$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
			Array("DESC" => GetMessage('POLLS'), "NAME" => "POLL_ID", "VIEW" => 1, "TYPE" => "select", "VALUE" =>$polls),
			Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE", "VIEW" => 1, "TYPE" => "string", "VALUE" =>''),
			Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'NEW_PQUEST'),
			Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => ''),
			Array("DESC" => GetMessage('TYPE'), "NAME" => "TYPE", "VIEW" => 1, "TYPE" => "select", "VALUE" => $type),
			Array("DESC" => GetMessage('REQUIRED'), "NAME" => "REQUIRED", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO'))),
			Array("DESC" => GetMessage('MULTIPLE'), "NAME" => "MULTIPLE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO'))),
			Array("DESC" => GetMessage('OPTIONS'), "NAME" => "OPTIONS", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => ''),
			Array("DESC" => GetMessage('VALUES'), "NAME" => "VALUES", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => ''),
			Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio", "VALUE" => Array("1" =>GetMessage('YES'), 2=>GetMessage('NO')))
		));
	}

	$APP->ModCreateBreadcumbs(GetMessage('POLLS_QUEST'),'?mod=polls&act=polls_quest');
	$APP->ModCreateBreadcumbs(GetMessage('NEW_POLLS'));
	$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
}
function EditPQuest($ID)
{
	global $APP , $arrModResult;
	$MULTY=GTAPP::Conf('multilang_sync');
	if($MULTY==TRUE)
	{
	$D_lang=GToptionlang::Get('',Array('ENABLED'=>1,'DEFAULT'=>1));
	$D_lang=$D_lang[0];
	}
	$row=GTpollsstms::Get('',Array('ID'=>$ID),'SORT');
	
	if($MULTY==TRUE)
	{
	$row2=$row;
	$row=$row[$ID][$D_lang['ID']];
	}
	else
	{
		$row=$row[0];
	}
	$R=GTpolls::Get();
	$polls=array(GetMessage('NOT_CHOOSEN')=>'0');
	foreach($R as $RKey=>$RVal)
	{
		$polls=array_merge($polls,array($RVal['TITLE']=>$RVal['ID']));
	}
	$polls=array_flip($polls);
	$type=array('DBLOCK'=>'DBLOCK','LIST'=>'LIST','STRING'=>'STRING','TEXT'=>'TEXT','DATE'=>'DATE','URL'=>'URL','RADIO'=>'RADIO');
	if($MULTY!=TRUE)
	{
		$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('ID'), "NAME" => "ID", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$row['ID']),
		Array("DESC" => '', "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$row['ID']),
		Array("DESC" => GetMessage('POLLS'), "NAME" => "POLL_ID", "VIEW" => 1, "TYPE" => "select:".$row['POLL_ID'], "VALUE" =>$polls),
		Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row['TITLE']),
		Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'EDIT_PQUEST'),
		Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['SORT']),
		Array("DESC" => GetMessage('TYPE'), "NAME" => "TYPE", "VIEW" => 1, "TYPE" => "select:".$row['TYPE'], "VALUE" => $type),
		Array("DESC" => GetMessage('REQUIRED'), "NAME" => "REQUIRED", "VIEW" => 1, "TYPE" => "radio:".$row['REQUIRED'], "VALUE" => Array("1" =>GetMessage('YES'), '0'=>GetMessage('NO'))),
		Array("DESC" => GetMessage('MULTIPLE'), "NAME" => "MULTIPLE", "VIEW" => 1, "TYPE" => "radio:".$row['MULTIPLE'], "VALUE" => Array("1" =>GetMessage('YES'), '0'=>GetMessage('NO'))),
		Array("DESC" => GetMessage('OPTIONS'), "NAME" => "OPTIONS", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $row['OPTIONS']),
		Array("DESC" => GetMessage('VALUES'), "NAME" => "VALUES", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $row['VALUES']),
		Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio:".$row['ACTIVE'], "VALUE" => Array("1" =>GetMessage('YES'), '0'=>GetMessage('NO')))
	));
	}
	else
	{
		$arrModResult['modHeader'] = Array(GetMessage("MAIN")=> Array(
		Array("DESC" => GetMessage('ID'), "NAME" => "ID", "VIEW" => 1, "TYPE" => "text", "VALUE" =>$row['ID']),
		Array("DESC" => '', "NAME" => "ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$row['ID']),
		Array("DESC" => GetMessage('POLLS'), "NAME" => "POLL_ID", "VIEW" => 1, "TYPE" => "select:".$row['POLL_ID'], "VALUE" =>$polls),
		Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE[".$D_lang['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$row['TITLE']),
		Array("DESC" => "", "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'EDIT_PQUEST'),
		Array("DESC" => GetMessage('SORT'), "NAME" => "SORT", "VIEW" => 1, "TYPE" => "string", "VALUE" => $row['SORT']),
		Array("DESC" => GetMessage('TYPE'), "NAME" => "TYPE", "VIEW" => 1, "TYPE" => "select:".$row['TYPE'], "VALUE" => $type),
		Array("DESC" => GetMessage('REQUIRED'), "NAME" => "REQUIRED", "VIEW" => 1, "TYPE" => "radio:".$row['REQUIRED'], "VALUE" => Array("1" =>GetMessage('YES'), '0'=>GetMessage('NO'))),
		Array("DESC" => GetMessage('MULTIPLE'), "NAME" => "MULTIPLE", "VIEW" => 1, "TYPE" => "radio:".$row['MULTIPLE'], "VALUE" => Array("1" =>GetMessage('YES'), '0'=>GetMessage('NO'))),
		Array("DESC" => GetMessage('OPTIONS'), "NAME" => "OPTIONS", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $row['OPTIONS']),
		Array("DESC" => GetMessage('VALUES'), "NAME" => "VALUES[".$D_lang['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $row['VALUES']),
		Array("DESC" => GetMessage('ACTIVE'), "NAME" => "ACTIVE", "VIEW" => 1, "TYPE" => "radio:".$row['ACTIVE'], "VALUE" => Array("1" =>GetMessage('YES'), '0'=>GetMessage('NO')))
	));
	}
	if($MULTY==TRUE)
	{
		$O_lang=GToptionlang::Get('',Array('ENABLED'=>1,'ID'=>'!'.$D_lang['ID']));
		foreach($O_lang as $val)
		{
			$rowL=$row2[$ID][$val['ID']]; 
			if(!empty($rowL))
			{
				$Props2[$val['ID']]['TITLE']=Array("DESC" => GetMessage('TITLE'), "NAME" => "TITLE[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" =>$rowL['TITLE']);
				$Props2[$val['ID']]['VALUES']=Array("DESC" => GetMessage('TITLE'), "NAME" => "VALUES[".$val['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" =>$rowL['VALUES']);
				$arrModResult['modHeader'][GetMessage($val['TITLE'])]=$Props2[$val['ID']];
			}
			
		}
	}
	//d($Props2);
	//d($arrModResult);
	$APP->ModCreateBreadcumbs(GetMessage('POLLS_QUEST'),'?mod=polls&act=polls_quest');
	$APP->ModCreateBreadcumbs($row['TITLE']);
	$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
}
function AddPQuest($arVar)
{
	$MULTY=GTAPP::Conf('multilang_sync');
	if($MULTY==TRUE)
	{
		unset($arVar['ADD']);
		$arVar['TYPE']=$arVar['TYPE'][0];
		$arVar['POLL_ID']=$arVar['POLL_ID'][0];
		$D_lang=GToptionlang::Get('',Array('ENABLED'=>1));
		foreach($D_lang as $val)
		{
			if($val['DEFAULT']=='1')
			{
				$arcM=$arVar;
				$arcM['TITLE']=$arcM['TITLE'][$val['ID']];
				$arcM['VALUES']=$arcM['VALUES'][$val['ID']];
				$ARM=$arcM;
			}
			
				//$arc['POLL_ID']=$arVar['POLL_ID'];
				$arc['TITLE']=$arVar['TITLE'][$val['ID']];
				$arc['VALUES']=$arVar['VALUES'][$val['ID']];
				$arc['LANG']=$val['ID'];
				$AR[]=$arc;
			
		}
		//d($ARM);
		GTpollsstms::Add($ARM,$AR);
		
		//d($AR);
	}
	else
	{
		$arVar['TYPE']=$arVar['TYPE'][0];
		$arVar['POLL_ID']=$arVar['POLL_ID'][0];
		GTpollsstms::Add($arVar);
		
	}header("Location: ./?mod=polls&act=polls_quest");
		die();
}
function UpdatePQuest($arVar)
{
	$MULTY=GTAPP::Conf('multilang_sync');
	if($MULTY==TRUE)
	{
		$arVar['TYPE']=$arVar['TYPE'][0];
		$arVar['POLL_ID']=$arVar['POLL_ID'][0];
		$D_lang=GToptionlang::Get('',Array('ENABLED'=>1));
		foreach($D_lang as $val)
		{
			if($val['DEFAULT']=='1')
			{
				$arcM=$arVar;
				$arcM['TITLE']=$arcM['TITLE'][$val['ID']];
				$arcM['VALUES']=$arcM['VALUES'][$val['ID']];
				$ARM=$arcM;
			}
			
				$arc['STMS_ID']=$arVar['ID'];
				$arc['POLL_ID']=$arVar['POLL_ID'];
				$arc['TITLE']=$arVar['TITLE'][$val['ID']];
				$arc['VALUES']=$arVar['VALUES'][$val['ID']];
				$arc['LANG']=$val['ID'];
				$AR[]=$arc;
			
		}
		GTpollsstms::Update($ARM);
		foreach($AR as $val)
		{
			GTpollsstms::UpdateMULTY($val);
		}
		
	}
	else
	{
		$arVar['TYPE']=$arVar['TYPE'][0];
		$arVar['POLL_ID']=$arVar['POLL_ID'][0];
		GTpollsstms::Update($arVar);
	}
	header("Location: ./?mod=polls&act=polls_quest");
		die();
}
/***/
function ListPollsFill()
{
	global $DB, $APP;
	$row=GTpolls::Get();
	foreach($row as $key=>$val)
	{
		if($row[$key]['ACTIVE']!=0)
		{$row[$key]['ACTIVE'] = GetMessage('YES');}else{$row[$key]['ACTIVE'] = GetMessage('NO');}
		 $arrModResult['modContent'][] =Array(
										"TITLE" => $row[$key]['TITLE'],
										"DESC" => $row[$key]['DESC'],
										"ACTIVE" => $row[$key]['ACTIVE'],
										"ID" => $row[$key]['ID']);
	}
		$arrModResult['modHeader'] = Array(
										"TITLE" =>GetMessage('TITLE'),
										"DESC" => GetMessage('DESC'),
										"ACTIVE" => GetMessage('ACTIVE'),
										"ID" => GetMessage('ID')
										);
		$APP->admModSetUrl('act=polls_answers&id=', 'edit');
		$APP->admModSetUrl('act=new_polls', 'add');
		$APP->admModSetUrl('act=del_pollsfill', 'action');
		$APP->admModSetUrl('act=polls_filter', 'polls');
		$APP->ModCreateBreadcumbs(GetMessage('POLLS_ANSWER'),'?mod=polls&act=polls_answers');
		$APP->ModCreateBreadcumbs(GetMessage('ALL_POLLS'));
			$APP->admModDetemineActions(Array('AdD', 'LIST', 'EDIT', 'DELETE'));
			$APP->admModShowElements($arrModResult['modHeader'],$arrModResult['modContent'],"list",
			Array('TILE','ID','ACTIVE'=>Array('3'=>' ',0=>GetMessage('NO'),1=>GetMessage('YES'))));
}
function ListPollsFills($ID)
{
	global $DB, $APP;
	$row=GTpollfills::Get('',Array('POLL_ID'=>$ID));//d($row);
	foreach($row as $key=>$val)
	{
		if($row[$key]['ACTIVE']!=0)
		{$row[$key]['ACTIVE'] = GetMessage('YES');}else{$row[$key]['ACTIVE'] = GetMessage('NO');}
		 $arrModResult['modContent'][] =Array(
										"POLL_ID" => $row[$key]['POLL_ID'],
										"USER_ID" => $row[$key]['USER_ID'],
										"IP" => $row[$key]['IP'],
										"CREATED" => $row[$key]['CREATED'],
										"ID" => $row[$key]['ID']);
	}
		$arrModResult['modHeader'] = Array(
										"POLL_ID" =>GetMessage('POLL_ID'),
										"USER_ID" => GetMessage('USER_ID'),
										"IP" => GetMessage('IP'),
										"CREATED" => GetMessage('CREATED'),
										"ID" => GetMessage('ID')
										);
		$APP->admModSetUrl('act=edit_answer&id=', 'edit');
		$APP->admModSetUrl('act=new_answer&id='.$ID, 'add');
		$APP->admModSetUrl('act=del_panw', 'action');
		$APP->admModSetUrl('act=panw_filter', 'panw');
		$APP->ModCreateBreadcumbs(GetMessage('POLLS_ANSWER'),'?mod=polls&act=polls_answer');
		$APP->ModCreateBreadcumbs(GetMessage('ALL_POLLS'));
			$APP->admModDetemineActions(Array('AdD', 'LIST', 'EDIT', 'DELETE'));
			$APP->admModShowElements($arrModResult['modHeader'],$arrModResult['modContent'],"list",
			Array('TILE','ID','ACTIVE'=>Array('3'=>' ',0=>GetMessage('NO'),1=>GetMessage('YES'))));
}
function NewPFill($ID)
{
	global $APP , $arrModResult;
	$MULTY=GTAPP::Conf('multilang_sync');
	if($MULTY==TRUE)
	{
	$D_lang=GToptionlang::Get('',Array('ENABLED'=>1,'DEFAULT'=>1));
	$D_lang=$D_lang[0];
	$O_lang=GToptionlang::Get('',Array('ENABLED'=>1));
	$OL=array();
	foreach($O_lang as $key=>$val)
	{
		$OL[$val['ID']]=$val;
	}
	$O_lang=$OL;
	//d($O_lang);
	}
	$R=GTpollsstms::Get('',Array('POLL_ID'=>$ID,'ACTIVE'=>1),'SORT');
	if($MULTY==TRUE)
	{
		$row2=$R;
		$FLD[GetMessage($D_lang['TITLE'])][]=Array("DESC" => '', "NAME" => "POLL_ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$ID);
		$FLD[GetMessage($D_lang['TITLE'])][]=Array("DESC" => '', "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'ADD_ANS');
		foreach($R as $key=>$val)
		{
			$r[]=$val;
		}
	$R=$r;	
	//$s[]=$R[0][$D_lang['ID']];
	//$R=$s;
	}
	else
	{
		$R=$R[0];
		$FLD[]=Array("DESC" => '', "NAME" => "POLL_ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$ID);
		$FLD[]=Array("DESC" => '', "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'ADD_ANS');
	}
	//d($R);
	if($MULTY==TRUE)
	{
		foreach($R as $key=>$vals)
		{
			foreach($vals as $k=>$val)
			{
				switch($val['TYPE'])
				{
				case 'LIST':
					$txt=(string)$val['VALUES'];
					$txt = explode("\n", $txt);
					$S=array(GetMessage('NOT_CHOOSEN')=>GetMessage('NOT_CHOOSEN'));
					foreach($txt as $Tkey=>$Tval)
					{
					$S=array_merge($S,array($Tval=>$Tval));
					}
					$S=array_flip($S);
					if($val['MULTIPLE']!=0)
					{
						$FLD[GetMessage($OL[$k]['TITLE'])][]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "TYPE" => "select:multiple", "VALUE" => $S);
					}
					else
					{
						$FLD[GetMessage($OL[$k]['TITLE'])][]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "TYPE" => "select", "VALUE" => $S);
					}
					break;
				case 'DBLOCK':
					$S=GTusers::LINK($val['OPTIONS']);
					$FLD[GetMessage($OL[$k]['TITLE'])][]=Array("DESC" => $val['TITLE'], "NAME" => 'FIELD['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select", "VALUE" =>$S);
					break;
				case 'STRING':
					$FLD[GetMessage($OL[$k]['TITLE'])][]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
					break;
				case 'TEXT':
					$FLD[GetMessage($OL[$k]['TITLE'])][]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '');
					break;
				case 'URL':
					$FLD[GetMessage($OL[$k]['TITLE'])][]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
					break;
				case 'RADIO':
					$txt=(string)$val['VALUES'];
					$txt = explode("\n", $txt);
					$s=array();
					$rad=array();
					foreach($txt as $Tkey=>$Tval)
					{
						$s[]="<label><input type=\"checkbox\" name=\"FIELD[".$val['ID']."][]\" value=\"".$Tval."\"> ".$Tval."<label><br />";
						$rad[GetMessage($OL[$k]['TITLE'])][]="<label><input type=\"radio\" name=\"FIELD[".$val['ID']."][]\" value=\"".$Tval."\"> ".$Tval."<label><br />";
					}
					$s1=implode('',$s);
					$r1=implode('',$rad[GetMessage($OL[$k]['TITLE'])]);
					$S=array_flip($S);
					if($val['MULTIPLE']!=0)
					{
						$FLD[GetMessage($OL[$k]['TITLE'])][]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD", "VIEW" => 1, "TYPE" => "raw", "VALUE" =>$s1);
					}
					else
					{
						$FLD[GetMessage($OL[$k]['TITLE'])][]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD", "VIEW" => 1, "TYPE" => "raw", "VALUE" => $r1);
					}
					break;
				}
			}			
		}

	}
	else
	{
		foreach($R as $key=>$val)
		{
			switch($val['TYPE'])
			{
			case 'LIST':
				$txt=(string)$val['VALUES'];
				$txt = explode("\n", $txt);
				$S=array(GetMessage('NOT_CHOOSEN')=>GetMessage('NOT_CHOOSEN'));
				foreach($txt as $Tkey=>$Tval)
				{
				$S=array_merge($S,array($Tval=>$Tval));
				}
				$S=array_flip($S);
				if($val['MULTIPLE']!=0)
				{
					$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "TYPE" => "select:multiple", "VALUE" => $S);
				}
				else
				{
					$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "TYPE" => "select", "VALUE" => $S);
				}
				break;
			case 'DBLOCK':
				$S=GTusers::LINK($val['OPTIONS']);
				$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => 'FIELD['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select", "VALUE" =>$S);
				break;
			case 'STRING':
				$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
				break;
			case 'TEXT':
				$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => '');
				break;
			case 'URL':
				$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => '');
				break;
			case 'RADIO':
				$txt=(string)$val['VALUES'];
				$txt = explode("\n", $txt);
				$s=array();
				foreach($txt as $Tkey=>$Tval)
				{
					$s[]="<label><input type=\"checkbox\" name=\"FIELD[".$val['ID']."][]\" value=\"".$Tval."\"> ".$Tval."<label><br />";
					$rad[]="<label><input type=\"radio\" name=\"FIELD[".$val['ID']."][]\" value=\"".$Tval."\"> ".$Tval."<label><br />";
				}
				$s1=implode('',$s);
				$r1=implode('',$rad);
				$S=array_flip($S);
				if($val['MULTIPLE']!=0)
				{
					$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD", "VIEW" => 1, "TYPE" => "raw", "VALUE" =>$s1);
				}
				else
				{
					$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD", "VIEW" => 1, "TYPE" => "raw", "VALUE" => $r1);
				}
				break;
			}	
		}
	}
	$arrModResult['modHeader']=array();
	if($FLD){$arrModResult['modHeader']=array_merge($arrModResult['modHeader'],$FLD);}
	$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
}

function EditPFill($ID)
{
	global $APP , $arrModResult;
	//$G=GTpolls::Get('',Array('ID'=>$ID));
	//$G=$G[0];
	$Rw=GTpollfills::Get('',Array('ID'=>$ID)); 
	$Rw=$Rw[$ID];
	$R=GTpollsstms::Get('',Array('POLL_ID'=>$Rw['POLL_ID']));
	$FLD[]=Array("DESC" => '', "NAME" => "FILL_ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$ID);
	$FLD[]=Array("DESC" => '', "NAME" => "POLL_ID", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>$Rw['POLL_ID']);
	$FLD[]=Array("DESC" => '', "NAME" => "ADD", "VIEW" => 1, "TYPE" => "hidden", "VALUE" =>'EDIT_PFill');
	foreach($R as $key=>$val)
	{
		switch($val['TYPE'])
		{
		case 'LIST':
			$txt=(string)$val['VALUES'];
			$txt = explode("\n", $txt);
			$S=array(GetMessage('NOT_CHOOSEN')=>GetMessage('NOT_CHOOSEN'));
			foreach($txt as $Tkey=>$Tval)
			{
			$Tval=trim($Tval);
			$S=array_merge($S,array($Tval=>$Tval));
			}
			$S=array_flip($S);
			if($val['MULTIPLE']!=0)
			{
				$txt2 = explode("\n", $Rw['SMTS_FILLS'][$val['ID']]['VALUE']);
				$selected='';
				$ch=array();
				foreach($txt2 as $RK=>$RV)
				{
					$RV=trim($RV);
					$ch[]=$RV;
				}
				if(!empty($ch)){$selected=implode(',',$ch);}
				$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "TYPE" => "select:multiple:".$selected, "VALUE" => $S);
			}
			else
			{
				$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "TYPE" => "select:".$Rw['SMTS_FILLS'][$val['ID']]['VALUE'], "VALUE" => $S);
			}
			break;
		case 'DBLOCK':
			$S=GTusers::LINK($val['OPTIONS']);
			$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => 'FIELD['.$val['ID'].']', "VIEW" => 1, "TYPE" => "select:".$Rw['SMTS_FILLS'][$val['ID']]['VALUE'], "VALUE" =>$S);
			break;
		case 'STRING':
			$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => $Rw['SMTS_FILLS'][$val['ID']]['VALUE']);
			break;
		case 'TEXT':
			$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "VIEW" => 1, "TYPE" => "textarea", "VALUE" => $Rw['SMTS_FILLS'][$val['ID']]['VALUE']);
			break;
		case 'URL':
			$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD[".$val['ID']."]", "VIEW" => 1, "TYPE" => "string", "VALUE" => $Rw['SMTS_FILLS'][$val['ID']]['VALUE']);
			break;
		case 'RADIO':
			$txt=(string)$val['VALUES'];
			$txt = explode("\n", $txt);
			$S=array();
			$s=array();
			//$S=array_flip($S);
			if($val['MULTIPLE']!=0)
			{
				foreach($txt as $Tkey=>$Tval)
				{
					$ch=array();
					$txt2 = explode("\n", $Rw['SMTS_FILLS'][$val['ID']]['VALUE']);
					foreach($txt2 as $RK=>$RV)
					{
						$RV=trim($RV);
						$Tval=trim($Tval);
						if($RV==$Tval){$ch[]=$Tval;}
					}
					if(!empty($ch)){$s[]="<label><input type=\"checkbox\" name=\"FIELD[".$val['ID']."][]\" value=\"".$Tval."\" checked> ".$Tval."<label><br />";}
						else
						{$s[]="<label><input type=\"checkbox\" name=\"FIELD[".$val['ID']."][]\" value=\"".$Tval."\"> ".$Tval."<label><br />";}
				}
				$s1=implode('',$s);
				$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD", "VIEW" => 1, "TYPE" => "raw", "VALUE" =>$s1);
			}
			else
			{	
				foreach($txt as $Tkey=>$Tval)
				{
					$ch=array();
					$txt2 = explode("\n", $Rw['SMTS_FILLS'][$val['ID']]['VALUE']);
					foreach($txt2 as $RK=>$RV)
					{
						$RV=trim($RV);
						$Tval=trim($Tval);
						if($RV==$Tval){$ch[]=$Tval;}
					}
					if(!empty($ch)){$s[]="<label><input type=\"radio\" name=\"FIELD[".$val['ID']."][]\" value=\"".$Tval."\" checked> ".$Tval."<label><br />";}
						else
						{$s[]="<label><input type=\"radio\" name=\"FIELD[".$val['ID']."][]\" value=\"".$Tval."\"> ".$Tval."<label><br />";}
				}
				$s1=implode('',$s);
				$FLD[]=Array("DESC" => $val['TITLE'], "NAME" => "FIELD", "VIEW" => 1, "TYPE" => "raw", "VALUE" =>$s1);
			}
			break;
		}	
	}
	$arrModResult['modHeader']=array();
	if($FLD){$arrModResult['modHeader'][GetMessage('MAIN')]=array_merge($arrModResult['modHeader'],$FLD);}
	$APP->admModShowElements($arrModResult['modHeader'], '', "edit",'','tabs');
}
function AddAns($arVar)
{
	foreach($arVar as $key=>$val)
	{
		if(is_array($val))
		{
			
			foreach($val as $KEY=>$VAL)
			{
				if(is_array($VAL))
				{
					$FV=array();
					foreach($VAL as $TKEY=>$TVAL)
					{
						$FV[]=$TVAL;
					}
					$arV['STMT_ID'][$KEY]=implode('\n',$FV);
				}
				else
				{
				$arV['STMT_ID'][$KEY]=$VAL;
				}
			}
		
		}
	}
	$Arv['POLL_ID']=$arVar['POLL_ID'];
	//d($arV);
	//d($Arv);
	$r=GTpollfills::Add($Arv,$arV);
	if ($r==TRUE)
	{
		header("Location: ./?mod=polls&act=polls_answers&id=".$Arv['POLL_ID']);
		die();
	}
	else
	{
		GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
	}
}
function EditAns($arVar)
{
	foreach($arVar as $key=>$val)
	{
		if(is_array($val))
		{
			
			foreach($val as $KEY=>$VAL)
			{
				if(is_array($VAL))
				{
					$FV=array();
					foreach($VAL as $TKEY=>$TVAL)
					{
						$FV[]=$TVAL;
					}
					$arV['STMT_ID'][$KEY]=implode('\n',$FV);
				}
				else
				{
				$arV['STMT_ID'][$KEY]=$VAL;
				}
			}
		
		}
	}
	$Arv['FILL_ID']=$arVar['FILL_ID'];
	$Arv['POLL_ID']=$arVar['POLL_ID'];
	$r=GTpollfills::Update($Arv,$arV);
	if ($r==TRUE)
	{
		header("Location: ./?mod=polls&act=polls_answers&id=".$Arv['POLL_ID']);
		die();
	}
	else
	{
		GTApp::Raise(GetMessage('NOT_SUCH_ARGUMENTS'));
	}
}
}
?>