<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Cache	= Cache::instance();
$Config	= Config::instance();
$L		= Language::instance();
$Text	= Text::instance();
$User	= User::instance();
$a		= Index::instance();
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'add':
		case 'edit':
			$block_new = &$_POST['block'];
			if ($_POST['mode'] == 'add') {
				$block	= [
					'position'	=> 'floating',
					'type'		=> xap($block_new['type']),
					'index'		=> substr(TIME, 3)
				];
			} else {
				$block	= &$Config->components['blocks'][$block_new['id']];
			}
			$block['title']		= $Text->set(
				$Config->module('System')->db('texts'),
				'System/Config/blocks/title',
				$block['index'],
				$block_new['title']
			);
			$block['active']	= $block_new['active'];
			$block['template']	= $block_new['template'];
			$block['start']		= $block_new['start'];
			$start				= &$block_new['start'];
			$start				= explode('T', $start);
			$start[0]			= explode('-', $start[0]);
			$start[1]			= explode(':', $start[1]);
			$block['start']		= mktime($start[1][0], $start[1][1], 0, $start[0][1], $start[0][2], $start[0][0]);
			unset($start);
			if ($block_new['expire']['state']) {
				$expire				= &$block_new['expire']['date'];
				$expire				= explode('T', $expire);
				$expire[0]			= explode('-', $expire[0]);
				$expire[1]			= explode(':', $expire[1]);
				$block['expire']	= mktime($expire[1][0], $expire[1][1], 0, $expire[0][1], $expire[0][2], $expire[0][0]);
				unset($expire);
			} else {
				$block['expire']	= 0;
			}
			if ($block['type'] == 'html') {
				$block['content'] = $Text->set(
					$Config->module('System')->db('texts'),
					'System/Config/blocks/content',
					$block['index'],
					xap($block_new['html'], true)
				);
			} elseif ($block['type'] == 'raw_html') {
				$block['content'] = $Text->set(
					$Config->module('System')->db('texts'),
					'System/Config/blocks/content',
					$block['index'],
					$block_new['raw_html']
				);
			} elseif ($_POST['mode'] == 'add') {
				$block['content'] = '';
			}
			if ($_POST['mode'] == 'add') {
				$Config->components['blocks'][] = $block;
				$User->add_permission('Block', $block['index']);
			} else {
				unset($Cache->{'blocks/'.$block['index'].'_'.$L->clang});
			}
			unset($block, $block_new);
			$a->save();
		break;
		case 'delete':
			if (isset($_POST['id'], $Config->components['blocks'][$_POST['id']])) {
				$block = &$Config->components['blocks'][$_POST['id']];
				$User->del_permission(
					$User->get_permission(
						null,
						'Block',
						$block['index']
					)[0]['id']
				);
				$Text->del(
					$Config->module('System')->db('texts'),
					'System/Config/blocks/title',
					$block['index']
				);
				$Text->del(
					$Config->module('System')->db('texts'),
					'System/Config/blocks/content',
					$block['index']
				);
				unset(
					$Cache->{'blocks/'.$block['index'].'_'.$L->clang},
					$block,
					$Config->components['blocks'][$_POST['id']]
				);
				$a->save();
			}
		break;
		case 'permissions':
			if (isset($_POST['block'], $_POST['block']['id'], $Config->components['blocks'][$_POST['block']['id']])) {
				$permission = $User->get_permission(
					null,
					'Block',
					$Config->components['blocks'][$_POST['block']['id']]['index']
				)[0]['id'];
				$result = true;
				if (isset($_POST['groups'])) {
					foreach ($_POST['groups'] as $group => $value) {
						$result = $result && $User->set_group_permissions([$permission => $value], $group);
					}
				}
				if (isset($_POST['users'])) {
					foreach ($_POST['users'] as $user => $value) {
						$result = $result && $User->set_user_permissions([$permission => $value], $user);
					}
				}
				$a->save($result);
			}
		break;
	}
} elseif (isset($_POST['edit_settings'])) {
	switch ($_POST['edit_settings']) {
		case 'apply':
		case 'save':
			$_POST['position'] = _json_decode($_POST['position']);
			if (is_array($_POST['position'])) {
				$blocks_array = [];
				foreach ($_POST['position'] as $position => $items) {
					foreach ($items as $item) {
						$item = (int)substr($item, 5);
						switch ($position) {
							default:
								$position = 'floating';
							break;
							case 'top':
							case 'left':
							case 'floating':
							case 'right':
							case 'bottom':
							break;
						}
						$Config->components['blocks'][$item]['position']	= $position;
						$blocks_array[]										= $Config->components['blocks'][$item];
					}
				}
				$Config->components['blocks']	= [];
				$Config->components['blocks']	= $blocks_array;
				unset($blocks_array, $position, $items, $item);
				if ($_POST['edit_settings'] == 'save') {
					$a->save();
				} else {
					$a->apply();
				}
			}
		break;
		case 'cancel':
			$a->cancel();
		break;
	}
}