<?
if($_POST)
{
	$arPost=$_POST;
	
		$RUSER=Array(
						"NAME"=>$arPost['NAME'],
						"LAST_NAME"=>$arPost['LAST_NAME'],
						"PATRONYMIC"=>$arPost['PATRONYMIC'],
						"LOGIN"=>$arPost['LOGIN'],
						"EMAIL"=>$arPost['EMAIL'],
						"PASSWD"=>$arPost['PASSWD'][0],
						"PASSWD2"=>$arPost['PASSWD'][1]);
						
	$res=GTusers::Add($RUSER);
	if($res==TRUE){$arResult['SUCCESS']=TRUE;}
}
elseif($_GET)
{
		$r=GTusers::Check($_GET);
		if(!$r[0])
		{
			define('GTINX_DIRECT_OUTPUT',TRUE);
			
			if($r['LOGIN']){echo '<i style="color:green; font-size:10px;">'.GetMessage('FREE').'</i>';}
			if($r['EMAIL']){echo '<i style="color:green; font-size:10px;">'.GetMessage('CORECT_EMAIL').'</i>';}
			
		}
		elseif($r[0])
		{
			define('GTINX_DIRECT_OUTPUT',TRUE);
			if($r[0]=='LOGIN'){echo '<i style="color:red; font-size:10px;">'.GetMessage('NOT_FREE').'</i>';}
			if($r[0]=='EMAIL'){echo '<i style="color:red; font-size:10px;">'.GetMessage('UNCORECT_EMAIL').'</i>';}
			
		}
}
else{
$arResult['INPUTS']= Array(
		Array("DESC"=>GetMessage('NAME'),"NAME"=>'NAME',"TYPE"=>'text',"VALUE"=>''),
		Array("DESC"=>GetMessage('LAST_NAME'),"NAME"=>'LAST_NAME',"TYPE"=>'text',"VALUE"=>''),
		Array("DESC"=>GetMessage('PATRONYMIC'),"NAME"=>'PATRONYMIC',"TYPE"=>'text',"VALUE"=>''),
		Array("DESC"=>GetMessage('LOGIN_TO_IN'),"NAME"=>'LOGIN',"TYPE"=>'text',"VALUE"=>''),
		Array("DESC"=>GetMessage('EMAIL'),"NAME"=>'EMAIL',"TYPE"=>'text',"VALUE"=>''),
		Array("DESC"=>GetMessage('PASSWORD_IN'),"NAME"=>'PASSWD[]',"TYPE"=>'password',"VALUE"=>''),
		Array("DESC"=>GetMessage('PASSWORD_REPEAT'),"NAME"=>'PASSWD[]',"TYPE"=>'password',"VALUE"=>''));
$APP->IncludeComponentTemplate($comTemplate);}
?>