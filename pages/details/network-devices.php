<?php
  $dev = array_key_exists('dev', $_GET) ? $_GET['dev'] : false;
  $action = array_key_exists('action', $_GET) ? $_GET['action'] : false;

  $msg = '';
  $frm = '';
  if (($action == 'del-nic') && ($dev)) {
    $msg = $lv->domain_nic_remove($name, base64_decode($dev)) ? $lang->get('network-remove-ok') :
				$lang->get('network-remove-error').': '.$lv->get_last_error();
  }
  if ($action == 'add-nic') {
    if (array_key_exists('mac', $_POST))
        $msg = $lv->domain_nic_add($name, $_POST['mac'], $_POST['network'], $_POST['nic_type']) ?
					$lang->get('network-add-ok') : $lang->get('network-add-error').': '.$lv->get_last_error();
    else {
	$nets = $lv->get_networks();
	$models = $lv->get_nic_models();

        $frm = '<div class="section">'.$lang->get('vm_network_add').'</div>
                <form method="POST">
                <table id="form-table">
                  <tr>
                    <td align="right"><b>'.$lang->get('vm_network_mac').': </b></td>
                    <td><input type="text" name="mac" value="'.$lv->generate_random_mac_addr().'"/></td>
                  </tr>
		  <tr>
		    <td align="right"><b>'.$lang->get('vm_network_net').': </b></td>
		    <td>
                      <select name="network">';

	for ($i = 0; $i < sizeof($nets); $i++)
		$frm .= '<option value="'.$nets[$i].'">'.$nets[$i].'</option>';

	$frm .= '    </select>
                    </td>
		  </tr>
                  <tr>
                    <td align="right"><b>'.$lang->get('vm_network_type').': </b></td>
                    <td>
                      <select name="nic_type">';

	for ($i = 0; $i < sizeof($models); $i++)
		$frm .= '<option value="'.$models[$i].'">'.$models[$i].'</option>';

	$frm .= '
                      </select>
                    </td>
                  </tr>
                  <tr align="center">
                    <td colspan="2"><input type="submit" value=" '.$lang->get('vm_network_add').' " /></td>
                  </tr>
                </table>
                </form>';
    }
  }

  $tmp = $lv->get_nic_info($name);
  $numDisks = sizeof($tmp);
  if (!$tmp)
    $numDisks = 0;
?>
  <!-- CONTENTS -->
  <div id="content">

    <script language="javascript">
    <!--
        function confirmAddition() {
            if (confirm('<?php echo $lang->get('vm_network_askadd') ?>')) {
                location.href = '?name=<?php echo $name.'&page='.$page ?>&action=add-nic';
            }
        }
        function askNicDeletion(mac, macb64) {
            if (confirm('<?php echo $lang->get('vm_network_askdel') ?>'))
                location.href = '?name=<?php echo $name.'&page='.$page.'&action=del-nic&dev=' ?>'+macb64;
        }
    -->
    </script>

<?php
    if ($msg):
?>
    <div id="msg"><b>Message: </b><?php echo $msg ?></div>
<?php
    endif;
?>
<?php
    if ($frm):
?>
    <div><?php echo $frm ?></div>
<?php
    endif;
?>

    <form action="#" method="POST">

    <div class="section"><?php echo $lang->get('vm_network_title') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm_network_num') ?>:</div>
      <div class="value"><?php echo $numDisks ?></div>
      <div class="nl" />
    </div>

<?php
    if (!$tmp)
	$tmp = array();

    for ($i = 0; $i < sizeof($tmp); $i++):
        $nic = $tmp[$i];
?>
    <!-- NIC SECTION -->
    <div class="section"><?php echo $lang->get('vm_network_nic') ?> #<?php echo $i + 1 ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm_network_mac') ?>:</div>
      <div class="value"><?php echo $nic['mac'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm_network_net') ?>:</div>
      <div class="value"><?php echo $nic['network'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm_network_type') ?>:</div>
      <div class="value"><?php echo $nic['nic_type'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('actions') ?>:</div>
      <div class="value">
        <input type="button" onclick="askNicDeletion('<?php echo $nic['mac'] ?>', '<?php echo base64_encode($nic['mac']) ?>')" value=" <?php echo $lang->get('vm_network_del') ?> " />
      </div>
      <div class="nl" />
    </div>
<?
    endfor;
    unset($tmp);
?>

    <!-- ACTIONS SECTION -->
    <div class="section"><?php echo $lang->get('actions') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('changes') ?>:</div>
      <div class="value">
        <input type="button" name="add-nic" value=" <?php echo $lang->get('vm_network_add') ?> " onclick="confirmAddition()" />
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
