<?

class GTFile {

	function __construct($iId){
		return GTFile::Get('*',array('ID'=>$iId));
	}
	function Get($arSelect,$cond){
		global $DB;
		$allowed = array(
			'ID'=>'`f`.`ID`',
			'TITLE'=>'`f`.`TITLE`',
			'DESC'=>'`f`.`DESC`',
			'REMOTE_NAME'=>'`f`.`REMOTE_NAME`',
			'LOCAL_NAME'=>'`f`.`LOCAL_NAME`',
			'LOCAL_DIR'=>'`f`.`LOCAL_DIR`',
			'MIME_TYPE'=>'`f`.`MIME_TYPE`',
			'OWNER'=>'`f`.`OWNER`',
			'ADDEDBY'=>'`f`.`ADDEDBY`',
			'CREATED'=>'`f`.`CREATED`',
			'UPDATED'=>'`f`.`UPDATED`',
			'SUBSYSTEM'=>'`f`.`SUBSYSTEM`',
			'SIZE'=>'`f`.`SIZE`',
			'TYPE'=>'`f`.`TYPE`',
			'WIDTH'=>'`f`.`WIDTH`',
			'HEIGHT'=>'`f`.`HEIGHT`',
			'URL'=>new GTTool(array('GTFile','GetUrl')),
			'HTML'=>new GTTool(array('GTFile','GetHtml')),
			'EXT'=>new GTTool(array('GTFile','GetExt')),
		);
		//determine tool-fields
		$TOOLS = array();
		
		
		//WHERE
		$WHERE = array();
		if(is_array($cond)){
			foreach($cond as $_k=>$_v){
				if(!array_key_exists($_k,$allowed)) continue; //ignore shit
				if(is_object($allowed[$_k])) continue; //ignore checks over tool-objects
				$WHERE[] = is_array($_v)
								? $allowed[$_k]." IN ('".implode("','",$_v)."')"
								: $allowed[$_k]."='$_v'";
			}
		}
		
		$allowed['*'] = $allowed; //nice hack, huh? !!!IMPORTANT - do this only after $WHERE processing
		$retFields = array();
		//$arSelect must be an array. 
		if(!is_array($arSelect))	$arSelect = explode(',',$arSelect);
		//process $arSelect for possible fields and arrays
		foreach($arSelect as $sSelect){
			$sSelect = trim($sSelect); //whitespace can be here after explode() call
			if(!array_key_exists($sSelect,$allowed)) continue; //ignore shit
			if(is_array($allowed[$sSelect])){	//get array keys as they mean fields
				foreach($allowed[$sSelect] as $_k =>$_v)
					$retFields[] = $_k;
			}else
				$retFields[] = $sSelect;
		}
		foreach($retFields as $RF){
			
			if(is_object($allowed[$RF])) //copy name to tools to avoid scanning $allowed for every field
				$TOOLS[] = $RF;
		}
		$RET = array();
		if(!empty($WHERE))
			$WHERE = "WHERE ".implode(' AND ',$WHERE);
		
		$sSql = "SELECT * FROM `g_files` `f` $WHERE";
		//d($sSql);
		$q = $DB->Query($sSql);				// COMMENT this to use cached query
		while($r = $DB->FetchAssoc($q)){	// COMMENT this to use cached query
//		$q = $DB->CachedQuery($sSql);	// UNcomment this to use cached query
//		foreach($q as $r){				// UNcomment this to use cached query
			$R = array();
			//d($r);
			foreach($retFields as $_f){
				$R[$_f] = in_array($_f,$TOOLS)
							? $allowed[$_f]->Get(array($r))	//call tool function to fill field
							: $r[$_f];	//just copy value
			}
			$RET[] = $R;
		}
		return $RET;
	}
	
	function GetUrl($arObj){
		return $arObj['LOCAL_DIR'].'/'.$arObj['LOCAL_NAME'];
	}	
	function GetExt($arObj){
		preg_match('/\.([a-z0-9]+)$/i',$arObj['REMOTE_NAME'],$em);
		return strtolower($em[1]);
	}
	
	function GetHtml($arObj){
		$url = GTFile::GetUrl($arObj);
		
		if($arObj['TYPE']=='IMAGE'){
			$T = $arObj['TITLE']?'title="'.$arObj['TITLE'].'" alt="'.$arObj['TITLE'].'"':'';
			$W = $arObj['WIDTH']?'width="'.$arObj['WIDTH'].'"':'';
			$H = $arObj['HEIGHT']?'height="'.$arObj['HEIGHT'].'"':'';
			$ret = "<img src=\"$url\" $W $H $T />";
		}else{
			$ret = "<a href=\"$sUrl\" title=\"$sTitle\">";
		}
		return $ret;
	}
	
