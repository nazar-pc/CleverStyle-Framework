$(
	function () {
		$(
			'textarea.EDITOR'
		).tinymce(
			{
				doctype                           : '<!doctype html>',
				theme                             : window.tinymce_theme !== undefined ? tinymce_theme : "advanced",
				skin                              : window.tinymce_skin !== undefined ? tinymce_skin : "o2k7",
				skin_variant                      : window.tinymce_skin_variant !== undefined ? tinymce_skin_variant : '',
				language                          : window.L.lang !== undefined ? L.lang : 'en',
				plugins                           : "advhr,advimage,advlink,advlist,autolink,autosave,contextmenu,directionality,emotions,fullscreen,inlinepopups,insertdatetime,layer,media,nonbreaking,noneditable,pagebreak,paste,preview,save,searchreplace,style,table,visualchars,xhtmlxtras",
				theme_advanced_buttons1           : "bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,bullist,numlist,|,link,unlink,anchor,image,media,emotions,charmap,code",
				theme_advanced_buttons2           : "save,newdocument,|,copy,cut,paste,pastetext,pasteword,|,undo,redo,|,search,replace,|,tablecontrols",
				theme_advanced_buttons3           : "insertlayer,moveforward,movebackward,absolute,|,advhr,cleanup,removeformat,visualaid,|,ltr,rtl,|,outdent,indent,blockquote,cite,abbr,acronym,del,ins,insertdate,inserttime,attribs,|,preview,fullscreen",
				theme_advanced_buttons4           : "styleselect,styleprops,formatselect,fontselect,fontsizeselect,|,visualchars,nonbreaking,pagebreak,restoredraft,|,help",
				theme_advanced_toolbar_location   : "top",
				theme_advanced_toolbar_align      : "center",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing           : true,
				width                             : '100%'/*,
			 file_browser_callback : EditorCallback !== undefined ? 'EditorCallback' : ''*/
			}
		);
		$(
			'textarea.EDITORH'
		).tinymce(
			{
				doctype                           : '<!doctype html>',
				theme                             : window.tinymce_theme !== undefined ? tinymce_theme : "advanced",
				skin                              : window.tinymce_skin !== undefined ? tinymce_skin : "o2k7",
				skin_variant                      : window.tinymce_skin_variant !== undefined ? tinymce_skin_variant : '',
				language                          : window.L.lang !== undefined ? L.lang : 'en',
				plugins                           : "advhr,advimage,advlink,advlist,autolink,autosave,contextmenu,directionality,emotions,fullscreen,inlinepopups,insertdatetime,layer,media,nonbreaking,noneditable,pagebreak,paste,preview,save,searchreplace,style,table,visualchars,xhtmlxtras",
				theme_advanced_buttons1           : "bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,bullist,numlist,|,link,unlink,anchor,image,media,emotions,charmap,code",
				theme_advanced_buttons2           : "save,newdocument,|,copy,cut,paste,pastetext,pasteword,|,undo,redo,|,search,replace,|,tablecontrols",
				theme_advanced_buttons3           : "insertlayer,moveforward,movebackward,absolute,|,advhr,cleanup,removeformat,visualaid,|,ltr,rtl,|,outdent,indent,blockquote,cite,abbr,acronym,del,ins,insertdate,inserttime,attribs,|,preview,fullscreen",
				theme_advanced_buttons4           : "styleselect,styleprops,formatselect,fontselect,fontsizeselect,|,visualchars,nonbreaking,pagebreak,restoredraft,|,help",
				theme_advanced_toolbar_location   : "external",
				theme_advanced_toolbar_align      : "center",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing           : true,
				width                             : '100%'/*,
			 file_browser_callback : EditorCallback !== undefined ? 'EditorCallback' : ''*/
			}
		);
		$(
			'textarea.SEDITOR'
		).tinymce(
			{
				doctype      : '<!doctype html>',
				theme        : window.tinymce_theme_mini !== undefined ? tinymce_theme_mini : "simple",
				skin         : window.tinymce_skin_mini !== undefined ? tinymce_skin_mini : "o2k7",
				skin_variant : window.tinymce_skin_variant_mini !== undefined ? tinymce_skin_variant_mini : '',
				language     : window.L.lang !== undefined ? L.lang : 'en',
				width        : '100%'
			}
		);
	}
);
function editor_deinitialization (id) {
	tinyMCE.execCommand('mceRemoveControl', false, id);
}
function editor_reinitialization (id) {
	tinyMCE.execCommand('mceAddControl', false, id);
}
function editor_focus (id) {
	tinyMCE.execCommand('mceFocus', false, id);
}