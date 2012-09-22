<?php
	$disk = array_key_exists('disk', $_GET) ? $_GET['disk'] : false;
	$action = array_key_exists('action', $_GET) ? $_GET['action'] : false;

	$msg = '';
	$frm = '';
	if (($action == 'del-disk') && ($disk)) {
		$msg = $lv->domain_disk_remove($name, $disk) ? $lang->get('disk-removed') : $lang->get('disk-not-removed').': '.$lv->get_last_error();
	}

	if ($action == 'add-disk') {
		$img = array_key_exists('img', $_POST) ? $_POST['img'] : false;

	if ($img)
		$msg = $lv->domain_disk_add($name, $_POST['img'], $_POST['dev'], $_POST['bus'], $_POST['driver']) ?
				$lang->get('disk-added') : ($lang->get('disk-not-added').': '.$lv->get_last_error());
	else
		$frm = '<div class="section">'.$lang->get('vm-disk-add').'</div>
                <form method="POST">
                <table id="form-table">
                  <tr>
                    <td align="right"><b>'.$lang->get('vm-disk-image').': </b></td>
                    <td><input type="text" name="img" /></td>
                  </tr>
		  <tr>
		    <td align="right"><b>'.$lang->get('vm-disk-location').': </b></td>
		    <td>
                      <select name="bus">
                        <option value="ide">IDE Bus</option>
                        <option value="scsi">SCSI Bus</option>
                      </select>
                    </td>
		  </tr>
                  <tr>
                    <td align="right"><b>'.$lang->get('vm-disk-type').': </b></td>
                    <td>
                      <select name="driver">
                        <option value="raw">raw</option>
			<option value="qcow">qcow</option>
			<option value="qcow2">qcow2</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td align="right"><b>'.$lang->get('vm-disk-dev').': </b></td>
                    <td><input type="text" name="dev" value="hdb" /></td>
                  </tr>
                  <tr align="center">
                    <td colspan="2"><input type="submit" value=" '.$lang->get('vm-disk-add').' " /></td>
                  </tr>
                </table>
                </form>';
	}

	$tmp = $lv->get_disk_stats($name);
	$tmp2 = $lv->get_cdrom_stats($name, true);
	$numDisks = sizeof($tmp);

	$addmsg = (sizeof($tmp2) > 0) ? ' (disk) + '.(sizeof($tmp2)).' (cdrom)' : '';
?>
  <!-- CONTENTS -->
  <div id="content">

    <script language="javascript">
    <!--
        function confirmAddition() {
            if (confirm('<?php echo $lang->get('vm-disk-askadd') ?>')) {
                location.href = '?name=<?php echo $name.'&page='.$page ?>&action=add-disk';
            }
        }
        function askDiskDeletion(disk) {
            if (confirm('<?php echo $lang->get('vm-disk-askdel') ?>'))
                location.href = '?name=<?php echo $name.'&page='.$page.'&action=del-disk&disk=' ?>'+disk;
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
<?php
    if ($frm):
?>
    <div><?php echo $frm ?></div>
<?php
    endif;
?>

    <form action="#" method="POST">

    <div class="section"><?php echo $lang->get('vm-disk-details') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-num') ?>:</div>
      <div class="value"><?php echo $numDisks.$addmsg ?></div>
      <div class="nl" />
    </div>

<?php
	for ($i = 0; $i < sizeof($tmp); $i++):
		$disk = $tmp[$i];
		$bus = ($disk['bus'] == 'ide') ? 'IDE' : 'SCSI';
?>
    <!-- DISK SECTION -->
    <div class="section"><?php echo $bus ?> Disk <?php echo $i + 1 ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-storage') ?>:</div>
      <div class="value"><?php echo $disk['file'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-type') ?>:</div>
      <div class="value"><?php echo $disk['type'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-dev') ?>:</div>
      <div class="value"><?php echo $disk['device'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-capacity') ?>:</div>
      <div class="value"><?php echo $lv->format_size($disk['capacity'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-allocation') ?>:</div>
      <div class="value"><?php echo $lv->format_size($disk['allocation'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-physical') ?>:</div>
      <div class="value"><?php echo $lv->format_size($disk['physical'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('actions') ?>:</div>
      <div class="value">
        <input type="button" onclick="askDiskDeletion('<?php echo $disk['device'] ?>')" value=" <?php echo $lang->get('vm-disk-remove') ?> " />
      </div>
      <div class="nl" />
    </div>
<?php
	endfor;

	for ($i = 0; $i < sizeof($tmp2); $i++):
		$disk = $tmp2[$i];
		$bus = ($disk['bus'] == 'ide') ? 'IDE' : 'SCSI';
?>
    <!-- DISK SECTION -->
    <div class="section"><?php echo $bus ?> CD-ROM <?php echo $i + 1 ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-storage') ?>:</div>
      <div class="value"><?php echo $disk['file'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-type') ?>:</div>
      <div class="value"><?php echo $disk['type'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-dev') ?>:</div>
      <div class="value"><?php echo $disk['device'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-capacity') ?>:</div>
      <div class="value"><?php echo $lv->format_size($disk['capacity'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-allocation') ?>:</div>
      <div class="value"><?php echo $lv->format_size($disk['allocation'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('vm-disk-physical') ?>:</div>
      <div class="value"><?php echo $lv->format_size($disk['physical'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?php echo $lang->get('actions') ?>:</div>
      <div class="value">
        <input type="button" onclick="askDiskDeletion('<?php echo $disk['device'] ?>')" value=" <?php echo $lang->get('vm-disk-remove') ?> " />
      </div>
      <div class="nl" />
    </div>

<?php
	endfor;
	unset($tmp);
	unset($tmp2);
?>

    <!-- ACTIONS SECTION -->
    <div class="section"><?php echo $lang->get('actions') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('changes') ?>:</div>
      <div class="value">
        <input type="button" name="add-disk" value=" <?php echo $lang->get('vm-disk-add') ?> " onclick="confirmAddition()" />
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
