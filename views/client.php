<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
 <head>
  <title> Intermedate server - Client Area</title>
  <meta name="Generator" content="EditPlus">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <link href="main.css" rel="stylesheet" type="text/css">

 </head>

 <body>

 <center>
 App: <b><?=search_in_vals($_SESSION['c_app'])?></b> <input type="button" value="Change Application" onclick="document.location='?action=napp'">

<?
	$feeds = get_feeds($_SESSION['c_app']);
	if (count($feeds))
	{
		$ctab = 0;
?>
<table width="100%">
<tr>
	<th><img src="img/tab.png" width="16" height="16" border="0" alt=""> Tab</td>
	<th><img src="img/cat.png" width="16" height="16" border="0" alt=""> Category</td>
	<th>Feed In</td>
	<th>Feed Out</td>
	<th>Freq</td>
	<th>Last</td>
	<th>Next</td>
	<th>Actions</td>
	<?if ($_SESSION['user']){?>
	<th>Admin</td>
	<?}?>
</tr>
<?if (is_array($feeds)) foreach($feeds as $k=>$v){?>
<tr>
	<td class="first<?if ($v[paused]){?> gry<?}?>"><?if ($ctab!=$v[tab]){?><?=search_in_vals($v[tab])?><? $ctab=$v[tab]; }?></td>
	<td class="tab<?if ($v[paused]){?> gry<?}?>"><?=search_in_vals($v[cat])?></td>
	<td class="tab<?if ($v[paused]){?> gry<?}?>"><a href="<?=$v['link']?>" target="_blank"><?=$v['link']?></a></td>
	<td class="tab<?if ($v[paused]){?> gry<?}?>"><a href="out.php?fid=<?=$v[id]?>" target="_blank"><?=FULL_URL.'out.php?fid='.$v['id']?></a></td>
	<td class="tab mid<?if ($v[paused]){?> gry<?}?>"><?=(int)$v[period]?></td>
	<td class="tab mid<?if ($v[paused]){?> gry<?}?>"><? $str = strtotime($v[updated]);if ($str) echo date("d-M-Y",$str);else echo "";?></td>
	<td class="tab mid<?if ($v[paused]){?> gry<?}?>"><? $str = strtotime($v[updated]);if ($str) echo date("d-M-Y",$str+3600*(int)$v[period]);else echo date("d-M-Y");?></td>	
	<td class="tab mid"><a href="?action=parse_now&fid=<?=$v[id]?>"><img src="img/internet-web-browser.png" title="Start" alt="Start" border="0" alt=""></a></td>
	<?if ($_SESSION['user']){?>
	<td class="tab mid"><a href="index.php?fid=<?=$v[id]?>&action=edit"><img src="img/document-properties.png" title="Edit" alt="Edit" border="0" alt=""></a></td>
	<?}?>
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
<?}?><br><br>
<table>
<tr>
	<td><a href="javascript:void(0);" onclick="window.open('?action=viewapp','app','width=500,height=500,scrollbars=yes')"><img src="img/format-indent-more.png" width="22" height="22" border="0" alt=""></a></td>
	<td><a href="javascript:void(0);" onclick="window.open('?action=viewapp','app','width=500,height=500,scrollbars=yes')">View app</a></td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td><a href="client.php?action=text"><img src="img/format-indent-more.png" width="22" height="22" border="0" alt=""></a></td>
	<td><a href="client.php?action=text">View tree as text</a></td>
</tr>
</table>


<?}else{?>
<center><b>No feed was added</b></center>
<?}?>

 </body>
</html>
