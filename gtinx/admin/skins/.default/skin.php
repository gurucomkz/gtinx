<?php

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
	<title><?php $APP->ShowTitle()?></title>
	<link rel="shortcut icon" href="<?=ADMIN_SKIN_PATH?>/favicon.png" />
	<link rel="stylesheet" type="text/css" href="<?=ADMIN_SKIN_PATH?>/skin.css" />
<meta name="MSSmartTagsPreventParsing" content="true" />
	<script type="text/javascript" src="<?=ADMIN_SKIN_PATH?>/skin.js"  charset="utf-8"></script>
	<script src="/gtinx/templates/.general/js/jquery-1.4.2.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="/gtinx/templates/.general/css/ui-lightness/jquery-ui-1.8.6.custom.css" />
	<script type="text/javascript" src="/gtinx/templates/.general/js/jquery-ui-1.8.6.custom.min.js"  charset="utf-8"></script>
	<script type="text/javascript" src="/gtinx/templates/.general/js/jquery.treeview.js"  charset="utf-8"></script>
	<script type="text/javascript" src="/gtinx/templates/.general/js/jquery.cookie.js"  charset="utf-8"></script>
	<?php $APP->ShowHead();?>
	<!--[if lte IE 6]>

		<link rel="stylesheet" type="text/css" href="<?=ADMIN_SKIN_PATH?>/commonie6.css" />
	<![endif]-->
	<!--[if lte IE 7]>

		<link rel="stylesheet" type="text/css" href="<?=ADMIN_SKIN_PATH?>/a1ie7.css" />
	<![endif]-->

</head>
<body>
<div id="megaroot">
	<div id="panel"><?php echo $APP->ShowPanel(FALSE);?><script>$(function() {
		$( "#tabs" ).tabs();
	});
</script></div>
	<table id="mainadmtbl">
		<tr height="100%">
			<td id="admcat" width="20%" height="50">
				<!-- main cat here -->
				<a href="#"><img src="<?=ADMIN_SKIN_PATH?>/images/content.png" /></a>
				<a href="#"><img src="<?=ADMIN_SKIN_PATH?>/images/settings.png" /></a>
				
			</td>
			<td rowspan="2" id="admdata" height="100%" valign="top">
				<!-- main data here -->
<?php
if ($_GET['debug'] == 1):
    echo "<pre>";
var_dump($APP->modOutput);
echo "</pre>";
endif;
if (is_object($APP->modOutput)):
?>
				<h1 id="modulename"><?=$APP->modOutput->name?></h1>
				<div id="breadcumbs"><a href="/gtinx/admin/">Рабочий стол</a> <?=$APP->modOutput->breadcumbs;

?></div>
<? if (is_array($APP->modOutput->filter)):?>
	<form action="<?=$APP->modOutput->filterUrl;?>" name="adm_filter" method="POST">
					<div id="filter">
					<p>Фильтр: </p>
					<table>
					<?foreach($APP->modOutput->filter as $v):?>
		            <tr>
						<th><?=$v['NAME']?></th>
						<td><?=$v['HTML']?></td>
		        	</tr>
					<?endforeach;?>
				<tr>
					<td align="center" colspan="2"><input type="submit" value="OK">&nbsp;<input type="reset" value="Отмена"></td>
				</tr>
					</table>
					</div>
				<div style="clear: both;"></div>
	</form>
				<?endif;?>
				<?
				if($APP->admModIsSetAction('ADD')):
				?>
			<div id="settings">

					<a href="<?=$APP->modOutput->addUrl;?>" class="panelsw"><span class="lspan"></span>Добавить<span class="rspan"></span></a>
					<?endif;?>
					<?if(is_array($APP->modOutput->userButton)):
						$arrUserButton = $APP->modOutput->userButton;
						foreach($arrUserButton as $sUrl => $sButton):?>
						<a href="<?=$sButton;?>" class="panelsw"><span class="lspan"></span><?=$sUrl;?><span class="rspan"></span></a>
						<?endforeach;?>
					<!---a href="#" class="panelsw"><span class="lspan"></span>Настроить<span class="rspan"></span></a--->
					<!---a href="#" class="panelsw"><span class="lspan"></span>Excel<span class="rspan"></span></a--->
				</div>
				<?endif;?>
