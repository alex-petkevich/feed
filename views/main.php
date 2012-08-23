 <?
 $nf = @explode("|",$feed['add_urls']);
 $nfeeds = array();
 foreach($nf as $k=>$v)
	if($v!='')
		$nfeeds[] = $v;
 ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
 <head>
  <title> Intermedate server</title>
  <meta name="Generator" content="EditPlus">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <link href="main.css" rel="stylesheet" type="text/css">
	<script type="text/javascript">
		var vwd = <?=(count($nfeeds) ? count($nfeeds) : 1)?>;
		function sw_add()
		{
			if (vwd < 5)
			{
				var el = document.getElementById('nf_'+vwd);
				el.style.display='block';
				vwd++;
				if (vwd == 5)
				{
					document.getElementById('sw_feed').style.display = 'none';
				}
			}
		}
	</script>
 </head>

 <body>
 <div align="right">&nbsp;<input type="button" value="Log out" onclick="document.location='/?action=logout'"></div>
  <?if ($config_saved){?><div style="color:green;font-weight:bold;" align="center">Values saved successfully</div><?}?>
  <?if ($feed_added){?><div style="color:green;font-weight:bold;" align="center">Feed changed successfully</div><?}?>
  <?if ($feed_error){?><div style="color:red;font-weight:bold;" align="center">Error, please fill 'New feed url' value</div><?}?>
  <?if ($feed_exists){?><div style="color:red;font-weight:bold;" align="center">Error, RSS feed has already been added</div><?}?>
<table width="100%">
 <tr>
	<td valign="top">
  <FIELDSET>
	<LEGEND><?if ($action=='edit'){?>Edit<?}else{?>Add new<?}?> feed</LEGEND>
 <form method=post action="?">
<?if ($action=='edit'){?> <b><?if($_SESSION[app]){?> <?=search_in_vals($_REQUEST[tab])?> <? if($_REQUEST[cat]){?>-&gt; <?=search_in_vals($_REQUEST[cat])?> <?}?> <?}else{?><?=$feed[name]?><?}?></b><?}?>
	 <table>
   <tr>
	<td>File:</td>
	<td>
	<table>
	<tr>
		<td>App name:</td>
		<td></td>
		<td>Tab name:</td>
		<td></td>
		<td>Category:</td>
	</tr>
	<tr>
		<td> <b><?=search_in_vals($_SESSION['app'])?></b> <input type="button" value="change" onclick="document.location='/?action=napp'"></td>
		<td>-&gt;</td>
		<td><?if ($_SESSION['app']){?>
	<select name="tab" onchange="window.location='?fid=<?=$_REQUEST['fid']?>&action=<?=$_REQUEST['action']?>&app=<?=$_REQUEST['app']?>&tab='+this.value" id="tab_id">
		<option value=""></option>
		<?foreach($vals as $k=>$v) if ($v['type'] == 'tab' && $_SESSION['app'] == $v['parent_id']) {?>
		<option value="<?=$v['id']?>"<?if ($v['id'] == $_REQUEST['tab']){?> selected<?}?>><?=$v['title']?></option>
		<?}?>
	</select>	<?}?></td>
		<td>-&gt;</td>
		<td><?if ($_REQUEST['tab']){?>
	<select name="cat" id="cat_id">
		<option value=""></option>
		<?foreach($vals as $k=>$v) if ($v['type'] == 'cat' && $_REQUEST['tab'] == $v['parent_id']) {?>
		<option value="<?=$v['id']?>"<?if ($v['id'] == $_REQUEST['cat']){?> selected<?}?>><?=$v['title']?></option>
		<?}?>
	</select>
	<?}?></td>
	</tr>
	</table>

	</td>
 </tr>
