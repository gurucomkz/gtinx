<?
if($_GET['STMT'])
{	
	require_once($_SERVER["DOCUMENT_ROOT"] . "/gtinx/init.php");
	define('GTINX_DIRECT_OUTPUT',TRUE);
	$ar = GTpollsstms::Get('',Array('POLL_ID'=>$_GET['STMT'],'ACTIVE'=>1));
	$MULTY=GTAPP::Conf('multilang_sync');
	if($MULTY){	
	$LANG =$_GET['LANG'];//d($LANG);
	$ar = GTpollsstms::Get('',Array('POLL_ID'=>$_GET['STMT'],'ACTIVE'=>1,'LANG'=>$LANG,'FORCE_LANG'=>1));
		foreach($ar as $key=>$val)
		{
			$arc[]=$ar[$key][$LANG];
			
		}
		$ar=$arc;
	}
	//d($ar);
	if(isset($_GET['FIELD']))
	{
		$arV['STMT_ID'][$ar[0]['ID']]=$_GET['FIELD'];
		$Arv['POLL_ID']=$_GET['STMT'];
		GTpollfills::Add($Arv,$arV);
	}
	if($MULTY!=TRUE)
	{
	$txt=(string)$ar[0]['VALUES'];
	$txt = explode("\n", $txt);
	echo '<h2>'.$ar[0]['TITLE'].'</h2>';
	foreach($txt as $key=>$val)
	{
	$val=trim($val);
	$s[]=GTpollfills::GetStmtFill('',Array('ID'=>$ar[0]['ID'],'VALUE'=>'~%'.$val.'%'),'','','',$count);// d($s);
	$c[$key]=$count;
	$all=$all+$count;
	}
	}
	/**/
	if($MULTY){
		$ar22 = GTpollsstms::Get('',Array('POLL_ID'=>$_GET['STMT'],'ACTIVE'=>1));//d($ar22);
		$all=0;
		$c=array();
		$LL=array();
		foreach($ar22 as $vals)
		{
			foreach($vals as $keys=>$VAL)
			{
				$txt[$keys]=(string)$VAL['VALUES'];
				$txt[$keys] = explode("\n", $txt[$keys]);
				foreach($txt[$keys] as $key=>$val)
				{
					$val=trim($val);
					$count='';
					$s[]=GTpollfills::GetStmtFill('',Array('ID'=>$ar[0]['ID'],'VALUE'=>'~%'.$val.'%'),'','','',$count);//d($s);
					$c[$keys][$key]=$count;
					$LL[$keys]=$keys;
					$all=$all+$count;
				}
			}
		}
		$d=array();
		foreach($LL as $val)
		{
			$d[]=$val;
		}
		$sum=array();
		foreach($c[$d[0]] as $key=>$val)
		{
			$sum[$key]=$val+$c[$d[1]][$key];
		}
		$c=$sum;
		$txt=(string)$ar[0]['VALUES'];
		$txt = explode("\n", $txt);
		echo '<h2>'.$ar[0]['TITLE'].'</h2>';
	}
	/**/
	$colors=array('#FF0000','#00FF00','#0000FF','#FFFF00','#00FFFF','#FF00FF');
	foreach($txt as $key=>$val)
	{
		$pr=0;
		if($all!=0)
		{
			$pr=round($c[$key]*100/$all,1);
		}
		echo '<div class="vname">'.$val.'</div>
		<div><div class="vgauge key'.$key.'" style="width:'.round($pr*0.7+1).'%;"></div><div class="vpercent">'.$pr.'%</div></div>';
	}
	echo '<div style="clear:both;"></div><div><strong>Всего проголосовало:</strong> '.$all.'</div>';

}
else
{
if(!$arVariables['DATEFMT']) $arVariables['DATEFMT'] = 'd F Y';
if(!$arVariables['SORTBY']) $arVariables['SORTBY'] = 'SORT';
if(!$arVariables['SORTDIR']) $arVariables['SORTDIR'] = 'ASC';
if(!$arVariables['CACHE']) 
	$arVariables['CACHE'] = 'N';
if($arVariables['CACHE'] == 'Y' && !isset($arVariables['CACHE_TIME']))
	$arVariables['CACHE_TIME'] = 3600;
$arVariables['CACHE_TIME'] = (int)$arVariables['CACHE_TIME'];
if(!trim($arVariables['POLL_ID']))
	return;

$arPoll = GTpolls::Get('*',array('ID'=>$arVariables['POLL_ID'],'ACTIVE'=>1));
$arPoll = $arPoll[0];
if(empty($arPoll)) return;

$arResult['POLL'] = array(
	'ID'=>$arPoll['ID'],
	'TITLE'=>$arPoll['TITLE'],
	'DESC'=>$arPoll['DESC'],
	'ACTIVE_FROM'=>GTDateFormat($arVariables['DATEFMT'],$arPoll['ACTIVE_FROM']),
	'ACTIVE_TO'=>GTDateFormat($arVariables['DATEFMT'],$arPoll['ACTIVE_TO'])
);
$arInputsSrc = GTpollsstms::Get('',Array('POLL_ID'=>$arVariables['POLL_ID'],'ACTIVE'=>1,'FORCE_LANG'=>1),array($arVariables['SORTBY'],$arVariables['SORTDIR']));
$MULTY=GTAPP::Conf('multilang_sync');
if($MULTY){	
$LANG = GTAPP::SiteLang();
foreach($arInputsSrc as $key=>$val)
{
	$arInputsSrc2[$key]=$arInputsSrc[$key][$LANG];
}
$arInputsSrc=$arInputsSrc2;
}
//d($arInputsSrc);
$arInputs = array();

foreach($arInputsSrc as $arInput){
	$ar = array();
	$ar['TITLE'] = $arInput['TITLE'];
	$ar['TYPE'] = $arInput['TYPE'];
	$ar['REQUIRED'] = $arInput['REQUIRED'];
	$ar['MULTIPLE'] = $arInput['MULTIPLE'];
	
	switch($arInput['TYPE']){
	case 'RADIO':
		$txt=(string)$arInput['VALUES'];
		$txt = explode("\n", $txt);
		$S=array();
		$s=array();
		foreach($txt as $Tkey=>$Tval)
		{
			$Tval=trim($Tval);
			$s[]="<input type=\"checkbox\" name=\"FIELD[".$arInput['ID']."][]\" value=\"".$Tval."\"> <label for=\"\">".$Tval."</label>";
			$S=array_merge($S,array($Tval=>$Tval));
		}
		$s1=implode('',$s);
		$S=array_flip($S);
		if($arInput['MULTIPLE']!=0)
		{
			echo'<tr><td>'.$arInput['TITLE'].'</td><td>'.$s1.'</td></tr>';
		}
		else
		{
			$s=array();
			$arId=array();
			$cntr = 1;
			foreach($S as $SKK=>$SVV)
			{
				$thisID = "POLL_$arPoll[ID]_Q_$arInput[ID]_V_$cntr";
				$s[]="<tr><td><input type=\"radio\" name=\"FIELD[".$arInput['ID']."][]\" value=\"".$SKK."\" id=\"$thisID\"></td><td><label for=\"$thisID\">".$SVV."</label></td></tr>";
				$cntr++;
			}
			$s2 = '<table>'.implode('',$s).'</table>';
		}
		$ar['HTML'] = $s2;
		break;
	}
	
	$arInputs[] = $ar;
}
$arResult['INPUTS'] = $arInputs;
$arResult['ELEMENTID']=$arId;
$arResult['SUBMIT_TEXT'] = $arPoll['SUBMIT_TEXT']?$arPoll['SUBMIT_TEXT']:GetMessage("SUBMIT");
//d($arResult);
$APP->IncludeComponentTemplate($comTemplate);
}
?>