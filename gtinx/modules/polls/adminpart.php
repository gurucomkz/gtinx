<?
	global $APP;
	require(GTROOT.'/modules/polls/controller.php');
	$CONTROL = new ControllerPolls;
	$APP->SetPageTitle(GetMessage('POLLS'));
	$USER_ID=$APP->GetCurrentUserID();
?>