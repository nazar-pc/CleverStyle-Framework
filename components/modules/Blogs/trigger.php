<?php
/**
 * @package        Blogs
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;
Event::instance()
	->on(
		'System/Config/routing_replace',
		function ($data) {
			$rc = explode('/', $data['rc']);
			$L  = Language::instance();
			if ($rc[0] != 'Blogs' && $rc[0] != path($L->Blogs)) {
				return;
			}
			$rc[0] = 'Blogs';
			if (!isset($rc[1])) {
				$rc[1] = 'latest_posts';
			}
			switch ($rc[1]) {
				case path($L->latest_posts):
					$rc[1] = 'latest_posts';
					break;
				case path($L->section):
					$rc[1] = 'section';
					break;
				case path($L->tag):
					$rc[1] = 'tag';
					break;
				case path($L->new_post):
					$rc[1] = 'new_post';
					break;
				case path($L->drafts):
					$rc[1] = 'drafts';
					break;
				case 'latest_posts':
				case 'section':
				case 'tag':
				case 'new_post':
				case 'edit_post':
				case 'drafts':
				case 'post':
				case 'atom.xml':
					break;
				default:
					if (mb_strpos($rc[1], ':') !== false) {
						$rc[2] = $rc[1];
						$rc[1] = 'post';
					} else {
						error_code(404);
						return;
					}
			}
			$data['rc'] = implode('/', $rc);
		}
	)
	->on(
		'System/Index/construct',
		function () {
			switch (Config::instance()->components['modules']['Blogs']['active']) {
				case -1:
					if (!admin_path()) {
						return;
					}
					require __DIR__.'/events/uninstalled.php';
					break;
				case 1:
					require __DIR__.'/events/enabled.php';
					if (admin_path() && current_module() == 'Blogs') {
						require __DIR__.'/events/enabled/admin.php';
					}
				default:
					if (!admin_path()) {
						return;
					}
					require __DIR__.'/events/installed.php';
			}
		}
	);
