<?

$defaults = array(

);

if($arVariables['USE_CAPTCHA']){
	if(!extension_loaded('gd'))
		return $APP->Raise('GD not loaded. Cannot use captcha. Authorization not available');
	
	$arResult['USE_CAPTCHA']=true;
	$arResult['CAPTCHA_RANDOM']=gfx_genCode();
}
$arResult['REGISTER_URL']=$arVariables['REGISTER_URL'];

if($_POST['doauth']){
	if($arVariables['USE_CAPTCHA'] && !GtinxCaptchaCheck($_POST['CAPTCHA_CODE'],$_POST['CAPTCHA_RANDOM']) /*captcha check*/){
	
		$arResult['FAIL'] = true;
	}else
	if($APP->CheckAuth($_POST['LOGIN'],$_POST['PASSWD'])){
		if($arVariables['SUCCESS_URL']){
			header("Location: ".$arVariables['SUCCESS_URL']);
			die();
		}else
			$arResult['SUCCESS'] = true;
	}else{
		$arResult['FAIL'] = true;
	}
}
$APP->IncludeComponentTemplate($comTemplate);
?>