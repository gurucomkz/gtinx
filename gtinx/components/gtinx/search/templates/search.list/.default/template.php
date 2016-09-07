<?
//d($arResult)?>
<?if(!empty($arResult['QUESTIONS'])):?>
<table>
<?foreach($arResult['QUESTIONS'] as $arItem): ?>
<tr>
	<td>
		<h2><a href="?ID=<?=$arItem['RLINK']?>" target="_blank"><?=$arItem['ITITLE'];?></a></h2>
	</td>
</tr>
<tr>
	<td class="shorttext"><?=$arItem['RSUBTEXT'];?></td>
</tr>
<tr>
	<td><br /><br /></td>
</tr>
<?endforeach;?>
</table>
<?=$arResult['PPT'];?>
<?endif;?>
<?if(empty($arResult['QUESTIONS'])):?>
<h6>К сожилению ничего не найдено</h6>
<?endif;?>
