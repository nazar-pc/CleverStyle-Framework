/**
 * @package		Comments
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
$(function () {
	var	L	= cs.Language;
	$('body').on(
		'click',
		'.cs-comments-comment-write-send',
		blogs_add_comment
	).on(
		'click',
		'.cs-comments-comment-write-edit',
		blogs_edit_comment
	).on(
		'click',
		'.cs-comments-comment-write-cancel',
		blogs_comment_cancel
	).on(
		'click',
		'.cs-comments-comment-text',
		function () {
			var textarea	= $('.cs-comments-comment-write-text');
			textarea.data(
				'parent',
				$(this).parent('article').prop('id').replace('comment_', '')
			).val('');
			typeof window.editor_deinitialization === 'function' && editor_deinitialization(
				textarea.prop('id')
			);
			$(this).after(
				$('.cs-comments-comment-write')
			);
			typeof window.editor_reinitialization === 'function' && editor_reinitialization(
				textarea.prop('id')
			);
			typeof window.editor_focus === 'function' && editor_focus(
				textarea.prop('id')
			);
			$('.cs-comments-comment-write-cancel').show();
			$('.cs-comments-add-comment').hide();
		}
	).on(
		'click',
		'.cs-comments-comment-edit',
		function () {
			var textarea	= $('.cs-comments-comment-write-text'),
				parent		= $(this).parent('article'),
				text		= parent.children('.cs-comments-comment-text');
			textarea.data(
				'id',
				parent.prop('id').replace('comment_', '')
			).val(text.html());
			typeof window.editor_deinitialization === 'function' && editor_deinitialization(
				textarea.prop('id')
			);
			text.after(
				$('.cs-comments-comment-write')
			);
			typeof window.editor_reinitialization === 'function' && editor_reinitialization(
				textarea.prop('id')
			);
			typeof window.editor_focus === 'function' && editor_focus(
				textarea.prop('id')
			);
			$('.cs-comments-comment-write-edit, .cs-comments-comment-write-cancel').show();
			$('.cs-comments-comment-write-send').hide();
		}
	).on(
		'click',
		'.cs-comments-comment-delete',
		blogs_delete_comment
	);
	function blogs_add_comment () {
		var textarea	= $('.cs-comments-comment-write-text');
		$.ajax(
			'api/Comments',
			{
				cache		: false,
				data		: {
					item	: textarea.data('item'),
					parent	: textarea.data('parent'),
					module	: textarea.data('module'),
					text	: textarea.val()
				},
				dataType	: 'json',
				type		: 'post',
				success		: function (result) {
					var no_comments	= $('.cs-blogs-no-comments');
					if (no_comments.length) {
						no_comments.remove();
					}
					if (textarea.data('parent') == 0) {
						$('.cs-comments-comments').append(result);
					} else {
						$('#comment_' + textarea.data('parent')).append(result);
					}
					blogs_comment_cancel();
				}
			}
		);
	}
	function blogs_edit_comment () {
		var textarea	= $('.cs-comments-comment-write-text'),
			id			= textarea.data('id');
		$.ajax(
			'api/Comments/' + id,
			{
				cache		: false,
				data		: {
					module	: textarea.data('module'),
					text	: textarea.val()
				},
				dataType	: 'json',
				type		: 'put',
				success		: function (result) {
					$('#comment_' + id).children('.cs-comments-comment-text').html(result);
					blogs_comment_cancel();
				}
			}
		);
	}
	function blogs_delete_comment () {
		var comment = $(this).parent('article'),
			id		= comment.prop('id').replace('comment_', '');
		$.ajax(
			'api/Comments/' + id,
			{
				cache		: false,
				data		: {
					module	: $('.cs-comments-comment-write-text').data('module')
				},
				dataType	: 'json',
				type		: 'delete',
				success	: function (result) {
					var	parent	= comment.parent();
					comment.remove();
					blogs_comment_cancel();
					if (result && !parent.find('.cs-comments-comment').length && !parent.find('.cs-comments-comment-delete').length) {
						parent.find('.cs-comments-comment-edit').after(result);
					}
				}
			}
		);
	}
	function blogs_comment_cancel () {
		$('.cs-comments-comment-text').show();
		var textarea	= $('.cs-comments-comment-write-text');
		textarea
			.data('parent', 0)
			.data('id', 0)
			.val('');
		typeof window.editor_deinitialization === 'function' && editor_deinitialization(
			textarea.prop('id')
		);
		$('.cs-comments-add-comment, .cs-comments-comment-write-send').show();
		$('.cs-comments-comment-write-edit, .cs-comments-comment-write-cancel').hide();
		$('.cs-comments-comments').next().after(
			$('.cs-comments-comment-write')
		);
		typeof window.editor_reinitialization === 'function' && editor_reinitialization(
			textarea.prop('id')
		);
	}
});
