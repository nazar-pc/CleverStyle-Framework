/**
 * @package   Uploader
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L				= cs.Language('uploader_')
uploader		= (file, progress, state) ->
	new Promise (resolve, reject) !->
		form_data	= new FormData
		form_data.append('file', file)
		xhr				= new XMLHttpRequest()
		state.xhr		= xhr
		xhr.onload		= !->
			data	= JSON.parse(@responseText)
			if @status >= 400
				reject(data)
			else
				resolve(data)
		xhr.onerror		= !->
			reject({timeout, xhr})
		xhr.onprogress	= (e) !->
			progress?(e, file)
		xhr.open('post'.toUpperCase(), 'api/Uploader')
		xhr.send(form_data)
files_handler	= (files, success, error, progress, state) !->
	uploaded_files = []
	next_upload = (uploaded_file) !->
		if uploaded_file
			uploaded_files.push(uploaded_file)
		file = files.shift()
		if file
			uploader(file, progress, state)
				.then (data) !->
					next_upload(data.url)
				.catch (e) !->
					if error
						error.call(error, L.file_uploading_failed(file.name, e.error_description), state.xhr, file)
					else
						cs.ui.notify(L.file_uploading_failed(file.name, e.error_description), 'error')
					next_upload()
		else
			if uploaded_files.length
				success(uploaded_files)
			else
				cs.ui.notify(L.no_files_uploaded, 'error')
	next_upload()
_on				= (element, event, callback) !->
	if element.addEventListener
		element.addEventListener(event, callback)
	else if element.on
		element.on(event, callback)
_off			= (element, event, callback) !->
	if element.removeEventListener
		element.removeEventListener(event, callback)
	else if element.off
		element.off(event, callback)
/**
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
 */
cs.file_upload	= (button, success, error, progress, multi, drop_element) ->
	if !success
		return
	state				= {}
	local_files_handler	= (files) !->
		total_files	= files.length
		total_size	= 0
		files		= for file in files
			total_size += file.size
			file
		if !files.length
			return
		progress_local = (e, file) !->
			if !e.lengthComputable
				return
			uploaded_bytes	= e.loaded / e.total * file.size
			total_uploaded	= total_size - file.size + uploaded_bytes
			for f in files
				total_uploaded -= f.size
			#progress(percent, size, uploaded_size, name, total_percent, total_size, total_uploaded, current_file, total_files)
			progress(
				Math.round(e.loaded / e.total * 100),
				file.size,
				uploaded_bytes,
				file.name,
				Math.round(total_uploaded / total_size * 100),
				total_size,
				total_uploaded,
				total_files - files.length,
				total_files
			)
		files_handler(files, success, error, progress && progress_local, state)
	input				= document.createElement('input')
		..type		= 'file'
		..multiple	= !!multi
		..addEventListener('change', !->
			if @files.length
				local_files_handler(@files)
		)
	click				= input.click.bind(input)
	_on(button, 'click', click)
	dragover	= (e) !->
		e.preventDefault()
	drop		= (e) !->
		e.preventDefault()
		files = e.originalEvent.dataTransfer.files
		if files
			if multi
				local_files_handler(files)
			else
				local_files_handler([files[0]])
	if drop_element
		_on(drop_element, 'dragover', dragover)
		_on(drop_element, 'drop', drop)
	{
		stop	: ->
			state?.xhr?.abort()
		destroy	: ->
			state?.xhr?.abort()
			_off(button, 'click', click)
			if drop_element
				_off(drop_element, 'dragover', dragover)
				_off(drop_element, 'drop', drop)
	}
