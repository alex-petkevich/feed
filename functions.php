<?
/////	functions	///////
	function auth()
	{
		global $DB;
		if (!$_SESSION['user'] && !$_REQUEST['login']) {
			require_once('views/login.php');
			exit;
		}
		if ($_REQUEST['login'])
		{
			$username = get_config('username');
			$password = get_config('password');
			if ($_REQUEST['user'] == $username && $_REQUEST['password'] == $password &&$_REQUEST['user']&&$_REQUEST['password'])
				$_SESSION['user'] = $_REQUEST['user'] ;
			else
			{
				$error = 1;
				require_once('views/login.php');
				exit;
			}
		}
	}

	function get_config($name)
	{
		global $DB;
		$name = $DB->Prepare($name);
		$res = $DB->SelectHash("Select `value` from config WHERE `name`='$name'",'value');
		return $res;
	}

	function set_config($name,$value)
	{
		global $DB;
		$name = $DB->Prepare($name);
		$value = $DB->Prepare($value);
		$ex = $DB->SelectHash("Select count(*) as cnt from config where `name`='".$name."'",'cnt');
		if ((int)$ex > 0)
			$SQL = "update config set value='$value' where name='$name'";
		else
			$SQL = "insert into config values('$name','$value')";
		$DB->Execute($SQL);
	}

	function add_feed($values)
	{
		global $DB;
/*		$ex = $DB->SelectHash("Select count(*) as cnt from links where `link`='".$feed."'",'cnt');
		if ($ex)
			return false;
		$values = array(
				'description' => $desc,
				'name' => $name,
				'link' => $feed,
				'period' => (int)$time,
				'created' => date('Y-m-d H:i:s')
			);
		$id = $DB->Insert($values,'links');
*/
		$values['created'] = date('Y-m-d H:i:s');
		$id = $DB->Insert($values,'links');
		return true;
	}

	function edit_feed($feed)
	{
		global $DB;
//		$ex = $DB->SelectHash("Select count(*) as cnt from links where `link`='".$feed['link']."'",'cnt');
//		if ($ex)
//			return false;
//print_r($feed);exit;
		$id = $DB->Update($feed,'links','id');
		return true;
	}

	function get_feeds($app=0)
	{
		global $DB,$Pages;
		$sqla = "where 1";
		if ($_SESSION['s'])
		{
			$s = $_SESSION['s'];
			$sqla .= " and name like '%$s%' or description like '%$s%' or `link` like '%$s%'";
		}
		if ($app)
			$sqla .= " and app='$app'";

		$res = $DB->SelectArray("select links.*,va.title as appt,vt.title as tabt,vc.title as catt from `links` left join vals va on va.id=links.app  left join vals vt on vt.id=links.tab left join vals vc on vc.id=links.cat $sqla order by appt,vc.priority");
		$mr = array();
		if(is_array($res))
		foreach($res as $k=>$v)
		{
			if ($v['sort'] =='2')
				$v['total_count'] = $DB->SelectHash("SELECT COUNT(*) as CNT from items WHERE link_id='$v[id]' AND to_days(pubdate)>=to_days(NOW())",'CNT');
			else
				$v['total_count'] = $DB->SelectHash("SELECT COUNT(*) as CNT from items WHERE link_id='$v[id]'",'CNT');
			$mr[$v['tabt']][] = $v;
		}
		$res = array();
		foreach($mr as $k=>$v)
			$res = array_merge($res,$v);
		return $res;
	}

	function get_items($feed)
	{
		global $DB,$Pages;
		return $DB->SelectArray("select * from `items` where link_id='$feed' order by pubdate desc");
	}

	function get_item($id)
	{
		global $DB,$Pages;
		return $DB->SelectHash("select i.*,l.app as app,l.tab as tab,l.cat as cat from `items` i,links l where i.id='$id' and l.id=i.link_id");
	}

	function get_last_item($id)
	{
		global $DB,$Pages;
		$it = $DB->SelectHash("select id,max(added),link_id from items where link_id='$id' group by link_id");
//		echo "select id,max(added),link_id from items where link_id='$id' group by link_id";exit;
		return get_item($it['id']);
	}

	function del_feed($id)
	{
		global $DB;
		$id = $DB->Prepare($id);
		$items = $DB->SelectRequest(" select id from `items` where link_id='".$id."'");
		$to_del = array();
		while($row = $DB->SQLNext($items))
		{
			$to_del[] = $row[0];
		}
		$dir = opendir(FULL_PATH.'images/');
		while($file = readdir($dir)){
			 if($file == "." || $file == ".."){
				continue;
			  list($iid,$est) = explode("_",$file);
			  if (in_array($iid,$to_del))
				  @unlink(FULL_PATH.'images/'.$file);
			}
		}
		closedir($dir);

		$DB->Execute("delete from `links` where id='".$id."'");
		$DB->Execute("delete from `items` where link_id='".$id."'");
	}
	function empty_feed($id)
	{
		global $DB;
		$id = $DB->Prepare($id);
		$items = $DB->SelectRequest(" select id from `items` where link_id='".$id."'");
		$to_del = array();
		while($row = $DB->SQLNext($items))
		{
			$to_del[] = $row[0];
		}
		$dir = opendir(FULL_PATH.'images/');
		while($file = readdir($dir)){
			 if($file == "." || $file == ".."){
				continue;
			  list($iid,$est) = explode("_",$file);
			  if (in_array($iid,$to_del))
				  @unlink(FULL_PATH.'images/'.$file);
			}
		}
		closedir($dir);

		$DB->Execute("delete from `items` where link_id='".$id."'");
	}
	function get_feed($id)
	{
		global $DB;
		$id = $DB->Prepare($id);
		return $DB->SelectHash("select * from `links` where id='".$id."'");
	}

	function crop_image($filename)
	{
		 $lst=GetImageSize($filename);
		 $image_width=$lst[0];
		 $image_height=$lst[1];
		 $image_format=$lst[2];

		 if (!$image_height || !$image_width)
			 return '';

		$new_width= get_config('image_width');
		$new_height= get_config('image_height');

		 if ($image_width<$image_height) {
		  $image_height=(int)($image_height*($new_width/$image_width));
		  $image_width=$new_width;
		 }
		 else {
		  $image_width=(int)($image_width*($new_height/$image_height));
		  $image_height=$new_height;
		 }

		 if ($image_format==1) {
		  $old_image=imagecreatefromgif($filename);
		 } elseif ($image_format==2) {
		  $old_image=imagecreatefromjpeg($filename);
		 } elseif ($image_format==3) {
		  $old_image=imagecreatefrompng($filename);
		 } else {
		  return;
		 }
		 $new_image=imageCreateTrueColor($image_width, $image_height);
		 $white = ImageColorAllocate($new_image, 255, 255, 255);
		 ImageFill($new_image, 0, 0, $white);
		 imagecopyresampled( $new_image, $old_image, 0, 0, 0, 0, $image_width, $image_height, imageSX($old_image), imageSY($old_image));

       $th_image = imagecreatetruecolor($new_width, $new_height);
       // cut out a rectangle from the resized image and store in thumbnail

		 if ($image_width>$image_height)
			imagecopy ($th_image, $new_image, 0, 0, ($image_width-$new_width)/2, 0, $image_width, $image_height);
		 else
			imagecopy ($th_image, $new_image, 0, 0, 0, ($image_height-$new_height)/2, $image_width, $image_height);

		 $path = pathinfo($filename);
		 $newname =str_replace(".".$path['extension'],'_c.'.$path['extension'],$filename) ;
		 if ($image_format==1) {
			 imagegif($th_image,$newname);
		 } elseif ($image_format==2) {
			 imageJpeg($th_image,$newname);
		 } elseif ($image_format==3) {
			 imagepng($th_image,$newname);
		 }
		 return $newname;
	}

	function download_image($vi,$newname)
	{
		if (!file_exists(FULL_PATH.'images/'.$newname) && preg_match("/^http\:/",trim($vi)))
		{

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $vi);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 300);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13");
			$temp = curl_exec($ch);
			curl_close($ch);

			$sfile = fopen(FULL_PATH.'images/'.$newname,'wb');
			@fwrite($sfile,$temp);

			fclose($sfile);
