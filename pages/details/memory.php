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
                return (confirm('<?php echo $lang->get('ask_apply') ?>'));
            if (change_el == 'ch-discard')
                return (confirm('<?php echo $lang->get('ask_discard') ?>'));
        }
    -->
    </script>

<?php
    if ($msg):
?>
    <div id="msg"><b><?php echo $lang->get('msg') ?>: </b><?php echo $msg ?></div>
<?php
    endif;
?>

    <form action="#" method="POST" onsubmit="return check();">

    <!-- HOST SECTION -->
    <div class="section"><?php echo $lang->get('host_mem_info') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('total_mem') ?>:</div>
      <div class="value"><?php echo $memory ?> MiB</div>
      <div class="nl" />
    </div>
    <!-- MACHINE SECTION -->
    <div class="section"><?php echo $lang->get('vm_mem_info') ?> (MiBs)</div>
    <div class="item">
      <div class="label"><?php echo $lang->get('mem_alloc_cur') ?>:</div>
      <div class="value">
        <input type="text" name="memory" value="<?php echo $guest_memory ?>" />
      </div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('mem_alloc_max') ?>:</div>
      <div class="value">
        <input type="text" name="maxmem" value="<?php echo $guest_maxmem ?>" />
      </div>
      <div class="nl" />
    </div>
    <!-- ACTIONS SECTION -->
    <div class="section"><?php echo $lang->get('actions') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('changes') ?>:</div>
      <div class="value">
        <input type="submit" name="ch-apply" value=" <?php echo $lang->get('btn_apply') ?> " onclick="setElement('change', this)" />
        <input type="submit" name="ch-discard" value=" <?php echo $lang->get('btn_discard') ?> " onclick="setElement('change', this)" />
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
