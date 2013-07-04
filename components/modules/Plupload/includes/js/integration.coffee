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
window.file_upload	= (button, success, error, progress, multi) ->
	files			= [];
	if !button.attr 'id'
		button.attr 'id', 'plupload_' + String((new Date).getTime()).replace('.', '')
	uploader		= new plupload.Uploader
		runtimes		: 'html5'
		browse_button	: button.attr('id')
		max_file_size	: if window.plupload_max_file_size then plupload_max_file_size else null
		url				: '/Plupload'
		multi_selection	: multi
		multipart		: true
	uploader.init()
	file_element	= $('#' + uploader.id + '_html5');
	if !file_element.attr('accept')
		file_element.removeAttr('accept')
	uploader.bind 'FilesAdded', ->
		uploader.refresh()
		uploader.start()
	if progress
		uploader.bind 'UploadProgress', (uploader, file) -> progress(file.percent, file.size, file.loaded, file.name)
	if success
		uploader.bind 'FileUploaded', (uploader, files_, res) ->
			response	= $.parseJSON(res.response)
			if !response.error
				files.push(response.result)
			else
				alert response.error.message
		uploader.bind 'UploadComplete', ->
			success(files)
			files	= [];
	if error
		uploader.bind 'Error', (uploader, error) -> error(error)
	this.stop		= ->
		uploader.stop()
	this.destroy	= ->
		uploader.destroy()
	this.browse		= ->
		file_element.click()
	this
return