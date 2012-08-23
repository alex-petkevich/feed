<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
 <head>
  <title> Select An Application </title>
  <meta name="Keywords" content="">
  <meta name="Description" content="">
 </head>

 <body>
 <?if (!$_REQUEST['popup']){?>
<div align="right"><a href="javascript:void(0);" onclick="document.getElementById('app_n').value='';document.getElementById('app_id').value='';document.getElementById('add_app').style.display='block'">[+] Add new app</a> | <a href="?action=admin">Admin Options</a> | <a href="?action=tree">Edit Tree</a> | <a href="/?action=logout">Logout</a></div>
<?}?>
 <table align="center">
     <tr>
         <td>
<?foreach($vals as $k=>$v) if ($v['type'] == 'app') {?>
 
 <h3><a href="?appl=<?=$v['id']?>"><?=$v['title']?></a><?if (!$client){?> / <a href="?action=edit_app&id=<?=$v['id']?>">setup</a> <a href="?action=del_val&id=<?=$v['id']?>" onclick="return confirm('Are you sure?')">del</a><?}?></h3>

<?}?>
<?if (!$client){?>
<br /><br />
<div style="<?if (!$_REQUEST['show_new']){?>display:none;<?}?>" id="add_app">
<form method=post action="?">
	Application name: <input type="text" name="title" id="app_n" value=""> <input type="submit" value="Ok">
<input type="hidden" name="action" value="ch_app">
<input type="hidden" name="id" value="" id="app_id">
</form></div>
<?}?>
</td><td>

<b>Content Alerts</b> (<a href="?action=refresh_alerts">refresh</a>)<br />
<?if (count($alerts)) { foreach($alerts as $k=>$v)if ($v[title]) {?>

<a href="?appl=<?=$v['id']?>"><?=$v['title']?></a>  <?if($v[id] && $v[tab]){?> -&gt <?=search_in_vals($v[tab])?> <? if($v[cat]){?>-&gt; <?=search_in_vals($v[cat])?> <?}}?> (<?=$v['updated']?> days)<br />

<?}
}
else {
?>
	<br />
	All feeds contain content
<?}?>

</td>
     </tr>
</table>
</body>
</html>
