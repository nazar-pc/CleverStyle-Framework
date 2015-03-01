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
_require_once(STORAGE.'/Composer/vendor/autoload.php', false);
Event::instance()
	->on('admin/System/components/modules/install/prepare', function ($data) {
		if (file_exists(MODULES."/$data[name]/meta.json")) {
			$meta = file_get_json(MODULES."/$data[name]/meta.json");
			if (isset($meta['require_composer'])) {
				Page::instance()->config([
					'name' => $meta['package'],
					'type' => Composer::COMPONENT_MODULE,
					'add'  => 1
				], 'cs.composer');
			}
		}
	})
	->on('admin/System/components/modules/uninstall/prepare', function ($data) {
		if (file_exists(MODULES."/$data[name]/meta.json")) {
			$meta = file_get_json(MODULES."/$data[name]/meta.json");
			if (isset($meta['require_composer'])) {
				Page::instance()->config([
					'name' => $meta['package'],
					'type' => Composer::COMPONENT_MODULE,
					'add'  => 0
				], 'cs.composer');
			}
		}
	})
	->on('admin/System/components/plugins/enable/prepare', function ($data) {
		if (file_exists(PLUGINS."/$data[name]/meta.json")) {
			$meta = file_get_json(PLUGINS."/$data[name]/meta.json");
			if (isset($meta['require_composer'])) {
				Page::instance()->config([
					'name' => $meta['package'],
					'type' => Composer::COMPONENT_PLUGIN,
					'add'  => 1
				], 'cs.composer');
			}
		}
	})
	->on('admin/System/components/plugins/disable/prepare', function ($data) {
		if (file_exists(PLUGINS."/$data[name]/meta.json")) {
			$meta = file_get_json(PLUGINS."/$data[name]/meta.json");
			if (isset($meta['require_composer'])) {
				Page::instance()->config([
					'name' => $meta['package'],
					'type' => Composer::COMPONENT_PLUGIN,
					'add'  => 0
				], 'cs.composer');
			}
		}
	});
