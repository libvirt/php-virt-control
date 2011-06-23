<?php
  if (array_key_exists('x', $_GET)) {
    ob_end_clean();
    $tmp = $lv->domain_send_pointer_event($name, $_GET['x'], $_GET['y'], 1, true);
    if (!$tmp)
	die( 'Error occured while sending pointer event: '.$lv->get_last_error() );

    die( $_GET['x'].','.$_GET['y'] );
  }

  $interval = array_key_exists('interval', $_POST) ? $_POST['interval'] : 5;
  $msg = '';
  if (!$lv->domain_is_running($name))
    $msg = 'Domain is not running';
  if (!$lv->supports('screenshot'))
    $msg = 'Host machine doesn\'t support getting domain screenshots';

  if (array_key_exists('keys', $_POST)) {
    $keys = $_POST['keys'];
    if (!strstr( $_POST['submit'], 'without' ))
	$keys .= '\n';

    $lv->domain_send_keys($name, $keys);
  }

 function error($w, $h, $msg) {
    $im = imagecreatetruecolor($w, $h);
    $text_color = imagecolorallocate($im, 233, 14, 11);
    imagestring($im, 5, 5, 20, 'We are sorry!', $text_color);

    $arr = explode("\n", $msg);
    for ($i = 0; $i < sizeof($arr); $i++) {
      imagestring($im, 5, 5, 50 + ($i * 20), $arr[$i], $text_color);
    }

    imagepng($im);
    imagedestroy($im);
 }

  if (array_key_exists('data', $_GET) && ($_GET['data'] == 'png')) {
    ob_end_clean();
    $tmp = $lv->domain_get_screenshot($name);
    Header('Content-Type: image/png');
    if (!$tmp)
        error(240, 130, "Cannot get the domain\nscreenshot for domain\nrequested.");
    else
        echo $tmp;

    exit;
  }
?>
  <!-- CONTENTS -->
  <div id="content">

<?php
    if ($msg):
?>
    <div class="section">Domain screenshot</div>
    <div id="msg"><b>Message: </b><?= $msg ?></div>
<?php
    else:
	$dims = $lv->domain_get_screen_dimensions($name);
?>

<div id="ajax-msg"></div>

    <script language="javascript">
    <!--
        timerId = null;
        delay = <?= $interval * 1000 ?>;
	var IE = document.all ? true : false;
	if (!IE) document.captureEvents(Event.MOUSEMOVE)
	document.onmousemove = getMouseXY;

	var tempX = 0
	var tempY = 0
	var screenshotX = 0
	var screenshotY = 0
	var imgX = 0
	var imgY = 0

	function sendMouse() {
		var ajaxRequest;
		try {
			ajaxRequest = new XMLHttpRequest();
		} catch (e) {
			try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) {
					alert("Cannot activate AJAX object!");
					return false;
				}
			}
		}
		ajaxRequest.onreadystatechange = function(){
			if(ajaxRequest.readyState == 4){
				var ajaxDisplay = document.getElementById('ajax-msg');
				ajaxDisplay.innerHTML = ajaxRequest.responseText;
			}
		}
		var loc = "<?= $_SERVER['REQUEST_URI'] ?>&x="+imgX+"&y="+imgY;
		ajaxRequest.open("GET", loc, true);
		ajaxRequest.send(null); 
	}

        function change_interval() {
		val = document.getElementById('interval').value;
		delay = val * 1000;
		alert('Delay has been changed to '+val+' seconds');
	}

        function update_screenshot() {
                src = "<?= $_SERVER['REQUEST_URI'].'&data=png' ?>";
                var date = new Date();
                src = src + '&date=' + date.getTime()
                document.getElementById('screenshot').src = src;

                clearTimeout(timerId);
                timerID = setTimeout("update_screenshot()", delay);
        }

	function change_interval() {
		val = document.getElementById('interval').value;
		delay = val * 1000;
		alert('Delay has been changed to '+val+' second(s)');

		update_screenshot();
	}

	function findPosX(obj)
	{
		var curleft = 0;
		if(obj.offsetParent)
			while(1) {
				curleft += obj.offsetLeft;
        		  	if(!obj.offsetParent)
			        	break;
				obj = obj.offsetParent;
			}
		else if(obj.x)
			curleft += obj.x;
		return curleft;
	}

	function findPosY(obj)
	{
		var curtop = 0;
		if(obj.offsetParent)
			while(1) {
				curtop += obj.offsetTop;
				if(!obj.offsetParent)
					break;
				obj = obj.offsetParent;
			}
		else if(obj.y)
			curtop += obj.y;
		return curtop;
	}

	function getScreenshotPos() {
		img = document.getElementById('screenshot');

		screenshotX = findPosX(img);
		screenshotY = findPosY(img);
	}

	function getMouseXY(e) {
		if (IE) {
			tempX = event.clientX + document.body.scrollLeft
			tempY = event.clientY + document.body.scrollTop
		} else {
			tempX = e.pageX
			tempY = e.pageY
		}
		if (tempX < 0) tempX = 0;
		if (tempY < 0) tempY = 0;

		if ((screenshotX == 0) || (screenshotY == 0)) {
			setTimeout("getScreenshotPos()", 500);
			return false;
		}

		imgX = tempX - screenshotX;
		imgY = tempY - screenshotY;

		if (((imgX > <?= $dims['width'] ?>) || (imgY > <?= $dims['height'] ?>))
			|| (imgX < 0) || (imgY < 0)) {
			imgX = 0;
			imgY = 0;
		}
	}

	function screenshotClick() {
		if ((imgX <= 0) || (imgY <= 0))
			return;

		sendMouse();
	}

        timerID = setTimeout("update_screenshot()", delay);
    -->
    </script>

    <!-- SETTINGS SECTION -->
    <form class="table-form" method="POST">
    <div class="section">Settings</div>
    <div class="item">
      <div class="label">Interval (sec):</div>
      <div class="value">
	<input type="text" name="interval" value="<?= $interval ?>" id="interval">
	<input type="button" value=" Change " onclick="change_interval()">
      </div>
      <div class="nl" />
    </div>

    <div class="section">Domain screenshot</div>

    <div class="screenshot"><img id="screenshot" src="<?= $_SERVER['REQUEST_URI'] ?>&amp;data=png" onclick="screenshotClick()"><br />
    <form class="table-form" method="POST">
    <tr>
      <td><input type="text" name="keys" style="width: <?= $dims['width'] - 260 ?>px" autocomplete="off">
	<input type="submit" name="submit" value="Send keys" style="width: 100px">
	<input type="submit" name="submit" value="Send without Enter" style="width: 150px">
      </td>
    </tr>
    </div>
<?php
    endif;
?>

    </form>

  </div>
