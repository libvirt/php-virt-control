<html>
<head>
 <title>php-virt-control - <?php echo $lang->get('title-vmc') ?></title>
 <link rel="STYLESHEET" type="text/css" href="manager.css" />
</head>
<body>
  <div id="header">
    <div id="headerLogo"></div>
  </div>

  <div id="conn-detail">
    <div style="float:right;text-align: right; width:220px;font-size:11px;font-style:italic">

  </div>
<h1><?php echo $lang->get('title-vmc') ?></h1>
<p>

<?php
	if (array_key_exists('username', $_POST))
		echo '<div id="msg-error">'.$lang->get('login-failed').'</div>';
?>

<form method="POST">
<table id="connections-edit">
	<tr>
		<td class="title" align="right"><?php echo $lang->get('username') ?>: </td>
		<td class="field"><input type="text" name="wusername"></td>
	</tr>
	<tr>
		<td class="title" align="right"><?php echo $lang->get('password') ?>: </td>
		<td class="field"><input type="password" name="wpassword"></td>
	</tr>
	<tr>
		<td colspan="2" class="submit"><input type="submit" value=" <?php echo $lang->get('login') ?> "></td>
	</tr>
</table>
</form>
</p>

</body>
</html>

