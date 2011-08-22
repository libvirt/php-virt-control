
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

    <div class="section"><?php echo $lang->get('error_page_title') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('error_connection_db_label') ?></div>
      <div class="value"><?php echo $lang->get('error_connection_db_text').' '.$lang->get($db->get_fatal_error()) ?></div>
      <div class="nl" />
    </div>
 
    </form> 
 
  </div> 
</body> 
</html> 
