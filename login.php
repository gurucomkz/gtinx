<?
require_once($_SERVER["DOCUMENT_ROOT"].'/gtinx/init.php');
$APP->SetPageTitle('Авторизация на сайте');
define('NOLEFTMENU',1);
?>
<?$APP->IncludeComponent("gtinx:core.auth", "damulogin", array(
					"USE_CAPTCHA" => "0",
					"REGISTER_URL" => "/register.php"					
					),
					false 
				);?>
<?
require_once($_SERVER["DOCUMENT_ROOT"].'/gtinx/finish.php');
?>