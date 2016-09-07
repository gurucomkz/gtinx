<?php
/**
 * RSS2 Feed Template for displaying RSS2 Comments feed.
 *
 * @package WordPress
 */
header('Content-Type: application/rss+xml; charset=UTF-8', true);
$lang=$_GET['lang']; if(empty($lang)){$lang='ru';}
$arItem=GTdblock::Get('*',Array('GROUP_ID'=>'!17','TYPE_ID'=>'!19','LANG'=>$lang,'FORCE_LANG'=>'1'),array('UPDATED','DESC'),array(0,20));//d($arItem);
echo '<?xml version="1.0" encoding="UTF-8"?'.'>';
?>

<rss version="2.0"
	 xmlns:atom="http://www.w3.org/2005/Atom"
	 xmlns:dc="http://purl.org/dc/elements/1.1/"
	 xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
	<title>Bizhelp RSS</title>
	<atom:link href="http://bizhelp.kz/gtinx/direct.php?mod=rss" rel="self" type="application/rss+xml" />
	<link>http://bizhelp.kz/gtinx/direct.php?mod=rss</link>
	<description>bizhelp.kz</description>
	<language>ru</language>
<?if(!empty($arItem)):?>
<?foreach($arItem as $Item):?>
<item>
<title><?=$Item['TITLE'];?></title>
<link><?echo htmlentities($Item['LINK']);?></link>
<pubDate><?=date('D, d M Y H:i:s +0600')?></pubDate>
<description><![CDATA[<p style="text-align: center;"><?=$Item['SHORTTEXT'];?>]]></description>
</item>
<?endforeach;?>
<?endif;?>
</channel>
</rss>