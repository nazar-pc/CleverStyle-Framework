/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
function blogs_add_comment () {
	var textarea	= $('.cs-blogs-comment-write-text');
	$.ajax(
		base_url+'/api/Blogs/add_comment',
		{
			cache		: false,
			data		: {
				post	: textarea.data('post'),
				parent	: textarea.data('parent'),
				text	: textarea.val()
			},
			dataType	: 'json',
			success	: function(result) {
				if (result.status == 'OK') {
					if (textarea.data('parent') == 0) {
						$('.cs-blogs-comments').append(result.content);
					} else {
						$('#comment_'+textarea.data('parent')).append(result.content);
					}
					blogs_comment_cancel();
				} else {
					alert(result.status);
				}
			},
			error	: function() {
				alert(L.comment_sending_connection_error);
			}
		}
	);
}
function blogs_edit_comment () {
	var textarea	= $('.cs-blogs-comment-write-text');
	$.ajax(
		base_url+'/api/Blogs/edit_comment',
		{
			cache		: false,
			data		: {
				id		: textarea.data('id'),
				text	: textarea.val()
			},
			dataType	: 'json',
			success	: function(result) {
				if (result.status == 'OK') {
					$('#comment_'+textarea.data('id')).children('.cs-blogs-comment-text').html(result.content);
					blogs_comment_cancel();
				} else {
					alert(result.status);
				}
			},
			error	: function() {
				alert(L.comment_editing_connection_error);
			}
		}
	);
}
function blogs_delete_comment () {
	var parent 	= $(this).parent('article'),
		id		= parent.prop('id').replace('comment_', '');
	$.ajax(
		base_url+'/api/Blogs/delete_comment',
		{
			cache		: false,
			data		: {
				id		: id
			},
			dataType	: 'json',
			success	: function(result) {
				if (result.status == 'OK') {
					if (result.content && parent.parent('article').find('.cs-blogs-comment').length == 1) {
						parent.parent('article').children('.cs-blogs-comment-edit').after(result.content);
					}
					$('#comment_'+id).remove();
					blogs_comment_cancel();
				} else {
					alert(result.status);
				}
			},
			error	: function() {
				alert(L.comment_deleting_connection_error);
			}
		}
	);
}
function blogs_comment_cancel () {
	$('.cs-blogs-comment-text').show();
	var textarea	= $('.cs-blogs-comment-write-text');
	textarea.data(
		'parent',
		0
	).data(
		'id',
		0
	).val('');
	typeof window.editor_deinitialization === 'function' && editor_deinitialization(
		textarea.prop('id')
	);
	$('.cs-blogs-comments').next().after(
		$('.cs-blogs-comment-write')
	);
	typeof window.editor_reinitialization === 'function' && editor_reinitialization(
		textarea.prop('id')
	);
	$('.cs-blogs-comment-write-send').show();
	$('.cs-blogs-comment-write-edit, .cs-blogs-comment-write-cancel').hide()
}