<script language="JavaScript">
	function sw_ed_tab(title,id)
	{
		document.getElementById('app_n').value=title;
		document.getElementById('app_id').value=id;
		document.getElementById('app_t').value='tab';
		document.getElementById('add_app').style.display='block';
		document.getElementById('ordtab').style.display='block';
		document.getElementById('ord').style.display='block'
	}
	function sw_ed_cat(pid,title,id)
	{
		document.getElementById('app_p').value=pid;
		document.getElementById('app_n').value=title;
		document.getElementById('app_id').value=id;
		document.getElementById('app_t').value='cat';
		document.getElementById('add_app').style.display='block';
		document.getElementById('ordtab').style.display='block';
		document.getElementById('ord').style.display='block'
	}
	function sw_add_tab()
	{
		document.getElementById('app_n').value='';
		document.getElementById('app_id').value='';
		document.getElementById('app_t').value='tab';
		document.getElementById('add_app').style.display='block';
		var otab = document.getElementById('ordtab');
		otab.options.length = 0;
		otab.options[otab.options.length] = new Option('', '');
<? foreach($tabs as $k=>$v){ ?>
		otab.options[otab.options.length] = new Option('<?=$v[title]?>', '<?=$v[id]?>');
<?}?>
		otab.style.display='inline';
		document.getElementById('ord').style.display='inline'
	}
	function sw_add_cat(id)
	{
		document.getElementById('app_p').value=id;
		document.getElementById('app_n').value='';
		document.getElementById('app_id').value='';
		document.getElementById('app_t').value='cat';
		document.getElementById('add_app').style.display='block';
		document.getElementById('ordtab').style.display='inline';
		var otab = document.getElementById('ordtab');
		otab.options.length = 0;
		otab.options[otab.options.length] = new Option('', '');
<?foreach($tabs as $k=>$v){ ?>
		if (id == '<?=$v[id]?>')
		{
			<?$cats =  get_values(0,$v[id]);foreach($cats as $kc=>$vc){?>
			otab.options[otab.options.length] = new Option('<?=$vc[title]?>', '<?=$vc[id]?>');
			<?}?>
		}
<?}?>
		otab.style.display='inline';
		document.getElementById('ord').style.display='inline';
	}
</script>

<table width="100%">
<tr>
	<td><b><?=$app[title]?></b> - SETUP PANEL</td>
	<td align="right"><a href="<?=FULL_URL?>">&laquo;Back</a> | <a href="?show_new=1">[+] Add new app</a> | <a href="?action=admin">Admin Options</a> | <a href="?action=tree">Edit Tree</a> | <a href="/?action=logout">Logout</a></td>
</tr>
</table>
<hr noshade size="1">
<table width="100%">
<tr>
	<td valign="top"><form method=post action="?">
<table>
<tr>
	<td>Application name:</td>
	<td><input type="text" name="title" value="<?=$app['title']?>"></td>
	<td></td>
</tr>
<tr>
	<td>Application version:</td>
	<td><input type="text" name="appid" value="<?=$app['appid']?>"></td>
	<td><a href="<?php echo FULL_URL;?>out.php?get_v=1"><?php echo FULL_URL;?>out.php?get_v=1</a></td>
</tr>
<tr>
	<td>Update Timestamp:</td>
	<td><input type="text" name="updated" value="<?=$app['updated']?>"></td>
	<td><a href="<?php echo FULL_URL;?>out.php?app_v=<?=$app['title']?>"><?php echo FULL_URL;?>out.php?app_v=<?=$app['title']?></a></td>
</tr>
<tr>
	<td>New Content:</td>
	<td><?php $max = $DB->SelectHash("SELECT id,unix_timestamp(MAX(updated)) AS tim FROM links WHERE app='".$app[id]."'  GROUP BY id ORDER BY tim DESC LIMIT 1"); echo date("Y-m-d H:i:s",$max[tim]);?></td>
	<td><?$item = get_last_item($max['id']);?> FID: <?=$item['link_id']?> - <?=search_in_vals($item['tab'])?> -&gt; <?=search_in_vals($item['cat'])?> | <?=$item['title']?></td>
