<?if($arResult['SUCCESS']):?>
<html>
<head>
<title>Подождите...</title>
<meta http-equiv='refresh' content='3; url=<?=$APP->GetCurPage(false,false)?>' />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
body{background-color:#BCCFDF; font-family: verdana;}
table.redirect{
	border: Black 2px ridge;
}
</style>
</head>
<body class="redirect">
<table width='100%' height='85%' align='center'>
<tr>
<td valign='middle'>
  <table align="center" cellpadding='4' class="redirect">
   <tr><td width='100%' align='center' nowrap='nowrap'><h2><?=GetMessage('PAGE_REDIRECT_TEXT')?></h2><br />
   <?=GetMessage('PAGE_REDIRECT_EXT')?><br /><br />
   (<a href='<?=$APP->GetCurPage(false, false)?>'><small class="redirect"><b><?=GetMessage('PAGE_REDIRECT_NOWAIT')?></b></small></a>)
   </td></tr>
</table>
</td>
</tr>
</table>
</body>
</html>
<?else:?>
<html>
<head>
<title>Требуется авторизация</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
body{background-color:#BCCFDF; font-family: verdana;}
table.redirect{
	border: Black 2px ridge;
}
</style>
</head>
<body class="redirect">
<table width='100%' height='85%' align='center'>
<tr>
<td valign='middle'>
  <table align="center" cellpadding='4' class="redirect">
   <tr><td width='100%' align='center' nowrap='nowrap'>
	<?if($arResult['FAIL']):?>
		Вы ввели неверные данные! Повторите попытку.
	<?endif;?>
	<form method="POST">
	     <input type="hidden" NAME="doauth" value="1">
	<table cellspacing="10" border="0">
		<tr><td align="right" >Логин:</td><td><input type="text" NAME="LOGIN" /></td></tr>
		<tr><td align="right" >Пароль:</td><td><input type="password" NAME="PASSWD" /></td></tr>

		<tr><td colspan="2" align="center"><input type="submit" value="<?=GetMessage('Login')?>" /></td></tr>

	</table>
	</form>
   </td></tr>
</table>
</td>
</tr>
</table>
</body>
</html>
<?endif;?>