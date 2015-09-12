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
	static function admin_blocks_get ($route_ids) {
		$Config = Config::instance();
		$Page   = Page::instance();
		$Text   = Text::instance();
		$db_id  = $Config->module('System')->db('texts');
		$blocks = $Config->components['blocks'];
		foreach ($blocks as &$block) {
			$block['title']   = $Text->process($db_id, $block['title'], true);
			$block['content'] = $block['content'] ? $Text->process($db_id, $block['content'], true) : '';
		}
		unset($block);
		if (isset($route_ids[0])) {
			foreach ($blocks as $block) {
				if ($block['index'] == $route_ids[0]) {
					$Page->json(
						[
							'title'    => $block['title'],
							'type'     => $block['type'],
							'active'   => (int)$block['active'],
							'template' => $block['template'],
							'start'    => date('Y-m-d\TH:i', $block['start'] ?: TIME),
							'expire'   => [
								'date'  => date('Y-m-d\TH:i', $block['expire'] ?: TIME),
								'state' => (int)($block['expire'] != 0)
							],
							'content'  => $block['content']
						]
					);
					return;
				}
			}
			error_code(404);
		} else {
			$Page->json($blocks ?: []);
		}
	}
	static function admin_blocks_post () {
		static::save_block_data($_POST);
	}
	static function admin_blocks_put ($route_ids) {
		if (!$route_ids[0]) {
			error_code(400);
			return;
		}
		static::save_block_data($_POST, $route_ids[0]);
	}
	static function admin_blocks_templates () {
		Page::instance()->json(
			_mb_substr(get_files_list(TEMPLATES.'/blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6)
		);
	}
	static function admin_blocks_types () {
		Page::instance()->json(
			array_merge(['html', 'raw_html'], _mb_substr(get_files_list(BLOCKS, '/^block\..*?\.php$/i', 'f'), 6, -4))
		);
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
				xap($block_new['content'], true)
			);
		} elseif ($block['type'] == 'raw_html') {
			$block['content'] = $Text->set(
				$Config->module('System')->db('texts'),
				'System/Config/blocks/content',
				$block['index'],
				$block_new['content']
			);
		}
		if (!$index) {
			$Config->components['blocks'][] = $block;
			Permission::instance()->add('Block', $block['index']);
		}
		$Config->save();
	}
}
