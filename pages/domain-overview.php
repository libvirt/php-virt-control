<?php
	$name = $vm;

	$res = $lvObject->getDomainObject($vm);
	$uuid = $lvObject->domainGetUUIDString($res);
	$info = $lvObject->domainGetInfo($name);
	$status = $lvObject->translateDomainState($info['state']);
	$desc = $lvObject->domainGetDescription($res);
	$arch = $lvObject->domainGetArch($res);
	$apic = $lvObject->domainGetFeature($res, 'apic');
	$acpi = $lvObject->domainGetFeature($res, 'acpi');
	$pae  = $lvObject->domainGetFeature($res, 'pae');
	$hap  = $lvObject->domainGetFeature($res, 'hap');
	$clock = $lvObject->domainGetClockOffset($res);
	$hostArch = $lvObject->getHostArchitecture();
	$domType = $lvObject->getDomainType($res);
	$domMachType = $lvObject->getDomainMachineType($res);
	unset($info);
?>

	<div id="s-overview" <?php if (!$subpage_active) echo ' style="display:none"' ?>>

	<!-- GENERAL SECTION -->
	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('general') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('name') ?>:</td>
			<td class="field">
				<?php echo $name ?>
			</td>
		</tr>
		<tr>
			<td class="title">UUID:</td>
			<td class="field"><?php echo $uuid ?></td>
			<input type="hidden" id="domainUUID" value="<?php echo $uuid ?>" />
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('state') ?>:</td>
			<td class="field"><?php echo $status ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('description') ?>:</td>
			<td class="field">
				<textarea rows="5" cols="60" name="description"><?php echo $desc ?></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('vm-details') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('arch') ?>:</td>
			<td class="field">
				<select name="arch" id="arch" onchange="checkArchCompatibility()">
<?php
	$archs = $lvObject->getAllSupportedArchitectures();

	for ($i = 0; $i < sizeof($archs); $i++)
		echo '<option value="'.$archs[$i].'" '.($archs[$i] == $arch ? ' selected="selected"' : '').'>'.$archs[$i].'</option>';
?>
				<?php echo $arch ?>
				</select>
				(<?php echo $lang->get('host-running').' '.$hostArch ?>)
				<input type="hidden" id="arch_old" name="arch_old" value="<?php echo $arch ?>" />
				<div id="msg-error" style="display: none"><?php echo $lang->get('incompatible-archs') ?></div>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('emulator-type') ?>:</td>
			<td class="field">
				<select name="emulatorType" id="emulatorType" onchange="changeMachineType()">
<?php
	$types = $lvObject->getTypesForArchitecture($arch);
	$ak = array_keys($types);
	for ($j = 0; $j < sizeof($ak); $j++)
		echo '<option value="'.$types[$ak[$j]].'" '.($types[$ak[$j]] == $domType ? ' selected="selected"' : '').'>'.$types[$ak[$j]].'</option>';
?>
				<?php echo $arch ?>
				</select>
				<input type="hidden" id="emulatorType_old" name="emulatorType_old" value="<?php echo $domType ?>" />
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('machine-type') ?>:</td>
			<td class="field">
				<select name="machineType" id="machineType">
<?php
	$tmp = $lvObject->getEmulatorInformationForArchitecture($arch);
	$machines = $tmp[$domType]['machines'];
	$ak = array_keys($machines);
	for ($j = 0; $j < sizeof($ak); $j++)
		echo '<option value="'.$machines[$ak[$j]].'" '.($machines[$ak[$j]] == $domMachType ? ' selected="selected"' : '').'>'.$machines[$ak[$j]].'</option>';
?>
				<?php echo $arch ?>
				</select>
				<input type="hidden" id="machineType_old" name="machineType_old" value="<?php echo $domMachType ?>" />
			</td>
		</tr>
		<tr>
			<td class="title">
				<?php echo $lang->get('cpu-features') ?>:
			</td>
			<td class="field">&nbsp;</td>
		</tr>
		<tr>
			<td class="title">
				<input type="checkbox" value="1" <?php echo ($apic ? 'checked="checked"' : '') ?> id="feature_apic" />
				<input type="hidden" id="feature_apic_old" value="<?php echo ($apic ? 1 : 0) ?>" />
			</td>
			<td class="field">APIC</td>
		</tr>
		<tr>
			<td class="title">
				<input type="checkbox" value="1" <?php echo ($acpi ? 'checked="checked"' : '') ?> id="feature_acpi" />
				<input type="hidden" id="feature_acpi_old" value="<?php echo ($acpi ? 1 : 0) ?>" />
			</td>
			<td class="field">ACPI</td>
		</tr>
		<tr>
			<td class="title">
				<input type="checkbox" value="1" <?php echo ($pae ? 'checked="checked"' : '') ?> id="feature_pae" />
				<input type="hidden" id="feature_pae_old" value="<?php echo ($pae ? 1 : 0) ?>" />
			</td>
			<td class="field">PAE</td>
		</tr>
		<tr>
			<td class="title">
				<input type="checkbox" value="1" <?php echo ($hap ? 'checked="checked"' : '') ?> id="feature_hap" />
				<input type="hidden" id="feature_hap_old" value="<?php echo ($hap ? 1 : 0) ?>" />
			</td>
			<td class="field">HAP</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('clock-offset') ?>:</td>
			<td class="field">
				<select name="clock_offset" id="clock_offset">
					<option value="utc" <?php echo ($clock == 'utc'  ? 'selected="selected"' : '') ?>>UTC</option>
					<option value="localtime" <?php echo ($clock == 'localtime'  ? 'selected="selected"' : '') ?>>localtime</option>
				</select>
				<input type="hidden" id="clock_offset_old" value="<?php echo $clock ?>" />
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

