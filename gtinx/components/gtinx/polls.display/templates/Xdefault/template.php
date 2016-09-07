<?

?>
<div class="pollX">
<form id="Poll">
<!--h2><?=$arResult['POLL']['TITLE']?></h2-->
<?
$MULTY=GTAPP::Conf('multilang_sync');
$LANG = GTAPP::SiteLang();//d($LANG);
?>
<?if($MULTY):?>
<input type="hidden" id="LANG" value="<?=$LANG;?>">
<?endif;?>
<?foreach($arResult['INPUTS'] as $arInput):?>
<h2><?=$arInput['TITLE']?></h2>
<?=$arInput['HTML']?>
<?endforeach;?>
<br />
<input class="poll_submit" type="submit" value="<?=$arResult['SUBMIT_TEXT']?>" />
<input type="hidden" name="STMT_ID" id="STMT_ID" value="<?=$arResult['POLL']['ID']?>">
</form>
<script>

function POLLS_ANSWER(ss)
{
	if((ss!=''))
	{
			var STMT_ID = encodeURI(document.getElementById('STMT_ID').value);
			var Lang = encodeURI(document.getElementById('LANG').value);
			$.get("/gtinx/components/gtinx/polls.display/component.php", {STMT: STMT_ID, FIELD: ss, LANG: Lang},
			  function(data){
				$('#Poll').html(data);
			  });
	}
}
    $("#Poll").submit(function() {
	var SS=$("#Poll input:radio:checked").val();
	POLLS_ANSWER(SS);
	return false;
    });
</script>

</div>