<?
	include_once('config.php');
	$Perf = new perfmonitor();

	$Perf->StartMeasure("p1");
	$DB = new cMySQL();
	$DB->Connect();
	$Perf->EndMeasure("p1");

	$Perf->StartMeasure("p2");
	$link = get_feed($_REQUEST[fid]);
	if ($link['app'] && strpos(get_config('pause'),"{".$link['app']."}")!==false)
		exit;
	if ($_REQUEST['fid'])
		$sqla = " AND link_id='$_REQUEST[fid]'";
	if (isset($_REQUEST['dguid']) && is_array($_REQUEST['dguid']))
		$sqla .= " AND replace(guid,'.','') NOT IN ('".implode("','",$_REQUEST['dguid'])."')";
	$Perf->EndMeasure("p2");

	switch ((int)$link['sort'])
	{
		case 1:
			$ord = "ORDER BY pubdate";
		break;
		case 2:
			$ord = "ORDER BY pubdate";
			$sqla .= " AND to_days(pubdate)>=to_days(NOW()) ";
		break;
		case 5:
			$ord = "ORDER BY pubdate";
			$sqla .= " AND unix_timestamp(pubdate)>unix_timestamp(NOW()) ";
		break;
		case 3:
			$ord = "ORDER BY pubdate DESC";
		break;
		case 4:
			$ord = "ORDER BY title";
		break;
		default:
			$ord = "";
	}

	$Perf->StartMeasure("p3");
	$items = $DB->SelectArray("SELECT *,unix_timestamp(pubdate) as upubdate FROM items WHERE 1 $sqla $ord");
	$Perf->EndMeasure("p3");

	$Perf->StartMeasure("p4");
	if ($_REQUEST['get_v'])
	{
		echo $items[0]["id"];exit;
	}
	if ($_REQUEST['app_v'])
	{
		echo "<xml>";
		$vals = get_values();
		foreach($vals as $k=>$v)
			if ($_REQUEST['app_v'] == $v[title] && $v[type] == 'app')
			{
				$time = $DB->SelectHash("SELECT unix_timestamp(max(updated)) as tim FROM links where app='".$v[id]."'",'tim');;
				echo "<current_version>".$v[appid]."</current_version>";
				echo "<last_article_change>".($v[updated] ? $v[updated] : $time)."</last_article_change>";
			}
		echo "</xml>";
		exit;
	}
	$Perf->EndMeasure("p4");
	header("Content-type: text/xml; Charset=UTF-8");
	$Perf->StartMeasure("p5");
?>
<? echo '<?xml version="1.0" encoding="utf-8"?>';?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
<title>Feeds</title>
<link><?=FULL_URL.'out.php?fid='.$_REQUEST['fid']?></link>
<description>Feeds List</description>
<language>en-us</language>
<pubDate><?=date('r')?></pubDate>
<?
	foreach($items as $k=>$v)
	{
		switch((int)$link['pubdate'])
		{
			case 2:
				$pubdate = $v['upubdate'];
			break;
			case 3:
				$pubdate = convert_readable($v['upubdate'] ?$v['upubdate'] : time());
			break;
			case 4:
				$pubdate = "";
			break;
			case 5:
				$pubdate = ($v['upubdate'] ? date('l, F j \a\t g:ia',$v['upubdate']) : date('l, F j \a\t g:ia',time()));
			break;
			default:
				$pubdate = ($v['upubdate'] ? date('l, F j',$v['upubdate']) : date('l, F j',time()));
		}
?>
<item>
<title><?php echo ($v['title'] ? html_to_utf8($v['title']) : "No Article Title Exists")?></title>
<link><?=($v[link] ? amps($v['link']) : FULL_URL.'badlink.html')?></link>
<?
	$encl = get_enclosures($v['enclosure'],$v['description'],$v['content']);
	$addit = get_additions($v['content']);
	get_additions($v['description']);
	foreach($encl as $ke => $ve)
		echo "<enclosure url=\"$ve[url]\" length=\"$ve[length]\" type=\"$ve[type]\" />";
    $v['content'] = remove_images($v['content']);
    $v['description'] = remove_images($v['description']);
?>
<description><![CDATA[<?=($v['description'] ? checkTags(html_to_utf8($v['description'])) : ($v['content'] ? substr(checkTags(html_to_utf8($v['content'])),0,60) : 'No description exists for this article' ))?>]]></description>
<content:encoded><![CDATA[<?=( $v['content'] ? checkTags(html_to_utf8($v['content'])) : ($v['description'] ? checkTags(html_to_utf8($v['description'])) : 'No content exists
for this article'))?>]]></content:encoded>
<pubDate><?=$pubdate?></pubDate>
<guid isPermaLink="false"><?=$v['guid']?></guid>
<?if ($link['is_dir']=='1' || !$pubdate){?>
<category>directory</category>
<? }else{ if ($v['category']){?>
<?
	$cats = explode("|",$v['category']);
	foreach($cats as $kc=>$vc)
	{
?>
<category><?=amps($vc)?></category>
<?}?><?}}
foreach($addit as $ka=>$va){
	if ($ka)
	{
		preg_match("|([\S]+)|",$ka,$mt);
		echo "<".str_replace('&#8221', '', $ka).">".checkTags(html_to_utf8($va))."</".$mt[1].">\n";
	}
}

if ($link['auto_microformat']=='1')
{
	echo "<CATEGORY>Event</CATEGORY>\n";
	echo "<CLASS>PRIVATE</CLASS>\n";
	echo "<DTSTART>".$v['upubdate']."</DTSTART>\n";
	echo "<DTEND>".($v['upubdate']+3600)."</DTEND>\n";
	echo "<SUMMARY>".($v['title'] ? html_to_utf8($v['title']) : "No Article Title Exists")."</SUMMARY>\n";
}
if ($link['auto_microformat']=='2')
{
	preg_match_all("|(\+?[0-9][0-9()-\s+]{4,20}[0-9])|",$v['content'],$res);
	foreach($res[1] as $k=>$v)
	{
		echo "<TEL>".$v."</TEL>\n";
	}
}
if ($link['auto_microformat']=='3')
{
	preg_match("#(?<=/)[^\s@]+@[^\s@](?=\s$)#",$v['content'],$res);
	if($res[1])
		echo "<EMAIL>".$res[1]."</EMAIL>\n";
}

?>
<?if ($link['auto_microformat']=='4'){?>
<LOCATION><?php echo fndLocation($v['content'])?></LOCATION>
<CATEGORIES><?php echo ($v['title'] ? html_to_utf8($v['title']) : "No Article Title Exists")?></CATEGORIES>
<DTSTART><?=$v['upubdate']?></DTSTART>
<DTEND><?=($v['upubdate']+3600)?></DTEND>
<SUMMARY><?php echo ($v['title'] ? html_to_utf8($v['title']) : "No Article Title Exists")?></SUMMARY>
<CLASS>PRIVATE</CLASS>
<?}?>
</item>
<?
	}
?>
</channel>
</rss>
<?
	$Perf->EndMeasure("p5");
?>