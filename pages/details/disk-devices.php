<?php
  $disk = array_key_exists('disk', $_GET) ? $_GET['disk'] : false;
  $action = array_key_exists('action', $_GET) ? $_GET['action'] : false;

  $msg = '';
  $frm = '';
  if (($action == 'del-disk') && ($disk)) {
    $msg = $lv->domain_disk_remove($name, $disk) ? 'Disk has been removed successfully' : 'Cannot remove disk: '.$lv->get_last_error();
  }
  if ($action == 'add-disk') {
    $img = array_key_exists('img', $_POST) ? $_POST['img'] : false;

    if ($img)
        $msg = $lv->domain_disk_add($name, $_POST['img'], $_POST['dev'], $_POST['bus'], $_POST['driver']) ?
                                    'Disk has been successfully added to the guest' :
                                    'Cannot add disk to the guest: '.$lv->get_last_error();
    else
        $frm = '<div class="section">Add a new disk device</div>
                <form method="POST">
                <table id="form-table">
                  <tr>
                    <td align="right"><b>Disk image: </b></td>
                    <td><input type="text" name="img" /></td>
                  </tr>
		  <tr>
		    <td align="right"><b>Location: </b></td>
		    <td>
                      <select name="bus">
                        <option value="ide">IDE Bus</option>
                        <option value="scsi">SCSI Bus</option>
                      </select>
                    </td>
		  </tr>
                  <tr>
                    <td align="right"><b>Driver: </b></td>
                    <td>
                      <select name="driver">
                        <option value="raw">raw</option>
			<option value="qcow">qcow</option>
			<option value="qcow2">qcow2</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td align="right"><b>Disk device in the guest: </b></td>
                    <td><input type="text" name="dev" value="hdb" /></td>
                  </tr>
                  <tr align="center">
                    <td colspan="2"><input type="submit" value=" Add new disk " /></td>
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
            if (confirm('Do you really want to add a new disk ?')) {
                location.href = '?name=<?= $name.'&page='.$page ?>&action=add-disk';
            }
        }
        function askDiskDeletion(disk) {
            if (confirm('Are you sure you want to delete disk '+disk+' from the guest?'))
                location.href = '?name=<?= $name.'&page='.$page.'&action=del-disk&disk=' ?>'+disk;
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

    <div class="section">Machine disk devices</div>
    <div class="item">
      <div class="label">Number of disks:</div>
      <div class="value"><?= $numDisks.$addmsg ?></div>
      <div class="nl" />
    </div>

<?php
    for ($i = 0; $i < sizeof($tmp); $i++):
      $disk = $tmp[$i];
      $bus = ($disk['bus'] == 'ide') ? 'IDE' : 'SCSI';
?>
    <!-- DISK SECTION -->
    <div class="section"><?= $bus ?> Disk <?= $i + 1 ?></div>
    <div class="item">
      <div class="label">Storage:</div>
      <div class="value"><?= $disk['file'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Driver type:</div>
      <div class="value"><?= $disk['type'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Domain device:</div>
      <div class="value"><?= $disk['device'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Capacity:</div>
      <div class="value"><?= $lv->format_size($disk['capacity'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Allocation:</div>
      <div class="value"><?= $lv->format_size($disk['allocation'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Physical disk size:</div>
      <div class="value"><?= $lv->format_size($disk['physical'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Action:</div>
      <div class="value">
        <input type="button" onclick="askDiskDeletion('<?= $disk['device'] ?>')" value=" Remove disk " />
      </div>
      <div class="nl" />
    </div>
<?
    endfor;
?>

<?php
    for ($i = 0; $i < sizeof($tmp2); $i++):
      $disk = $tmp2[$i];
      $bus = ($disk['bus'] == 'ide') ? 'IDE' : 'SCSI';
?>
    <!-- DISK SECTION -->
    <div class="section"><?= $bus ?> CD-ROM <?= $i + 1 ?></div>
    <div class="item">
      <div class="label">Storage:</div>
      <div class="value"><?= $disk['file'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Driver type:</div>
      <div class="value"><?= $disk['type'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Domain device:</div>
      <div class="value"><?= $disk['device'] ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Capacity:</div>
      <div class="value"><?= $lv->format_size($disk['capacity'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Allocation:</div>
      <div class="value"><?= $lv->format_size($disk['allocation'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Physical disk size:</div>
      <div class="value"><?= $lv->format_size($disk['physical'], 2) ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">Action:</div>
      <div class="value">
        <input type="button" onclick="askDiskDeletion('<?= $disk['device'] ?>')" value=" Remove disk " />
      </div>
      <div class="nl" />
    </div>

<?php
    endfor;
    unset($tmp);
    unset($tmp2);
?>

    <!-- ACTIONS SECTION -->
    <div class="section">Actions</div>
    <div class="item">
      <div class="label">Changes:</div>
      <div class="value">
        <input type="button" name="add-disk" value=" Add new disk " onclick="confirmAddition()" />
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
