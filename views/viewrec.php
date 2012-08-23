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

<table width="100%" cellpadding="0" cellspacing="0">
<tr>

<td style="padding:10px;text-align:center;font-weight:bold;border:1px solid #000000;"><a href="?action=viewapp&c_tab=<?=$c_tab?>" style="text-decoration:none;color:#000000;">HOME</a></td>
<td style="padding:10px;text-align:center;font-weight:bold;width:100px;">&nbsp;</td>
<td style="padding:10px;text-align:center;font-weight:bold;border:1px solid #000000;"><?if ($prev){?><a href="?action=viewrec&c_tab=<?=$c_tab?>&id=<?=$prev?>" style="text-decoration:none;color:#000000;">BACK</a><?}else{?><span style="color:grey;">BACK</span><?}?></td>
<td style="padding:10px;text-align:center;font-weight:bold;border:1px solid #000000;border-left:0px;"><?if ($next){?><a href="?action=viewrec&c_tab=<?=$c_tab?>&id=<?=$next?>" style="text-decoration:none;color:#000000;">NEXT</a><?}else{?><span style="color:grey;">NEXT</span><?}?></td>

</tr>
</table>
<br />
<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #000000;">
<tr>
<td style="padding:15px;">
<h2 style="margin-bottom:0px;"><?=$item[title]?></h2>
<span style="font-size:10px;"><?=$item[description]?></span><br /><br />

<?=$item[content]?>

</td></tr></table>

</body>
</html>