/*
			$file = @fopen($vi,'rb');
			$sfile = fopen(FULL_PATH.'images/'.$newname,'wb');
			while(!@feof($file))
				@fwrite($sfile,fread($file,4096));
			fclose($sfile);
			fclose($file);
*/
			$nname = crop_image(FULL_PATH.'images/'.$newname);
			@unlink(FULL_PATH.'images/'.$newname);
			$path = pathinfo($nname);
			$newname = $path['basename'];
		}
		return $newname;
	}

function object2array($object)
{
  $return = NULL;

  if(is_array($object))
  {
      foreach($object as $key => $value)
          $return[$key] = object2array($value);
  }
  else
  {
      $var = @get_object_vars($object);

      if($var)
      {
          foreach($var as $key => $value)
              $return[$key] = object2array($value);
      }
      else
          return @strval($object); // strval and everything is fine
  }
  return $return;
}

  function GetTableStruct($table)
  {
			 $struct = $GLOBALS['DB']->SelectHash("SHOW CREATE TABLE `".$table."`;");
			 $struct['Create Table'] = str_replace("`","",$struct['Create Table']);
			 return $struct['Create Table'];
  }

  function purge_items()
  {
		global $DB;
	  $to = get_config("out_purge_period");
	  $DB->Execute("DELETE FROM items WHERE TO_DAYS(NOW()) - TO_DAYS(added) > $to");
	  $dir = opendir(FULL_PATH.'images/');
	  while($file = readdir($dir)){
		 if($file == "." || $file == ".."){
			continue;
		  $time = filemtime(FULL_PATH.'images/'.$file);
		  if (time() - $time > $to*24*3600)
			  @unlink(FULL_PATH.'images/'.$file);
		}
	  }
	  closedir($dir);
	  set_config('out_last_purge',date('Y-m-d H:i:s'));
  }

  function get_value($id)
  {
		global $DB;
		$res = $DB->SelectHash("select * from `vals` where id='$id'");
		return $res;
  }

  function get_values($app=0,$tab=0,$ord=0)
  {
		global $DB;
		$sql = "select * from `vals` where 1 ";
		if ($app)
		  $sql .= " and parent_id='$app' and `type`='tab'";
		if ($tab)
		  $sql .= " and parent_id='$tab' and `type`='cat'";
		if ($ord)
			$sql .= " order by title";
		else
			$sql .= " order by priority";
		$res = $DB->SelectArray($sql);
		return $res;
  }

  function search_in_vals($id)
  {
	  return $GLOBALS[DB]->SelectHash("Select title from vals where id='$id'",'title');
  }

  function add_value($arr)
  {
	 return $GLOBALS[DB]->Set($arr,'vals','id');
  }
  function del_value($id)
  {
	  $GLOBALS[DB]->Execute("delete from vals where id='$id'");
  }