</tr>
<tr>
	<td colspan="3">&nbsp;</td>
</tr>
<tr>
	<td>Client name:</td>
	<td><input type="text" name="cname" value="<?=$app['cname']?>"></td>
	<td></td>
</tr>
<tr>
	<td>Client Email:</td>
	<td><input type="text" name="cemail" value="<?=$app['cemail']?>"></td>
	<td></td>
</tr>
<tr>
	<td>Client Password:</td>
	<td><input type="text" name="cpassword" value="<?=$app['cpassword']?>"></td>
	<td></td>
</tr>
<tr>
	<td colspan="3">&nbsp;</td>
</tr>
<tr>
	<td>CampusCurrent Rep:</td>
	<td><input type="text" name="crep" value="<?=$app['crep']?>"></td>
	<td></td>
</tr>
<tr>
	<td>CampusCurrent Rep Email:</td>
	<td><input type="text" name="crepemail" value="<?=$app['crepemail']?>"></td>
	<td></td>
</tr>
<tr>
	<td colspan="3">&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Ok"></td>
</tr>
</table>
	  
	

<input type="hidden" name="action" value="ch_app">
<input type="hidden" name="id" value="<?=$app[id]?>">
</form></td>
	<td align="right" valign="top"><div style="width:200px;height:300px;overflow : auto;border:1px solid #C0C0C0;text-align:left;padding:5px;">
<?if (count($tabs)) foreach($tabs as $k=>$v){?>
<a href="javascript:void(0);" onclick="sw_ed_tab('<?=$v['title']?>','<?=$v['id']?>')" style="text-decoration:none;color:#000000;"><?=$v[title]?></a> <a href="?action=del_val&id=<?=$v[id]?>&eid=<?=$_REQUEST[id]?>" style="text-decoration:none;color:#000000;">[x]</a><br />
<?$cats =  get_values(0,$v[id]);?>
<?if (count($cats)) foreach($cats as $kc=>$vc){?>
&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="sw_ed_cat('<?=$vc['parent_id']?>','<?=$vc['title']?>','<?=$vc['id']?>')" style="text-decoration:none;color:#000000;"><?=$vc[title]?></a> <a href="?action=del_val&id=<?=$vc[id]?>&eid=<?=$_REQUEST[id]?>" style="text-decoration:none;color:#000000;">[x]</a><br />
<?}?>
&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="sw_add_cat('<?=$v['id']?>');">[+] new cat</a><br />
<?} else {?>
	<center>no tabs was added</center>
<?}?>
</div>
<a href="javascript:void(0);" onclick="sw_add_tab()">[+] new tab</a>
<br />
<div style="display:none;" id="add_app">
<form method=post action="?">
	Input name: <input type="text" name="title" id="app_n" value=""> <select name="ord" id="ord" style="display:none;"><option value=""></option><option value="after">after</option><option value="before">before</option></select> 
	<select name="ordtab" id="ordtab" style="display:none;">
	<option></option>
<?if (count($tabs)) foreach($tabs as $k=>$v){?>
		<option value="<?=$v[id]?>"><?=$v[title]?></option>
<?$cats =  get_values(0,$v[id]);?>
<?if (count($cats)) foreach($cats as $kc=>$vc){?>
		<option value="<?=$vc[id]?>">&nbsp;&nbsp;&nbsp;<?=$vc[title]?></option>
<?}}?>
	</select>
	<input type="submit" value="Ok">
<input type="hidden" name="action" value="ch_app">
<input type="hidden" name="id" value="" id="app_id">
<input type="hidden" name="type" value="tab" id="app_t">
<input type="hidden" name="eid" value="<?=$_REQUEST[id]?>">
<input type="hidden" name="tid" value="" id="app_p">
</form></div>

</td>
</tr>
</table>