<tr>
	<td>New feed url:</td>
	<td><input type="radio" name="type" value="rss"<?if (!$feed['id'] || $feed['type'] == 'rss'){?> checked<?}?>> RSS <input type="radio" name="type" value="google"<?if ($feed['type'] == 'google'){?> checked<?}?>> Google Calendar <input type="radio" name="type" value="twitter"<?if ($feed['type'] == 'twitter'){?> checked<?}?>> Twitter <br />

	<input type="text" name="username" size="30" value="<?=$feed['link']?>"> <A HREF="javascript:void(0);" onclick="sw_add();" id="sw_feed">[+] new feed</A>
	<div id="nf_1"<?if (!$nfeeds[0]){?> style="display:none"<?}?>><input type="text" name="nfeed[]" size="30" value="<?=$nfeeds[0]?>" ></div>
	<div id="nf_2"<?if (!$nfeeds[1]){?> style="display:none"<?}?>><input type="text" name="nfeed[]" size="30" value="<?=$nfeeds[1]?>" ></div>
	<div id="nf_3"<?if (!$nfeeds[2]){?> style="display:none"<?}?>><input type="text" name="nfeed[]" size="30" value="<?=$nfeeds[2]?>" ></div>
	<div id="nf_4"<?if (!$nfeeds[3]){?> style="display:none"<?}?>><input type="text" name="nfeed[]" size="30" value="<?=$nfeeds[3]?>" ></div>
	<div id="nf_5"<?if (!$nfeeds[4]){?> style="display:none"<?}?>><input type="text" name="nfeed[]" size="30" value="<?=$nfeeds[4]?>" ></div>

	</td>
 </tr>
 <tr>
	<td>Reload period:</td>
	<td><input type="text" name="password" size="5" value="<?=$feed['period']?>"> <select name="per"><option value="1">minutes</option><option value="60">hours</option></select></td>
 </tr>
  <tr>
	<td>Comments:</td>
	<td><textarea name="description"><?=$feed['description']?></textarea></td>
 </tr>
  <tr>
	<td>Parse by category:</td>
	<td><input type="text" name="parse_cat" size="30" value="<?=$feed['parse_cat']?>"></td>
 </tr>
  <tr>
	<td>Microformat from Post Data?</td>
	<td>
	<input type="checkbox" name="is_dir"<?php if($feed['is_dir']=='1'){?> checked="checked"<?php }?> value="1" onclick="if (this.checked) document.getElementById('auto_mc').style.display='block';else document.getElementById('auto_mc').style.display='none';"> Yes
	</td>
 </tr>
 <tr><td colspan="2">
 <table width="100%" id="auto_mc"<?if ($feed['is_dir']!='1'){?> style="display:none;"<?}?>>
  <tr>
	<td>&nbsp;</td>
	<td><input type="Radio" name="auto_microformat" value="0" <?if ($feed['auto_microformat']=='0'||!$feed['auto_microformat']){?> checked<?}?>> Directory&nbsp;&nbsp;&nbsp;<input type="Radio" name="auto_microformat" value="4" <?if ($feed['auto_microformat']=='4'){?> checked<?}?>> Event</td>
 </tr>
 </table></td></tr>
  <tr>
	<td>Pubdate:</td>
	<td><input type="Radio" name="pubdate" value="1" <?if ($feed['pubdate']=='1'||!$feed['pubdate']){?> checked<?}?>> <?=date('l, F j')?>&nbsp;&nbsp;&nbsp;<input type="Radio" name="pubdate" value="5"> <?=date('\a\t g:ia')?>&nbsp;&nbsp;&nbsp;<input type="Radio" name="pubdate" value="2" <?if ($feed['pubdate']=='2'){?> checked<?}?>> From Feed&nbsp;&nbsp;&nbsp;<input type="Radio" name="pubdate" value="3" <?if ($feed['pubdate']=='3'){?> checked<?}?>> Relative Date&nbsp;&nbsp;&nbsp;<input type="Radio" name="pubdate" value="4" <?if ($feed['pubdate']=='4'){?> checked<?}?>> empty</td>
 </tr>
  <tr>
	<td>Order items:</td>
	<td>
	<select name="sort">
		<option value="0"<?if (!$feed['sort']){?> selected<?}?>>Unordered</option>
		<option value="1"<?if ($feed['sort']=='1'){?> selected<?}?>>Chronological (all)</option>
		<option value="2"<?if ($feed['sort']=='2'){?> selected<?}?>>Chronological (today and future only)</option>
		<option value="2"<?if ($feed['sort']=='5'){?> selected<?}?>>Chronological (future only)</option>
		<option value="3"<?if ($feed['sort']=='3'){?> selected<?}?>>Reverse chronological</option>
		<option value="4"<?if ($feed['sort']=='4'){?> selected<?}?>>Alphabetical by title</option>
	</select>

	</td>
 </tr>
</table>
 <br>
	 <table width="100%">
	 <tr>
		<td> <input type="submit" name='newfeed' value="Save"> <?if ($action=='edit'){?><input type="button" name='can' value="Cancel" onclick="window.location='?'">  <input type="submit" name='newfeedrel' value="Save and reload">  <input type="submit" name='newfeedstart' value="Save and start"><?}?>
	 </tr>
	 </table>
<input type="hidden" name="action" value="<? if ($action=='edit'){?>editfeed<?}else{?>newfeed<?}?>">
<input type="hidden" name="fid" value="<?=$feed['id']?>">
  </form>
  </FIELDSET>
<form action="?" method="post" id='frmVal'>
<input type="hidden" name="action" value="add_value">
<input type="hidden" name="fid" value="<?=$feed['id']?>">
<input type="hidden" name="title" value="" id="p_tit">
<input type="hidden" name="type" value="" id="p_type">
<?if ($_REQUEST['app']){?>
<input type="hidden" name="app" value="<?=$_REQUEST['app']?>">
<input type="hidden" name="parent_id" value="<?=$_REQUEST['app']?>">
<?}?>
<?if ($_REQUEST['cat']){?>
<input type="hidden" name="cat" value="<?=$_REQUEST['cat']?>">
<input type="hidden" name="parent_id" value="<?=$_REQUEST['cat']?>">
<?}?>
<?if ($_REQUEST['tab']){?>
<input type="hidden" name="tab" value="<?=$_REQUEST['tab']?>">
<input type="hidden" name="parent_id" value="<?=$_REQUEST['tab']?>">
<?}?>
</form>
	</td>
	<td valign="top">
	  <FIELDSET>
	<LEGEND>Search</LEGEND>
