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
		'System/Request/routing_replace',
		function ($data) {
			$rc = explode('/', $data['rc']);
			$L  = new Prefix('blogs_');
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
		'admin/System/components/modules/uninstall/before',
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
		'admin/System/components/modules/install/after',
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
