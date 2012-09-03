/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
$(function () {
	$('.cs-blogs-comment-write-send').click(blogs_add_comment);
	$('.cs-blogs-comment-text').click(function () {
		var textarea	= $('.cs-blogs-comment-write-text');
		textarea.data(
			'parent',
			$(this).parent('article').prop('id').replace('comment_', '')
		).val('');
		typeof window.editor_deinitialization === 'function' && editor_deinitialization(
			textarea.prop('id')
		);
		$(this).after(
			$('.cs-blogs-comment-write')
		);
		typeof window.editor_reinitialization === 'function' && editor_reinitialization(
			textarea.prop('id')
		);
		typeof window.editor_focus === 'function' && editor_focus(
			textarea.prop('id')
		);
		$('.cs-blogs-comment-write-cancel').show()
	});
	$('.cs-blogs-comment-write-cancel').click(blogs_add_comment_cancel);
});