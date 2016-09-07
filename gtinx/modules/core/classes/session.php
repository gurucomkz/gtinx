<?php

class GTSession {

	function Cleanup($iTimeout=false,$iMintime=false){
		global $DB;
		if ($iTimeout === false) $iTimeout = 3600;
		if ($iMintime === false) $iMintime = 600;
		$ctime1 = time() - $iTimeout;
		$ctime2 = time() - $iMintime;
		$DB->Query("DELETE FROM `g_sessions` WHERE (`SID` != '' AND `TIMELAST` < '$ctime1') OR (`SID` = '' AND `TIMELAST` < '$ctime2')");
	}
	function Kill($sSID, $sIp=''){
		global $DB;
		if ($sSID) {
			$rid = "WHERE (`SID` = '$sSID')";
			$expire = time() - 3600;
			if ($sIp) $rid .= " AND (`IP` = '$sIp' OR `TIMELAST` < '$expire')";
		} else {
			$rid = "WHERE (`IP` = '$sIp') AND (`SID` = '')";
		}
		$DB->query("DELETE FROM `g_sessions` $rid");
	}
	function IsOnline($sUsername)
	{
		global $DB;
		//$ip1 = get_user_ip();
		if (IsEmpty($sUsername)) return false;
		$expire = time() - 3600;
		$tmpqu = $DB->QResult("SELECT COUNT(*) FROM `g_sessions` WHERE `USER_NAME`='$sUsername' AND `TIMELAST` > '$expire'");
		
		return $tmpqu > 0;
	}
	function Create($sUsername, $iUserId, $sInfo1, $sInfo2)
	{
		global $DB;
		$ip1 = get_user_ip();
		GTSession::Cleanup();
		GTSession::Kill(null, $ip1);
		$url = addslashes($_SERVER['REQUEST_URI']);
		if (!GTSession::IsOnline($sUsername)) {
			// sid must be unique
			do{
				$newsid = md5(gen_string());
			}while($DB->QResult("SELECT COUNT(*) FROM `g_sessions` WHERE `SID` = '$newsid'"));

			$DB->query("INSERT INTO `g_sessions` (`SID`,`IP`,`TIMESTART`,`TIMELAST`,`USER_ID`,`USER_NAME`,`INFO1`,`INFO2`,`URL`)" .
				" VALUES('$newsid','$ip1',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),'$iUserId','$sUsername','" . addslashes($info1) . "','" . addslashes($info2) . "','$url')");
			CookieSet('gtinx_sid', $newsid, time() + 3600 * 5);
			return $newsid;
		} else {
			return false;
		}
	}
    
	function Update($info1='', $info2='')
	{
		global $DB;
		// $sid = Cookie('sid_'.$iUserId);
		$sid = Cookie('gtinx_sid');
		$ip1 = get_user_ip();
		$url = addslashes($_SERVER['REQUEST_URI']);
		$expire = time() - 240;
		$tmpqu = $DB->QResult("SELECT COUNT(*) FROM `g_sessions` WHERE (`SID`='$sid') && (`IP`='$ip1'/* OR `TIMELAST` < '$expire'*/)");
		if ($tmpqu == 1) {
			$DB->Query("UPDATE `g_sessions` SET 
							`INFO1`='" . addslashes($info1) . "', `INFO2`='" . addslashes($info2) . "', 
							`TIMELAST`=UNIX_TIMESTAMP()/*, `IP`='$ip1' */, `URL`='$url'
						WHERE `SID` = '$sid' ");
			CookieSet('gtinx_sid', $sid, time() + 3600);
		} 
	}
	function GetUser()
	{
		global $DB;
		GTSession::Cleanup();
		$ip1 = get_user_ip();
		$expire = time() - 360;

		$sSid = Cookie('gtinx_sid');
		if ($sSid) {
			$userquery = $DB->Query("SELECT `USER_NAME`, `USER_ID`, `SID`, `IP` FROM `g_sessions` WHERE `SID`='$sSid'");
			if ($userquery && $DB->NumRows($userquery) == 1) {
				$CUSER = $DB->FetchAssoc($userquery);
				if ($CUSER['IP'] == $ip1|| $CUSER['TIMELAST'] < $expire) {
					return $CUSER;
				} 
			} 
		}
		return array();
	}
}

?>