TinyMCE is a custom build with [https://github.com/tinymce/tinymce/pull/561] patch applied (for Shadow DOM support) and with following plugins: advlist anchor charmap code codesample colorpicker contextmenu fullscreen hr image link lists media nonbreaking noneditable pagebreak paste preview searchreplace tabfocus table textcolor visualblocks visualchars wordcount.

To get similar build apply patch on top of TinyMCE and make build:
```bash
grunt
grunt bundle --themes modern --plugins advlist,anchor,charmap,code,codesample,colorpicker,contextmenu,fullscreen,hr,image,link,lists,media,nonbreaking,noneditable,pagebreak,paste,preview,searchreplace,tabfocus,table,textcolor,visualblocks,visualchars,wordcount
```
