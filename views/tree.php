 <div>&nbsp;<input type="button" value="&laquo; Back" onclick="document.location='/?'"></div>
 <form method=post action="?">
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
		<td><select name="app" onchange="window.location='?fid=<?=$_REQUEST['fid']?>&action=<?=$_REQUEST['action']?>&app='+this.value" id='app_id'>
		<option value=""></option>
		<?foreach($vals as $k=>$v) if ($v['type'] == 'app') {?>
		<option value="<?=$v['id']?>"<?if ($v['id'] == $_REQUEST['app']){?> selected<?}?>><?=$v['title']?></option>
		<?}?>
	</select></td>
		<td>-&gt;</td>
		<td><?if ($_REQUEST['app']){?>
	<select name="tab" onchange="window.location='?fid=<?=$_REQUEST['fid']?>&action=<?=$_REQUEST['action']?>&app=<?=$_REQUEST['app']?>&tab='+this.value" id="tab_id">
		<option value=""></option>
		<?foreach($vals as $k=>$v) if ($v['type'] == 'tab' && $_REQUEST['app'] == $v['parent_id']) {?>
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
	<tr>
		<td><a href="javascript:void(0);" onclick="window.open('?action=edit_vals&type=app&popup=1','eapp','width=300,height=500');">edit</a></td>
		<td>&nbsp;</td>
		<td><?if ($_REQUEST['app']){?><a href="javascript:void(0);" onclick="window.open('?action=edit_vals&type=tab&popup=1&parent=<?=$_REQUEST['app']?>','etab','width=300,height=500');">edit</a><?}?></td>
		<td>&nbsp;</td>
		<td><?if ($_REQUEST['tab']){?><a href="javascript:void(0);" onclick="window.open('?action=edit_vals&type=cat&popup=1&parent=<?=$_REQUEST['tab']?>','ecat','width=300,height=500');">edit</a><?}?></td>
	</tr>
	</table>
	
	</td>
 </tr>
 </table>