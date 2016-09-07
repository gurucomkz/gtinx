<script type="text/javascript">
function __PMloginform(){
	var oLF = document.getElementById('pploginform'); 
	oLF.style.height = document.getElementById('wrapper').clientHeight+'px';
	var oPLF = document.getElementById('_popup_login_form'); 
	if(oLF.style.display=='none'){oLF.style.display='block';}else{oLF.style.display='none';} }

</script>
<div id="pploginform" class="popupdisplay" style="display: none;">
<div class="popupblock">
<div class="loginform1">
<form method="post" id="_popup_login_form" action="/login.php">
	<table width="100%" cellspacing="10">
		<tr><td class="popupheader" colspan="3"><?=GetMessage('AUTH')?></td></tr>	
		<tr><td width="80"><?=GetMessage('LOGIN')?></td>
			<td colspan="2"><input type="text" name="LOGIN" class="wide" value="" /></td>
		</tr>

		<tr>
			<td><?=GetMessage('PASSWORD')?></td>
			<td colspan="2"><input type="password" name="PASSWD" class="wide" value="" /></td>
		</tr>
	<?if($arResult['USE_CAPTCHA']):?>
		<tr><td >Введите код:</td>
			<td width="100"><img src="/gtinx/direct.php?mod=core&act=gfx&random_num=<?=$arResult['CAPTCHA_RANDOM']?>" alt='Шифр' id="logincapcha" title='Шифр'></td>
			<td>
				<a href="#" onclick="document.getElementById('logincapcha').src='/gtinx/direct.php?mod=core&act=gfx&random_num=<?=$arResult['CAPTCHA_RANDOM']?>#'+Math.random(1000)" class="ajax">Картинка не видна?</a><br />
				<input type="text" NAME="CAPTCHA_CODE" SIZE="6" MAXLENGTH="6" style="margin-top:10px;" /></td>
		
			</tr>
		 <input type="hidden" NAME="CAPTCHA_RANDOM" value="<?=$arResult['CAPTCHA_RANDOM']?>">
	<?endif;?>
		<tr><td></td>
		<td><input type="submit" class="btn" value="Вход" /></td>
		<td align="right">
			<a href="/lostpw.php"><?=GetMessage('FORGOT_PASSWORD')?></a><br><br>
		<?if($arResult['REGISTER_URL']):?>
			<a href="<?=$arResult['REGISTER_URL']?>"><?=GetMessage('REGISTER')?></a>
		<?endif;?>
		</td>
		</tr>
		<input type="hidden" NAME="doauth" value="1" />
	</table>
</form>
<div style="clear:both"></div>
<br /><br />
<p align="right"><a onClick="__PMloginform();" class="ajax">закрыть окно</a></p>
</div>
</div>
</div>