<?php
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
    <div class="section">Host processor information</div>
    <div class="item">
      <div class="label">CPU count:</div>
      <div class="value"><?= $cpus ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Max. per guest:</div>
      <div class="value"><?= $max ?> vCPUs</div>
      <div class="nl" />
    </div>
    <!-- MACHINE SECTION -->
    <div class="section">Machine processor information</div>
    <div class="item">
      <div class="label">vCPU count:</div>
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
