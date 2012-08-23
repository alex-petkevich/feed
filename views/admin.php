 <div>&nbsp;<input type="button" value="&laquo; Back" onclick="document.location='/?'"></div>
  <?if ($config_saved){?><div style="color:green;font-weight:bold;" align="center">Values saved successfully</div><?}?>
<table width="100%">
 <tr>
	<td width="50%" valign="top">
  <FIELDSET>
	<LEGEND>General settings</LEGEND>
<form method=post action="?">
	 <table>
 <tr>
	<td>Feeds per page:</td>
	<td><input type="text" name="conf[feeds_per_page]" value="<?=get_config('feeds_per_page')?>"></td>
 </tr>
 <tr>
	<td>Admin username:</td>
	<td><input type="text" name="conf[username]" value="<?=get_config('username')?>"></td>
 </tr>
 <tr>
	<td>Admin password:</td>
	<td><input type="password" name="conf[password]" value="<?=get_config('password')?>"></td>
 </tr>
 <tr>
	<td>Email:</td>
	<td><input type="text" name="conf[email]" value="<?=get_config('email')?>"></td>
 </tr>
 </table>
 <br>
 <input type="submit" name='save' value="Apply">

<input type="hidden" name="action" value="admin">
  </form>  </FIELDSET>
	  <FIELDSET>
	<LEGEND>Outgoing feed management</LEGEND>
  <form method=post action="?">
	 <table border=0>
 <tr>
	<td>Last unloading:</td>
	<td><?=get_config('out_last_purge')?></td>
 </tr>
 <tr>
	<td>Unload old content once per:</td>
	<td><input type="text" name="conf[out_purge_period]" value="<?if (get_config('out_purge_period') != '999999'){?><?=get_config('out_purge_period')?><?}?>" id="out_purge" size=2<?if (get_config('out_purge_period') == '999999'){?> disabled="disabled"<?}?>> days  <input type="checkbox" name="out_never" value="1"<?if (get_config('out_purge_period') == '999999'){?> checked<?}?> onclick="document.getElementById('out_purge').disabled=this.checked"> never </td>
 </tr>
 <tr>
	<td>Global pause:</td>
	<td><input type="checkbox" name="pause" value="1" onclick="if (this.checked) document.getElementById('pause').value='1'; else document.getElementById('pause').value='0';" <? if (strpos(get_config('pause'),"{".$_SESSION['app']."}")!==false){?> checked<?}?> /></td>
 </tr>
  <tr>
	<td>Cropped images size:</td>
	<td>width: <input type="text" name="conf[image_width]" value="<?=get_config('image_width')?>" size=2> x height: <input type="text" name="conf[image_height]" value="<?=get_config('image_height')?>" size=2></td>
 </tr>
</table>
 <input type="hidden" name="conf[pause]" id='pause' value="<?=get_config('out_purge_period')?>">
 <br>
	 <table width="100%">
	 <tr>
		<td> <input type="submit" name='save' value="Apply">
</td>
		<td align="right"> <input type="button" name='parse_now' value="Purge now" onclick="window.location='?action=purge&adm=1'">
</td>
	 </tr>
	 </table>

 <input type="hidden" name="action" value="admin">
 </form>

  </FIELDSET>

	</td>
	<td width="50%" valign="top">
  <FIELDSET>
	<LEGEND>Backup management</LEGEND>
<form method=post action="?">
	 <table>
 <tr>
	<td>Last backup: </td>
	<td><?=get_config('last_backup')?></td>
 </tr>
 <tr>
	<td>Backup once per:</td>
	<td><input type="text" name="conf[backup_period]" size=2 value="<?=get_config('backup_period')?>"> days</td>
 </tr>
 <tr>
	<td>Backup to:</td>
	<td><input type="text" name="conf[backup_to]" value="<? $bd = get_config('backup_to'); echo $bd ? $bd : FULL_PATH;?>"></td>
 </tr>
 </table>
	 <br>
	 <table width="100%">
	 <tr>
		<td> <input type="submit" name='save' value="Apply">
</td>
		<td align="right"> <input type="button" name='backup_now' value="Backup now" onclick="window.location='?action=backup_now'">
</td>
	 </tr>
	 </table>

<input type="hidden" name="action" value="admin">
  </form>  </FIELDSET>

	</td>
 </tr>
	 </table>
