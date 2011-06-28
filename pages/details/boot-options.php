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
                return (confirm('<?= $lang->get('ask_apply') ?>'));
            if (change_el == 'ch-discard')
                return (confirm('<?= $lang->get('ask_discard') ?>'));
        }
    -->
    </script>

<?php
    if ($msg):
?>
    <div id="msg"><b><?= $lang->get('msg') ?>: </b><?= $msg ?></div>
<?php
    endif;
?>

    <form action="#" method="POST" onsubmit="return check();">

    <!-- HOST SECTION -->
    <div class="section"><?= $lang->get('vm_boot_opts') ?></div>
    <div class="item">
      <div class="label"><?= $lang->get('vm_boot_dev1') ?>:</div>
      <div class="value">
        <select name="bd_1st">
          <option value="hd" <?= (($bd_1st == 'hd') ? 'selected="selected"' : '') ?>><?= $lang->get('vm_boot_hdd') ?></option>
          <option value="cdrom" <?= (($bd_1st == 'cdrom') ? 'selected="selected"' : '') ?>><?= $lang->get('vm_boot_cd') ?></option>
          <option value="fd" <?= (($bd_1st == 'fd') ? 'selected="selected"' : '') ?>><?= $lang->get('vm_boot_fda') ?></option>
          <option value="network" <?= (($bd_1st == 'network') ? 'selected="selected"' : '') ?>><?= $lang->get('vm_boot_pxe') ?></option>
        </select>
      </div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?= $lang->get('vm_boot_dev2') ?>:</div>
      <div class="value">
        <select name="bd_2nd">
          <option value="-" <?= (($bd_2nd == '-') ? 'selected="selected"' : '') ?>><?= $lang->get('vm_boot_none') ?></option>
          <option value="hd" <?= (($bd_2nd == 'hd') ? 'selected="selected"' : '') ?>><?= $lang->get('vm_boot_hdd') ?></option>
          <option value="cdrom" <?= (($bd_2nd == 'cdrom') ? 'selected="selected"' : '') ?>><?= $lang->get('vm_boot_cd') ?></option>
          <option value="fd" <?= (($bd_2nd == 'fd') ? 'selected="selected"' : '') ?>><?= $lang->get('vm_boot_fda') ?></option>
          <option value="network" <?= (($bd_2nd == 'network') ? 'selected="selected"' : '') ?>><?= $lang->get('vm_boot_pxe') ?></option>
        </select>
      </div>
      <div class="nl" />
    </div>
    <!-- ACTIONS SECTION -->
    <div class="section"><?= $lang->get('actions') ?></div>
    <div class="item">
      <div class="label"><?= $lang->get('changes') ?>:</div>
      <div class="value">
        <input type="submit" name="ch-apply" value=" <?= $lang->get('btn_apply') ?> " onclick="setElement('change', this)" />
        <input type="submit" name="ch-discard" value=" <?= $lang->get('btn_discard') ?> " onclick="setElement('change', this)" />
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
