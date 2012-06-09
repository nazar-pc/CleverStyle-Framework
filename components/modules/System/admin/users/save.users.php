<?php
if (!isset($_POST['mode'])) {
	return;
}
global $Config, $Page, $Index, $User, $L;
switch ($_POST['mode']) {
	case 'add':
		if ($_POST['email']) {
			$result = $User->registration($_POST['email'], false);
			if ($Index->save((bool)$result)) {
				$Page->notice($L->user_was_added($_POST['email'], $result['password']));
			}
		}
	break;
	case 'add_bot':
		if ($_POST['name'] && ($_POST['user_agent'] || $_POST['ip'])) {
			$Index->save($User->add_boot($_POST['name'], $_POST['user_agent'], $_POST['ip']));
		}
	break;
	case 'edit_raw':
		if (
			isset($_POST['user']['id']) &&
			$_POST['user']['id'] != 1 &&
			$_POST['user']['id'] != 2 &&
			!in_array(3, (array)$User->get_user_groups($_POST['user']['id']))
		) {
			$User->set($_POST['user'], null, $_POST['user']['id']);
			$User->__finish();
			$Index->save(true);
		}
	break;
	case 'edit':
		if (isset($_POST['user']) && !in_array(3, (array)$User->get_user_groups($_POST['user']['id']))) {
			if ($_POST['user']['id'] == 1 || $_POST['user']['id'] == 2) {
				break;
			}
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
		} elseif (isset($_POST['bot'])) {

		}
	break;
	case 'deactivate':
		if (isset($_POST['id'])) {
			if ($_POST['id'] == 1 || $_POST['id'] == 2) {
				break;
			}
			$id = (int)$_POST['id'];
			if ($id != 1 && $id != 2) {
				$User->set('status', 0, $id);
				$Index->save(true);
			}
		}
	break;
	case 'activate':
		if (isset($_POST['id'])) {
			if ($_POST['id'] == 1 || $_POST['id'] == 2) {
				break;
			}
			$id = (int)$_POST['id'];
			if ($id != 1 && $id != 2) {
				$User->set('status', 1, $id);
				$Index->save(true);
			}
		}
	break;
	case 'permissions':
		if (isset($_POST['id'], $_POST['permission'])) {
			if ($_POST['id'] == 2) {
				break;
			}
			$Index->save(
				$User->set_user_permissions($_POST['permission'], $_POST['id'])
			);
		}
	break;
	case 'groups':
		if (isset($_POST['user'], $_POST['user']['id'], $_POST['user']['groups']) && $_POST['user']['groups']) {
			if ($_POST['user']['id'] == 2 || in_array(3, (array)$User->get_user_groups($_POST['user']['id']))) {
				break;
			}
			$_POST['user']['groups'] = _json_decode($_POST['user']['groups']);
			foreach ($_POST['user']['groups'] as &$group) {
				$group = (int)substr($group, 5);
			}
			unset($group);
			$Index->save(
				$User->set_user_groups($_POST['user']['groups'], $_POST['user']['id'])
			);
		}
	break;
}