function html_to_utf8 ($data)
{
//	$data = strip_tags($data,"<strong><ul><ol><li><em><strike><u></u>");
	$data = preg_replace("|&#8211;|","-",$data);
	$data = preg_replace("|(&#\d+;)+|","",$data);
	$data = preg_replace("|[^a-zA-Z0-9\s;!@#$%\^&*\(\)_\-+\|/\?,.<>`~'\"=\[\]]\:+|","",$data);
//	$data = preg_replace("|(&#\d+;)+|","",$data);
//	$data = normalize($data);
	$data = str_replace("http//","http://",$data);
	return preg_replace("|[\n\r]+|","",charset_decode_utf_8(amps($data)));

//return str_replace("&","&amp;",preg_replace("/\\&\\#([0-9]{3,10})\\;/e", '_html_to_utf8("\\1")', str_replace("&amp;","&",$data)));
}

function amps($data)
{
	return str_replace("&","&amp;", str_replace("&amp;","&",$data));
}
function charset_decode_utf_8 ($string) {
      /* Only do the slow convert if there are 8-bit characters */
    /* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */
    if (! ereg("[\200-\237]", $string) and ! ereg("[\241-\377]", $string))
        return $string;

    // decode three byte unicode characters
    $string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",
    "'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'",
    $string);

    // decode two byte unicode characters
    $string = preg_replace("/([\300-\337])([\200-\277])/e",
    "'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'",
    $string);

    return $string;
}
function _html_to_utf8 ($data)
{
	$t = 1;
	/*
	if (mb_detect_encoding($data, "UTF-8") == "UTF-8")
		return $data;
	else
		return utf8_encode($data);
*/
//return utf8_encode($data);
return $data;
if ($data > 127)
{
$i = 5;
while (($i--) > 0)
{
if ($data != ($a = $data % ($p = pow(64, $i))))
{
$ret = chr(base_convert(str_pad(str_repeat(1, $i + 1), 8, "0"), 2, 10) + (($data - $a) / $p));
for ($i; $i > 0; $i--)
$ret .= chr(128 + ((($data % pow(64, $i)) - ($data % ($p = pow(64, $i - 1)))) / $p));
break;
}
}
}
else
$ret = "&#$data;";
return $ret;
}

