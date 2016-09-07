<?
require_once($_SERVER["DOCUMENT_ROOT"].'/gtinx/init.php');
$APP->SetPageTitle('Регистрация на сайте');
define('NOLEFTMENU',1);
?>
<?$APP->IncludeComponent("gtinx:core.register", ".default", array(
					"USE_CAPTCHA" => "0",
					),
					false 
				);?>
<?
require_once($_SERVER["DOCUMENT_ROOT"].'/gtinx/finish.php');
?>