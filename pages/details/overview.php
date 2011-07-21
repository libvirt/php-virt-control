<?php
	if ((array_key_exists('ch-discard', $_POST)) && ($_POST['ch-discard']))
		Die(Header('Location: '.$_SERVER['REQUEST_URI']));
	if ((array_key_exists('ch-apply', $_POST)) && ($_POST['ch-apply'])) {
		$features = array('apic', 'acpi', 'pae', 'hap');
		for ($i = 0; $i < sizeof($features); $i++) {
			$feature = $features[$i];
			$val = (array_key_exists('feature_'.$feature, $_POST) && ($_POST['feature_'.$feature])) ? true : false;

			$lv->domain_set_feature($res, $feature, $val);
		}

		$lv->domain_set_clock_offset($res, $_POST['clock_offset']);
		$lv->domain_set_description($res, $_POST['description']);
	}

	$uuid = libvirt_domain_get_uuid_string($res);
	$info = $lv->domain_get_info($name);
	$status = $lv->domain_state_translate($info['state']);
	$desc = $lv->domain_get_description($res);
	$arch = $lv->domain_get_arch($res);
	$apic = $lv->domain_get_feature($res, 'apic');
	$acpi = $lv->domain_get_feature($res, 'acpi');
	$pae  = $lv->domain_get_feature($res, 'pae');
	$hap  = $lv->domain_get_feature($res, 'hap');
	$clock = $lv->domain_get_clock_offset($res);
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
                return (confirm('<?= $lang->get('ask_apply') ?>'));
            if (change_el == 'ch-discard')
                return (confirm('<?= $lang->get('ask_discard') ?>'));
        }
    -->
    </script>

    <form action="#" method="POST" onsubmit="return check();">

    <!-- GENERAL SECTION -->
    <div class="section"><?= $lang->get('general') ?></div>
    <div class="item">
      <div class="label"><?= $lang->get('name') ?>:</div>
      <div class="value"><?= $name ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">UUID:</div>
      <div class="value"><?= $uuid ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?= $lang->get('state') ?>:</div>
      <div class="value"><?= $status ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?= $lang->get('description') ?>:</div>
      <div class="value">
        <textarea rows="10" cols="60" name="description"><?= $desc ?></textarea>
      </div>
      <div class="nl" />
    </div>
    <!-- MACHINE DETAILS SECTION -->
    <div class="section"><?= $lang->get('vm_details') ?>: </div>
    <div class="item">
      <div class="label"><?= $lang->get('arch') ?>:</div>
      <div class="value"><?= $arch ?></div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">APIC:</div>
      <div class="value">
        <input type="checkbox" value="1" <?= ($apic ? 'checked="checked"' : '') ?> name="feature_apic" />
      </div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">ACPI:</div>
      <div class="value">
        <input type="checkbox" value="1" <?= ($acpi ? 'checked="checked"' : '') ?> name="feature_acpi" />
      </div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">PAE:</div>
      <div class="value">
        <input type="checkbox" value="1" <?= ($pae ? 'checked="checked"' : '') ?> name="feature_pae" />
      </div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label">HAP:</div>
      <div class="value">
        <input type="checkbox" value="1" <?= ($hap ? 'checked="checked"' : '') ?> name="feature_hap" />
      </div>
      <div class="nl" />
    </div>
    <div class="item">
      <div class="label"><?= $lang->get('clock-offset') ?>:</div>
      <div class="value">
        <select name="clock_offset">
          <option value="utc" <?= ($clock == 'utc'  ? 'selected="selected"' : '') ?>>UTC</option>
          <option value="localtime" <?= ($clock == 'localtime'  ? 'selected="selected"' : '') ?>>localtime</option>
        </select>
      </div>
      <div class="nl" />
    </div>
    <!-- ACTIONS SECTION -->
    <div class="section"><?= $lang->get('actions') ?></div>
    <div class="item">
      <div class="label"><?= $lang->get('changes') ?>:</div>
      <div class="value">
        <input type="submit" name="ch-apply" value=" <?= $lang->get('btn_apply') ?> " onclick="setElement('change', this)" />
        <input type="submit" name="ch-discard" value=" <?= $lang->get('btn_discard') ?> " onclick="setElement('change', this)" />
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
