
<html> 
<head> 
 <title>php-virt-control - <?= $lang->get('title_vmc') ?></title> 
 <link rel="STYLESHEET" type="text/css" href="manager.css"> 
</head> 
<body> 
  <div id="header"> 
    <div id="headerLogo"></div> 
  </div> 
 
  <!-- CONTENTS --> 
  <div id="content"> 

    <div class="section"><?= $lang->get('error_page_title') ?></div>
    <div class="item">
      <div class="label"><?= $lang->get('error_connection_db_label') ?></div>
      <div class="value"><?= $lang->get('error_connection_db_text').' '.$lang->get($db->get_fatal_error()) ?></div>
      <div class="nl" />
    </div>
 
    </form> 
 
  </div> 
</body> 
</html> 
