<?php
  if ((array_key_exists('ch-discard', $_POST)) && ($_POST['ch-discard']))
	Die(Header('Location: '.$_SERVER['REQUEST_URI']));

  $ci  = $lv->get_connect_information();
  $max = $ci['hypervisor_maxvcpus'];
  unset($ci);

  $msg = '';
  $cpus = array_key_exists('cpu_count', $_POST) ? $_POST['cpu_count'] : false;
  if ($cpus) {
    $msg = $lv->domain_change_numVCpus($name, $cpus) ? 'Number of VCPUs for next run has been altered successfully'
           : 'Cannot change guest VCPU count: '.$lv->get_last_error();
  }
  $info = $lv->domain_get_info($name);
  $guest_cpu_count = $info['nrVirtCpu'];
  unset($info);
  $ci  = $lv->host_get_node_info();
  $cpus = $ci['cpus'];
  unset($ci);
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
    <div class="section"><?php echo $lang->get('host_pcpu_info') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('pcpus') ?>:</div>
      <div class="value"><?php echo $cpus ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('max_per_guest') ?>:</div>
      <div class="value"><?php echo $max ?> vCPUs</div>
      <div class="nl" />
    </div>
    <!-- MACHINE SECTION -->
    <div class="section"><?php echo $lang->get('vm_vcpu_info') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vcpus') ?>:</div>
      <div class="value">
        <select name="cpu_count">
<?
        for ($i = 1; $i <= $max; $i++)
            echo '<option value='.$i.' '.($i == $guest_cpu_count ? 'selected="selected"' : '').'>'.$i.'</option>';
?>
        </select>
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
