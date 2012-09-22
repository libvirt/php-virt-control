<?php
	define('MOUSE_POINTER_DEBUG', false);

	if (array_key_exists('get-dims', $_GET)) {
		ob_end_clean();
		$tmp = $lv->domain_get_screen_dimensions($name);
		if (!$tmp)
			die( 'Error occured while getting screen dimensions '.$lv->get_last_error() );

		die('ok: '.$tmp['width'].'x'.$tmp['height']);
	}

	if (array_key_exists('x', $_GET)) {
		ob_end_clean();
		$tmp = $lv->domain_send_pointer_event($name, $_GET['x'], $_GET['y'], (int)$_GET['bm'], $_GET['autorelease'] ? true : false);
		if (!$tmp)
			die( 'Error occured while sending pointer event: '.$lv->get_last_error() );

		//die( $_GET['x'].','.$_GET['y'] );
		die('ok');
	}

	if (array_key_exists('send_keys', $_GET)) {
		ob_end_clean();
		$tmp = $lv->domain_send_keys($name, $_GET['send_keys']);
		if (!$tmp)
			die( 'Error occured while sending keys: '.$lv->get_last_error() );

		die('ok');
	}

	$interval = array_key_exists('interval', $_POST) ? $_POST['interval'] : 1;
	$msg = '';
	if (!$lv->domain_is_running($name))
		$msg = 'Domain is not running';
	if (!$lv->supports('screenshot'))
		$msg = 'Host machine doesn\'t support getting domain screenshots';

	function error($w, $h, $msg) {
		$im = imagecreatetruecolor($w, $h);
		$text_color = imagecolorallocate($im, 233, 14, 11);
		imagestring($im, 5, 5, 20, 'We are sorry!', $text_color);

		$arr = explode("\n", $msg);
		for ($i = 0; $i < sizeof($arr); $i++)
			imagestring($im, 5, 5, 50 + ($i * 20), $arr[$i], $text_color);

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
    <div class="section"><?php echo $lang->get('dom-screenshot') ?></div>
    <div id="msg"><b><?php echo $lang->get('msg') ?>: </b><?php echo $msg ?></div>
<?php
	else:
		$dims = $lv->domain_get_screen_dimensions($name);
?>

<div id="ajax-msg"></div>

    <script language="javascript">
    <!--
        timerId = null;
        delay = <?php echo $interval * 1000 ?>;

<?php
	if (ALLOW_EXPERIMENTAL_VNC):
?>
	var IE = document.all ? true : false;
	if (!IE) document.captureEvents(Event.MOUSEMOVE || Event.CLICK)
	document.onmousemove = getMouseXY;
	document.onmousedown = handleMouseClick;

	var gButtonMask = 0;
	var tempX = 0;
	var tempY = 0;
	var screenshotX = 0;
	var screenshotY = 0;
	var imgX = 0;
	var imgY = 0;
	var maxWidth = <?php echo $dims['width'] ?>;
	var maxHeight = <?php echo $dims['height'] ?>;
	document['onkeypress'] = detectEvent;
	var req_data = false;

	function get_time() {
		return Math.round((new Date()).getTime());
	}

        function request_data(uri) {
		var xmlhttp;
		if (window.XMLHttpRequest)
			xmlhttp = new XMLHttpRequest();
		else
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");

		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				req_data = xmlhttp.responseText;
			}
		}

		xmlhttp.open("GET", uri, true);
		xmlhttp.send(null);

		start = get_time();
		while (!req_data) {
			end = get_time();
			total = end - start;

			/* Timeout of 100ms exceeded */
			if (total > 100)
				break;
		}

		return req_data;
	}

	function write_error(msg) {
		if (msg.indexOf('ok') != -1) {
			// Invalid result. Should be dimensions results so set width
			parseAndSetKeyBoxWidth(msg);
			return;
		}

		document.getElementById('ajax-msg').innerHTML = msg;
	}

        function write_message(msg) {
                document.getElementById('ajax-msg').innerHTML = msg;
        }

	function parseAndSetKeyBoxWidth(data) {
		if (tmp.indexOf('x') == -1)
			return;

		tmp = data.split(':')[1];
		data = tmp.split('x');
		maxWidth = parseInt(data[0]);
		maxHeight = parseInt(data[1]);
		width = maxWidth - 260;
		document.getElementById('keys').style.width = width+"px";
	}

	function sendMouse() {
<?php
	if (MOUSE_POINTER_DEBUG):
?>
                write_message('Click intercepted on position x = '+imgX+', y = '+imgY+', debugging => screenshot at ['+
                                screenshotX+', '+screenshotY+'], temp at ['+tempX+', '+tempY+'], buttonMask = '+gButtonMask);
<?php
	endif;
?>
		autorelease = (document.getElementById('setting-autorelease').checked) ? 1 : 0;
		data = request_data('<?php echo $_SERVER['REQUEST_URI'] ?>&x='+imgX+'&y='+imgY+'&bm='+gButtonMask+'&autorelease='+autorelease);
		if (!data)
			return;

		if (data.indexOf('ok') != -1) {
			// Invalid result. Should be dimensions results so set width
			parseAndSetKeyBoxWidth(data);
			return;
		}

		if (data) {
			update_screenshot();
			write_message(data);
		}
		else
			write_message('Cannot process the request.');
	}

	function handleMouseClick(e) {
		var rightclick;
		if (!e) var e = window.event;
		if (e.which) rightclick = (e.which == 3);
		else if (e.button) rightclick = (e.button == 2);

		/* For VNC */
		if (rightclick == true)
			gButtonMask = 4;
		else
			gButtonMask = 1;

		if ((imgX <= 0) || (imgY <= 0))
			return;

		sendMouse();
	}

	function getDimensions() {
		data = request_data('<?php echo $_SERVER['REQUEST_URI'] ?>&get-dims=1');
		if (!data)
			return;

		parseAndSetKeyBoxWidth(data);
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
			tempX = event.clientX + document.body.scrollLeft;
			tempY = event.clientY + document.body.scrollTop;
		} else {
			tempX = e.pageX;
			tempY = e.pageY;
		}
		if (tempX < 0) tempX = 0;
		if (tempY < 0) tempY = 0;

		imgX = tempX - screenshotX;
		imgY = maxHeight - (tempY - screenshotY);

