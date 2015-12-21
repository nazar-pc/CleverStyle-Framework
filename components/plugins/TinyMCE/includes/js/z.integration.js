// Generated by LiveScript 1.4.0
/**
 * @package   TinyMCE
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2012-2015, Nazar Mokrynskyi
 * @license   GNU Lesser General Public License 2.1, see license.txt
 */
(function(){
  var uploader_callback, uploader, base_config, x$;
  tinymce.baseURL = '/components/plugins/TinyMCE/includes/js';
  uploader_callback = undefined;
  uploader = cs.file_upload && cs.file_upload(null, function(files){
    tinymce.uploader_dialog.close();
    if (files.length) {
      uploader_callback(files[0]);
    }
    uploader_callback = undefined;
  }, function(error){
    tinymce.uploader_dialog.close();
    alert(error.message);
  }, function(file){
    var progress;
    if (!tinymce.uploader_dialog) {
      progress = document.createElement('progress', 'cs-progress');
      tinymce.uploader_dialog = cs.ui.modal(progress);
      tinymce.uploader_dsialog.progress = progress;
      tinymce.uploader_dialog.style.zIndex = 100000;
      tinymce.uploader_dialog.open();
    }
    tinymce.uploader_dialog.progress.value = file.percent || 1;
  });
  base_config = {
    doctype: '<!doctype html>',
    theme: cs.tinymce && cs.tinymce.theme !== undefined ? cs.tinymce.theme : 'modern',
    skin: cs.tinymce && cs.tinymce.skin !== undefined ? cs.tinymce.skin : 'lightgray',
    language: cs.Language.clang !== undefined ? cs.Language.clang : 'en',
    menubar: false,
    plugins: 'advlist anchor charmap code codesample colorpicker contextmenu fullscreen hr image link lists media nonbreaking noneditable pagebreak paste preview searchreplace tabfocus table textcolor visualblocks visualchars wordcount',
    resize: 'both',
    toolbar_items_size: 'small',
    width: '100%',
    convert_urls: false,
    remove_script_host: false,
    relative_urls: false,
    table_style_by_css: true,
    file_picker_callback: uploader && function(callback){
      uploader_callback = callback;
      uploader.browse();
    },
    setup: function(editor){
      editor.on('change', function(){
        var event;
        editor.save();
        event = document.createEvent('Event');
        event.initEvent('change', false, true);
        editor.getElement().dispatchEvent(event);
      });
    }
  };
  x$ = tinymce;
  x$.editor_config_full = importAll$({
    toolbar1: 'styleselect fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bold italic underline strikethrough superscript subscript | forecolor backcolor | fullscreen',
    toolbar2: 'undo redo | bullist numlist outdent indent blockquote codesample | link unlink anchor image media charmap hr nonbreaking pagebreak | visualchars visualblocks | searchreplace | preview code'
  }, base_config);
  x$.editor_config_simple = importAll$({
    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link image media | code'
  }, base_config);
  x$.editor_config_inline = importAll$({
    inline: true,
    menubar: false
  }, tinymce.editor_config_full);
  x$.editor_config_simple_inline = importAll$({
    inline: true,
    menubar: false
  }, tinymce.editor_config_simple);
  function importAll$(obj, src){
    for (var key in src) obj[key] = src[key];
    return obj;
  }
}).call(this);
