  <!-- CONTENTS -->
  <div id="content">

    <form action="#" method="POST">

    <div class="section">Machine multimedia devices</div>
    <div class="item">
      <div class="label">Console:</div>
      <div class="value"><?= $lv->domain_get_multimedia_device($res, 'console', true) ?></div>
      <div class="nl" />
    </div>

    <div class="item">
      <div class="label">Input device:</div>
      <div class="value"><?= $lv->domain_get_multimedia_device($res, 'input', true) ?></div>
      <div class="nl" />
    </div>

    <div class="item">
      <div class="label">Graphics device:</div>
      <div class="value"><?= $lv->domain_get_multimedia_device($res, 'graphics', true) ?></div>
      <div class="nl" />
    </div>

    <div class="item">
      <div class="label">Video:</div>
      <div class="value"><?= $lv->domain_get_multimedia_device($res, 'video', true) ?></div>
      <div class="nl" />
    </div>

    <!-- ACTIONS SECTION -->
    <div class="section">Actions</div>
    <div class="item">
      <div class="label">Changes:</div>
      <div class="value">
        None (this page is currently read-only)
      </div>
      <div class="nl" />
    </div>

    </form>

  </div>
