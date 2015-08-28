/**
 * @package        TinyMCE
 * @category    plugins
 * @author        Moxiecode Systems AB
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration into CleverStyle CMS)
 * @copyright    Moxiecode Systems AB
 * @license        GNU Lesser General Public License 2.1, see license.txt
 */
tinymce.baseURL = '/components/plugins/TinyMCE/includes/js';
$(function () {
	var uploader_callback;
	var uploader                        = cs.file_upload ? cs.file_upload(
		null,
		function (files) {
			tinymce.uploader_dialog.close();
			if (files.length) {
				uploader_callback(files[0]);
			}
			uploader_callback = undefined;
		},
		function (error) {
			tinymce.uploader_dialog.close();
			alert(error.message);
		},
		function (file) {
			if (!tinymce.uploader_dialog) {
				var progress                         = document.createElement('progress', 'cs-progress');
				tinymce.uploader_dialog              = cs.ui.modal(progress);
				tinymce.uploader_dialog.progress     = progress;
				tinymce.uploader_dialog.style.zIndex = 100000;
				tinymce.uploader_dialog.open();
			}
			tinymce.uploader_dialog.progress.value = file.percent || 1;
		}
	) : false;
	var base_config                     = {
		doctype              : '<!doctype html>',
		theme                : cs.tinymce && cs.tinymce.theme !== undefined ? cs.tinymce.theme : 'modern',
		skin                 : cs.tinymce && cs.tinymce.skin !== undefined ? cs.tinymce.skin : 'lightgray',
		language             : cs.Language.clang !== undefined ? cs.Language.clang : 'en',
		menubar              : false,
		plugins              : 'advlist anchor charmap code colorpicker contextmenu fullscreen hr image link lists media nonbreaking noneditable pagebreak paste preview searchreplace tabfocus table textcolor visualblocks visualchars wordcount',
		resize               : 'both',
		toolbar_items_size   : 'small',
		width                : '100%',
		convert_urls         : false,
		remove_script_host   : false,
		relative_urls        : false,
		table_style_by_css   : true,
		file_picker_callback : uploader ? function (callback) {
			uploader_callback = callback;
			uploader.browse();
		} : null
	};
	tinymce.editor_config               = $.extend(
		{
			toolbar1 : 'styleselect fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bold italic underline strikethrough superscript subscript | forecolor backcolor',
			toolbar2 : 'undo redo | bullist numlist outdent indent blockquote | link unlink anchor image media charmap hr nonbreaking pagebreak | visualchars visualblocks | searchreplace | fullscreen preview code'
		},
		base_config
	);
	tinymce.simple_editor_config        = $.extend(
		{
			toolbar : 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link image media | code'
		},
		base_config
	);
	tinymce.inline_editor_config        = $.extend(
		{
			inline  : true,
			menubar : false
		},
		tinymce.editor_config
	);
	tinymce.simple_inline_editor_config = $.extend(
		{
			inline  : true,
			menubar : false
		},
		tinymce.simple_editor_config
	);
	cs.async_call([
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
		},
		function () {
			/**
			 * Small inline editor
			 */
			$('.SIMPLE_INLINE_EDITOR').prop('required', false).tinymce(tinymce.simple_inline_editor_config);
		}
	]);
});
function editor_deinitialization (textarea) {
	$(textarea).tinymce().remove();
}
function editor_reinitialization (textarea) {
	var $textarea = $(textarea);
	if ($textarea.hasClass('EDITOR')) {
		$textarea.tinymce(tinymce.editor_config).load();
	} else if ($textarea.hasClass('SIMPLE_EDITOR')) {
		$textarea.tinymce(tinymce.simple_editor_config).load();
	} else if ($textarea.hasClass('INLINE_EDITOR')) {
		$textarea.tinymce(tinymce.inline_editor_config).load();
	} else if ($textarea.hasClass('SIMPLE_INLINE_EDITOR')) {
		$textarea.tinymce(tinymce.simple_inline_editor_config).load();
	}
}
function editor_focus (textarea) {
	$(textarea).tinymce().focus();
}