	function AddLocal($sLocalPath,$arSaveParams = array()){
		global $DB,$APP;
		$arEditable = array('SUBSYSTEM','OWNER','DESC','TITLE');
		$ret = array();
		
		$sLocalPath = str_replace(array('../','..\\','\\'),array('','','/'),$sLocalPath);
		$sLocalPath = preg_replace('/[\/\\]{2,}/','/',$sLocalPath);
		$linuxDocroot = str_replace('\\','/',GTDOCROOT);
		
		if($sLocalPath{0}!='/') $sLocalPath = '/'.$sLocalPath;
		if(0!==strpos($linuxDocroot,$sLocalPath))
			$sLocalPathA = $linuxDocroot.'/'.$sLocalPath;
		if(! is_readable($sLocalPathA)) return false;
		
		$ret['LOCAL_NAME'] = basename($sLocalPath);
		$ret['LOCAL_DIR'] = dirname($sLocalPath);
		
		$imageTypes = explode(' ','jpg jpeg gif png bmp xbmp');
		
		preg_match('/([^\.]*)\.([a-z0-9\.]+)$/i',$ret['LOCAL_NAME'],$em);
		$fext = strtolower($em[2]);
		$remotefn0 = count($em>2)?$em[1]:$ret['LOCAL_NAME'];
		
		
		$ret['TYPE'] = in_array($fext,$imageTypes)?'IMAGE':'OTHER';
		$ret['LOCAL_DIR'] = '/medialibrary/images/'; break;
		
		$ret['CREATED'] = $ret['UPDATED'] = time();
		$ret['ADDEDBY'] = $APP->GetCurrentUserId();
		$ret['SIZE'] = filesize($sLocalPathA);
		//nasty way to determine mime type
		if(extension_loaded('fileinfo')){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$ret['MIME_TYPE'] = finfo_file($finfo, $sLocalPathA);
			finfo_close($finfo);
			if(!$ret['MIME_TYPE']) unset ($ret['MIME_TYPE']); //make database set default value
		}elseif(function_exists('mime_content_type')){
			$ret['MIME_TYPE'] = mime_content_type($sLocalPathA);
		}
		if($ret['TYPE']=='IMAGE' && function_exists('getimagesize')){
			$fsz = @getimagesize( $sLocalPathA );
			$ret['WIDTH'] = $fsz[0];
			$ret['HEIGHT'] = $fsz[1];
		}
		
		$ret['EXT'] = $fext;
		//now process external values to fullfill our record
		if(is_array($arSaveParams))
			foreach($arSaveParams as $spK=>$spV){
				if(!in_array($spK,$arEditable)) continue;
				$ret[$spK]=addslashes($spV);
			}
		
		$SQLF = "`".implode("`,\n`",array_keys($ret))."`";
		$SQLV = "'".implode("',\n'",$ret)."'";
		$SQL = "INSERT INTO `g_files` ($SQLF) VALUES ($SQLV)";
		
		//d($SQL);
		$DB->Query($SQL);
		$ret['ID'] = $DB->InsertId();
		$ret['HTML'] = GTFile::GetHtml($ret);
		$ret['URL'] = GTFile::GetUrl($ret);
		return $ret;
	}
	
	function Update($wToSet,$cond){
		//$arEditable = array('SUBSYSTEM','OWNER','DESC','TITLE');
		global $APP;
		$APP->Raise("FIXME!!! GTFile::Update is a stub!!!");
		return false;
	}
	
	function Delete($iId,$bUnlink = false){
		global $DB,$APP;
		$arCond = array('ID'=>$iId);
		if(!$APP->UserAble('SU'))
			$arCond['OWNER'] = $APP->GetCurrentUserId();
		$F = GTFile::Get('*',$arCond);
		if(empty($F)) 
			return -1;
		$F = $F[0];
		$DB->Query("DELETE FROM `g_files` WHERE `ID`='$F[ID]' LIMIT 1");
		if($bUnlink)
			@unlink(GTDOCROOT."/".$F['LOCAL_DIR'].'/'.$F['LOCAL_NAME']);
		
		return 1;
	}
	
	function ProcessPostedFiles($arSaveParams = array()){
		$arRet = array();
		foreach($_FILES as $k=>$v){
			$PF = array();
			if(is_array($v['name'])){
				foreach($v['name'] as $iFKey=>$dummy){
					$PF = array();
					foreach($v as $sFDesc=>$sFDescVal)
						$PF[$sFDesc] = $sFDescVal[$iFKey];
					
					$iNewId = GTFile::AddPostedFile($PF,$arSaveParams);
					if($iNewId) $arRet[$k][] = $iNewId;
				}
			}else{
				$PF = $v;
				$iNewId = GTFile::AddPostedFile($PF,$arSaveParams);
				if($iNewId) $arRet[$k] = $iNewId;
			}
		}
		return $arRet;
	}
	
