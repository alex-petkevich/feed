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
App: <b><?=search_in_vals($_SESSION['c_app'])?></b><br /><br />
<?
	$feeds = get_feeds($_SESSION['c_app']);
	if (count($feeds))
	{
		$ctab = "";
?>

<?if (is_array($feeds)) foreach($feeds as $k=>$v){?>
<?if ($ctab!=$v[tab]){?>
<?=search_in_vals($v[tab])?><br>
<?$ctab=$v[tab]; } ?>
&nbsp;&nbsp;&nbsp;<?=search_in_vals($v[cat])?> ***<a href="out.php?fid=<?=$v[id]?>" target="_blank"><?=FULL_URL.'out.php?fid='.$v['id']?></a><br>
<?}?>


<?}else{?>
<center><b>No feed was added</b></center>
<?}?>

 </body>
</html>
