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
					textarea.val('');
					blogs_add_comment_cancel();
				} else {
					alert(result.status);
				}
			},
			error	: function() {
				alert('error');
			}
		}
	);
}
function blogs_add_comment_cancel () {
	var textarea	= $('.cs-blogs-comment-write-text');
	textarea.data(
		'parent',
		0
	).val('');
	typeof window.editor_deinitialization === 'function' && editor_deinitialization(
		textarea.prop('id')
	);
	$('.cs-blogs-comments').after(
		$('.cs-blogs-comment-write')
	);
	typeof window.editor_reinitialization === 'function' && editor_reinitialization(
		textarea.prop('id')
	);
	$('.cs-blogs-comment-write-cancel').hide()
}