<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	cs\Event,
	cs\Page;
Event::instance()
	->on(
		'admin/System/components/modules/install/prepare',
		function ($data) {
			if (file_exists(MODULES."/$data[name]/meta.json")) {
				$meta = file_get_json(MODULES."/$data[name]/meta.json");
				Page::instance()->config(
					[
						'name' => $meta['package'],
						'type' => Composer::COMPONENT_MODULE,
						'mode' => Composer::MODE_ADD
					],
					'cs.composer'
				);
			}
		}
	)
	->on(
		'admin/System/components/modules/uninstall/prepare',
		function ($data) {
			if (file_exists(MODULES."/$data[name]/meta.json")) {
				$meta = file_get_json(MODULES."/$data[name]/meta.json");
				Page::instance()->config(
					[
						'name' => $meta['package'],
						'type' => Composer::COMPONENT_MODULE,
						'mode' => Composer::MODE_DELETE
					],
					'cs.composer'
				);
			}
		}
	)
	->on(
		'admin/System/components/plugins/enable/prepare',
		function ($data) {
			if (file_exists(PLUGINS."/$data[name]/meta.json")) {
				$meta = file_get_json(PLUGINS."/$data[name]/meta.json");
				Page::instance()->config(
					[
						'name' => $meta['package'],
						'type' => Composer::COMPONENT_PLUGIN,
						'mode' => Composer::MODE_DELETE
					],
					'cs.composer'
				);
			}
		}
	)
	->on(
		'admin/System/components/plugins/disable/prepare', // TODO use frontend events
		function ($data) {
			if (file_exists(PLUGINS."/$data[name]/meta.json")) {
				$meta = file_get_json(PLUGINS."/$data[name]/meta.json");
				Page::instance()->config(
					[
						'name' => $meta['package'],
						'type' => Composer::COMPONENT_PLUGIN,
						'add'  => false
					],
					'cs.composer'
				);
			}
		}
	);
