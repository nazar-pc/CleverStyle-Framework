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
	h,
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language,
	cs\Page,
	cs\Route,
	cs\User;

if (!Event::instance()->fire('Blogs/edit_post')) {
	return;
}

$Posts       = Posts::instance();
$Config      = Config::instance();
$module_data = $Config->module('Blogs');
$L           = Language::instance();
$Page        = Page::instance();
$Route       = Route::instance();
$User        = User::instance();
if ($module_data->new_posts_only_from_admins && !$User->admin()) {
	throw new ExitException(403);
}
if (
	!isset($Route->route[1]) ||
	!($post = $Posts->get($Route->route[1]))
) {
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
$Page->title(
	$L->editing_of_post($post['title'])
);
$module = path($L->Blogs);
if (isset($_POST['title'], $_POST['sections'], $_POST['content'], $_POST['tags'], $_POST['mode'])) {
	$draft = false;
	switch ($_POST['mode']) {
		case 'draft':
			$draft = true;
		case 'save':
			$save = true;
			if (empty($_POST['title'])) {
				$Page->warning($L->post_title_empty);
				$save = false;
			}
			if (empty($_POST['sections']) && $_POST['sections'] !== '0') {
				$Page->warning($L->no_post_sections_specified);
				$save = false;
			}
			if (empty($_POST['content'])) {
				$Page->warning($L->post_content_empty);
				$save = false;
			}
			if (empty($_POST['tags'])) {
				$Page->warning($L->no_post_tags_specified);
				$save = false;
			}
			if ($save) {
				if ($Posts->set($post['id'], $_POST['title'], null, $_POST['content'], $_POST['sections'], _trim(explode(',', $_POST['tags'])), $draft)) {
					interface_off();
					_header('Location: '.$Config->base_url()."/$module/$post[path]:$post[id]");
					return;
				} else {
					$Page->warning($L->post_saving_error);
				}
			}
			break;
		case 'delete':
			if ($Posts->del($post['id'])) {
				interface_off();
				_header('Location: '.$Config->base_url()."/$module");
				return;
			} else {
				$Page->warning($L->post_deleting_error);
			}
			break;
	}
}
$disabled     = [];
$max_sections = $module_data->max_sections;
$content      = uniqid('post_content');
$Page->replace($content, isset($_POST['content']) ? $_POST['content'] : $post['content']);
$sections = get_sections_select_post($disabled);
if (count($sections['in']) > 1) {
	$sections = [
		$L->post_section,
		h::{'select.cs-blogs-new-post-sections[is=cs-select][size=7][required]'}(
			get_sections_select_post($disabled),
			[
				'name'     => 'sections[]',
				'disabled' => $disabled,
				'selected' => isset($_POST['sections']) ? $_POST['sections'] : $post['sections'],
				$max_sections < 1 ? 'multiple' : false
			]
		).
		($max_sections > 1 ? h::br().$L->select_sections_num($max_sections) : '')
	];
} else {
	$sections = false;
}
$Page->content(
	h::form(
		h::{'h2.cs-text-center'}(
			$L->editing_of_post($post['title'])
		).
		h::{'div.cs-blogs-post-preview-content'}().
		h::{'table.cs-table.cs-blogs-post-form[right-left] tr| td'}(
			[
				$L->post_title,
				h::{'h1.cs-blogs-new-post-title[contenteditable=true]'}(
					isset($_POST['title']) ? $_POST['title'] : $post['title']
				)
			],
			$sections,
			[
				$L->post_content,
				(
				functionality('inline_editor') ? h::{'cs-editor-inline div.cs-blogs-new-post-content'}(
					$content
				) : h::{'cs-editor textarea.cs-blogs-new-post-content[is=cs-textarea][autosize][name=content][required]'}(
					isset($_POST['content']) ? $_POST['content'] : $post['content']
				)
				).
				h::br().
				$L->post_use_pagebreak
			],
			[
				$L->post_tags,
				h::{'input.cs-blogs-new-post-tags[is=cs-input-text][name=tags][required]'}(
					[
						'value'       => htmlspecialchars_decode(
							isset($_POST['tags']) ? $_POST['tags'] : implode(', ', $post['tags']),
							ENT_QUOTES | ENT_HTML5 | ENT_DISALLOWED | ENT_SUBSTITUTE
						),
						'placeholder' => 'CleverStyle, CMS, Open Source'
					]
				)
			]
		).
		(!$sections ? h::{'input[type=hidden][name=sections[]][value=0]'}() : '').
		h::{'button.cs-blogs-post-preview[is=cs-button]'}(
			$L->preview,
			[
				'data-id' => $post['id']
			]
		).
		h::{'button[is=cs-button][type=submit][name=mode][value=save]'}(
			$L->publish
		).
		h::{'button[is=cs-button][type=submit][name=mode][value=draft]'}(
			$L->to_drafts
		).
		h::{'button[is=cs-button][type=submit][name=mode][value=delete]'}(
			$L->delete
		).
		h::{'button[is=cs-button]'}(
			$L->cancel,
			[
				'type'    => 'button',
				'onclick' => 'history.go(-1);'
			]
		)
	)
);
