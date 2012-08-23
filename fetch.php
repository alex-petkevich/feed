<?
	set_time_limit(3600);
	if ($_REQUEST['is_single'])
		$is_single = $_REQUEST['is_single'];
	if (!$is_single)
		chdir('/home/d0ma1ns/public_html/feed/');
	$deb = $_REQUEST[debug];
	include_once('config.php');
	$DB = new cMySQL();
	$DB->Connect();

//	date_default_timezone_set("UTC");
	

/////////////
//$is_single=76;


	if ($is_single)
		$feeds = $DB->SelectArray("SELECT * FROM links WHERE id='$is_single'");
	else
	{
//		$last_back = strtotime(get_config("last_backup"));
//		if (time() - $last_back > (int)get_config("backup_period")*24*3600)
//			include_once('./backup.php');
		if (time() - strtotime(get_config("out_last_purge")) > (int)get_config("out_purge_period")*24*3600)
			purge_items();
			
		$feeds = $DB->SelectArray("SELECT * FROM links WHERE paused=0 AND (unix_timestamp(NOW()) - unix_timestamp(updated) > `period` * 60 OR UNIX_TIMESTAMP(updated)=0 OR updated IS NULL OR last_added=-1) AND period > 0 AND period IS NOT NULL");
	}
	foreach($feeds as $k=>$v)
	{

		if ($v['app'] && strpos(get_config('pause'),"{".$v['app']."}")!==false)
			continue;
		$v['link'] = trim(str_replace("feed:/","http:/",$v['link']));
		empty_feed($v['id']);

		switch($v['type'])
		{
			case "google":
				$qty_fetched = fetch_google($v['link'],$v['id'],$v['parse_cat']);
			break;
			case "twitter":
				$qty_fetched = fetch_twitter($v['link'],$v['id'],$v['parse_cat']);
			break;
			default:
				$qty_fetched = fetch_info($v['link'],$v['id'],$v['parse_cat']);
		}
		if ($v['add_urls'])
		{
			$urls = explode("|",$v['add_urls']);
			foreach($urls as $ku=>$vu)
			{
				if ($vu)
				{
					$vu = trim(str_replace("feed:/","http:/",$vu));
					switch($v['type'])
					{
						case "google":
							$qty_fetched += fetch_google($vu,$v['id'],$v['parse_cat']);
						break;
						case "twitter":
							$qty_fetched += fetch_twitter($vu,$v['id'],$v['parse_cat']);
						break;
						default:
							$qty_fetched += fetch_info($vu,$v['id'],$v['parse_cat']);
					}
				}
			}
		}
		$DB->Execute("UPDATE links SET last_added_past = last_added,last_added='$qty_fetched',updated=now() WHERE id='$v[id]'");
	}


function fetch_info($link,$id,$parse_cat)
{
	global $DB,$deb;
		$i = 1;
		$qty_fetched = 0;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		curl_setopt($ch, CURLE_OPERATION_TIMEOUTED, 300);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

		$temp = curl_exec($ch);
		curl_close($ch);

//		$temp = file_get_contents($v['link']);
		$rss = @simplexml_load_string ($temp);
//print_r($rss->channel->item);exit;

		if (!$rss)
		{
			if (get_config('email'))
				mail(get_config('email'),'Input feed downloading error','I want to notify that last access to the '.$link.' was Failed.');
			$DB->Execute("UPDATE links SET last_added='-1',updated=now() WHERE id='$id'");
		}
		else
		{
				if (isset($rss->channel) && isset($rss->channel->item))
				for ($i=0;$i<count($rss->channel->item);$i++) {
					$item = $rss->channel->item[$i];
					$content = $item->children('http://purl.org/rss/1.0/modules/content/');
					$fullDescription = $content->encoded;
					$it = object2array($item);
					//$fullDescription = $rss->xpath("//item[{$i}]/content:encoded");	

					$pubDate = strtotime($it[pubDate]);
					if (!$it[guid])
					{
						$it[guid] = $id.'-'.$pubDate;
					}
					$check = $DB->SelectHash("SELECT * FROM items WHERE link_id='$id' and guid='".$it[guid]."'");
					$check['pubDate'] = date('Y-m-d H:i:s',$pubDate);
					$check['title'] = $it[title];
					$check['description'] = $it[description];
					$check['content'] = (string)$fullDescription[0][0];

					$check['link'] = $it['link'];
					if (is_array($it[category]))
						$check['category'] = implode('|',$it[category]);
					else
						$check['category'] = $it[category];
					if ($parse_cat && strpos($check['category'],$parse_cat)===false)
						continue;
					$enc = array();
					if(is_array($it[enclosure]))
					{
						foreach($it[enclosure] as $kc=>$vc)
						{
							if (substr($vc['@attributes']['type'],0,5) == 'image' && $vc['@attributes']['url']!='')
							{
								$path = pathinfo($vc['@attributes']['url']);
								$newname = substr(md5(uniqid(rand(), true)),0,10).'_'.$path['basename'];;
								$newname = download_image($vc['@attributes']['url'],$newname);
								$enc[] = FULL_URL.'images/'.$newname;
							}
							if (substr($vc['@attributes']['type'],0,5) == 'video' && $vc['@attributes']['url']!='')
							{
								$enc[] = $vc['@attributes']['url'].'*'.$vc['@attributes']['length'].'*'.$vc['@attributes']['type'];
							}
						}
						$check['enclosure'] = implode("|",$enc);
					}
					$check['link_id'] = $id;
					$check['guid'] = $it[guid];

					
					if (!$check['id'])
					{
						$check['added'] = date('Y-m-d H:i:s');
						
						$iid = $DB->Insert($check,'items');
						$check['id'] = $iid;
						// download and resize images in description
						if (preg_match_all("|<img[^>]+src\s*=[\"']([^\"']+)|",$check['description'],$matches))
						{
							foreach($matches[1] as $ki=>$vi)
							{
								$path = pathinfo($vi);
								$newname = $iid.'_'.$path['basename'];
								$newname = download_image($vi,$newname);

								$check['description'] = str_replace($vi,FULL_URL.'images/'.$newname,$check['description']);
							}

							$DB->Update($check,'items','id');
						}
						// download and resize images in content
						if (preg_match_all("|(<img[^>]+src\s*=[\"'][^\"']+[\"'].+?[^>]*?>)|",$check['content'],$matches))
						{
							foreach($matches[1] as $ki=>$vi)
							{
								preg_match("|src\s*=[\"']([^\"']+)|",$vi,$fmat);
								$path = pathinfo($fmat[1]);
								$newname = $iid.'_'.$path['basename'];
								$newname = download_image($fmat[1],$newname);
								$nvi = str_replace($fmat[1],FULL_URL.'images/'.$newname,$vi);
								$nvi = preg_replace("|width\s*=\s*[\"']\d+[\"']|","",$nvi);
								$nvi = preg_replace("|height\s*=\s*[\"']\d+[\"']|","",$nvi);
								$check['content'] = str_replace($vi,$nvi,$check['content']);
							}
							$DB->Update($check,'items','id');
						}

						$qty_fetched++;
					}
					else
						$DB->Update($check,'items','id');
//					$i++;
				}
		}
				return $qty_fetched;
}

