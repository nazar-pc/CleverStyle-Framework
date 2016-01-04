###*
 * @package   Plupload
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   GNU GPL v2, see license.txt
 ###
###*
 * Files uploading interface
 *
 * @param {object}				button
 * @param {function}			success
 * @param {function}			error
 * @param {function}			progress
 * @param {bool}				multi
 * @param {object}|{object}[]	drop_element
 *
 * @return {function}
###
cs.file_upload	= (button, success, error, progress, multi, drop_element) ->
	button			= $(button)
	files			= []
	browse_button	= $('<button id="plupload_' + (new Date).getTime() + '" style="display:none;"/>').appendTo('body')
	uploader		= new plupload.Uploader
		browse_button	: browse_button.get(0)
		max_file_size	: cs.plupload?.max_file_size ? null
		multi_selection	: multi
		multipart		: true
		runtimes		: 'html5'
		url				: '/Plupload'
		drop_element	: drop_element || button.get(0)
	uploader.init()
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
					if error
						error response.error.message
					else
						alert response.error.message
		)
		uploader.bind(
			'UploadComplete'
			->
				if files.length
					success(files)
					files	= []
		)
	uploader.bind(
		'Error'
		(uploader, error_details) ->
			if error
				error error_details
			else
				alert error_details.message
	)
	@stop		= ->
		uploader.stop()
	@destroy	= ->
		browse_button.nextAll('.moxie-shim:first').remove()
		browse_button.remove()
		button.off('click.cs-plupload')
		uploader.destroy()
	@browse		= ->
		input	= browse_button.nextAll('.moxie-shim:first').children()
		if !input.attr('accept')
			input.removeAttr('accept')
		browse_button.click()
	if button.length
		button.on('click.cs-plupload', @browse)
	@
return