<?php
	if (MOUSE_POINTER_DEBUG):
?>
		write_message('Debug: x = '+imgX+', y = '+imgY+', debugging => screenshot at ['+
				screenshotX+', '+screenshotY+'], temp at ['+tempX+', '+tempY+']');
<?php
	endif;
?>

		if (((imgX > maxWidth) || (imgY > maxHeight))
			|| (imgX < 0) || (imgY < 0)) {
			imgX = 0;
			imgY = 0;
		}
	}

        function send_keys(hitEnter) {
		write_message('');
		val = document.getElementById('keys').value;
		document.getElementById('keys').value = '';
		if (hitEnter)
			val += '\\n';
		ret = request_data('<?php echo $_SERVER['REQUEST_URI'] ?>&send_keys='+val);
		if (ret != 'ok') {
			if (ret == false)
				ret = 'Cannot process Ajax request';

			if (ret.indexOf('ok:') == -1)
				write_message('Error: '+ret);
		}
		else
			update_screenshot();
	}

        function detectEvent(e) {
                if ((imgX <= 0) || (imgY <= 0))
                        return true;

                var evt = e || window.event;
                //alert(evt.type+' -> '+evt.keyCode+' ?= '+evt.charCode);

		var sChar=String.fromCharCode( evt.charCode );
		ret = request_data('<?php echo $_SERVER['REQUEST_URI'] ?>&send_keys='+sChar);
		if (ret != 'ok') {
			if (ret == false)
				ret = 'Cannot process Ajax request';
			if (ret.indexOf('ok:') == -1)
				write_message('Error: '+ret);
		}
		else
			update_screenshot();
        }

<?php
    endif;
?>
        function update_screenshot() {
		clearTimeout(timerId);
                src = "<?php echo $_SERVER['REQUEST_URI'].'&data=png' ?>";
                var date = new Date();
		cDate = date.getTime();
                src = src + '&date=' + encodeURIComponent(cDate) + encodeURIComponent(cDate + Math.floor(Math.random() * 11));
                document.getElementById('screenshot').src = src;

<?php
    if (ALLOW_EXPERIMENTAL_VNC):
?>
		getDimensions();
<?php
    endif;
?>
		/* Update time specified *after* the screenshot loaded successfully */
		document.getElementById('screenshot').onload = function() {
			timerID = setTimeout("update_screenshot()", delay);

			setTimeout("getScreenshotPos()", 250);
		}
        }

        function change_interval() {
                val = document.getElementById('interval').value;
                delay = val * 1000;
		alert('<?php echo str_replace('%d', "'+val+'", $lang->get('screenshot-delay')); ?>');
                alert('Delay has been changed to '+val+' second(s)');

                update_screenshot();
        }

        timerID = setTimeout("update_screenshot()", delay);
    -->
    </script>

    <!-- SETTINGS SECTION -->
    <form class="table-form" method="POST">
    <div class="section"><?php echo $lang->get('settings') ?></div>
    <div class="item">
      <div class="label"><?php echo $lang->get('interval_sec') ?>:</div>
      <div class="value">
	<input type="text" name="interval" value="<?php echo $interval ?>" id="interval">
	<input type="button" value=" <?php echo $lang->get('change') ?> " onclick="change_interval()">
<?php
    if (ALLOW_EXPERIMENTAL_VNC):
?>
	<input type="checkbox" id="setting-autorelease" value="1" checked="checked" /> <?php echo $lang->get('screenshot-settings-auto-release'); ?>
<?php
    endif;
?>
      </div>
      <div class="nl" />
    </div>

    <div class="section"><?php echo $lang->get('dom_screenshot') ?></div>

    <div class="screenshot"><img id="screenshot" src="<?php echo $_SERVER['REQUEST_URI'] ?>&amp;data=png" oncontextmenu="return false;" /><br />
<?php
    if (ALLOW_EXPERIMENTAL_VNC):
?>
    <form class="table-form" method="POST">
    <tr>
      <td><input type="text" id="keys" style="width: <?php echo $dims['width'] - 260 ?>px" autocomplete="off">
	<input type="button" value="Send keys" style="width: 100px" onclick="send_keys(true)">
	<input type="button" value="Send without Enter" style="width: 150px" onclick="send_keys(false)">
      </td>
    </tr>
    </div>
    <div id="ajax-msg2"></div>
<?php
    endif;
    endif;
?>

    </form>

  </div>
