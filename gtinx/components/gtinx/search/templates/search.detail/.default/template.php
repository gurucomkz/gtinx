<?$arItem = $arResult['CONTENT'];?>
<h2><?=$arItem['TITLE'];?></h2>
<?if (trim($arItem['SHORTIMG'])):?>
			<img src="<?=$arItem['SHORTIMG']?>">
		<?endif;?>
<?if($arVariables['SHOW_DATE']=='Y'):?>
<p class="date">
<?=GTDateFormat("d F Y",$arItem['UPDATED'])?>
</p>
<?endif;?>
<p class="full-text">
<?=$arItem['FULLTEXT']?>
</p>
<?if(!empty($arItem['PROPERTIES'])):?>
	<?foreach($arItem['PROPERTIES'] as $arProps):?>
		<h3><?=$arProps['NAME']?></h3>
		<p><?=$arProps['VALUE']?></p>
	<?endforeach;?>
<?endif;?>
