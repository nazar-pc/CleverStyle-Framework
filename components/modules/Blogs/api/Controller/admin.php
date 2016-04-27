<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs\api\Controller;
use
	cs\Config,
	cs\ExitException,
	cs\modules\Blogs\Sections;

trait admin {
	static function admin___get_settings () {
		$module_data = Config::instance()->module('Blogs');
		return [
			'posts_per_page'                => $module_data->posts_per_page,
			'max_sections'                  => $module_data->max_sections,
			'enable_comments'               => $module_data->enable_comments,
			'new_posts_only_from_admins'    => $module_data->new_posts_only_from_admins,
			'allow_iframes_without_content' => $module_data->allow_iframes_without_content
		];
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin___save_settings ($Request) {
		$data = $Request->data('posts_per_page', 'max_sections', 'enable_comments', 'new_posts_only_from_admins', 'allow_iframes_without_content');
		if (!$data) {
			throw new ExitException(400);
		}
		if (!Config::instance()->module('Blogs')->set($data)) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function admin_sections_get ($Request) {
		$id       = $Request->route_ids(0);
		$Sections = Sections::instance();
		if ($id) {
			$data = $Sections->get($id);
			if (!$data) {
				throw new ExitException(404);
			}
			return $data;
		}
		return $Sections->get_all();
	}
}
