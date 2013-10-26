<?php
	$name = $vm;

	$devs = $lvObject->domainGetBootDevices($name);
	$bd_1st = $devs[0];
	$bd_2nd = (sizeof($devs) > 1) ? $devs[1] : '-';
	unset($devs);
?>

	<div id="s-boot" <?php if (!$subpage_active) echo ' style="display:none"' ?>>

	<!-- GENERAL SECTION -->
	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('vm-boot-opts') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-boot-dev1') ?>:</td>
			<td class="field">
				<select name="bd_1st" id="bd_1st">
				  <option value="hd" <?php echo (($bd_1st == 'hd') ? 'selected="selected"' : '') ?>><?php echo $lang->get('vm-boot-hdd') ?></option>
				  <option value="cdrom" <?php echo (($bd_1st == 'cdrom') ? 'selected="selected"' : '') ?>><?php echo $lang->get('vm-boot-cd') ?></option>
				  <option value="fd" <?php echo (($bd_1st == 'fd') ? 'selected="selected"' : '') ?>><?php echo $lang->get('vm-boot-fda') ?></option>
				  <option value="network" <?php echo (($bd_1st == 'network') ? 'selected="selected"' : '') ?>><?php echo $lang->get('vm-boot-pxe') ?></option>
				</select>
				<input type="hidden" name="bd_1st_old" id="bd_1st_old" value="<?php echo $bd_1st ?>" />
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-boot-dev2') ?>:</td>
			<td class="field">
				<select name="bd_2nd" id="bd_2nd">
			          <option value="-" <?php echo (($bd_2nd == '-') ? 'selected="selected"' : '') ?>><?php echo $lang->get('vm-boot-none') ?></option>
				  <option value="hd" <?php echo (($bd_2nd == 'hd') ? 'selected="selected"' : '') ?>><?php echo $lang->get('vm-boot-hdd') ?></option>
				  <option value="cdrom" <?php echo (($bd_2nd == 'cdrom') ? 'selected="selected"' : '') ?>><?php echo $lang->get('vm-boot-cd') ?></option>
				  <option value="fd" <?php echo (($bd_2nd == 'fd') ? 'selected="selected"' : '') ?>><?php echo $lang->get('vm-boot-fda') ?></option>
				  <option value="network" <?php echo (($bd_2nd == 'network') ? 'selected="selected"' : '') ?>><?php echo $lang->get('vm-boot-pxe') ?></option>
				</select>
				<input type="hidden" name="bd_2nd_old" id="bd_2nd_old" value="<?php echo $bd_2nd ?>" />
			</td>
		</tr>
		<tr>
			<td class="title">&nbsp;</td>
			<td class="field" style="padding-top: 25px">
				<input type="submit" class="submit" style="cursor: pointer" value="<?php echo $lang->get('save-changes') ?>" />
			</td>
		</tr>
	</table>

	</div>

