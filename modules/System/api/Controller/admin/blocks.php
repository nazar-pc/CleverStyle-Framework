<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Config,
	cs\ExitException,
	cs\Permission,
	cs\Text;

trait blocks {
	/**
	 * Get array of blocks data or data of specific block if id specified
	 *
	 * If block id specified - extended form of data will be returned
	 *
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	public static function admin_blocks_get ($Request) {
		$Config = Config::instance();
		$Text   = Text::instance();
		$db_id  = $Config->module('System')->db('texts');
		$index  = $Request->route_ids(0);
		if (!$index) {
			/**
			 * @var array $blocks
			 */
			$blocks = $Config->components['blocks'];
			foreach ($blocks as &$block) {
				$block = static::admin_blocks_get_prepare($db_id, $Text, $block);
			}
			return array_values($blocks) ?: [];
		}
		$block = static::get_block_by_index($index);
		if (!$block) {
			throw new ExitException(404);
		}
		return static::admin_blocks_get_prepare($db_id, $Text, $block);
	}
	/**
	 * @param int   $db_id
	 * @param Text  $Text
	 * @param array $block
	 *
	 * @return array
	 */
	protected static function admin_blocks_get_prepare ($db_id, $Text, $block) {
		$block['active']  = (int)$block['active'];
		$block['title']   = $Text->process($db_id, $block['title'], true);
		$block['content'] = $block['content'] ? $Text->process($db_id, $block['content'], true) : '';
		$block['start']   = date('Y-m-d\TH:i', $block['start'] ?: time());
		$block['expire']  = [
			'date'  => date('Y-m-d\TH:i', $block['expire'] ?: time()),
			'state' => (int)($block['expire'] != 0)
		];
		return $block;
	}
	/**
	 * Add new block
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_blocks_post ($Request) {
		static::save_block_data($Request->data);
	}
	/**
	 * Update block's data
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_blocks_put ($Request) {
		$index = $Request->route_ids(0);
		if (!$index) {
			throw new ExitException(400);
		}
		static::save_block_data($Request->data, $index);
	}
	/**
	 * Delete block
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_blocks_delete ($Request) {
		$index = $Request->route_ids(0);
		if (!$index) {
			throw new ExitException(400);
		}
		$Config     = Config::instance();
		$db_id      = $Config->module('System')->db('texts');
		$Permission = Permission::instance();
		$Text       = Text::instance();
		$found      = false;
		/**
		 * @var array $blocks
		 */
		$blocks = $Config->components['blocks'];
		foreach ($blocks as $i => $block) {
			if ($block['index'] == $index) {
				unset($Config->components['blocks'][$i]);
				$found = $i;
				break;
			}
		}
		if ($found === false) {
			throw new ExitException(404);
		}
		$block_permission = $Permission->get(null, 'Block', $index);
		if ($block_permission) {
			$Permission->del($block_permission[0]['id']);
		}
		$Text->del($db_id, 'System/Config/blocks/title', $index);
		$Text->del($db_id, 'System/Config/blocks/content', $index);
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Get array of available block types
	 */
	public static function admin_blocks_types () {
		return array_merge(['html', 'raw_html'], _mb_substr(get_files_list(BLOCKS, '/^block\..*?\.php$/i', 'f'), 6, -4));
	}
	/**
	 * Update blocks order
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_blocks_update_order ($Request) {
		$order = $Request->data('order');
		if (!is_array($order)) {
			throw new ExitException(400);
		}
		$Config = Config::instance();
		/**
		 * @var array $blocks
		 */
		$blocks           = $Config->components['blocks'];
		$indexed_blocks   = array_combine(
			array_column($blocks, 'index'),
			$blocks
		);
		$new_blocks_order = [];
		$all_indexes      = [];
		/**
		 * @var array[] $order
		 */
		foreach ($order as $position => $indexes) {
			foreach ($indexes as $index) {
				$all_indexes[]      = $index;
				$block              = $indexed_blocks[$index];
				$block['position']  = $position;
				$new_blocks_order[] = $block;
			}
		}
		foreach ($blocks as $block) {
			if (!in_array($block['index'], $all_indexes)) {
				$new_blocks_order[] = $block;
			}
		}
		$Config->components['blocks'] = $new_blocks_order;
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param array     $block_new
	 * @param false|int $index Index of existing block, if not specified - new block being added
	 *
	 * @throws ExitException
	 */
	protected static function save_block_data ($block_new, $index = false) {
		$Config = Config::instance();
		$db_id  = $Config->module('System')->db('texts');
		$Text   = Text::instance();
		$block  = [
			'position' => 'floating',
			'type'     => xap($block_new['type']),
			'index'    => intval(substr(round(microtime(true) * 1000), 3), 10)
		];
		if ($index) {
			$block = &static::get_block_by_index($index);
			if (!$block) {
				throw new ExitException(404);
			}
		}
		$block['title']   = $Text->set($db_id, 'System/Config/blocks/title', $block['index'], $block_new['title']);
		$block['active']  = $block_new['active'];
		$block['type']    = $block_new['type'];
		$block['start']   = $block_new['start'];
		$block['start']   = strtotime($block_new['start']);
		$block['expire']  = $block_new['expire']['state'] ? strtotime($block_new['expire']['date']) : 0;
		$block['content'] = '';
		if ($block['type'] == 'html') {
			$block['content'] = $Text->set($db_id, 'System/Config/blocks/content', $block['index'], xap($block_new['content'], true));
		} elseif ($block['type'] == 'raw_html') {
			$block['content'] = $Text->set($db_id, 'System/Config/blocks/content', $block['index'], $block_new['content']);
		}
		if (!$index) {
			$Config->components['blocks'][] = $block;
			Permission::instance()->add('Block', $block['index']);
		}
		if (!$Config->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param int $index
	 *
	 * @return array|false
	 */
	protected static function &get_block_by_index ($index) {
		/**
		 * @var array $blocks
		 */
		$blocks = Config::instance()->components['blocks'];
		foreach ($blocks as &$block) {
			if ($block['index'] == $index) {
				return $block;
			}
		}
		$false = false;
		return $false;
	}
}
