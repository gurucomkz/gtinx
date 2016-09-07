<?
// controller for dblocks
Class EditLibController {
       
	   function __construct() 
		{		
			global $DB;
		//new switch for GET	

			switch($_POST['act'])
			{
				case 'setpagecnt':$this->setPageCnt();break;
				case 'createnewpage':$this->mkNewPage();break;
				case 'createnewdir':$this->mkNewDir();break;
				case 'diroptions': $this->setDirOptions(); break;
				case 'getcompopts': $this->getComponentsOptions(); break;
				case 'pageoptions':$this->setPageOptions();break;
				case 'editmenu':$this->seteditmenu();break;
				
				/*default:
					$this->ListGroup();
					break;*/
			}
			
			switch($_GET['act'])
			{
				case 'getcompopts': $this->getComponentsOptions(); break;
				//case 'setpagecnt': echo "foool"; print_r($GLOBALS); die;
				case 'getdiroptions': $this->getDirOptions(); break;
				case 'getpageoptions': $this->getPageOptions(); break;
				case 'getpagecnt': $this->getPageCnt(); break;
				case 'geteditmenu': $this->geteditmenu(); break;
			}
			
        }
		function geteditmenu()
		{
			global $APP;
			
			$type=$_REQUEST['type'];
			$lang=$_REQUEST['lang'];
			$pdir = str_replace(array('../','..\\'),array('',''),$_REQUEST['dname']);
			if(preg_match('/^\/?gtinx/',$pdir) || preg_match('/[^a-z]/',$type)) 
				die( "{\"result\":false}");
			
				$iMenuTree=$APP->GetMenuTree($type,'',1,2,$pdir,$foundPass);
			
			$b=Array();
			
			foreach($iMenuTree as $iValue)
			{
				$d=Array();
				foreach($iValue as $k=>$v)
				{
					$d[$k] = base64_encode($v);
				}
				$b[]=phpArray2JSONObject($d);
			}
			echo "{\"filepass\":\"$foundPass\",\"menu\":[".implode(',',$b)."]}";
			die();
		}
		
		function seteditmenu()
		{
			$ar=$_REQUEST;
			$lang=$ar['lang'];
			$dname=$ar['filepass'];
			$type=$ar['type'];
			
			$menu=array();
			for($i=0; $i<=count($ar); $i++)
			{
				$NAME='';
				$URL='';
				if($ar['TITLE'.$i])
				{
					$NAME=htmlspecialchars(addslashes(trim($ar['TITLE'.$i])));
					$URL=htmlspecialchars(addslashes(trim($ar['URL'.$i])));
					$TARGET=htmlspecialchars(addslashes(trim($ar['TARGET'.$i])));
					$menu[]="\n\tarray(\n\t\t'NAME'=>'".$NAME."',\n\t\t'URL'=>'".$URL."',\n\t\t'TARGET'=>'".$TARGET."'\n\t\t)";
				}
			}
			
			$firs=GTDOCROOT;
			$file=str_replace($firs, '', $dname);
			if(is_writable(GTDOCROOT.$file))
			{
				$m="<?\n".'$GTMENUITEMS = array('.implode(',',$menu)."\n);\n?>";
				file_put_contents(GTDOCROOT.$file,$m);
				die("{\"result\":true}");
			}
				die("{\"result\":false}");
		}
		function setDirOptions()
		{	
			$pdir = str_replace(array('../','..\\'),array('',''),$_POST['dfile']);
			if(preg_match('/^\/?gtinx/',$cdir)) 
				die( "{\"result\":false}");
			$supported = array('META','TITLE');
			$SECTION = array();
			list($lnDefault,$lnUse) = $this->getLangs();
				
			$arRet = array();
			if(is_writable(GTDOCROOT.'/'.$pdir))
			{
				$sNewCnt = '<'.'?'."\n";
				foreach($supported as $k)
					$sNewCnt .=	 '$SECTION["'.$k.'"]="'.htmlspecialchars($_POST[$k]).'";'."\n";
			
				$sNewCnt .= '?'.'>';
				file_put_contents(GTDOCROOT.'/'.$pdir.'/.section'.($lnUse?'.'.$lnUse:'').'.php',$sNewCnt);				
				die("{\"result\":true}");
			}
			
			die("{\"result\":false}");
		}
		function getDirOptions()
		{	
			$pdir = str_replace(array('../','..\\'),array('',''),$_GET['dfile']);
			if(preg_match('/^\/?gtinx/',$cdir)) 
				die( "{\"result\":false}");
			$SECTION = array('META'=>'not set','TITLE'=>'not set');
			$arRet = array();
			list($lnDefault,$lnUse) = $this->getLangs();
			$FN = GTDOCROOT.'/'.$pdir.'/.section'.($lnUse?'.'.$lnUse:'').'.php';
			if(is_readable($FN))
			{
				@include($FN);				
			}
			foreach($SECTION as $_k => $_v)
				$arRet[$_k] = base64_encode($_v);
			echo phpArray2JSONObject($arRet);
			die();
		}
		function mkNewDir()
		{
			$pfile = 'index.php';
			$cdir = str_replace(array('../','..\\'),array('',''),$_POST['dir']);
			$pdir = str_replace(array('../','..\\'),array('',''),$_POST['dfile']);
			$dtitle = htmlspecialchars($_POST['dtitle']);
			$dmeta =  htmlspecialchars($_POST['dmeta']);			
			$ptitle = htmlspecialchars($_POST['ptitle']);
			$pmeta =  htmlspecialchars($_POST['pmeta']);
			
			if(preg_match('/^\/?gtinx/',$cdir)) 
				die( "{\"result\":false}");
			$fdir = GTDOCROOT.'/'.$cdir.'/'.$pdir;
			$fname = $fdir.'/'.$pfile;
			if(file_exists($fdir))
				die("{\"result\":false,\"msg\":'Folder exists'}");
			
			if(is_writable(dirname($fdir)))
			{
				if(!mkdir($fdir))
					die("{\"result\":false,\"msg\":'Dir not created'}");
					
				$sNewCnt = '<'.'?'."\n".
							'$SECTION["META"]="'.$dmeta.'";'."\n".
							'$SECTION["TITLE"]="'.$dtitle.'";'."\n".
							'?'.'>';
				file_put_contents($fdir.'/.section.php',$sNewCnt);
				//prepare header and footer
				$f = array();
				$f['HEAD']='<'."?\n".
							'require_once($_SERVER["DOCUMENT_ROOT"]."/gtinx/init.php");'."\n".
							'$APP->SetPageTitle("'.$ptitle.'");'."\n".
							'$APP->SetPageMeta("'.$pmeta.'");'."\n".
							'?'.'>'."\n";
				$f['FOOTER']="\n".'<'.'?'."\n".
							'require_once($_SERVER["DOCUMENT_ROOT"]."/gtinx/finish.php");'."\n".
							'?'.'>';
				$f['CONTENT'] = 'Text here...';
			
				$sNewCnt = $f['HEAD'].$f['CONTENT'].$f['FOOTER'];
				$iBytes = file_put_contents($fname,$sNewCnt);
				if($iBytes == strlen($sNewCnt))
					die( "{\"result\":true, \"written\":$iBytes }");
				else
					die("{\"result\":false}");
			}
		}
		
		function mkNewPage()
		{
			$pfile = str_replace(array('../','..\\','/','\\'),array('','','',''),$_POST['pfile']);
			$pdir = str_replace(array('../','..\\'),array('',''),$_POST['dir']);
			$ptitle = htmlspecialchars($_POST['ptitle']);
			$pmeta =  htmlspecialchars($_POST['pmeta']);
			
			
			if(preg_match('/^\/?gtinx/',$pdir)) 
				die( "{\"result\":false}");
				
			$fname = GTDOCROOT.'/'.$pdir.'/'.$pfile;
			if(file_exists($fname))
				die("{\"result\":false,\"msg\":'File exists'}");
				
			if(is_writable(dirname($fname)))
			{
				$f = array();
				
				//prepare header and footer
				$f['HEAD']='<'."?\n".
							'require_once($_SERVER["DOCUMENT_ROOT"]."/gtinx/init.php");'."\n".
							'$APP->SetPageTitle("'.$ptitle.'");'."\n".
							'$APP->SetPageMeta("'.$pmeta.'");'."\n".
							'?'.'>'."\n";
				$f['FOOTER']="\n".'<'.'?'."\n".
							'require_once($_SERVER["DOCUMENT_ROOT"]."/gtinx/finish.php");'."\n".
							'?'.'>';
				$f['CONTENT'] = 'Text here...';
			
				$sNewCnt = $f['HEAD'].$f['CONTENT'].$f['FOOTER'];
				$iBytes = file_put_contents($fname,$sNewCnt);
				if($iBytes == strlen($sNewCnt))
					die( "{\"result\":true, \"written\":$iBytes }");
				else
					die("{\"result\":false}");
			}else 
				die("{\"result\":false,\"msg\":'Directory is now writable'}");
		} 
		function setPageCnt()
		{
			list($lnDefault,$lnUse) = $this->getLangs();
			
			$fname = str_replace('../','',$_POST['fname']);
			$fcnt = stripslashes($_POST['fcnt']);
			if(preg_match('/^\/?gtinx/',$fname)) 
				die( "{\"result\":false}");
			$FNd = $FN = GTDOCROOT.'/'.$fname;
			if($lnUse)
				$FN = preg_replace('/\.php$/','.'.$lnUse.'.php',$FNd);
			
			if(is_writable(($ex=file_exists($FN))?$FN:dirname($FN)) && file_exists($FNd))
			{
				$f = array();
				if(!$ex){
					//prepare header and footer
					if($lnUse){
						$fd = phpFileParse(file_get_contents($FNd));
						$f['HEAD']=$fd['HEAD'];
						$f['FOOTER']=$fd['FOOTER'];
					}else{
						$f['HEAD']='<'."?\n".
									'require_once($_SERVER["DOCUMENT_ROOT"]."/gtinx/init.php");'."\n".
									'$APP->SetPageTitle("Page title");'."\n".
									'$APP->SetPageMeta("gtinx ))");'."\n".
									'?'.">\n";
						$f['FOOTER']="\n<"."?\n".
									'require_once($_SERVER["DOCUMENT_ROOT"]."/gtinx/finish.php");\n'.
									'?'.'>';
					}
					$f['CONTENT'] = $fcnt;
				}else{
					$f = phpFileParse(file_get_contents($FN));
					$f['CONTENT'] = $fcnt;
				}
				$sNewCnt = $f['HEAD'].$f['CONTENT'].$f['FOOTER'];
				$iBytes = file_put_contents($FN,$sNewCnt);
				if($iBytes == strlen($sNewCnt))
					die( "{\"result\":true, \"written\":$iBytes }");
				else
					die("{\"result\":false}");
			}else 
				die("{\"result\":false}");
		}
		function getPageOptions()
		{
			$fname = str_replace(array('../','..\\'),array('',''),$_REQUEST['fname']);
			//echo GTDOCROOT.'/'.$fname;
			if(preg_match('/^\/?gtinx/',$cdir)) 
				die( "{\"result\":false}");
			list($lnDefault,$lnUse) = $this->getLangs();
				
			$FN = GTDOCROOT.'/'.$fname;
			
			if(file_exists($FN))
			{
			
				if($lnUse){
					$FNx = preg_replace('/\.php$/','.'.passedVal('lang').'.php',$FN);
					if(file_exists($FNx))
						$FN = $FNx;
				}
			
				$arCnt = phpFileParse(file_get_contents($FN));
				$arRet = array('TITLE'=>'','META'=>'','HEADER'=>'');
				$h = $arCnt['HEAD']; 
				$arCnt = array();
				if(preg_match('/(?<!\/\/|#)\$APP-\>SetPageTitle\(["\'](.*)["\']\)/',$h,$mcTitle)){
					$arCnt['TITLE'] = $mcTitle[1];
				}
				if(preg_match('/(?<!\/\/|#)\$APP-\>SetPageMeta\(["\'](.*)["\']\)/',$h,$mcMeta)){
					$arCnt['META'] = $mcMeta[1];
				}
				if(preg_match('/(?<!\/\/|#)\$APP-\>SetPageHeader\(["\'](.*)["\']\)/',$h,$mcHeader)){
					$arCnt['HEADER'] = $mcHeader[1];
				}
				
				foreach($arCnt as $_k => $_v)
					$arRet[$_k] = base64_encode($_v);
				echo phpArray2JSONObject($arRet);
				die();
			}
		}
		function setPageOptions()
		{
			list($lnDefault,$lnUse) = $this->getLangs();
			$supported = array('HEADER','META','TITLE');
			
			$fname = str_replace('../','',passedVal('dfile'));
			if(preg_match('/^\/?gtinx/',$fname)) 
				die( "{\"result\":false}");
			$FNd = $FN = GTDOCROOT.'/'.$fname;
			if($lnUse)
				$FN = preg_replace('/\.php$/','.'.$lnUse.'.php',$FNd);
			if(is_writable(($ex=file_exists($FN))?$FN:dirname($FN)) && file_exists($FNd))
			{
				$f = array();
				if(!$ex){
					//prepare header and footer
					if($lnUse){
						$fd = phpFileParse(file_get_contents($FNd));
						$f['HEAD']=$fd['HEAD'];
						$f['CONTENT']=$fd['CONTENT'];
						$f['FOOTER']=$fd['FOOTER'];
					}else{
						$f['HEAD']='<'."?\n".
									'require_once($_SERVER["DOCUMENT_ROOT"]."/gtinx/init.php");'."\n".
									'$APP->SetPageTitle("Page title");'."\n".
									'$APP->SetPageMeta("gtinx ))");'."\n".
									'?'.">\n";
						$f['FOOTER']="\n<"."?\n".
									'require_once($_SERVER["DOCUMENT_ROOT"]."/gtinx/finish.php");\n'.
									'?'.'>';
						$f['CONTENT'] = 'Text here...';
					}
				}else{
					$f = phpFileParse(file_get_contents($FN));
				}
				foreach($supported as $k){
					$cntr = 0;
					$CAP = capitalize($k);
					$f['HEAD'] = preg_replace('/(?<!\/\/|#)\$APP-\>SetPage'.$CAP.'\(["\'].*["\']\)/',
												'$APP->SetPage'.$CAP.'("'.htmlspecialchars($_POST[$k]).'")'
											,$f['HEAD'],-1,$cntr);
					if(!$cntr)
						$f['HEAD'] = preg_replace('/\?\>$/',
												'$APP->SetPage'.$CAP.'("'.htmlspecialchars($_POST[$k]).'");'."\n?".'>'
											,$f['HEAD']);
				}
				
				$sNewCnt = $f['HEAD'].$f['CONTENT'].$f['FOOTER'];
				file_put_contents($FN,$sNewCnt);				
				die("{\"result\":true,\"fname\":\"$FN\"}");
			}
			die("{\"result\":false}");
		}
		
		function getPageCnt($nocontent=false)
		{
			$fname = str_replace(array('../','..\\'),array('',''),passedVal('fname'));
			//echo GTDOCROOT.'/'.$fname;
			$arRet = array();
			if(preg_match('/^\/?gtinx/',$cdir)) 
				die( "{\"result\":false}");
			$FN = GTDOCROOT.'/'.$fname;
			if(file_exists($FN))
			{
			
				list($lnDefault,$lnUse) = $this->getLangs();
				
				if($lnUse){
					$FNx = preg_replace('/\.php$/','.'.passedVal('lang').'.php',$FN);
					if(!file_exists($FNx)){
						$arRet['message'] = base64_encode(GetMessage("MULTILANG_BASED_ON_DEFAULT"))	;
					}else
						$FN = $FNx;
				}
				$arCnt = phpFileParse(file_get_contents($FN));
				if($nocontent) unset($arCnt['CONTENT']);
				foreach($arCnt as $_k => $_v)
					$arRet[$_k] = base64_encode($_v);
				echo phpArray2JSONObject($arRet);
			}
			die();
		}
		
		function getComponentsOptions()
		{
			$sCid = preg_replace('/[^a-z0-9]/i','',$_REQUEST['id']);
			$sCcode = $_REQUEST['code'];
			$R = $this->dasmCompCaller($sCcode);
			//check if component directory exists
			if(!$R['NAME'] || !componentPresent($R['NAME']))die();
			//get available templates for this component
			
			$R['NAME'] = base64_encode($R['NAME']);
			$R['TPL'] = base64_encode($R['TPL']);
			$R['OTHER'] = base64_encode($R['OTHER']);
			echo phpArray2JSONObject($R);
			die();
			
		}
		//very dangerous moment!
		function dasmCompCaller($code){
			$code=trim($code);
			$R = array('NAME'=>'','TPL'=>'','OPTS'=>array());
			//check for sanity
			if(preg_match('/(<'.'\?|\?'.'>)/',$code)) die("{\"result\":false,\"msg\":'PHP tags not allowed'}");
			if(!preg_match('/^\$APP->IncludeComponent/',$code)) die("{\"result\":false,\"msg\":'no component call'}");
			$sc = substr(stripslashes($code),22);
			if(preg_match_all('/([a-z_0-9]+)\s*\(/',$sc,$mcs)) {
				for($x=1;$fc=$mcs[$x][0];$x++){
					if(strtolower($fc)!=='array')
						die("{\"result\":false,\"msg\":'illegal function call $fc'}");
				}
			}
			
			$M = 'begin';
			$H = array();
			//d($sc); 
			for($x = 0;$x<strlen($sc);$x++){
				$c = $sc{$x};
				//echo "$M ".htmlspecialchars($c)." $V<br />";
				switch($M){
					case 'err':
						die("{\"result\":false,\"msg\":'Parse error at pos $x'}");
					case 'begin':
						$params = array();
						$V = '';
						switch($c){
							case ' ': case "\t":case "\r": case "\n": case "(": break;
							case '"':case "'":
								$M = 'param'; break;
							default: $M = 'err'; break;
						}
						break;
					case 'param':
						switch($c){
							case '"':case "'":
								$params[] = $V; $V = '';
								$M = 'waitc'; break;
							default: $V .= $c; break;
						}
						break;
					case 'waitc':
						switch($c){
							case ' ': case "\t": case "\r": case "\n": break;
							case ',':	$M = 'waitp'; break;
							default: 	$M = 'err'; break;
						}
						break;
					case 'waitp':
						switch($c){
							case ')': $M = 'end'; break;
							case ';': $M = 'err'; break;
							case ' ': case "\t": case "\r": case "\n": break;
							case '"': case "'": $M = 'param'; break;
							default: $V .= $c; $M = 'cp'; break;
						}
						break;
					case 'cp':
						switch($c){
							case ',': $V .= $c; $M = 'waitp'; break;
							case ')':  $M = 'err'; break;
							case '(':  $M = 'cp('; $H[]=$M; 
							default: $V .= $c; break;
						}
						break;
					
					case 'cp(':
						switch($c){
							case ')': $M = 'back'; $V .= $c; break;
							case "'":case "'": $M = "cp$c"; $H[]=$M; 
							default: $V .= $c; break;
						}
						break;
					case 'cp"':
						switch($c){
							case '"': $M = 'back';
							default: $V .= $c; break;
						}
						break;
					case "cp'":
						switch($c){
							case "'": $M = 'back';
							default: $V .= $c; break;
						}
						break;
					case 'back':
						
						if(count($H)>1) {
							//rebuld $H
							$H1 = array(); foreach($H as $VV) $H1[]=$VV; $H=$H1;
							unset($H[count($H)-1]);
							$M = $H[count($H)-1]; 
						}else {
							$M = 'waitp';
							$params[]=$V; 
						}
						$x--;
						break;
					case 'end': break;
					default: 
						//echo "";
						$params[].=$V; 
				}			
			}
			$R['NAME'] = $params[0];
			$R['TPL'] = $params[1];
			$R['OTHER'] = $params[2];
			return $R;
		}
				/*END TYPES*/
		function __destruct()
		{
		}
		
		function getLangs(){
			global $APP;
			$lnDefault = $lnUse = '';
			$lang = trim(passedVal('lang'));
			if($APP->Conf('multilang_sync') && $lang!==''){
				//fetch all langs
				$allLangs = cachedQuery("SELECT `ID`,`DEFAULT` FROM `g_lang` WHERE `ENABLED`='1' ORDER BY `DEFAULT` DESC");
				
				foreach($allLangs as $xLng){
					if($xLng['DEFAULT']) {
						$lnDefault = $xLng['ID'];
						if($lang == $xLng['ID'])
							break;
					}else
					if($lang == $xLng['ID']){
						$lnUse = $lang;
						break;
					}
				}
			}
			return array($lnDefault, $lnUse);
		}
		
}
?>