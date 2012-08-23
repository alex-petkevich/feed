<?
	if ($_REQUEST[save])
	{
		if (is_array($_REQUEST['appid']))
		{
			foreach($_REQUEST['appid'] as $k=>$v)
				if ($k)
					$DB->Execute("Update vals set appid='$v' where id='$k'");
			$ok = 1;
		}
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
 <head>
  <title> update app </title>
  <meta name="Generator" content="EditPlus">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
 </head>

 <body>
  <form method=post action="?">
  <?if ($ok){?><div style="color:#00ff00;">saved successfully</div><?}?>
  <table>
	<?
		$vals = get_values();
		foreach($vals as $k=>$v)
		{
			if ($v['type'] == 'app')
			{
	?>
<tr>
	<td><?=$v[title]?></td>
	<td><input type="text" name="appid[<?=$v[id]?>]" value="<?=$v[appid]?>"></td>
</tr>
	<?}}?>
	</table>
	<input type="submit" name="save" value="Save">
	<input type="hidden" name="action" value="updateapp">
  </form>
 </body>
</html>
