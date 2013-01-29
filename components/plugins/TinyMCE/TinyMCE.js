/**
 * @package		TinyMCE
 * @category	plugins
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration into CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU Lesser General Public License 2.1, see license.txt
 */
$(function () {
	async_call([
		function () {
			$('textarea.EDITOR').tinymce(
				{
					doctype                           : '<!doctype html>',
					theme                             : window.tinymce_theme !== undefined ? tinymce_theme : "advanced",
					skin                              : window.tinymce_skin !== undefined ? tinymce_skin : "o2k7",
					skin_variant                      : window.tinymce_skin_variant !== undefined ? tinymce_skin_variant : '',
					language                          : window.L.clang !== undefined ? L.clang : 'en',
					plugins                           : "advhr,advimage,advlink,advlist,autolink,autosave,contextmenu,directionality,emotions,fullscreen,insertdatetime,media,nonbreaking,noneditable,pagebreak,paste,preview,save,searchreplace,style,table,visualchars,xhtmlxtras",
					theme_advanced_buttons1           : "bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,bullist,numlist,|,link,unlink,anchor,image,media,emotions,charmap,code",
					theme_advanced_buttons2           : "save,newdocument,|,copy,cut,paste,pastetext,pasteword,|,undo,redo,|,search,replace,|,tablecontrols",
					theme_advanced_buttons3           : "advhr,cleanup,removeformat,visualaid,|,ltr,rtl,|,outdent,indent,|,blockquote,cite,abbr,acronym,del,ins,insertdate,inserttime,attribs,|,preview,fullscreen",
					theme_advanced_buttons4           : "styleselect,styleprops,formatselect,fontselect,fontsizeselect,|,visualchars,nonbreaking,pagebreak,restoredraft,|,help",
					theme_advanced_toolbar_location   : "top",
					theme_advanced_toolbar_align      : "center",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing           : true,
					width                             : '100%'/*,
					file_browser_callback : EditorCallback !== undefined ? 'EditorCallback' : ''*/
				}
			);
		},
		function () {
			$('textarea.EDITORH').tinymce(
				{
					doctype                           : '<!doctype html>',
					theme                             : window.tinymce_theme !== undefined ? tinymce_theme : "advanced",
					skin                              : window.tinymce_skin !== undefined ? tinymce_skin : "o2k7",
					skin_variant                      : window.tinymce_skin_variant !== undefined ? tinymce_skin_variant : '',
					language                          : window.L.clang !== undefined ? L.clang : 'en',
					plugins                           : "advhr,advimage,advlink,advlist,autolink,autosave,contextmenu,directionality,emotions,fullscreen,insertdatetime,media,nonbreaking,noneditable,pagebreak,paste,preview,save,searchreplace,style,table,visualchars,xhtmlxtras",
					theme_advanced_buttons1           : "bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,bullist,numlist,|,link,unlink,anchor,image,media,emotions,charmap,code",
					theme_advanced_buttons2           : "save,newdocument,|,copy,cut,paste,pastetext,pasteword,|,undo,redo,|,search,replace,|,tablecontrols",
					theme_advanced_buttons3           : "advhr,cleanup,removeformat,visualaid,|,ltr,rtl,|,outdent,indent,|,blockquote,cite,abbr,acronym,del,ins,insertdate,inserttime,attribs,|,preview,fullscreen",
					theme_advanced_buttons4           : "styleselect,styleprops,formatselect,fontselect,fontsizeselect,|,visualchars,nonbreaking,pagebreak,restoredraft,|,help",
					theme_advanced_toolbar_location   : "external",
					theme_advanced_toolbar_align      : "center",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing           : true,
					width                             : '100%'/*,
					file_browser_callback : EditorCallback !== undefined ? 'EditorCallback' : ''*/
				}
			);
		},
		function () {
			$('textarea.SEDITOR').tinymce(
				{
					doctype                           : '<!doctype html>',
					theme                             : window.tinymce_theme !== undefined ? tinymce_theme : "advanced",
					skin                              : window.tinymce_skin !== undefined ? tinymce_skin : "o2k7",
					skin_variant                      : window.tinymce_skin_variant !== undefined ? tinymce_skin_variant : '',
					language                          : window.L.clang !== undefined ? L.clang : 'en',
					plugins                           : "advhr,advimage,advlink,advlist,autolink,autosave,contextmenu,directionality,emotions,fullscreen,insertdatetime,media,nonbreaking,noneditable,pagebreak,paste,preview,save,searchreplace,style,table,visualchars,xhtmlxtras",
					theme_advanced_buttons1           : "bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,link,unlink,anchor,image,media,emotions,charmap,|,visualchars,nonbreaking,|,code",
					theme_advanced_buttons2           : "bullist,numlist,|,ltr,rtl,|,outdent,indent,|,tablecontrols",
					theme_advanced_buttons3           : "blockquote,cite,abbr,acronym,|,styleprops,formatselect,fontselect,fontsizeselect,|,fullscreen",
					theme_advanced_buttons4           : "",
					theme_advanced_toolbar_location   : "top",
					theme_advanced_toolbar_align      : "center",
					theme_advanced_resizing           : false,
					width                             : '100%'/*,
					file_browser_callback : EditorCallback !== undefined ? 'EditorCallback' : ''*/
				}
			);
		}
	]);
});
function editor_deinitialization (id) {
	tinyMCE.execCommand('mceRemoveControl', false, id);
}
function editor_reinitialization (id) {
	tinyMCE.execCommand('mceAddControl', false, id);
}
function editor_focus (id) {
	tinyMCE.execCommand('mceFocus', false, id);
}
