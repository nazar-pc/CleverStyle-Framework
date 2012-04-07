<?php
if (!isset($_POST['mode'])) {
	return;
}
global $Config, $Page, $Index, $User, $L;
switch ($_POST['mode']) {
	case 'edit_raw':
		$User->set($_POST['user'], false, $_POST['user']['id']);
		$User->__finish();
		$Index->save();
	break;
	case 'edit':
		$user_data = &$_POST['user'];
		$columns = array(
			'id',
			'login',
			'username',
			'password',
			'email',
			'language',
			'timezone',
			'status',
			'block_until',
			'gender',
			/*'country',
			'region',
			'district',
			'city',*/
			'birthday',
			'avatar',
			'website',
			'icq',
			'skype',
			'about'
		);
		foreach ($user_data as $item => &$value) {
			if (in_array($item, $columns) && $item != 'data') {
				$value = xap($value, false);
			} elseif ($item != 'data') {
				unset($user_data[$item]);
			}
		}
		unset($item, $value, $columns);
		if ($_POST['user']['block_until'] > TIME) {
			$block_until				= $user_data['block_until'];
			$block_until				= explode('T', $block_until);
			$block_until[0]				= explode('-', $block_until[0]);
			$block_until[1]				= explode(':', $block_until[1]);
			$user_data['block_until']	= mktime(
				$block_until[1][0],
				$block_until[1][1],
				0,
				$block_until[0][1],
				$block_until[0][2],
				$block_until[0][0]
			);
			unset($block_until);
		} else {
			$user_data['block_until']	= 0;
		}
		if ($_POST['user']['birthday'] < TIME) {
			$birthday				= $user_data['birthday'];
			$birthday				= explode('-', $birthday);
			$user_data['birthday']	= mktime(
				0,
				0,
				0,
				$birthday[1],
				$birthday[2],
				$birthday[0]
			);
			unset($birthday);
		} else {
			$user_data['block_until']	= 0;
		}
		if ($user_data['password']) {
			if (strlen($user_data['password']) < $Config->core['password_min_length']) {
				$Page->warning($L->password_too_short);
			} elseif (password_check($user_data['password']) < $Config->core['password_min_strength']) {
				$Page->warning($L->password_too_easy);
			} else {
				$user_data['password_hash'] = hash('sha512', $user_data['password']);
			}
		}
		unset($user_data['password']);
		if ($user_data['login']) {
			$user_data['login_hash'] = hash('sha224', $user_data['login']);
		}
		if ($user_data['email']) {
			$user_data['email_hash'] = hash('sha224', $user_data['email']);
		}
		$User->set($user_data, '', $user_data['id']);
		$User->__finish();
		$Index->save(true);
	break;
	case 'deactivate':
		$id = (int)$_POST['id'];
		if ($id != 1 && $id != 2) {
			$User->set('status', 0, $id);
			$Index->save(true);
		}
	break;
	case 'activate':
		$id = (int)$_POST['id'];
		if ($id != 1 && $id != 2) {
			$User->set('status', 1, $id);
			$Index->save(true);
		}
	break;
}