function fetch_twitter($link,$id,$parse_cat)
{
		global $DB;
		$i = 1;
		$qty_fetched = 0;
		$temp = get_page($link);

		$rss = @simplexml_load_string ($temp);


		if (!$rss)
		{
			$guid = "error";
			$check = $DB->SelectHash("SELECT * FROM items WHERE link_id='$id' and guid='".$guid."'");
			$check['title'] = "Twitter Update Required";
			$check['content'] = "We're sorry, Twitter has changed their feed requirements.  We are working on an
update to display this section again.  For more information please contact info@mycampuscurrent.com.";
			$check['description'] = "We're sorry, Twitter has changed their feed requirements.  We are working on an
update to display this section again.  For more information please contact info@mycampuscurrent.com.";
			$check['link_id'] = $id;
			$check['guid'] = $guid;
			$check['added'] = date('Y-m-d H:i:s');
			$iid = $DB->Insert($check,'items');
		}
		else
		{
				foreach ($rss->entry as $item) {
					$it = object2array($item);
					$fullDescription = $it['content']." ".$it['author']['name'];
										

					$pubDate = strtotime($it[published]);
					if (!$it[id])
					{
						$it[id] = $id.'-'.$pubDate;
					}
					$link = "";
					$img = "";
					foreach($it['link'] as $k=>$v)
					{
						if ($v['@attributes']['type'] == 'text/html')
							$link = $v['@attributes']['href'];
						if (substr($v['@attributes']['type'],0,5) == 'image')
							$img = $v['@attributes']['href'];
					}
					$guid = $it[id];
					$check = $DB->SelectHash("SELECT * FROM items WHERE link_id='$id' and guid='".$guid."'");
					$check['pubDate'] = date('Y-m-d H:i:s',$pubDate);
					$check['title'] = substr($it[title],0,60);
					$check['description'] = $fullDescription;
					$check['content'] = $fullDescription;

					$check['link'] = $link;
					$check['category'] = "";
					$enc = array();
					if ($img)
					{
						$path = pathinfo($img);
						$newname = substr(md5(uniqid(rand(), true)),0,10).'_'.$path['basename'];;
						$newname = download_image($img,$newname);
						$enc[] = FULL_URL.'images/'.$newname;
					}
					if (count($enc))
						$check['enclosure'] = implode("|",$enc);
					$check['link_id'] = $id;
					$check['guid'] = $guid;

					if (!$check['id'])
					{
						$check['added'] = date('Y-m-d H:i:s');
						$iid = $DB->Insert($check,'items');
						$check['id'] = $iid;

						$qty_fetched++;
					}
					else
						$DB->Update($check,'items','id');
					$i++;
				}
		}

		return $qty_fetched;
}

