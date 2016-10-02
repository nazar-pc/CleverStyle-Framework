Frontend loading in CleverStyle Framework might occur in different ways.

Depending on system configuration sources minification, combining, vulcanization, preloading and other useful features might happen to your CSS/JavaScript/HTML under the hood.

#### Caching, minification and combining
When *Cache and compress JavaScript, CSS and HTML* option is enabled in system administration, following will happen under the hood:
* CSS and HTML files will be analyzed
* small (under 4 KiB) files like images and fonts will be embedded into resulting CSS
* relative paths to larger files in CSS will be corrected to absolute (since cached CSS file will be located in another place), `?md5_hash` will be added based on file contents (replaces existing query string if there was any)
* CSS imports will be also embedded into parent CSS
* JS inside of HTML files (both inline and `script[src]`) will be combined into one block and inlined
* CSS inside of HTML (both inline and `link[rel=import][type=css]`) will go through the same procedure as regular CSS files and will be inlined
* CSS and JS code will additionally go through simple minification process
* Everything will be compressed with Gzip and placed in `/storage/public_cache`

During this process files are combined into logical bundles while taking into account dependencies between components.
All necessary dependencies structures and information about bundled files are cached in `/strorage/public_cache` in JSON files.

#### Vulcanization
If vulcanization is enabled, then CSS and JS will be left inlined in HTML files, otherwise they will be placed into separate files in order to be CSP-compatible.
NOTE: CSS is always inlined currently, this is because Polymer only supports `style[is=custom-style]`, but no `link`-based counterpart.

#### Frontend loading
CleverStyle Framework uses dependencies between components to load files in following order:
* system files from `/includes/{css|js|html}`
* theme files from `/themes/{theme_name}/{css|js|html}`
* globally used files of components (files that are not listed in [assets key of meta.json](/docs/quick-start/Module-architecture.md#more-about-assets-property) file and included on any page)
* files from all dependencies of current module (preserving order between dependencies themselves if any as well)
* module files from `/modules/{module_name}/includes/{css|js|html}`

#### Optimized frontend loading
In this mode system files, theme files and globally used files of components are loaded first and when they finish loading completely the rest of files are loaded afterwards.
This typically increases speed of initial page load.

This feature is only available when caching is used.

#### Preloading
System automatically generates `Link: rel=preload` headers that are typically used for HTTP2 Server Push, additionally corresponding `link[rel=preload]` tags are generated.
This feature takes into account optimized frontend loading and if used will only use files that are loaded initially.

Preloading feature is smart enough to preload images and fonts from within CSS files that are not inlined (but skips files that had `?` in URL, you can use this to avoid preloading non-critical fonts and images).

#### Bundled third-party libraries
System includes few third-party libraries that are available as AMD modules, they can be found in [/includes/js/modules](/includes/js/modules) directory.
Also Polymer, Alameda and sprintf.js are always present unconditionally.
