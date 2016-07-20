<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	cs\Cache,
	cs\Config,
	cs\DB,
	cs\Event,
	cs\ExitException,
	cs\Language\Prefix,
	cs\Menu,
	cs\Request,
	cs\User;

Event::instance()
	->on(
		'System/Request/routing_replace/after',
		function ($data) {
			if (!Config::instance()->module('Blogs')->enabled()) {
				return;
			}
			$rc = explode('/', $data['rc']);
			if ($data['current_module'] != 'Blogs' || !$data['regular_path']) {
				return;
			}
			if (!$rc[0]) {
				$rc[0] = 'latest_posts';
			}
			$L = new Prefix('blogs_');
			switch ($rc[0]) {
				case 'latest_posts':
				case 'section':
				case 'tag':
				case 'new_post':
				case 'edit_post':
				case 'drafts':
				case 'post':
				case 'atom.xml':
					break;
				case path($L->latest_posts):
					$rc[0] = 'latest_posts';
					break;
				case path($L->section):
					$rc[0] = 'section';
					break;
				case path($L->tag):
					$rc[0] = 'tag';
					break;
				case path($L->new_post):
					$rc[0] = 'new_post';
					break;
				case path($L->drafts):
					$rc[0] = 'drafts';
					break;
				default:
					if (mb_strpos($rc[0], ':') !== false) {
						$rc[1] = $rc[0];
						$rc[0] = 'post';
					} else {
						throw new ExitException(404);
					}
			}
			$data['rc'] = implode('/', $rc);
		}
	)
	->on(
		'api/Comments/add',
		function ($data) {
			$module_data = Config::instance()->module('Blogs');
			if (
				$module_data->enabled() &&
				$data['module'] == 'Blogs' &&
				$module_data->enable_comments &&
				User::instance()->user() &&
				Posts::instance()->get($data['item'])
			) {
				$data['allow'] = true;
				return false;
			}
		}
	)
	->on(
		'api/Comments/edit',
		function ($data) {
			$User        = User::instance();
			$module_data = Config::instance()->module('Blogs');
			if (
				$module_data->enabled() &&
				$data['module'] == 'Blogs' &&
				$module_data->enable_comments &&
				$User->user() &&
				($data['user'] == $User->id || $User->admin())
			) {
				$data['allow'] = true;
				return false;
			}
		}
	)
	->on(
		'api/Comments/delete',
		function ($data) {
			$User        = User::instance();
			$module_data = Config::instance()->module('Blogs');
			if (
				$module_data->enabled() &&
				$data['module'] == 'Blogs' &&
				$module_data->enable_comments &&
				$User->user() &&
				($data['user'] == $User->id || $User->admin())
			) {
				$data['allow'] = true;
				return false;
			}
		}
	)
	->on(
		'admin/System/Menu',
		function () {
			$L       = new Prefix('blogs_');
			$Menu    = Menu::instance();
			$Request = Request::instance();
			foreach (['browse_sections', 'browse_posts', 'general'] as $section) {
				$Menu->add_item(
					'Blogs',
					$L->$section,
					[
						'href'    => "admin/Blogs/$section",
						'primary' => $Request->route_path(0) == $section
					]
				);
			}
		}
	)
	->on(
		'admin/System/modules/uninstall/before',
		function ($data) {
			if ($data['name'] != 'Blogs') {
				return;
			}
			time_limit_pause();
			$Posts    = Posts::instance();
			$Sections = Sections::instance();
			foreach (Sections::instance()->get_all() as $section) {
				$Sections->del($section['id']);
			}
			unset($section);
			$posts = DB::instance()->db(Config::instance()->module('Blogs')->db('posts'))->qfas(
				"SELECT `id`
				FROM `[prefix]blogs_posts`"
			) ?: [];
			foreach ($posts as $post) {
				$Posts->del($post);
			}
			Cache::instance()->del('Blogs');
			time_limit_pause(false);
		}
	)
	->on(
		'admin/System/modules/install/after',
		function ($data) {
			if ($data['name'] != 'Blogs') {
				return;
			}
			Config::instance()->module('Blogs')->set(
				[
					'posts_per_page'                => 10,
					'max_sections'                  => 3,
					'enable_comments'               => 1,
					'new_posts_only_from_admins'    => 1,
					'allow_iframes_without_content' => 1
				]
			);
		}
	);
