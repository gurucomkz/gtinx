<?

class GMail
{

	function makeMessage($shablonText, $shablon)
	{
		global $CONF;
		$messageText = '';
		
		if(!$shablon["fromName"]) $shablon["fromName"] = $CONF["FROM_NAME"];
		if(!$shablon["fromAddress"]) $shablon["fromAddress"] = $CONF["FROM_ADDR"];
		
		$randstr = md5(rand());
		
		$headers = "Reply-To: <support@dnr.kz>\r\n".
				"From: \"$shablon[fromName]\" <$shablon[fromAddress]> \r\n".
				"To: \"$shablon[toName]\" <$shablon[toAddress]> \r\n".
				"MIME-Version: 1.0\r\n".
				"Content-Type: multipart/alternative; boundary=\"----=_$randstr.GTINX\"\r\n".
				"X-Priority: 3 (Normal)\r\n".
				"Importance: Normal\r\n".
				"X-MimeOLE: Produced By GTINX";
		$body = 
				"This is a multi-part message in MIME format.\r\n\r\n".
				"------=_$randstr.GTINX\r\n".
				"Content-Type: text/plain; charset=\"windows-1251\"\r\n".
				"Content-Transfer-Encoding: quoted-printable\r\n\r\n";
				
		$body .= GMail::transCode('win', 'win', GMail::replaceMacros($shablonText, $shablon), true);
		$body.= "\r\n\r\n".
				"------=_$randstr.GTINX--\r\n".
				"\r\n.\r\n";
		

		return array($headers,$body);
	}

	function replaceMacros($shablonText, $shablon)
	{
		// Макросо подстановщик, который заменяет в shablonText все макросы по карте shablon
		$i0=0; $i1=0; $len=0;
		$text= $ret="";
		
		$text = $shablonText;
		$len = strlen($shablonText);
		while(($i1=strpos($text,"<",$i0))!==FALSE)
		{
			$ret .= substr($text,$i0, $i1-$i0);
			$i0=$i1+1;
			$i1 = strpos($text,">", $i0);
			if($i1===FALSE)
			{
				//Report(LL_WARN, "Gluky v stroke shablona($shablon[shablonName]). Ne naydeno'>'");
				$i0++;
				continue;
			}
			$ret .= $shablon[substr($text,$i0, $i1-$i0)];
			$i0 = $i1+1;
		}
		$ret .= substr($text,$i0, $len-$i0);
		$ret .= "\r\n";
		return $ret;
	}

	function sendMessage($shablon)	// Берет из базы шаблон для отправки сообщения. Корректирует макросы. Отправляет письмо
	{
		global $TABLES,$DB;
		//shablon:	shablonName - имя шаблона
		//		fromName	- имя отправителя
		//		fromAddress	- адрес отправителя
		//		toName	- имя получателя
		//		toAddress	- адрес получателя
		$repString = '';
		$shablonQuery = '';
		
		{
			//Запрос на текст шаблона
			$shablonTres = $DB->query("SELECT templ_id, templ_name, templ_text, temp_subject FROM `$TABLES[mailtemplates]` WHERE templ_name='$shablon[shablonName]'");
			
			if(!$DB->numRows($shablonTres))
			{
				//Report(LL_CRASH,"Message template($shablon[shablonName]) not found");
			}
			else
			{
				// если заголовок не указан прямо, то он может быть взят из БД
				$shablonRow = $DB->fetchArray($shablonTres);
				$shablon["subject"] = $shablonRow['temp_subject'];
				list($additional_headers,$body) = GMail::makeMessage($shablonRow['templ_text'], $shablon);
				if(mail($shablon["toAddress"],	
					GMail::transCode('win', 'win', $shablon["subject"], true,true),
					$body,
					$additional_headers))
				//Report(LL_NOTICE, "Message '$shablon[shablonName]' for <$shablon[toAddress]> send successfully");
				
			}
		}
		return $body;
	}

	function myMail($subj, $msg, $to, $enc = 'win', $ctype='text\plain', $transcode = FALSE, $cc = '', $bcc = '') {
		$encs = array('win' => 'windows-1251',
					'koi' => 'koi8-r',
					'lat' => 'iso-8859-15');

		$addhdr .= "Content-Type: $ctype; charset=\"$encs[$enc]\"\r\n";
		if ($transcode && $transcode!=$enc) {
			$subj = GMail::transCode($enc, $transcode, $subj);
			$msg = GMail::transCode($enc, $transcode, $msg);
		}
		$tox='';
		if(is_array($to)) {
			foreach($to as $pers) $tox .= ($tox!='')?', '.$pers:$pers;
		}else{$tox = $to;}

		mail( $tox, $subj, $msg , $addhdr , $additional_parameters);

	}
}


?>