<?switch ($APP->modOutput->actType):
    default:
    case "list":
        ?>
        <form action="<?=$APP->modOutput->actionUrl;?>" method="POST" enctype="multipart/form-data" id="moduleform">
				<?if ($APP->modOutput->header):?>
					<table class="adm_list">
					<tr>
						<th class="gtitemcheckbox"><input type="checkbox" name="checkAll" title="Отметить все"></th>
					<?foreach($APP->modOutput->header as $arrModHead):?>
					<th><?=$arrModHead;?></th>
					<?endforeach?>
					</tr>
					<?if(is_array($APP->modOutput->content) && !empty($APP->modOutput->content)):?>
						<?foreach($APP->modOutput->content as $kc => $arrModContents):?>
								<tr>
									<td class="gtitemcheckbox"><input type="checkbox" name="checkID[]" title="Отметить" value="<?=$arrModContents['ID']?>"></td>
								<?$edited=0;?>
								<?foreach($arrModContents as $k=> $sModContent):?>
									<?if(($k=="NAME" || !key_exists("NAME", $arrModContents) && $edited!=1) && $APP->admModIsSetAction('ADD')):$edited++;?>
									<td><a href="<?=$APP->modOutput->editUrl;?><?=$arrModContents['ID']?>"><?=$sModContent?></a></td>
									<?else:?>
									<td><?=$sModContent?></td>
									<?endif;?>
								<?endforeach;?>
								</tr>
						<?endforeach;?>
					<?else:?>
						<tr>
							<td colspan="<?=1+count($APP->modOutput->header)?>" class="nocontentmsg">Нет записей</td>
						</tr>
				<?endif;?>
        	</table>
<?php
if(!empty($APP->modOutput->allowAction)):
?>
 <br />
<table class="multiaction" border="0" cellpadding="0" cellspacing="0">
	<tbody
	><tr class="top"><td class="left"><div class="empty"></div></td><td><div class="empty"></div></td><td class="right"><div class="empty"></div></td></tr>
	<tr>
		<td class="left"><div class="empty"></div></td>
		<td class="content">
			<table border="0" cellpadding="0" cellspacing="0">
				<tbody><tr>

		<td class="gtitemcheckbox">
			<input title="Применить действие для всех записей в списке" name="action_target" id="action_target" value="selected" type="checkbox">
		</td>
		<td><label title="Применить действие для всех записей в списке" for="action_target">Для всех</label></td>
<td><div class="separator"></div></td>
		<td>
			<?if($APP->admModIsSetAction('EDIT')):?>
				<a href="#"><?=GetMessage('BT_EDIT')?></a>
			<?php endif;?>
		</td>

		<td>
			<?if($APP->admModIsSetAction('DELETE')):?>
			<a href="#" onclick="document.forms.moduleform.submit()"><?=GetMessage('BT_DELETE')?></a>
			<?endif;?>
		</td>
		<td>
			<select name="action">
				<option value="">- действия -</option>
				<option value="activate">активировать</option><option value="deactivate">деактивировать</option>
			</select>
		</td>
		<td><input name="apply" value="Применить" disabled="disabled" type="submit"></td>

				</tr>
			</tbody></table>
		</td>
		<td class="right"><div class="empty"></div></td>
	</tr>
	<tr class="bottom"><td class="left"><div class="empty"></div></td><td><div class="empty"></div></td><td class="right"><div class="empty"></div></td></tr>
</tbody></table>
<?endif;?>

		<?endif;?>
