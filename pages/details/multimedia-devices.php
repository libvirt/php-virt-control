  <!-- CONTENTS -->
  <div id="content">

    <form action="#" method="POST">

    <div class="section"><?= $lang->get('vm_multimedia_title') ?></div>
    <div class="item">
      <div class="label"><?= $lang->get('vm_multimedia_console') ?>:</div>
      <div class="value"><?= $lv->domain_get_multimedia_device($res, 'console', true) ?></div>
      <div class="nl" />
    </div>

    <div class="item">
      <div class="label"><?= $lang->get('vm_multimedia_input') ?>:</div>
      <div class="value"><?= $lv->domain_get_multimedia_device($res, 'input', true) ?></div>
      <div class="nl" />
    </div>

    <div class="item">
      <div class="label"><?= $lang->get('vm_multimedia_graphics') ?>:</div>
      <div class="value"><?= $lv->domain_get_multimedia_device($res, 'graphics', true) ?></div>
      <div class="nl" />
    </div>

    <div class="item">
      <div class="label"><?= $lang->get('vm_multimedia_video') ?>:</div>
      <div class="value"><?= $lv->domain_get_multimedia_device($res, 'video', true) ?></div>
      <div class="nl" />
    </div>

    <!-- ACTIONS SECTION -->
    <div class="section"><?= $lang->get('actions') ?></div>
    <div class="item">
      <div class="label"><?= $lang->get('changes') ?>:</div>
      <div class="value">
        <?= $lang->get('details_readonly') ?>
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
