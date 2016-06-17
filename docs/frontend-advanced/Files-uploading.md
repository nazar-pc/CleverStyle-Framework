First of all, files uploading is implemented not by system core, but by modules (for instance, Plupload) with `file_upload` functionality.

However, since files uploading is crucial feature for many applications, how files uploading should work is specified here in order to maintain cross-compatible modules.

On frontend side there is one simple method `cs.file_upload(button, success, error, progress, multi, drop_element)`.

All arguments are optional!

* `button` - DOM element or jQuery object, clicking on which files selection dialog will appear
* `success` - callback that will be called after successful uploading of all files, accepts one argument `files` with array of absolute urls of all uploaded files
* `error` - callback that will be called if error occurred in Plupload, accepts 3 arguments `error`, `xhr`, `file` with error text, xhr object and file object being uploaded
* `progress` - callback that will be called when file uploading progress changes, accepts 9 arguments `percent`, `size`, `uploaded_size`, `name`, `total_percent`, `total_size`, `total_uploaded`, `current_file`, `total_files` current progress in percents, total file size, size of uploaded part, file name, total percents, size and uploaded size (useful when multiple files being uploaded) and currently uploading file number and total number of files to be uploaded
* `multi` - allows to enable multiple files selection (defaults to `false`)
* `drop_element` - if specified - it will be possible to drop files on that element for uploading (defaults to `button`, first argument)

Method returns object, which can be used for some necessary actions using its methods:
* `stop` - call it if you want to stop upload process at any time
* `destroy` - call it to stop uploading and remove event listeners from `button` or `drop_element` elements

This is basically all on frontend, you just specify where to click or where to drop file and get array of URLs to uploaded files in callback, however, that files will be removed soon if you do not finish uploading process on backend.

Example of usage in TinyMCE plugin (LiveScript):
```livescript
uploader_callback = undefined
button            = document.createElement('button')
uploader          = cs.file_upload?(
    button
    (files) !->
        tinymce.uploader_dialog?.close()
        if files.length
            uploader_callback(files[0])
        uploader_callback := undefined
    (error) !->
        tinymce.uploader_dialog?.close()
        cs.ui.notify(error, 'error')
    (file) !->
        if !tinymce.uploader_dialog
            progress                             = document.createElement('progress', 'cs-progress')
            tinymce.uploader_dialog              = cs.ui.modal(progress)
            tinymce.uploader_dialog.progress     = progress
            tinymce.uploader_dialog.style.zIndex = 100000
            tinymce.uploader_dialog.open()
        tinymce.uploader_dialog.progress.value = file.percent || 1
)
...
    file_picker_callback : uploader && (callback) !->
        uploader_callback := callback
        button.click()
...
```
