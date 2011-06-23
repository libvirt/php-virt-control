<?php
  if ((array_key_exists('ch-discard', $_POST)) && ($_POST['ch-discard'])) 
    Die(Header('Location: '.$_SERVER['REQUEST_URI']));

  $ci  = $lv->host_get_node_info();
  $memory = round($ci['memory'] / 1024);
  unset($ci);

  $pmemory = array_key_exists('memory', $_POST) ? $_POST['memory'] : false;
  $pmaxmem = array_key_exists('maxmem', $_POST) ? $_POST['maxmem'] : false;

  $msg = '';
  if ($pmemory && $pmaxmem) {
    $msg = $lv->domain_change_memory_allocation($name, $pmemory, $pmaxmem) ? 'Memory allocation for next run has been altered successfully'
           : 'Cannot change guest memory allocation: '.$lv->get_last_error();
  }

  $info = $lv->domain_get_info($name);
  $guest_memory = round($info['memory'] / 1024);
  $guest_maxmem = round($info['maxMem'] / 1024);
  unset($info);
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
    <div class="section">Host memory information</div>
    <div class="item">
      <div class="label">Total memory:</div>
      <div class="value"><?= $memory ?> MiB</div>
      <div class="nl" />
    </div>
    <!-- MACHINE SECTION -->
    <div class="section">Machine memory allocation (in MiBs)</div>
    <div class="item">
      <div class="label">Current allocation:</div>
      <div class="value">
        <input type="text" name="memory" value="<?= $guest_memory ?>" />
      </div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Max. allocation:</div>
      <div class="value">
        <input type="text" name="maxmem" value="<?= $guest_maxmem ?>" />
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
