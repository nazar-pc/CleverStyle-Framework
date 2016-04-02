<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs\api;
use
	h,
	cs\Config,
	cs\ExitException,
	cs\Language\Prefix,
	cs\Page,
	cs\User,
	cs\modules\Blogs\Posts,
	cs\modules\Blogs\Sections;

class Controller {
	static function __get_settings () {
		$User        = User::instance();
		$module_data = Config::instance()->module('Blogs');
		return [
			'inline_editor'              => functionality('inline_editor'),
			'max_sections'               => $module_data->max_sections,
			'new_posts_only_from_admins' => $module_data->new_posts_only_from_admins, //TODO use this on frontend
			'can_delete_posts'           => //TODO use this on frontend
				$User->admin() &&
				$User->get_permission('admin/Blogs', 'index') &&
				$User->get_permission('admin/Blogs', 'edit_post')
		];
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function posts_get ($Request) {
		$id = $Request->route_ids(0);
		if ($id) {
			$post = Posts::instance()->get($id);
			if (!$post) {
				throw new ExitException(404);
			}
			return $post;
		} else {
			// TODO: implement latest posts
		}
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function posts_post ($Request, $Response) {
		$Config      = Config::instance();
		$module_data = $Config->module('Blogs');
		$L           = new Prefix('blogs_');
		$User        = User::instance();
		if (!$User->admin() && $module_data->new_posts_only_from_admins) {
			throw new ExitException(403);
		}
		if (!$User->user()) {
			throw new ExitException($L->for_registered_users_only, 403);
		}
		$data = static::check_request_data($Request, $L);
		if (!$data) {
			throw new ExitException(400);
		}
		$Posts = Posts::instance();
		$id    = $Posts->add($data['title'], $data['path'], $data['content'], $data['sections'], $data['tags'], $data['mode'] == 'draft');
		if (!$id) {
			throw new ExitException($L->post_adding_error, 500);
		}
		$Response->code = 201;
		return [
			'id'  => $id,
			'url' => $Config->base_url().'/'.path($L->Blogs).'/'.$Posts->get($id)['path'].":$id"
		];
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function posts_put ($Request) {
		$Config = Config::instance();
		$L      = new Prefix('blogs_');
		$User   = User::instance();
		$id     = $Request->route(1);
		$data   = static::check_request_data($Request, $L);
		if (!$id || !$data) {
			throw new ExitException(400);
		}
		$Posts = Posts::instance();
		$post  = $Posts->get($id);
		if (!$post) {
			throw new ExitException(404);
		}
		if (
			!$User->admin() ||
			!$User->get_permission('admin/Blogs', 'index') ||
			!$User->get_permission('admin/Blogs', 'edit_post')
		) {
			throw new ExitException(403);
		}
		if (!$Posts->set($id, $data['title'], $data['path'], $data['content'], $data['sections'], $data['tags'], $data['mode'] == 'draft')) {
			throw new ExitException($L->post_saving_error, 500);
		}
		return [
			'id'  => $id,
			'url' => $Config->base_url().'/'.path($L->Blogs).'/'.$Posts->get($id)['path'].":$id"
		];
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function posts_delete ($Request) {
		$L    = new Prefix('blogs_');
		$User = User::instance();
		$id   = $Request->route(1);
		if (!$id) {
			throw new ExitException(400);
		}
		$Posts = Posts::instance();
		$post  = $Posts->get($id);
		if (!$post) {
			throw new ExitException(404);
		}
		if (
			$post['user'] != $User->id &&
			!(
				$User->admin() &&
				$User->get_permission('admin/Blogs', 'index') &&
				$User->get_permission('admin/Blogs', 'edit_post')
			)
		) {
			throw new ExitException(403);
		}
		if (!$Posts->del($id)) {
			throw new ExitException($L->post_deleting_error, 500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 * @param Prefix      $L
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	protected static function check_request_data ($Request, $L) {
		$data = $Request->data('title', 'sections', 'content', 'tags', 'mode');
		if (!$data) {
			throw new ExitException(400);
		}
		$data['path'] = $Request->data('path');
		if (empty($data['title'])) {
			throw new ExitException($L->post_title_empty, 400);
		}
		if (empty($data['sections']) || !is_array($data['sections'])) {
			throw new ExitException($L->no_post_sections_specified, 400);
		}
		if (empty($data['content'])) {
			throw new ExitException($L->post_content_empty, 400);
		}
		if (empty($data['tags']) || !is_array($data['tags'])) {
			throw new ExitException($L->no_post_tags_specified, 400);
		}
		return $data;
	}
	static function posts_preview () {
		$Config = Config::instance();
		$User   = User::instance();
		if (!$User->user()) {
			throw new ExitException(403);
		}
		$L    = new Prefix('blogs_');
		$Page = Page::instance();
		if (empty($_POST['title'])) {
			$Page->warning($L->post_title_empty);
			$Page->json($Page->Top);
			return;
		}
		if (empty($_POST['sections']) && $_POST['sections'] !== '0') {
			$Page->warning($L->no_post_sections_specified);
			$Page->json($Page->Top);
			return;
		}
		if (empty($_POST['content'])) {
			$Page->warning($L->post_content_empty);
			$Page->json($Page->Top);
			return;
		}
		if (empty($_POST['tags'])) {
			$Page->warning($L->no_post_tags_specified);
			$Page->json($Page->Top);
			return;
		}
		$Posts       = Posts::instance();
		$Sections    = Sections::instance();
		$post        = isset($_POST['id']) ? $Posts->get($_POST['id']) : [
			'date'           => TIME,
			'user'           => $User->id,
			'comments_count' => 0
		];
		$module      = path($L->Blogs);
		$module_data = $Config->module('Blogs');
		$Page->json(
			h::{'section.cs-blogs-post article'}(
				h::header(
					h::h1(xap($_POST['title'])).
					((array)$_POST['sections'] != [0] ? h::p(
						h::icon('bookmark').
						implode(
							', ',
							array_map(
								function ($section) use ($Sections, $L, $module) {
									$section = $Sections->get($section);
									return h::a(
										$section['title'],
										[
											'href' => "$module/".path($L->section)."/$section[full_path]"
										]
									);
								},
								(array)$_POST['sections']
							)
						)
					) : '')
				).
				xap($_POST['content'], true, $module_data->allow_iframes_without_content)."\n".
				h::footer(
					h::p(
						h::icon('tags').
						implode(
							', ',
							array_map(
								function ($tag) use ($L, $module) {
									$tag = xap($tag);
									return h::a(
										$tag,
										[
											'href' => "$module/".path($L->tag)."/$tag",
											'rel'  => 'tag'
										]
									);
								},
								_trim($_POST['tags'])
							)
						)
					).
					h::hr().
					h::p(
						h::time(
							$L->to_locale(date($L->_datetime_long, $post['date'])),
							[
								'datetime' => date('c', $post['date'])
							]
						).
						h::icon('user').$User->username($post['user']).
						(
						$module_data->enable_comments ? h::icon('comments').$post['comments_count'] : ''
						)
					)
				)
			).
			h::br(2)
		);
	}
	static function sections_get () {
		return Sections::instance()->get_all() ?: [];
	}
}
