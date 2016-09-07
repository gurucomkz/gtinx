<?
	global $APP;
	require(GTROOT.'/modules/users/controller.php');
	$APP->SetPageTitle(GetMessage('USERS'));
	$CONTROL = new UserControl;		
?>