<?php
  $dev = array_key_exists('dev', $_GET) ? $_GET['dev'] : false;
  $action = array_key_exists('action', $_GET) ? $_GET['action'] : false;

  $msg = '';
  $frm = '';
  if (($action == 'del-nic') && ($dev)) {
    $msg = $lv->domain_nic_remove($name, base64_decode($dev)) ? 'Network card has been removed successfully' :
		'Cannot remove disk: '.$lv->get_last_error();
  }
  if ($action == 'add-nic') {
    if (array_key_exists('mac', $_POST))
        $msg = $lv->domain_nic_add($name, $_POST['mac'], $_POST['network'], $_POST['nic_type']) ?
					'Network card has been successfully added to the guest' :
					'Cannot add NIC to the guest: '.$lv->get_last_error();
    else {
	$nets = $lv->get_networks();
	$models = $lv->get_nic_models();

        $frm = '<div class="section">Add a new network interface card</div>
                <form method="POST">
                <table id="form-table">
                  <tr>
                    <td align="right"><b>MAC Address: </b></td>
                    <td><input type="text" name="mac" value="'.$lv->generate_random_mac_addr().'"/></td>
                  </tr>
		  <tr>
		    <td align="right"><b>Network: </b></td>
		    <td>
                      <select name="network">';

	for ($i = 0; $i < sizeof($nets); $i++)
		$frm .= '<option value="'.$nets[$i].'">'.$nets[$i].'</option>';

	$frm .= '    </select>
                    </td>
		  </tr>
                  <tr>
                    <td align="right"><b>NIC Type: </b></td>
                    <td>
                      <select name="nic_type">';

	for ($i = 0; $i < sizeof($models); $i++)
		$frm .= '<option value="'.$models[$i].'">'.$models[$i].'</option>';

	$frm .= '
                      </select>
                    </td>
                  </tr>
                  <tr align="center">
                    <td colspan="2"><input type="submit" value=" Add new NIC " /></td>
                  </tr>
                </table>
                </form>';
    }
  }

  $tmp = $lv->get_nic_info($name);
  $numDisks = sizeof($tmp);
?>
  <!-- CONTENTS -->
  <div id="content">

    <script language="javascript">
    <!--
        function confirmAddition() {
            if (confirm('Do you really want to add a new network interface card ?')) {
                location.href = '?name=<?= $name.'&page='.$page ?>&action=add-nic';
            }
        }
        function askNicDeletion(mac, macb64) {
            if (confirm('Are you sure you want to delete interface with MAC address '+mac+' from the guest?'))
                location.href = '?name=<?= $name.'&page='.$page.'&action=del-nic&dev=' ?>'+macb64;
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
<?php
    if ($frm):
?>
    <div><?= $frm ?></div>
<?php
    endif;
?>

    <form action="#" method="POST">

    <div class="section">Machine network devices</div>
    <div class="item">
      <div class="label">Number of NICs:</div>
      <div class="value"><?= $numDisks ?></div>
      <div class="nl" />
    </div>

<?php
    for ($i = 0; $i < sizeof($tmp); $i++):
        $nic = $tmp[$i];
?>
    <!-- NIC SECTION -->
    <div class="section">Network interface card #<?= $i + 1 ?></div>
    <div class="item">
      <div class="label">MAC Address:</div>
      <div class="value"><?= $nic['mac'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Network:</div>
      <div class="value"><?= $nic['network'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">NIC Type:</div>
      <div class="value"><?= $nic['nic_type'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Action:</div>
      <div class="value">
        <input type="button" onclick="askNicDeletion('<?= $nic['mac'] ?>', '<?= base64_encode($nic['mac']) ?>')" value=" Remove network interface " />
      </div>
      <div class="nl" />
    </div>
<?
    endfor;
    unset($tmp);
?>

    <!-- ACTIONS SECTION -->
    <div class="section">Actions</div>
    <div class="item">
      <div class="label">Changes:</div>
      <div class="value">
        <input type="button" name="add-nic" value=" Add new a new network interface " onclick="confirmAddition()" />
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