<form method=get action="?">
	Filter links: <input type="text" name="s" value="<?=$_SESSION['s']?>" /> <input type="submit" value='go'><input type="hidden" name="action" value='search'>
</form>
  </FIELDSET>

	</td>
 </tr>
 </table>
 <?/*
 App: <b><?=search_in_vals($_SESSION['app'])?></b> <input type="button" value="Change Application" onclick="document.location='/?action=napp'">
 */?>
<?
	$feeds = get_feeds($_SESSION['app']);
	if (count($feeds))
	{
?>
<table width="100%">
<tr>
	<th>Feed Id</td>
	<th>Name</td>
	<th>Feed URL</td>
	<th>Parsed</td>
	<th>Reload (hrs)</td>
	<th>Updated at</td>
	<th>Last added items</td>
	<th>Total feed items</td>
	<th>view / compare</td>
	<th>Pause</td>
	<th>Action buttons</td>
</tr>
<?if (is_array($feeds)) foreach($feeds as $k=>$v){?>
<tr>
	<td class="tab mid<?if ($v[paused]){?> gry<?}?>"><?=$v[id]?></td>
	<td valign="top" class="tab <?if ($v[paused]){?> gry<?}?>"><?if($v[app] && $v[tab]){?> <?=search_in_vals($v[tab])?> <? if($v[cat]){?>-&gt; <?=search_in_vals($v[cat])?> <?}?> <?}else{?><?=$v[name]?><?}?></td>
	<td class="tab<?if ($v[paused]){?> gry<?}?>"><a href="<?=$v['link']?>" target="_blank"><?=wordwrap($v['link'],60,"<br/>",1)?></a><?if ($v[add_urls]){?><div style="color:#3399FF"><? $ex = explode("|",$v['add_urls']); foreach($ex as $ke=>$ve) if (!$ve) unset($ex[$ke]); echo implode("<br>",$ex);?></div><?}?></td>
	<td class="tab mid<?if ($v[paused]){?> gry<?}?>"><?if ($v[last_added] > 0 && $v[parse_cat]!='' ) { ?><a href="out.php?fid=<?=$v[id]?>">Yes</a><?}else{?>No<?}?></td>
	<td class="tab mid<?if ($v[paused]){?> gry<?}?>"><?if ($v[period]){?><?=($v[period] > 59 ? ceil($v[period]/60).'h' :'0h '.$v[period].'m' ) ?><?}else{?>Manual<?}?></td>
	<td class="tab mid<?if ($v[paused]){?> gry<?}?>"> <?if (strtotime($v[updated]) === false) { ?>never<?} else {?> <? $diff = time() - strtotime($v[updated]); $days = floor($diff/(3600*24));$hours = floor(($diff-$days*3600*24)/3600); $mins = floor(($diff-$days*3600*24-$hours*3600)/60); echo $days .' days '.$hours.'h '.$mins.'m ago' ?> <? } ?></td>
	<td class="tab mid<?if ($v[paused]){?> gry<?}?>"><?if ($v[last_added] == '-1'){?><font color="red"><b>!error!</b></font><?}else{?><?=(int)($v[last_added_past] - $v[last_added])?><?}?></td>
	<td class="tab mid<?if ($v[paused]){?> gry<?}?>"><?=(int)$v[total_count]?></td>
	<td class="tab mid"><a href="out.php?fid=<?=$v[id]?>">&laquo;&raquo;</a> / <a href="?fid=<?=$v[id]?>&action=compare">&laquo;&raquo;</a></td>
	<td class="tab mid<?if ($v[paused]){?> gry<?}?>"><?if ($v[paused]){?><a href="?fid=<?=$v[id]?>&action=pause">activate</a><?}else{?><a href="?fid=<?=$v[id]?>&action=pause">pause</a><?}?></td>
	<td class="tab mid"><a href="?fid=<?=$v[id]?>&action=edit">edit</a>&nbsp;<a href="javascript:void(0);" onclick="if (confirm('Are you sure?')) window.location='?fid=<?=$v[id]?>&action=delete';">delete</a>&nbsp;<a href="javascript:void(0);" onclick="if (confirm('Are you sure?')) window.location='?fid=<?=$v[id]?>&action=empty';">reload</a><br><a href="?action=parse_now&fid=<?=$v[id]?>">start!</a>&nbsp;<?if ($v[app]){?><a href="client.php?appl=<?=$v[app]?>">tree</a><?}?></td>
</tr>
<?}?>
</table>
<?if (count($Pages[Listing]) > 0){?>
<table width=100% align=center border=0>
   <tr>
      <td align=center>
      <?foreach ($Pages[Listing] as $it){?>
	<?if ($it[Num] == $Pages['Current']){?>
	   <b><?=$it[Show]?></b>
	<?}else{?>
	   <a href="?p=<?=$it[Num]?>"><?=$it[Show]?></a>
      <?}}?>
      </td>
   </tr>
</table>
<?}?>
<?}else{?>
<center><b>No feed was added</b></center>
<?}?>

 </body>
</html>
