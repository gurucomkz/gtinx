<?if($arResult['SUCCESS']):?>
	УРАААААА!
<?else:?>
	<?if($arResult['FAIL']):?>
		АШИПКА!!!
	<?endif;?>
	<form method="POST">
	     <input type="hidden" NAME="doauth" value="1">
	<table cellspacing="10" border="0">
		<tr><td >Логин:</td><td><input type="text" NAME="LOGIN" /></td></tr>
		<tr><td >Пароль:</td><td><input type="password" NAME="PASSWD" /></td></tr>
	
	<?if($arResult['USE_CAPTCHA']):?>
		<tr><td >Шифр: </td><td><img src="/gtinx/direct.php?mod=core&act=gfx&random_num=<?=$arResult['CAPTCHA_RANDOM']?>" alt='Шифр' title='Шифр'></td></tr>
		<tr><td >Введите шифр:</td><td><input type="text" NAME="CAPTCHA_CODE" SIZE="6" MAXLENGTH="6"></td></tr>
	     <input type="hidden" NAME="CAPTCHA_RANDOM" value="<?=$arResult['CAPTCHA_RANDOM']?>">
	<?endif;?>
		<tr><td colspan="2" align="center"><input type="submit" value="<?=GetMessage('Login')?>" /></td></tr>
	<?if($arResult['REGISTER_URL']):?>
		<tr><td colspan="2" align="right"><a href="<?=$arResult['REGISTER_URL']?>">Зарегистрироваться?</a></td></tr>
	<?endif;?>
	</table>
	</form>
<?endif;?>