	function AddPostedFileGroup($gName,$arSaveParams = array()){
		$v = $_FILES[$gName];
		$arRet = array();
		if(!is_array($v['name'])) return array();
		foreach($v['name'] as $iFKey=>$dummy){
			$PF = array();
			foreach($v as $sFDesc=>$sFDescVal)
				$PF[$sFDesc] = $sFDescVal[$iFKey];
			
			$iNewId = GTFile::AddPostedFile($PF,$arSaveParams);
			if($iNewId) $arRet[$iFKey] = $iNewId;
		}
		return $arRet;
	}
	
	function AddPostedFile($fDesc,$arSaveParams = array()){
		global $APP,$DB,$MIME_TYPES_R;
		$arEditable = array('SUBSYSTEM','OWNER','DESC','TITLE');
		if(!is_array($fDesc)){
			if(isset($_FILES[$fDesc]) && !empty($_FILES[$fDesc]))
				$fDesc = $_FILES[$fDesc];
			else return false;
		}
		//d($fDesc);
		$ret = array();
		$imageTypes = explode(' ','jpg jpeg gif png bmp xbmp');
		if(''==$fDesc['name'])RETURN;
		if($fDesc['error']) {
			return $APP->Raise('File '.htmlspecialchars($fDesc['name']).' upload error: '.GetMessage('UPLOAD_ERR'.$fDesc['error']));
		}
		$ret['REMOTE_NAME'] = $fDesc['name'];
		
		preg_match('/([^\.]*)\.([a-z0-9]+)$/i',$fDesc['name'],$em);
		$fext = strtolower($em[2]);
		$remotefn0 = count($em>2)?$em[1]:$fDesc['name'];
		
		
		$ret['TYPE'] = in_array($fext,$imageTypes)?'IMAGE':'OTHER';
		switch($ret['TYPE']){
			case 'IMAGE': 
				$ret['LOCAL_DIR'] = '/medialibrary/images/'; break;
			case 'OTHER': 
			default:
				$ret['LOCAL_DIR'] = '/medialibrary/files/'; break;
		}
		
		do{	//make sure such file does not exists
			$ret['LOCAL_NAME'] = (time()%1000000).'_'.rand().'_'.preg_replace('/[^a-z0-9_-]/i','_',$remotefn0).'.'.$fext;
			$localPath = GTDOCROOT.$ret['LOCAL_DIR'].$ret['LOCAL_NAME'];
		}while(file_exists($localPath));
		
		if(!move_uploaded_file($fDesc['tmp_name'],$localPath)){
			return $APP->Raise('Uploaded File '.htmlspecialchars($fDesc['name']).' cannot be moved to destination directory.');
		}
		$ret['CREATED'] = $ret['UPDATED'] = time();
		$ret['ADDEDBY'] = $APP->GetCurrentUserId();
		$ret['SIZE'] = $fDesc['size'];
		$ret['MIME_TYPE'] = $fDesc['type'];
		
		if($ret['TYPE']=='IMAGE' && function_exists('getimagesize')){
			$fsz = @getimagesize( $localPath );
			$ret['WIDTH'] = $fsz[0];
			$ret['HEIGHT'] = $fsz[1];
		}
		//now process external values to fullfill our record
		if(is_array($arSaveParams))
			foreach($arSaveParams as $spK=>$spV){
				if(!in_array($spK,$arEditable)) continue;
				$ret[$spK]=addslashes($spV);
			}
		
		$SQLF = "`".implode("`,\n`",array_keys($ret))."`";
		$SQLV = "'".implode("',\n'",$ret)."'";
		$SQL = "INSERT INTO `g_files` ($SQLF) VALUES ($SQLV)";
		
		//d($SQL);
		$DB->Query($SQL);
		$ret['EXT'] = $fext;
		$ret['ID'] = $DB->InsertId();
		$ret['HTML'] = GTFile::GetHtml($ret);
		$ret['URL'] = GTFile::GetUrl($ret);
		return $ret;
	}


};

