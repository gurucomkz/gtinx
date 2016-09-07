<?php

class GTComments {
    function count($sParent, $iParentId)
    {
    	global $APP;
        if (!empty($sParent) && !empty($iParentId)) {
			$iParentIds = verifyArray($iParentId,true);
			if(!empty($iParentIds)){
				$hRes = $APP->DB->query("SELECT distinct `ELEMENT_ID` AS `ELID` , count(`ID`) as `COUNT`  FROM `g_comments` WHERE SUBSYSTEM='$sParent' AND `ELEMENT_ID` IN ('$iParentId') GROUP BY `ELEMENT_ID`");
				
				if(count($iParentIds)>1){
					$arRet = array();
					while($r = $APP->DB->fetchAssoc($hRes))
						$arRet[$r['ELID']] = (int)$r['ELEMENT_ID'];
					foreach($iParentIds as $id)
						if(!isset($arRet[$id])) $arRet[$id] = 0;
					return $arRet;
				}else{
					$r = $APP->DB->fetchAssoc($hRes);
					return (int)$r['COUNT'];
				}
			}
        }
		return 0;
    }
}

?>