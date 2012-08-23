<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
 <head>
  <title> Intermedate server - Preview App</title>
  <meta name="Generator" content="EditPlus">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <link href="main.css" rel="stylesheet" type="text/css">
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">

 </head>

 <body>
<?=search_in_vals($_SESSION['c_app'])?><br /><br />
<?
	if (count($feeds))
	{
?>
<table width="100%" style="border:1px solid #000000;" cellpadding="0" cellspacing="0">
<tr>
<?$t=0;if (is_array($vals)) foreach($vals as $k=>$v) if ($v[parent_id] == $_SESSION['c_app']){?>

<td style="padding:10px;text-align:center;font-weight:bold;<?if ($t){?>border-left:1px solid #000000;<?}$t=1;?><?if ($c_tab == $v[id]){?>background-color:#4C70FC;color:#ffffff;<?}?>"><a href="?action=viewapp&c_tab=<?=$v[id]?>" style="text-decoration:none;color:<?if ($c_tab == $v[id]){?>#ffffff<?}else{?>#000000<?}?>;"><?=$v['title']?></a></td>

<?}}?>
</tr>
</table>

<?if (is_array($feeds)) foreach($feeds as $k=>$v) if ($v[tab] == $c_tab){?>
<table width="100%" style="padding:5px;border:1px solid #000000;color:#ffffff;font-weight:bold;background-color:#4C4B49;margin-top:10px;">
<tr>
	<td><?=$v['catt']?></td>
</tr>
</table>

<?
	$items = get_items($v['id']);
	if (is_array($items))
	foreach($items as $ki=>$vi)
	{
		$encl =  get_enclosures($vi['enclosure'],$vi['description'],$vi['content']);
?>

<table width="100%" style="padding:2px;border:1px solid #000000;font-weight:bold;margin-top:6px;">
<tr>
	<td width="100"><?if ($encl){?><img src="<?=$encl[0][url]?>" width="75" height="75"><?}?></td>
	<td><a href="?action=viewrec&c_tab=<?=$c_tab?>&id=<?=$vi['id']?>" style="text-decoration:none;color:#000000;"><?=$vi['title']?></a><br />
		 <font color="#808080"><?=date("l, F j",strtotime($vi['pubdate']))?></font>
		 </td>
</tr>
</table>

<?
	}
?>

<?}?>
</body>
</html>