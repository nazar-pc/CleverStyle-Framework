/**
 * @package		TinyMCE
 * @category	plugins
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration into CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU Lesser General Public License 2.1, see license.txt
 */
window.tinymce.baseURL	= '/components/plugins/TinyMCE';
$(function () {
	var base_config			= {
		doctype					: '<!doctype html>',
		theme					: window.tinymce_theme !== undefined ? tinymce_theme : "modern",
		skin					: window.tinymce_skin !== undefined ? tinymce_skin : "'lightgray'",
		language				: window.L.clang !== undefined ? L.clang : 'en',
		menubar					: false,
		plugins					: "advlist,anchor,charmap,code,contextmenu,emoticons,fullscreen,hr,image,link,lists,media,nonbreaking,noneditable,pagebreak,paste,preview,searchreplace,tabfocus,table,visualblocks,visualchars,wordcount,textcolor",
		resize					: 'both',
		toolbar_items_size		: 'small',
		width					: '100%',
		convert_urls			: false,
		remove_script_host		: false,
		relative_urls			: false,
		file_browser_callback	: window.file_upload ? function (field_name) {
			if (!tinymce.uploader_dialog) {
				tinymce.uploader_dialog		= $('<div title="Uploading..." class="cs-center"></div>')
					.html('<div style="margin-left: -10%; width: 20%;"><div class="uk-progress uk-progress-striped uk-active"><div class="uk-progress-bar"></div></div></div>')
					.appendTo('body')
					.cs_modal()
					.css('z-index', 100000);
			}
			file_upload(
				null,
				function (files) {
					tinymce.uploader_dialog.cs_modal('hide');
					if (files.length) {
						$('#' + field_name).val(files[0]);
					}
				},
				function (error) {
					tinymce.uploader_dialog.cs_modal('hide');
					alert(error);
				},
				function (file) {
					tinymce.uploader_dialog.find('.uk-progress-bar').width((file.percent ? file.percent : 1) + '%');
					tinymce.uploader_dialog.cs_modal('show');
				}
			).browse();
		} : null
	};
	tinymce.editor_config	= $.extend(
		{
			toolbar1	: "styleselect fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bold italic underline strikethrough superscript subscript | forecolor backcolor",
			toolbar2	: "insertfile undo redo | bullist numlist outdent indent blockquote | link unlink anchor image media emoticons charmap hr nonbreaking pagebreak | visualchars visualblocks | searchreplace | fullscreen preview code"
		},
		base_config
	);
	tinymce.simple_editor_config	= $.extend(
		{
			toolbar	: "insertfile undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link image media emoticons | code"
		},
		base_config
	);
	tinymce.inline_editor_config	= $.extend(
		{
			inline	: true,
			menubar	: false,
			toolbar	: "insertfile undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link image media emoticons | code"
		},
		base_config
	);
	async_call([
		function () {
			/**
			 * Full editor
			 */
			$('.EDITOR').prop('required', false).tinymce(tinymce.editor_config);
		},
		function () {
			/**
			 * Simple editor
			 */
			$('.SIMPLE_EDITOR').prop('required', false).tinymce(tinymce.simple_editor_config);
		},
		function () {
			/**
			 * Inline editor
			 */
			$('.INLINE_EDITOR').prop('required', false).tinymce(tinymce.inline_editor_config);
		}
	]);
});
function editor_deinitialization (id) {
	$('#' + id).tinymce().remove();
}
function editor_reinitialization (id) {
	var	textarea	= $('#' + id);
	if (textarea.hasClass('EDITOR')) {
		textarea.tinymce(tinymce.editor_config).load();
	} else if (textarea.hasClass('SIMPLE_EDITOR')) {
		textarea.tinymce(tinymce.simple_editor_config).load();
	} else if (textarea.hasClass('INLINE_EDITOR')) {
		textarea.tinymce(tinymce.inline_editor_config).load();
	}
}
function editor_focus (id) {
	$('#' + id).tinymce().focus();
}
