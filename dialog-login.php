
<html> 
<head> 
 <title>php-virt-control - <?php echo $lang->get('title_vmc') ?></title> 
 <link rel="STYLESHEET" type="text/css" href="manager.css"> 
</head> 
<body> 
  <div id="header"> 
    <div id="headerLogo"></div> 
  </div> 
 
  <!-- CONTENTS --> 
  <div id="content"> 

<?php
	if (array_key_exists('user', $_POST))
		echo '<div id="msg"><b>Message: </b>'.$lang->get('login_invalid').'</div>';
?>

    <form method="POST">

    <div class="section"><?php echo $lang->get('login_page_title') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('login_enter_login') ?></div>
      <div class="value"><input type="text" name="user"></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('login_enter_password') ?></div>
      <div class="value"><input type="password" name="password"></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">&nbsp;</div>
      <div class="value"><input type="submit" value="<?php echo $lang->get('login_submit') ?>"></div>
      <div class="nl" />
    </div>
 
    </form> 
 
  </div> 
</body> 
</html> 
