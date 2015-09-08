<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Config,
	cs\Page,
	cs\Permission,
	cs\Text;
trait blocks {
	static function admin_blocks_get () {
		$Config = Config::instance();
		$Text   = Text::instance();
		$db_id  = $Config->module('System')->db('texts');
		$blocks = $Config->components['blocks'];
		foreach ($blocks as &$block) {
			$block['title']   = $Text->process($db_id, $block['title'], true);
			$block['content'] = $block['content'] ? $Text->process($db_id, $block['content'], true) : '';
		}
		Page::instance()->json($blocks ?: []);
	}
	static function admin_blocks_post () {
		static::save_block_data($_POST['block']);
	}
	static function admin_blocks_put ($route_ids) {
		if (!$route_ids[0]) {
			error_code(400);
			return;
		}
		static::save_block_data($_POST['block'], $route_ids[0]);
	}
	/**
	 * @param array     $block_new
	 * @param false|int $index Index of existing block, if not specified - new block being added
	 */
	protected static function save_block_data ($block_new, $index = false) {
		$Config = Config::instance();
		$Text   = Text::instance();
		$block  = [
			'position' => 'floating',
			'type'     => xap($block_new['type']),
			'index'    => substr(TIME, 3)
		];
		if ($index) {
			foreach ($Config->components['blocks'] as &$block) {
				if ($block['index'] == $index) {
					break;
				}
			}
			if ($block['index'] != $index) {
				error_code(404);
				return;
			}
		}
		$block['title']    = $Text->set(
			$Config->module('System')->db('texts'),
			'System/Config/blocks/title',
			$block['index'],
			$block_new['title']
		);
		$block['active']   = $block_new['active'];
		$block['type']     = $block_new['type'];
		$block['template'] = $block_new['template'];
		$block['start']    = $block_new['start'];
		$block['start']    = strtotime($block_new['start']);
		$block['expire']   = 0;
		$block['content']  = '';
		if ($block_new['expire']['state']) {
			$block['expire'] = strtotime($block_new['expire']['date']);
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
		}
		if (!$index) {
			$Config->components['blocks'][] = $block;
			Permission::instance()->add('Block', $block['index']);
		}
	}
}
