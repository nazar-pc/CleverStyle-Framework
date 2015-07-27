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
}
