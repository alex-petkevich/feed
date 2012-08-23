<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
 <head>
  <title> Login </title>
  <meta name="Keywords" content="">
  <meta name="Description" content="">
 </head>

 <body>
  <form method=post action="?">
  <?if ($error){?><div style="color:red" align="center">Incorrect login info</div><?}?>
	<table align="center">
	<tr>
		<td>Login:</td>
		<td><input type="text" name="user"></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="Password" name="password"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name='login' value="Login"></td>
	</tr>
	</table>
  </form>
 </body>
</html>