</form>
        
		
	<?break;

    case "edit":
        if ($APP->modOutput->header):?>
        <form action="<?=$APP->modOutput->actionUrl;?>" method="POST" enctype="multipart/form-data">
        <?switch($APP->modOutput->listType):
        	case "tabs":
        		?>
        <div id="tabs">
        <ul class="tabNavigation">
        		<?foreach(array_keys($APP->modOutput->header) as $sLiTabKey):?>
        			<?$liTabKeys[$sLiTabKey] = gen_string();?>
		        	<li><a class="" href="#tabs-_<?=$liTabKeys[$sLiTabKey];?>"><?=$sLiTabKey;?></a></li>
		        <?endforeach;?>
	    	</ul>

        	<?foreach($APP->modOutput->header as $ik => $arrModHead):?>
        		<div id="tabs-_<?=$liTabKeys[$ik];?>" class="adm_tabs">
        		<?if(is_array($arrModHead)):?>
	        		<table class="gtitemedit">
	        			<?php
	        			$arrReturn = $APP->AdmParseModFields($arrModHead);
	        		foreach($arrReturn as $v):?>
						<?if($v['TYPE']!='hidden'):?>
			            <tr>
							<th><?=$v['NAME']?></th>
							<td><?=$v['HTML']?></td>
			        	</tr>
						<?else:?>
							<?=$v['HTML']?>
						<?endif;?>
					
					<?endforeach;?>
					</table>
        		<?else:?>
        		<?=$arrModHead?>
        		<?endif;?>
        		</div>
        	<?endforeach;?>



		</div>
		<?break;
		case 'list':?>
				<table class="gtitemedit">
						<?php
	            $arrReturn = $APP->AdmParseModFields($APP->modOutput->header);
	        foreach($arrReturn as $v):?>
		            <tr>
						<th><?=$v['NAME']?></th>
						<td><?=$v['HTML']?></td>
		        	</tr>
			<?endforeach;?>
	        <tr>
	        	<td colspan=2><?=$APP->modOutput->dHtml;?></td>
	        </tr>

						</table>
		<?	break;
		?>

		<?endswitch;?>
		<table>
		<tr>
						<td align="center"><input type="submit" value="OK">&nbsp;<input type="reset" value="Отмена"></td>
					</tr>
						</table>
		</form>
				<?php
        endif;
        break;
        endswitch;
				if($APP->OUTPUT):
					//echo "<div style=\"border: 1px dashed red\"><h3 style=\"border-bottom:1px dashed red;margin:0\">Прямой вывод</h3>".$APP->OUTPUT."</h3></div>";
					echo $APP->OUTPUT;
				endif;
        else:
            echo $APP->OUTPUT;
        endif;

        ?>
			</td>
		</tr>
		<tr>
			<td  valign="top"><div id="menucontainer">
				<!-- options here -->
				<?php
        $arAdmOptions = $APP->admGetModulesOptTree();
		foreach($arAdmOptions as $armSection=>$arModOptions): 
		echo "<div id=\"arm$armSection\">";
        foreach($arModOptions as $iModOpt=>$arModOpt): 
			if($arModOptions[$iModOpt-1]['LEVEL']>$arModOpt['LEVEL'] && $iModOpt) {
				
				for($x = $arModOpt['LEVEL'];$x<$arModOptions[$iModOpt-1]['LEVEL'];$x++)
					echo "</div>";
			}
			
        ?>
				<div title="Двойной клик по иконке раскрывает дочерние элементы" class="modopt level<?=$arModOpt['LEVEL']?>" id="iam<?=$armSection.$iModOpt?>" style="padding-left: <?=((int)$arModOpt['LEVEL']) * 16 + 6?>px; background: <?=((int)$arModOpt['LEVEL'] - 1) * 16?>px top no-repeat url(<?=$arModOpt['IMAGE']?>);">
					<a href="<?=$arModOpt['URL']?>" title="<?=htmlspecialchars($arModOpt['DESC'])?>"><?=$arModOpt['NAME']?></a>
				</div>
			<?if($arModOptions[$iModOpt+1]['LEVEL']>$arModOpt['LEVEL']): ?>
				<div class="modoptchildgrp childrenofiam<?=$armSection.$iModOpt?>">
			<?endif?>
		<?endforeach;?>
		</div>
		<?endforeach;?>
			</div></td>
		</tr>
		<script type="text/javascript">
		$(function(){
			$('.modopt').bind('click',function(){
				$('.childrenof'+this.id).toggleClass('visible');
				var c = $.cookie('admintree') || '';
				if(0 < c.indexOf(this.id+'|')){
					while(0 < c.indexOf(this.id+'|'))
						c = c.replace(this.id+'|','');
				}else
					c += this.id+'|';
				$.cookie('admintree',c);
			});
			var c = $.cookie('admintree') || '';
			var ca = c.split('|');
			//alert(ca);
			for(var x in ca){
				$('.childrenof'+ca[x]).toggleClass('visible');
			}
		});
		</script>
		<tr class="gtbottomline">
			<td>
				<!--GTiNX &copy; GT SKILLTEX, LLP, 2010<?=(2010!=date("Y")?'-'.date("Y"):'')?>-->
			</td>
			<td align="right">
				<!--feedback and support links-->
			</td>
		</tr>
	</table>
</div>
</body>