function fetch_google($link,$id,$parse_cat)
{
	global $DB;
				$i = 1;
		$qty_fetched = 0;
		$temp = get_page($link);

		$rss = @simplexml_load_string ($temp);


		if (!$rss)
		{
			if (get_config('email'))
				mail(get_config('email'),'Input feed downloading error','I want to notify that last access to the '.$link.' was Failed.');
			$DB->Execute("UPDATE links SET last_added='-1',updated=now() WHERE id='$id'");
		}
		else
		{
				foreach ($rss->entry as $item) {
					$it = object2array($item);

					$fullDescription = $it['content'];
										

					$pubDate = strtotime($it[published]);
					if (!$it[id])
					{
						$it[id] = $id.'-'.$pubDate;
					}
					$link = $it['link'][0]['@attributes']['href'];
					$guid = ($link ? $link : $it[id]);
					$check = $DB->SelectHash("SELECT * FROM items WHERE link_id='$id' and guid='".$guid."'");
					$check['pubDate'] = date('Y-m-d H:i:s',$pubDate);
					$check['title'] = $it[title];
					$check['description'] = $it[summary];
					$check['content'] = $fullDescription;

					$check['link'] = $link;
					$check['category'] = "";
//					if ($parse_cat && strpos($check['category'],$parse_cat)===false)
//						continue;
					$enc = array();
					if(is_array($it[enclosure]))
					{
						foreach($it[enclosure] as $kc=>$vc)
						{
							if (substr($vc['@attributes']['type'],0,5) == 'image')
							{
								$path = pathinfo($vc['@attributes']['url']);
								$newname = substr(md5(uniqid(rand(), true)),0,10).'_'.$path['basename'];;
								$newname = download_image($vc['@attributes']['url'],$newname);
								$enc[] = FULL_URL.'images/'.$newname;
							}
						}
						$check['enclosure'] = implode("|",$enc);
					}
					if ($check['link'])
					{
						$images = get_google_images($check['link']);
						$enc = array();
						foreach($images as $kc=>$vc)
						{
							if ($vc)
							{
								$newname = substr(md5(uniqid(rand(), true)),0,15).'.jpg';
								$newname = download_image($vc,$newname);
								$enc[] = FULL_URL.'images/'.$newname;
							}
						}
						if (count($enc))
							$check['enclosure'] .= implode("|",$enc);
					}
					$check['link_id'] = $id;
					$check['guid'] = $guid;

					//parse additions
					$check['content'] = preg_replace("|event Status\:([^\n\r]+)|i","%% STATUS:$1 %%",$check['content']);
					$check['content'] = preg_replace("|who:([^\n\r]+)|i","%% ORGANIZER:$1 %%",$check['content']);
					$check['content'] = preg_replace("|Event Description:([^\n\r]+)|i","%% SUMMARY:$1 %%",$check['content']);
					$check['content'] = preg_replace("|When:([^\n\r<]+)|i","%% DTSTART:$1 %%",$check['content']);

					if (!$check['id'])
					{
						$check['added'] = date('Y-m-d H:i:s');
						$iid = $DB->Insert($check,'items');
						$check['id'] = $iid;
						// download and resize images in description
						if (preg_match_all("|<img[^>]+src\s*=[\"']([^\"']+)|",$check['description'],$matches))
						{
							foreach($matches[1] as $ki=>$vi)
							{
								$path = pathinfo($vi);
								$newname = $iid.'_'.$path['basename'];
								$newname = download_image($vi,$newname);

								$check['description'] = str_replace($vi,FULL_URL.'images/'.$newname,$check['description']);
							}

							$DB->Update($check,'items','id');
						}
						// download and resize images in content
						if (preg_match_all("|(<img[^>]+src\s*=[\"'][^\"']+[\"'].+?[^>]*?>)|",$check['content'],$matches))
						{
							foreach($matches[1] as $ki=>$vi)
							{
								preg_match("|src\s*=[\"']([^\"']+)|",$vi,$fmat);
								$path = pathinfo($fmat[1]);
								$newname = $iid.'_'.$path['basename'];
								$newname = download_image($fmat[1],$newname);
								$nvi = str_replace($fmat[1],FULL_URL.'images/'.$newname,$vi);
								$nvi = preg_replace("|width\s*=\s*[\"']\d+[\"']|","",$nvi);
								$nvi = preg_replace("|height\s*=\s*[\"']\d+[\"']|","",$nvi);
								$check['content'] = str_replace($vi,$nvi,$check['content']);
							}
							$DB->Update($check,'items','id');
						}

						$qty_fetched++;
					}
					else
						$DB->Update($check,'items','id');
					$i++;
				}
		}

				return $qty_fetched;
}

function get_google_images($link)
{
	$temp = get_page($link);
	if (preg_match("|has moved.+HREF=\"(.+?)\"|i",$temp,$match))
		$temp = get_page($match[1]);

	$links = array();
	preg_match_all("|(https://docs.google.com/leaf.+?)\"|",$temp,$res);
	foreach($res[1] as $k=>$v)
	{
		$page = get_page($v);
		preg_match("|(https://docs\.google\.com/uc\?.+?)\"|",$page,$fnd);
		$links[] = $fnd[1];
	}
	return $links;
}

function get_page($link)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $link);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	
	curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13");

	$temp = curl_exec($ch);
	curl_close($ch);
	return $temp;
}

?>