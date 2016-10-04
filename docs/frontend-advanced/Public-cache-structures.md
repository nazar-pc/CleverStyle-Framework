When CleverStyle Framework creates public cache with CSS/JS/HTML files it also keeps additional structures in JSON files, that contain mapping of cached files to pages/components they belong to.

### Structure files

There are 2 such files for each theme in `storage/public_cache`:
* {theme_name}.json - base file
* {theme_name}.optimized.json - additional file, used when frontend optimization was selected in administration settings

`{theme_name}` should, obviously, be replaced with actual theme name.

#### {theme_name}.json
This file contains an array with 3 elements: dependencies, assets map, map of not embedded resources:
```json
[
	{
		"System" : [
			"editor",
			"simple_editor",
			"file_upload"
		]
	},
	{
		"admin/System" : {
			"js"   : "/storage/pcache/CleverStyle:admin+System.js?58e6c",
			"html" : "/storage/pcache/CleverStyle:admin+System.html?abea3"
		},
		"System"       : {
			"html" : "/storage/pcache/CleverStyle:System.html?717f0",
			"js"   : "/storage/pcache/CleverStyle:System.js?687ff",
			"css"  : "/storage/pcache/CleverStyle:System.css?fecf7"
		}
	},
	[]
]
```

Dependencies array is a simple mapping of component name to an array of its dependencies.

Assets map contains paths (like in `assets` key in `meta.json`) to the page where assets should be used and value is another array with optional keys `css`, `js` and `html` that contain paths relatively to the root of the website to the combined file with `?hash` at the end, where `hash` is based on contents of combined file.

Not embedded resources are such resources, that are related to some file from assets map (it will be used as key in this array), but were not embedded there because of various reasons (like configuration, file size, etc.), not embedded files are presented in form of arrays of paths relatively to the root of the website with `?hash` at the end, where `hash` is based on contents of the file.

#### {theme_name}.optimized.json
This array is based on the contents of `{theme_name}.json`, it contains an array with 2 array items:
```json
[
	[
		"/storage/pcache/CleverStyle:admin+System.js?58e6c",
		"/storage/pcache/CleverStyle:admin+System.html?abea3"
	],
	[
		"/storage/pcache/CleverStyle:System.html?717f0",
		"/storage/pcache/CleverStyle:System.js?687ff",
		"/storage/pcache/CleverStyle:System.css?fecf7"
	]
]
```

First array contains paths from assets map that are specific to some page, they are called optimized assets.
Second array contains paths from assets map that are used on all pages of the website.

### Structure usage
`{theme_name}.json` file is always used with public cache, `{theme_name}.optimized.json` is only used when frontend optimization was selected in administration settings.

#### No frontend optimization
Assets map is processed using information about current page and dependencies structure and necessary files are added on page.
Also, files added on page together with not embedded resources are added to preload header to be pushed by server if it supports HTTP/2 server push.

#### With frontend optimization
With frontend optimization only files that are used on all pages are added to the page explicitly and pushed with HTTP/2 server push (alongside with not embedded resources of these files).

The rest of files that are necessary for this page will be included on page in JSON form and will actually be included on page when files that are necessary on all pages will be executed.

This optimization allows to paint screen faster by loading app shell as soon as possible and only when shell is ready load the rest.
