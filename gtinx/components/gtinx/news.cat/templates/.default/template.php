<? 
$tmp = $APP->GetCurPage();
$tmp .= FALSE===strpos('?',$tmp)?'?':'&amp;';
?>
<?if(!empty($arResult['SUBTYPES'])):?>
	<?foreach($arResult['SUBTYPES'] as $arItem):?>
	<div style="width:44%; margin-right: 5%; display: inline-block; float: left;">
		<h2><a href="<?=$arItem['NEXT_URL']?>"><?=$arItem['NAME']?></a></h2>
		<?=$arItem['DESC']?>
	</div>
	<?endforeach;?>
<?elseif(is_array($arResult['NEWS']) && count($arResult['NEWS'])>1):?>
	<?foreach($arResult['NEWS'] as $arItem):?>
	<?if(strstr($arItem['NEXT_URL'],"ITEM"))
		{
			$txt=str_replace("/kz","",$tmp."ITEM=#ID#");
			GTdblock::UpLink($txt,$arItem['ID']);
		}
		else
		{
			$txt=str_replace("/kz","",$_SERVER['REQUEST_URI']."?ID=#ID#");
			GTdblock::UpLink($txt,$arItem['ID']);
		}	
	?>
	<div class="short_post" style="margin-bottom: 20px; clear:both;">
		<div class="post_title"><a href="<?=$arItem['NEXT_URL']?>"><?=$arItem['TITLE']?></a></div>
		<?=$arItem['SHORTTEXT']?>
	</div>
	<?endforeach;?>
	<?=$arResult['PPT']?>
<?elseif(is_array($arResult['NEWS']) && count($arResult['NEWS'])==1):$arItem=$arResult['NEWS'][0];?>
	<div>
	<?=$arItem['FULLTEXT']?>
	</div>
	<div style="clear: both;"></div>
	<h4><a href="<?=$arResult['BACK_URL']?>">назад к вопросам</a></h4>
<?endif;?>