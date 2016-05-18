There are two possible types of routing in CleverStyle Framework (while only one of them can be used at once, they can't be mixed):
* files-based
* controller-based

### In general
There are some general rules how system core processes routes for different pages.

In generic form page URL looks like `admin|api|cli/Module_name/path/sub_path/more`, while all parts except `Module_name` are optional.

Working directory (`working_dir` hereinafter) for files and controller searching depends on prefix before module name:
* no prefix - `/components/modules/Module_name`
* `admin` prefix - `/components/modules/Module_name/admin`
* `api` prefix - `/components/modules/Module_name/api`
* `cli` prefix - `/components/modules/Module_name/cli`

`path` and `sub_path` are two levels of routing supported by system core, and they should be non-numeric (purely numeric elements are ignored here and next path element will be taken), everything else might be implemented by developer if needed.

### Files-based routing
This type of routing is very simple.

Independently on path `working_dir/index.php` file will be included if exists. If page URL doesn't have any `path` - this will be the only file included.

If `path` is present then additionally `working_dir/path.php` file will be included (may not exist if there is `sub_path` in `index.json` ([about index.json](/docs/Module-architecture.md#adminindexjson)).

If `sub_path` is present then additionally `working_dir/path/sub_path.php` file will be included.

#### File-based routing for API
There are some additional rules for API to improve routing for RESTful API.

First of all, in addition to:
* `working_dir/index.php`
* `working_dir/path.php`
* `working_dir/path/sub_path.php`

files that all become optional next files will be checked and included:
* `working_dir/index_{request_method_lowercase}.php`
* `working_dir/path_{request_method_lowercase}.php`
* `working_dir/path/sub_path_{request_method_lowercase}.php`

So, all regular files names will be suffixed by underscore and request method.

Also routes processing is more strict. In case when neither `working_dir/path/sub_path.php` nor `working_dir/path/sub_path_{request_method_lowercase}.php` file exists (and similarly for situations when there is no `sub_path` or `path`) then `404 Not Found` response will be generated (in non-API calls it would result in page without content).

And the last - if there is second level of routing in `index.json` - `sub_path` is required to be specified (alternatively module may change this behavior with event `System/Request/routing_replace`), but system will not do that).

### Controller-based routing
Controller-based routing is similar to files-based, so, read about it first, please.

Difference here is that instead of files static methods are used like follows:
* `\cs\modules\Module_name\Controller::index($ids, $path)`
* `\cs\modules\Module_name\Controller::path($ids, $path)`
* `\cs\modules\Module_name\Controller::path_sub_path($ids, $path)`
* `\cs\modules\Module_name\api\Controller::index_{request_method_lowercase}($ids, $path)`
* `\cs\modules\Module_name\api\Controller::path_{request_method_lowercase}($ids, $path)`
* `\cs\modules\Module_name\api\Controller::path_sub_path_{request_method_lowercase}($ids, $path)`

Rules about methods existence similar to files, controllers classes are different for regular pages, `admin` pages, `api` and `cli`, just like files:
* `\cs\modules\Module_name\Controller`
* `\cs\modules\Module_name\admin\Controller`
* `\cs\modules\Module_name\api\Controller`
* `\cs\modules\Module_name\cli\Controller`

Also, please, note that `\cs\Request` and `\cs\Response` instances are passed as arguments into methods for convenience.

One more convenient feature of controller-based routing comparing to file-based is that value, returned from controller method, that is different than `null` will be passed to `cs\Page::json()` for API calls and to `::content()` for other calls.
This allows to avoid direct `cs\Page` usage in many cases and makes methods a bit closer to pure functions.

And lastly, with Controller-based routing you can override controller by placing similar one into `cs\custom` namespace (namely, for `cs\modules\Blogs\Controller` customized version should be as `cs\custom\modules\Blogs\Controller` and it can even extend original controller).

### `\cs\Request::$route`, `\cs\Request::$route_ids` and `\cs\Request::$route_path`
* `\cs\Request::$route` is primary and contains whole page URL after `Module_name` dropped by `/` on elements (part after and including `?` is dropped from the end also)
* `\cs\Request::$route_ids` - contains only parts that are integers
* `\cs\Request::$route_path` - contains non-integers parts that are used for, basically, routing

This is done for convenience, because usually integers represent some kind of id or page number and it is very useful to have them separately from the rest.

This means, that `api/Module_name/items/10/comments/5` will be mapped to file `working_dir/items/comments.php` and `\cs\Request::$route_ids` will contain `[10, 5]`.
