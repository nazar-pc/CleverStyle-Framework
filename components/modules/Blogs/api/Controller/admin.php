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
	cs\Language,
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
		return static::sections_get($Request);
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function admin_sections_post ($Request, $Response) {
		$data = $Request->data('title', 'path', 'parent');
		if (!$data) {
			throw new ExitException(400);
		}
		$Sections = Sections::instance();
		$id       = $Sections->add($data['parent'], $data['title'], $data['path']);
		if (!$id) {
			throw new ExitException(Language::instance()->blogs_changes_save_error, 500);
		}
		$Response->code = 201;
		return [
			'id'  => $id,
			'url' => Config::instance()->base_url().'/Blogs/section/'.$Sections->get($id)['full_path']
		];
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function admin_sections_put ($Request) {
		$id   = $Request->route_ids(0);
		$data = $Request->data('title', 'path', 'parent');
		if (!$id || !$data) {
			throw new ExitException(400);
		}
		$Sections = Sections::instance();
		if (!$Sections->get($id)) {
			throw new ExitException(404);
		}
		if (!$Sections->set($id, $data['parent'], $data['title'], $data['path'])) {
			throw new ExitException(Language::instance()->blogs_changes_save_error, 500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function admin_sections_delete ($Request) {
		$id = $Request->route_ids(0);
		if (!$id) {
			throw new ExitException(400);
		}
		$Sections = Sections::instance();
		if (!$Sections->get($id)) {
			throw new ExitException(404);
		}
		if (!$Sections->del($id)) {
			throw new ExitException(Language::instance()->blogs_changes_save_error, 500);
		}
	}
}
