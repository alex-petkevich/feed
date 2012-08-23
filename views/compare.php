<?
	// get content

	$feed = get_feed($_REQUEST['fid']);
	$link = $feed['link']; //: FULL_URL.'out.php?fid='.$feed['id']);
	$link = trim(str_replace("feed:/","http:/",$link));
	$type = $feed['type'];
/*
	$file = @fopen($link,'rt');
	if (!$file)
	{
		die("Can't open $link: connection error");
	}
	$irss = "";
	while(!feof($file))
		$irss .= fgets($file);
	fclose($file);
*/
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $link);
	curl_setopt($curl, CURLOPT_HEADER, 0);
   curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	$irss = curl_exec($curl);
	if (curl_errno($curl))
	{
		die("$link: ".curl_error($curl));
	}
	curl_close($curl);

	$link = FULL_URL.'out.php?fid='.$feed['id'];
	/*
	$file = @fopen($link,'rt');
	if (!$file)
	{
		die("Can't open $link: connection error");
	}
	$orss = "";
	while(!feof($file))
		$orss .= fgets($file);
	fclose($file);
	*/
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $link);
	curl_setopt($curl, CURLOPT_HEADER, 0);
   curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	$orss = curl_exec($curl);
	if (curl_errno($curl))
	{
		die("$link: ".curl_error($curl));
	}
	curl_close($curl);



	$orssA = simplexml_load_string ($orss);
	$irssA = simplexml_load_string ($irss);


	function check_rss($text)
	{
		return preg_match("|<rss.+?</rss|is",$text);
	}

	function check_channel($text)
	{
		return preg_match("|<channel.+?</channel|is",$text);
	}
	function get_item_count($rss,$type='rss')
	{
		return ($type == 'google' ? (int)count($rss->entry) : (int)count($rss->channel->item));
	}
	function get_title_count($rss,$type='rss')
	{
		$cnt = 0;
		if ($type == 'google')
		{
			foreach ($rss->entry as $item) {
				$it = object2array($item);
				if ($it[title])
					$cnt++;
			}
		}
		else
		{
			foreach ($rss->channel->item as $item) {
				$it = object2array($item);
				if ($it[title])
					$cnt++;
			}
		}
		return $cnt;
	}
	function get_desc_count($rss,$type='rss')
	{
		$cnt = 0;
		if ($type=='google')
		{
			foreach ($rss->entry as $item) {
				$it = object2array($item);
				if ($it[summary])
					$cnt++;
			}
		}
		else
		{
			foreach ($rss->channel->item as $item) {
				$it = object2array($item);
				if ($it[description])
					$cnt++;
			}
		}
		return $cnt;
	}
	function get_guid_count($rss,$type='rss')
	{
		$cnt = 0;
		if ($type=='google')
		{
			foreach ($rss->entry as $item) {
				$it = object2array($item);
				if ($it['link'][0]['@attributes']['href'])
					$cnt++;
			}
		}
		else
		{
			foreach ($rss->channel->item as $item) {
				$it = object2array($item);
				if ($it[guid])
					$cnt++;
			}
		}
		return $cnt;
	}
	function get_content_count($rss,$type='rss')
	{
		$cnt = 0;
		$i = 1;
		if ($type=='google')
		{
			foreach ($rss->entry as $item) {
				$it = object2array($item);
				if ($it[content])
					$cnt++;
			}
		}
		else
		{
			foreach ($rss->channel->item as $item) {
				$desc = $rss->xpath("//item[{$i}]/content:encoded");
				$cont = (string)$desc[0][0];

				if ($cont)
					$cnt++;
				$i++;
			}
		}
		return $cnt;
	}
	function colorize($text)
	{
		return 
  preg_replace("|(&lt;title.*?&gt;)(.+?)(&lt;/title&gt;)|iU","$1<b>$2</b>$3",str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",nl2br(preg_replace("~(&quot;|&#039;)[^<>]*(&quot;|&#039;)~iU", '<span style="color: #DD0000">$0</span>',
  preg_replace("~&lt;!--.*--&gt;~iU", '<span style="color: #FF8000">$0</span>',
  preg_replace("~(&lt;[^\s!]*\s)([^<>]*)([/?]?&gt;)~iU", '$1<span style="color: #007700">$2</span>$3',
  preg_replace("~&lt;[^<>]*&gt;~iU", '<span style="color: #0000BB">$0</span>',
  htmlspecialchars($text,ENT_QUOTES))))))));
	}
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
 <head>
  <title> Compare</title>
  <meta name="Generator" content="EditPlus">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
 </head>

 <body>
 <a href="?">&laquo; Back</a> 
  <table width="900" align="center" border=0>
  <tr>
	<td width="47%"><b>Source</b><br> (<a href="<?=$feed['link']?>" target="_blank"><?=wordwrap($feed['link'],70,"<br />\n",1);?></a>)</td>
	<td width="6%">&nbsp;</td>
	<td width="47%"><b>Destination</b><br> (<a href="<?=FULL_URL.'out.php?fid='.$feed['id']?>" target="_blank"><?=FULL_URL.'out.php?fid='.$feed['id']?></a>)</td>
  </tr>
  <tr>
	<td><div style="width:500px;height:500px;overflow : scroll;border:1px solid #C0C0C0;font-size:10pt;" ><nobr><?=colorize($irss)?></nobr></div></td>
	<td>&nbsp;</td>
	<td><div style="width:500px;height:500px;overflow : scroll;border:1px solid #C0C0C0;font-size:10pt;"><nobr><?=colorize($orss)?></nobr></div></td>
  </tr>
  <tr>
	<td width="47%" align="center">
	<br>
	<table>
	<tr>
		<td>RSS:</td>
		<td><?=(check_rss($irss) ? "Present" : "Not Present")?></td>
	</tr>
	<tr>
		<td>Channel:</td>
		<td><?=(check_channel($irss) ? "Present" : "Not Present")?></td>
	</tr>
	<tr>
		<td>Item Count:</td>
		<td><?=get_item_count($irssA,$type)?></td>
	</tr>
	<tr>
		<td>Title:</td>
		<td><?=get_title_count($irssA,$type)?></td>
	</tr>
	<tr>
		<td>Description:</td>
		<td><?=get_desc_count($irssA,$type)?></td>
	</tr>
	<tr>
		<td>Content:</td>
		<td><?=get_content_count($irssA,$type)?></td>
	</tr>
	<tr>
		<td>Guid:</td>
		<td><?=get_guid_count($irssA,$type)?></td>
	</tr>
	</table>
	</td>
	<td width="6%">&nbsp;</td>
	<td width="47%" align="center">
	<br>
	<table>
	<tr>
		<td>RSS:</td>
		<td><?=(check_rss($orss) ? "Present" : "Not Present")?></td>
	</tr>
	<tr>
		<td>Channel:</td>
		<td><?=(check_channel($orss) ? "Present" : "Not Present")?></td>
	</tr>
	<tr>
		<td>Item Count:</td>
		<td><?=get_item_count($orssA)?></td>
	</tr>
	<tr>
		<td>Title:</td>
		<td><?=get_title_count($orssA)?></td>
	</tr>
	<tr>
		<td>Description:</td>
		<td><?=get_desc_count($orssA)?></td>
	</tr>
	<tr>
		<td>Content:</td>
		<td><?=get_content_count($orssA)?></td>
	</tr>
	<tr>
		<td>Guid:</td>
		<td><?=get_guid_count($orssA)?></td>
	</tr>
	</table>
	</td>
  </tr>

  </table>

 </body>
</html>
