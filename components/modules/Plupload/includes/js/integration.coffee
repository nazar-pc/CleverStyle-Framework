###*
 * @package		Plupload
 * @category	modules
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration into CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU GPL v2, see license.txt
 ###
###*
 * Files uploading interface
 *
 * @param {object}		button
 * @param {function}	success
 * @param {function}	error
 * @param {function}	progress
 * @param {bool}		multi
 *
 * @return {function}
###
cs.file_upload	= (button, success, error, progress, multi) ->
	files			= []
	browse_button	= $('<button id="plupload_' + (new Date).getTime() + '" style="display:none;"/>').appendTo('body')
	uploader		= new plupload.Uploader
		browse_button	: browse_button.get(0)
		max_file_size	: cs.plupload?.max_file_size ? null
		multi_selection	: multi
		multipart		: true
		runtimes		: 'html5'
		url				: '/Plupload'
	uploader.init()
	if button
		button.click ->
			setTimeout (->
				input	= browse_button.nextAll('.moxie-shim:first').children()
				if !input.attr('accept')
					input.removeAttr('accept')
				browse_button.click()
			), 0
	uploader.bind 'FilesAdded', ->
		uploader.refresh()
		uploader.start()
	if progress
		uploader.bind(
			'UploadProgress'
			(uploader, file) ->
				progress(file.percent, file.size, file.loaded, file.name)
		)
	if success
		uploader.bind(
			'FileUploaded'
			(uploader, files_, res) ->
				response	= $.parseJSON(res.response)
				if !response.error
					files.push(response.result)
				else
					alert response.error.message
		)
		uploader.bind(
			'UploadComplete'
			->
				success(files)
				files	= []
		)
	if error
		uploader.bind(
			'Error'
			(uploader, error) ->
				error(error)
		)
	this.stop		= ->
		uploader.stop()
	this.destroy	= ->
		browse_button.remove()
		uploader.destroy()
		$('.moxie-shim').each ->
			if $(this).html() == ''
				$(this).remove()
	this.browse		= ->
		setTimeout (->
			input	= browse_button.nextAll('.moxie-shim:first').children()
			if !input.attr('accept')
				input.removeAttr('accept')
			browse_button.click()
		), 0
	this
return