<?php
	$res = $lvObject->getDomainObject($vm);
?>
	<div id="s-multimedia" <?php if (!$subpage_active) echo ' style="display:none"' ?>>

	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $lang->get('vm-multimedia') ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-multimedia-console') ?>:</td>
			<td class="field">
				<?php echo $lvObject->domainGetMultimediaDevice($res, 'console', true) ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-multimedia-input') ?>:</td>
			<td class="field">
				<?php echo $lvObject->domainGetMultimediaDevice($res, 'input', true) ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-multimedia-graphics') ?>:</td>
			<td class="field">
				<?php echo $lvObject->domainGetMultimediaDevice($res, 'graphics', true) ?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-multimedia-sound') ?>:</td>
			<td class="field">
				<?php
					$tmp = $lvObject->domainGetMultimediaDevice($res, 'sound', true);
					if ($tmp == false)
						echo $lang->get('vm-soundhw-type-none');
					else
						echo $tmp;
				?>
			</td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('vm-multimedia-video') ?>:</td>
			<td class="field">
				<?php echo $lvObject->domainGetMultimediaDevice($res, 'video', true) ?>
			</td>
		</tr>
	</table>

	</div>
