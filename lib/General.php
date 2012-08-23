<?
###########################################################
#        PHP SCRIPT ENGINE v2.0
#        General functions
#
#        Petkevich Alexander (C) 2003
#        mrdoggy@tut.by
###########################################################

##        Debug function
function Debug($message = "",$Type='WARN',$recurse = 0,$rec_cnt = 0)
{
	$rec_cnt++;
	if (is_numeric($Type))
	{
		switch($Type)
		{
			case "1":
			$Type = "FATAL";
			break;
			default:
			$Type = "WARN";
		}
	}
	switch (DBG_MESSAGES)
	{
		case 1:
		/*            if (is_array($message))
		{

		echo "<b>Array with ".count($message)." elements</b><br>";
		echo "<table border=1 bgcolor=red align=center width=70%>\n";
		echo "<tr>\n";
		echo "<td valign=top>KEY</td>\n";
		echo "<td>VALUE</td>";
		echo "</tr>\n";
		foreach ($message as $key => $val)
		{
		echo "<tr>\n";
		echo "<td valign=top><b>$key</b></td>\n";
		echo "<td>";
		if ($rec_cnt < 7)
		Debug($val,$Type,1,$rec_cnt);
		else
		echo '_Recursion_';
		echo "&nbsp;</td>";
		echo "</tr>\n";
		}
		echo "</table>\n";
		}
		elseif(is_object($message))
		{
		echo "<table border=1>\n";
		foreach ($message as $key => $val)
		{
		echo "<tr>\n";
		echo "<td valign=top><b>$key</b></td>\n";
		echo "<td>";
		if ($rec_cnt < 7  )
		Debug($val,$Type,1,$rec_cnt);
		else
		echo '_Recursion_';
		echo "&nbsp;</td>";
		echo "</tr>\n";
		}
		echo "</table>\n";
		}
		elseif(is_null($message))
		echo "Variable is empty<br>";
		elseif(is_uploaded_file($message))
		echo "Uploaded file $message<br>";
		else
		echo $message."<br>";
		*/
		echo "<pre>";
		print_r($message);
		echo "</pre>";
		break;
		case 2:
		savelog($message);
		default:
		return;
	}
	if ($Type=='FATAL') 
	exit;
	$rec_cnt = 0;
}

##        formatting url, setting default parameters and redirect
function Redirect($link = "")
{
	if (MULTI_LANG == "1")
	{
	   global $CURRENT_LANG;
   	if (strpos($link,SERVER_NAME."?")!==false)
   	   if (strpos($link,"lng_ver")===false)
            $link = preg_replace("/(.*?)\?(.*)/","\\1?lng_ver=$CURRENT_LANG".("\\2" ? "&\\2" : ""),$link);
	   else
   	   if (strpos($link,"lng_")===false)
   	      $link = str_replace(SERVER_NAME."/",SERVER_NAME."lng_$CURRENT_LANG/",$link);
	}

	if (strlen(SID) && !preg_match("/".SID."/",$link))
	if (preg_match("/\?/",$link))
	   $link = preg_replace("/(.*?)\?(.*)/","\\1?".SID.("\\2" ? "&\\2" : ""),$link);
	else
	   $link = $link . "?" . SID;
	header("Location: $link");
	exit;
}

##        Remove directory recursivy
function RemoveDir($dir)
{
	$d = dir($dir);
	if ($d)
	{
		while(false !== ($entry = $d->read()))
		{
			if ($entry != "." && $entry != "..")
			{
				if (is_dir($dir."/".$entry))
				RemoveDir($dir."/".$entry);
				else
				unlink($dir."/".$entry);
			}
		}
		$d->close();
	}
	@rmdir($dir);
}

//        Upload file to browser
function UploadToBrowser($file,$name)
{
	$attachment = (strstr($HTTP_USER_AGENT, "MSIE")) ? "" : " attachment";
	if(strpos($HTTP_SERVER_VARS['HTTP_USER_AGENT'], 'MSIE')){
		// IE cannot download from sessions without a cache
		header('Cache-Control: public');
	}
	else
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	header("Content-Type: application/octet-stream");
	header("Content-Length: ".filesize($file));
	header("Content-Disposition: $attachment; filename=".$name);
	$fn=fopen($file, "rb");
	fpassthru($fn);
}


function sendmail($from,$to,$subj,$body,$type='txt',$attach="")
{
	if ($type == "txt")
	$mime = new MIME_mail($from, $to, $subj, "\n".$body);
	else
	{
		$mime = new MIME_mail($from, $to, $subj);
		$mime->attach($body, "", HTML, BIT7);
	}
	if ((file_exists($attach)) && ($attach!="")) {
		$mime->fattach($attach, basename($attach), OCTET);
	}
	if (DBG_EMAILS)
	savelog($mime->print_mail());
	$mime->send_mail();
}

function savelog($text)
{
	$flog = fopen(DBG_F_NAME,"a") or die("Error writting log file ".DBG_F_NAME);
	fputs($flog,"[".date("d/m/Y H:i:s")."] ".$text."\n");
	fclose($flog);
}

function file_put_content ($filename,$string) {
	@$fp = fopen($filename,"a");
	if(!$fp) {
		Return -1;
	}
	@$res = fwrite($fp,$string."\n");
	fclose($fp);
	Return $res;
}
function FormatPrice($price)
{
	$price = "$".number_format($price,2,"."," ");
	return $price;
}

//
function copyall($oldname, $newname)
{
 if(is_file($oldname)){
    $perms = fileperms($oldname);
   return copy($oldname, $newname) && chmod($newname, $perms);
 }
  else if(is_dir($oldname)){
    copyalldir($oldname, $newname);
  }
  else{
    die("Cannot copy file: $oldname (it's neither a file nor a directory)");
  } 
}
    
function copyalldir($oldname, $newname) 
{
 if(!is_dir($newname)){
    mkdir($newname);
	chmod("$newname", 0777);
  }
  $dir = opendir($oldname);
  while($file = readdir($dir)){
    if($file == "." || $file == ".."){
      continue;
   }
    copyall("$oldname/$file", "$newname/$file");
  }
  closedir($dir);
}

function getfilesize($bytes) {
   if ($bytes >= 1099511627776) {
       $return = round($bytes / 1024 / 1024 / 1024 / 1024, 2);
       $suffix = "TB";
   } elseif ($bytes >= 1073741824) {
       $return = round($bytes / 1024 / 1024 / 1024, 2);
       $suffix = "GB";
   } elseif ($bytes >= 1048576) {
       $return = round($bytes / 1024 / 1024, 2);
       $suffix = "MB";
   } elseif ($bytes >= 1024) {
       $return = round($bytes / 1024, 2);
       $suffix = "KB";
   } else {
       $return = $bytes;
       $suffix = "Byte";
   }
   if ($return == 1) {
       $return .= " " . $suffix;
   } else {
       $return .= " " . $suffix . "s";
   }
   return $return;
}

function ExtractOneValue(&$arr,$val)
{
	$Res = array();
	foreach($arr as $k=>$v)
		$Res[] = $v[$val];
	return $Res;
}

?>