<?php
	$id = array_key_exists('id', $_GET) ? $_GET['id'] : false;
	$sent = array_key_exists('sent', $_POST) ? $_POST['sent'] : false;

	if ($connObj->isMyConnection($id)):
		if ($sent) {
			$ids = empty($_POST['userId']) ? array() : array_keys($_POST['userId']);
			$oldIds = explode(',', $_POST['oldIds']);

			$newIds = array();
			$lessIds = array();
			for ($i = 0; $i < sizeof($ids); $i++) {
				$f = false;
				for ($j = 0; $j < sizeof($oldIds); $j++)
					if ($ids[$i] == $oldIds[$j])
						$f = true;

				if (!$f)
					$newIds[] = $ids[$i];
			}

			for ($i = 0; $i < sizeof($oldIds); $i++) {
				$f = false;
				for ($j = 0; $j < sizeof($ids); $j++)
					if ($oldIds[$i] == $ids[$j])
						$f = true;

				if (!$f) {
					if ($oldIds[$i])
						$lessIds[] = $oldIds[$i];
				}
			}

			for ($i = 0; $i < sizeof($newIds); $i++)
				$connObj->addUserAssoc($id, $newIds[$i]);
			for ($i = 0; $i < sizeof($lessIds); $i++)
				$connObj->delUserAssoc($id, $lessIds[$i]);
		}
?>

<h1><?php echo $lang->get('my-connection-assoc') ?></h1>

<?php
	if (isset($msg))
		echo "<div id=\"msg-info\"><b>{$lang->get('msg')}: </b>$msg</div>";
?>

	<form method="POST">

	<!-- GENERAL SECTION -->
	<table id="connections-edit" width="100%">
		<tr>
			<td colspan="2" class="section"><?php echo $connObj->getName($id) ?></td>
		</tr>
		<tr>
			<td class="title"><?php echo $lang->get('username') ?>: </td>
			<td class="field-checkbox">
<?php
	$ret = $connObj->getAllowedUsers($id, false);
	$users = $user->getUsers();
	for ($i = 0; $i < sizeof($users); $i++) {
		$checked = false;
		for ($j = 0; $j < sizeof($ret); $j++)
			if ($ret[$j]['id'] == $users[$i]['id'])
				$checked = true;
		echo '<input type="checkbox" name="userId['.$users[$i]['id'].']" value="1" '.($checked ? ' checked="checked"' : '').'/>'.$users[$i]['username'].'<br />';
	}
?>
			</td>
		</tr>
		<tr>
			<td class="title">&nbsp;</td>
			<td class="field"><input type="submit" class="submit" style="cursor: pointer" value="<?php echo $lang->get('user-assoc-submit') ?>" /></td>
			<input type="hidden" name="id" value="<?php echo $id ?>" />
			<input type="hidden" name="sent" value="1" />
			<input type="hidden" name="oldIds" value="<?php
				for ($i = 0; $i < sizeof($ret); $i++)
					echo $ret[$i]['id'].',';
			?>" />
		</tr>
	</table>

	</form>
<?php
	else:
		echo '<div id="msg-error">'.$lang->get('permission-denied').'</div>';
	endif;
?>
