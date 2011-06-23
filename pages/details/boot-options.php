<?
  if ((array_key_exists('ch-discard', $_POST)) && ($_POST['ch-discard'])) 
    Die(Header('Location: '.$_SERVER['REQUEST_URI']));

  $bd1 = array_key_exists('bd_1st', $_POST) ? $_POST['bd_1st'] : false;
  $bd2 = array_key_exists('bd_2nd', $_POST) ? $_POST['bd_2nd'] : false;

  $msg = '';
  if ($bd1) {
    $msg = $lv->domain_change_boot_devices($name, $bd1, $bd2) ? 'Boot options for next run have been altered successfully'
           : 'Cannot change guest boot options: '.$lv->get_last_error();
  }

  $devs = $lv->domain_get_boot_devices($res);
  $bd_1st = $devs[0];
  $bd_2nd = (sizeof($devs) > 1) ? $devs[1] : '-';
  unset($devs);
?>
  <!-- CONTENTS -->
  <div id="content">

    <script language="javascript">
    <!--
        var change_el;

        function setElement(t, x) {
            if (t == 'change')
                change_el = x.name;
        }
        function check() {
            if (change_el == 'ch-apply')
                return (confirm('Do you really want to apply your changes?'));
            if (change_el == 'ch-discard')
                return (confirm('Do you really want to discard your changes?'));
        }
    -->
    </script>

<?php
    if ($msg):
?>
    <div id="msg"><b>Message: </b><?= $msg ?></div>
<?php
    endif;
?>

    <form action="#" method="POST" onsubmit="return check();">

    <!-- HOST SECTION -->
    <div class="section">Virtual machine boot options</div>
    <div class="item">
      <div class="label">First boot device:</div>
      <div class="value">
        <select name="bd_1st">
          <option value="hd" <?= (($bd_1st == 'hd') ? 'selected="selected"' : '') ?>>Hard-drive</option>
          <option value="cdrom" <?= (($bd_1st == 'cdrom') ? 'selected="selected"' : '') ?>>CD-ROM</option>
          <option value="fd" <?= (($bd_1st == 'fd') ? 'selected="selected"' : '') ?>>Floppy</option>
          <option value="network" <?= (($bd_1st == 'network') ? 'selected="selected"' : '') ?>>Network boot (PXE)</option>
        </select>
      </div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Second boot device:</div>
      <div class="value">
        <select name="bd_2nd">
          <option value="-" <?= (($bd_2nd == '-') ? 'selected="selected"' : '') ?>>None</option>
          <option value="hd" <?= (($bd_2nd == 'hd') ? 'selected="selected"' : '') ?>>Hard-drive</option>
          <option value="cdrom" <?= (($bd_2nd == 'cdrom') ? 'selected="selected"' : '') ?>>CD-ROM</option>
          <option value="fd" <?= (($bd_2nd == 'fd') ? 'selected="selected"' : '') ?>>Floppy</option>
          <option value="network" <?= (($bd_2nd == 'network') ? 'selected="selected"' : '') ?>>Network boot (PXE)</option>
        </select>
      </div>
      <div class="nl" />
    </div>
    <!-- ACTIONS SECTION -->
    <div class="section">Actions</div>
    <div class="item">
      <div class="label">Changes:</div>
      <div class="value">
        <input type="submit" name="ch-apply" value=" Apply changes " onclick="setElement('change', this)" />
        <input type="submit" name="ch-discard" value=" Discard changes " onclick="setElement('change', this)" />
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
