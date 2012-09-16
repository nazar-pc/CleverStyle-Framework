/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
$(function () {
	$(document).on('click', '.cs-blogs-comment-write-send', blogs_add_comment);
	$(document).on('click', '.cs-blogs-comment-write-edit', blogs_edit_comment);
	$(document).on('click', '.cs-blogs-comment-write-cancel', blogs_comment_cancel);
	$(document).on('click', '.cs-blogs-comment-text', function () {
		blogs_comment_cancel();
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
		$('.cs-blogs-comment-write-cancel').show();
	});
	$(document).on('click', '.cs-blogs-comment-edit', function () {
		blogs_comment_cancel();
		var textarea	= $('.cs-blogs-comment-write-text'),
			parent		= $(this).parent('article'),
			text		= parent.children('.cs-blogs-comment-text');
		textarea.data(
			'id',
			parent.prop('id').replace('comment_', '')
		).val(text.html());
		typeof window.editor_deinitialization === 'function' && editor_deinitialization(
			textarea.prop('id')
		);
		text.hide().after(
			$('.cs-blogs-comment-write')
		);
		typeof window.editor_reinitialization === 'function' && editor_reinitialization(
			textarea.prop('id')
		);
		typeof window.editor_focus === 'function' && editor_focus(
			textarea.prop('id')
		);
		$('.cs-blogs-comment-write-edit, .cs-blogs-comment-write-cancel').show();
		$('.cs-blogs-comment-write-send').hide();
	});
	$(document).on('click', '.cs-blogs-comment-delete', blogs_delete_comment);
	$('.cs-blogs-post-preview').mousedown(function () {
		blogs_post_preview($(this).data('id'))
	});
});