function normalize($string) {
    $ext = array(192, 193, 194, 195, 196, 197, 224, 225, 226, 227, 228, 229, 199, 231, 200, 201, 202, 203, 232, 233, 234, 235, 204, 205, 206, 207, 236, 237, 238, 239, 210, 211, 212, 213, 214, 216, 242, 243, 244, 245, 246, 248, 209, 241, 217, 218, 219, 220, 249, 250, 251, 252, 221, 255, 253);

    $norm = array(65, 65, 65, 65, 65, 65, 97, 97, 97, 97, 97, 97, 67, 99, 69, 69, 69, 69, 101, 101, 101, 101, 73, 73, 73, 73, 105, 105, 105, 105, 79, 79, 79, 79, 79, 79, 111, 111, 111, 111, 111, 111, 78, 110, 85, 85, 85, 85, 117, 117, 117, 117, 89, 121, 121);

    $string = utf8tounicode($string);
    // Using array insersect is slower
    foreach ($ext as $k => $e) {
        if ($pos = array_search($e, $string)) {
            $string[$pos] = $norm[$k];
        }
    }
    $string = unicodetoutf8($string);
    return $string;
}

function get_enclosures($enc,$desc,$content)
{
	$res = array();
	if ($enc) {
		$encl = explode("|",$enc);
		foreach($encl as $kc=>$vc)
		{
			list($url,$len,$type) = explode('*',$vc);
			if ($type!='')
				$res[] = array('url'=>$url,'length'=>$len,'type'=>$type);
			else
			{
				$path = pathinfo($url);
				$type = $path['extension'];
				if ($type == 'jpg')
					$type = 'jpeg';
				$res[] = array('url'=>$url,'length'=>remotefsize($url),'type'=>'image/'.$type);
			}
		}
	}
	else
	{
		$img = "";$type="";
		if (preg_match_all("|<img[^>]+src\s*=[\"']([^\"']+)|i",$desc,$matches))
		{
			foreach($matches[1] as $ki=>$vi)
			{
				$path = pathinfo($vi);
				$image = $path['basename'];
				$img = $vi;
				$type = $path['extension'];
				if ($type == 'jpg')
					$type = 'jpeg';
				$len = remotefsize($img);
			}
		}
		// download and resize images in content
		if (!$img && preg_match_all("|<img[^>]+src\s*=[\"']([^\"']+)[\"'].+?[^>]*?>|i",$content,$matches))
		{
			foreach($matches[1] as $ki=>$vi)
			{
				$path = pathinfo($vi);
				$image = $path['basename'];
				$img = $vi;
				$type = $path['extension'];
				if ($type == 'jpg')
					$type = 'jpeg';
				$len = remotefsize($img);
			}
		}
		if ($img)
		{
			$res[] = array('url'=>$img,'length'=>$len,'type'=>'image/'.$type);
		}
	}
	return $res;
}
   function remove_images($text) {
       return preg_replace("|(<a.+?>)?\s*<img.+?>\s*(</a>)?|i","",$text);
   }

   function remotefsize($url) {
       $sch = parse_url($url, PHP_URL_SCHEME);
       if (($sch != "http") && ($sch != "https") && ($sch != "ftp") && ($sch != "ftps")) {
           return false;
       }
       if (($sch == "http") || ($sch == "https")) {
           $headers = get_headers($url, 1);
           if ((!array_key_exists("Content-Length", $headers))) { return false; }
           return $headers["Content-Length"];
       }
       if (($sch == "ftp") || ($sch == "ftps")) {
           $server = parse_url($url, PHP_URL_HOST);
           $port = parse_url($url, PHP_URL_PORT);
           $path = parse_url($url, PHP_URL_PATH);
           $user = parse_url($url, PHP_URL_USER);
           $pass = parse_url($url, PHP_URL_PASS);
           if ((!$server) || (!$path)) { return false; }
           if (!$port) { $port = 21; }
           if (!$user) { $user = "anonymous"; }
           if (!$pass) { $pass = "phpos@"; }
           switch ($sch) {
               case "ftp":
                   $ftpid = ftp_connect($server, $port);
                   break;
               case "ftps":
                   $ftpid = ftp_ssl_connect($server, $port);
                   break;
           }
           if (!$ftpid) { return false; }
           $login = ftp_login($ftpid, $user, $pass);
           if (!$login) { return false; }
           $ftpsize = ftp_size($ftpid, $path);
           ftp_close($ftpid);
           if ($ftpsize == -1) { return false; }
           return $ftpsize;
       }
   }

   function checkTags($text)
   {
	   $skip_list = array("img","br");
		preg_match_all("|<([^/]+?)[>\s]|",$text,$otags);
		preg_match_all("|</([^\s>]+?)>|",$text,$ctags);
		$tags_1 = array();
		$tags_2 = array();
		foreach($otags[1] as $k=>$v)
			$tags_1[trim($v)]++;
		foreach($ctags[1] as $k=>$v)
			$tags_2[trim($v)]++;
		foreach($tags_1 as $k=>$v)
	    {
			if (in_array($k,$skip_list))
				continue;
			if ($v != (int)$tags_2[$k])
			{
				$diff = (int)$tags_2[$k] - (int)$v;
				if ($diff > 0)
					$text = preg_replace("|</$k.*?>|","",$text,$diff);
				elseif ($diff < 0)
					$text = preg_replace("|<$k.*?>|","",$text,$diff);
			}
		}
		$text = preg_replace("|location\s*:\s*([^\.\n\r]+)|is",'',$text);
		return $text;
   }

	function fndLocation($text) 
	{
		if (preg_match("|location\s*:\s*([^\.\n\r]+)|is",$text,$res))
			return $res[1];
		return "To be determined";
	}

   function get_additions(&$text)
   {
	  $res = array();
	  preg_match_all("|%%(.+?):(.+?)%%|",$text,$mat);

	  foreach($mat[1] as $k=>$v)
	  {
		  $key = str_replace(';','',trim($v));
		  $val = trim($mat[2][$k]);
		  if (preg_match("|^dt|i",$key) && !preg_match("/^\d+$/",$val))
		  {
			  $time = strtotime($val);
			  $val = $time;
		  }
		  if(preg_match('|=\s*[^"](.+)|',$key,$mat))
		  {
			  $key=str_replace('=','="',$key).'"';
		  }
		  $res[$key] = strip_tags($val);
	  }
	  $text = preg_replace("|%%(.+?)%%|","",$text);
	  return $res;
   }

	function convert_readable($time)
	{
		if (date("mdY") == date("mdY",$time))
			return "today";
		if (date("mdY",time+3600*24) == date("mdY",$time))
			return "tomorrow";
		if (date("mdY",time-3600*24) == date("mdY",$time))
			return "yesterday";

		if (date("W") == date("W",$time))
			return 'this '.date("l",$time);
		if (date("W",time()+3600*24*7) == date("W",$time))
			return 'next '.date("l",$time);
		if (date("W",time()-3600*24*7) == date("W",$time))
			return 'last '.date("l",$time);

		return date('l, F j',$time);

	}

	function cmpAlerts($a,$b)
	{
       $al = strtolower($a[title]);
       $bl = strtolower($b[title]);
       if ($al == $bl) {
           return 0;
       }
       return ($al > $bl) ? +1 : -1;
	}

?>