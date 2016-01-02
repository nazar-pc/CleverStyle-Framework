/**
 * @package   Uploader
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L			= cs.Language('uploader_')
uploader	= (file, progress, state) ->
	form_data	= new FormData
	form_data.append('file', file)
	state.ajax = $.ajax(
		url			: 'api/Uploader'
		type		: 'post'
		data		: form_data
		xhrFields	:
			onprogress	: (e) !->
				progress?(e, file)
		processData	: false
		contentType	: false
		error		: ->
	)
files_handler = (files, success, error, progress, state) !->
	uploaded_files = []
	next_upload = (uploaded_file) !->
		if uploaded_file
			uploaded_files.push(uploaded_file)
		file = files.shift()
		if file
			uploader(file, progress, state)
				.then(
					(data) -> next_upload(data.url)
				)
				.catch(
					(e) !->
						if error
							error.call(error, L.file_uploading_failed(file.name, e.responseJSON.error_description), e, file)
						else
							cs.ui.notify(L.file_uploading_failed(file.name, e.responseJSON.error_description), 'error')
						next_upload()
				)
		else
			if uploaded_files.length
				success(uploaded_files)
			else
				cs.ui.notify(L.no_files_uploaded, 'error')
	next_upload()
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
	state	= {}
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
	input = document.createElement('input')
		..type		= 'file'
		..multiple	= !!multi
		..addEventListener('change', !->
			if @files.length
				local_files_handler(@files)
		)
	$(button).on('click.cs-uploader', input~click)
	if drop_element
		$(drop_element)
			.on('dragover.cs-uploader', (e) !->
				e.preventDefault()
			)
			.on('drop.cs-uploader', (e) !->
				e.preventDefault()
				files = e.originalEvent.dataTransfer.files
				if files
					if multi
						local_files_handler(files)
					else
						local_files_handler([files[0]])
			)
	{
		stop	: ->
			state?.ajax?.abort()
		destroy	: ->
			state?.ajax?.abort()
			$(button).off('click.cs-uploader')
			if drop_element
				$(drop_element)
					.off('dragover.cs-uploader')
					.off('drop.cs-uploader')
	}
