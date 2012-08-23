<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
 <head>
  <title> Edit Values </title>
  <meta name="Keywords" content="">
  <meta name="Description" content="">
 </head>

 <body>

<center><?foreach($vals as $k=>$v) if ($v['type'] == $type && (!$_REQUEST[parent] || $_REQUEST[parent] == $v['parent_id'])) {?>
 
 <h4><?=$v['title']?> / <a href="javascript:void(0);" onclick="document.getElementById('app_n').value='<?=$v['title']?>';document.getElementById('app_id').value='<?=$v['id']?>';document.getElementById('add_app').style.display='block'">edit</a> <a href="?action=del_val&id=<?=$v['id']?>&type=<?=$type?>&parent=<?=$_REQUEST[parent]?>">del</a></h4>

<?}?>
<br /><br />
<a href="javascript:void(0);" onclick="document.getElementById('app_n').value='';document.getElementById('app_id').value='';document.getElementById('add_app').style.display='block'">Add new value</a><br />
<div style="display:none;" id="add_app">
<form method=post action="?">
	Name: <input type="text" name="title" id="app_n" value=""> <input type="submit" value="Ok">
<input type="hidden" name="action" value="ch_app">
<input type="hidden" name="id" value="" id="app_id">
<input type="hidden" name="type" value="<?=$type;?>">
<input type="hidden" name="parent" value="<?=$_REQUEST[parent];?>">
</form></div>
<br /><br />
<a href="javascript:void(0);" onclick="window.opener.location=window.opener.location;window.close();">Close Window</a><br />

</center> 

</body>
</html>
