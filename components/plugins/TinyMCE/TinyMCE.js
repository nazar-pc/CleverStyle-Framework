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
	var base_config	= {
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
				tinymce.uploader_dialog		= $('<div title="Uploading..." class="cs-center"></div>').dialog({
					autoOpen	: false
				});
				tinymce.uploader_dialog.parent().css('z-index', 100000);
			}
			file_upload(
				null,
				function (files) {
					tinymce.uploader_dialog.dialog('close');
					if (files.length) {
						$('#' + field_name).val(files[0]);
					}
				},
				function (error) {
					tinymce.uploader_dialog.dialog('close');
					alert(error);
				},
				function (file) {
					tinymce.uploader_dialog.html(file.percent + '%').dialog('open');
				}
			).browse();
		} : null
	};
	async_call([
		function () {
			/**
			 * Full editor
			 */
			var local_config	= {};
			$.extend(
				local_config,
				base_config,
				{
					toolbar1	: "styleselect fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bold italic underline strikethrough superscript subscript | forecolor backcolor",
					toolbar2	: "insertfile undo redo | bullist numlist outdent indent blockquote | link unlink anchor image media emoticons charmap hr nonbreaking pagebreak | visualchars visualblocks | searchreplace | fullscreen preview code"
				}
			);
			$('textarea.EDITOR').prop('required', false).tinymce(local_config);
		},
		function () {
			/**
			 * Simple editor
			 */
			var local_config	= {};
			$.extend(
				local_config,
				base_config,
				{
					toolbar	: "insertfile undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link image media emoticons | code"
				}
			);
			$('textarea.SEDITOR').prop('required', false).tinymce(local_config);
		},
		function () {
			/**
			 * Inline editor
			 */
			var local_config	= {};
			$.extend(
				local_config,
				base_config,
				{
					inline	: true,
					menubar	: false,
					toolbar	: "insertfile undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link image media emoticons | code"
				}
			);
			$('.IEDITOR').prop('required', false).tinymce(local_config);
		}
	]);
});
function editor_deinitialization (id) {
	$('#'+id).tinymce().destroy();
}
function editor_reinitialization (id) {
	$('#'+id).tinymce({});
}
function editor_focus (id) {console.log('f/'+id);
	$('#'+id).tinymce().execCommand('mceFocus');
}