$MIME_TYPES = array (
'application/andrew-inset'        =>  'ez',
'application/mac-binhex40'        =>  'hqx',
'application/mac-compactpro'      =>  'cpt',
'application/msword'              =>  'doc',
'application/octet-stream'        =>  'bin dms lha lzh exe class so dll',
'application/oda'                 =>  'oda',
'application/pdf'                 =>  'pdf',
'application/postscript'          =>  'ai eps ps',
'application/smil'                =>  'smi smil',
'application/vnd.ms-excel'        =>  'xls',
'application/vnd.ms-powerpoint'   =>  'ppt',
'application/vnd.wap.wbxml'       =>  'wbxml',
'application/vnd.wap.wmlc'        =>  'wmlc',
'application/vnd.wap.wmlscriptc'  =>  'wmlsc',
'application/x-bcpio'             =>  'bcpio',
'application/x-cdlink'            =>  'vcd',
'application/x-chess-pgn'         =>  'pgn',
'application/x-cpio'              =>   'cpio',
'application/x-csh'               =>   'csh',
'application/x-director'          =>   'dcr dir dxr',
'application/x-dvi'               =>   'dvi',
'application/x-futuresplash'      =>   'spl',
'application/x-gtar'              =>   'gtar',
'application/x-gzip'              =>   'gz',
'application/x-hdf'               =>   'hdf',
'application/x-javascript'        =>   'js',
'application/x-koan'              =>   'skp skd skt skm',
'application/x-latex'             =>   'latex',
'application/x-netcdf'            =>   'nc cdf',
'application/x-sh'                =>   'sh',
'application/x-shar'              =>   'shar',
'application/x-shockwave-flash'   =>   'swf',
'application/x-stuffit'           =>   'sit',
'application/x-sv4cpio'           =>   'sv4cpio',
'application/x-sv4crc'            =>   'sv4crc',
'application/x-tar'               =>   'tar',
'application/x-tcl'               =>   'tcl',
'application/x-tex'               =>   'tex',
'application/x-texinfo'           =>   'texinfo texi',
'application/x-troff'             =>   't tr roff',
'application/x-troff-man'         =>   'man',
'application/x-troff-me'          =>   'me',
'application/x-troff-ms'          =>   'ms',
'application/x-ustar'             =>   'ustar',
'application/x-wais-source'       =>   'src',
'application/xhtml+xml'           =>   'xhtml xht',
'application/xml'                 =>   'xml',
'application/zip'                 =>   'zip',
'audio/basic'                     =>   'au snd',
'audio/midi'                      =>   'mid midi kar',
'audio/mpeg'                      =>   'mpga mp2 mp3',
'audio/x-aiff'                    =>   'aif aiff aifc',
'audio/x-mpegurl'                 =>   'm3u',
'audio/x-pn-realaudio'            =>   'ram rm',
'audio/x-pn-realaudio-plugin'     =>   'rpm',
'audio/x-realaudio'               =>   'ra',
'audio/x-wav'                     =>   'wav',
'chemical/x-pdb'                  =>   'pdb',
'chemical/x-xyz'                  =>   'xyz',
'image/bmp'                       =>   'bmp',
'image/gif'                       =>   'gif',
'image/ief'                       =>   'ief',
'image/jpeg'                      =>   'jpeg jpg jpe',
'image/png'                       =>   'png',
'image/tiff'                      =>   'tiff tif',
'image/vnd.djvu'                  =>   'djvu djv',
'image/vnd.wap.wbmp'              =>   'wbmp',
'image/x-cmu-raster'              =>   'ras',
'image/x-portable-anymap'         =>   'pnm',
'image/x-portable-bitmap'         =>   'pbm',
'image/x-portable-graymap'        =>   'pgm',
'image/x-portable-pixmap'         =>   'ppm',
'image/x-rgb'                     =>   'rgb',
'image/x-xbitmap'                 =>   'xbm',
'image/x-xpixmap'                 =>   'xpm',
'image/x-xwindowdump'             =>   'xwd',
'model/iges'                      =>   'igs iges',
'model/mesh'                      =>   'msh mesh silo',
'model/vrml'                      =>   'wrl vrml',
'text/css'                        =>   'css',
'text/html'                       =>   'html htm',
'text/plain'                      =>   'asc txt'  ,
'text/richtext'                   =>   'rtx',
'text/rtf'                        =>   'rtf',
'text/sgml'                       =>   'sgml sgm',
'text/tab-separated-values'       =>   'tsv',
'text/vnd.wap.wml'                =>   'wml',
'text/vnd.wap.wmlscript'          =>   'wmls',
'text/x-setext'                   =>   'etx',
'text/xml'                        =>   'xml xsl',
'video/mpeg'                      =>   'mpeg mpg mpe',
'video/quicktime'                 =>   'qt mov',
'video/vnd.mpegurl'               =>   'mxu',
'video/x-msvideo'                 =>   'avi',
'video/x-sgi-movie'               =>   'movie',
'x-conference/x-cooltalk'         =>   'ice'
);

//set reverse map
foreach($MIME_TYPES as $__n => $__o) $MIME_TYPES_R[$__o] = $__n;

?>