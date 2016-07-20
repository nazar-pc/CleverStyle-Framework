`$Request` - is system object, that provides unified source of information about request, instance can be obtained in such way:
 ```php
 $Request = \cs\Request::instance();
 ```

### [Methods](#methods) [Properties](#properties) [Events](#events)

<a name="methods" />
###[Up](#) Methods

`$Request` object has next public method:
* init()
* init_from_globals()
* init_server()
* init_query()
* init_data_and_files()
* init_cookie()
* init_route()
* header()
* query()
* data()
* files()
* cookie()
* route()
* route_path()
* route_ids()
* analyze_route_path()

#### init($server : string[], $query : array, $data : array, $files : array[], $data_stream : null|resource|string, $cookie : string[], $request_started :: float)
Initialize request with specified data, internally increases counter in static property `::$request_id` and calls consequently `::init_server()`, `:init_query()`, `::init_data_adn_files()`, `::init_cookie()` and `::init_route()`

#### init_from_globals()
Initialize request object from superglobals `$_SERVER`, `$_GET`, `$_POST`, `$_COOKIE` and `$_FILES` (including parsing `php://input` when necessary), just wrapper around `::init()`

#### init_server($server = [] : array)
Initialize server configuration (including request headers), `$server` structure is the same as `$_SERVER`

#### init_query($query = [] : array)
Initialize query array, `$query` structure is the same as `$_GET`

#### init_data_and_files($data = [] : array, $files = [] : array[], $data_stream = null : null|resource|string, $copy_stream = true : bool)
Initialize request data and files, `$data` structure is the same as `$_POST`, `$files` structure is either the same as `$_FILES` or normalized (where `$_FILES['file'][0]['name']` instead of `$_FILES['file']['name'][0]`)

#### init_cookie($cookie = []: string[])
Initialize cookies array, `$cookie` structure is the same as `$_COOKIE`

#### init_route()
Initializes route, required to be called after `::init_server()`

#### header($name : string) : false|string
Get header by name, if header not present - returns `false`

#### query(...$name : string[]|string[][]) : mixed|mixed[]|null
Get query parameter by name, if parameter (ar least one in case of many) not present - returns `null`

#### data(...$name : string[]|string[][]) : mixed|mixed[]|null
Get data item by name, if item (ar least one in case of many) not present - returns `null`

#### files($name : string) : array|null
Get file item by name

#### cookie($name : string) : null|string
Get cookie by name

#### route($index : int) : int|null|string
Get route part by index

#### route_path($index : int) : null|string
Get route path part by index

#### route_ids($index : int) : int|null
Get route ids part by index

#### analyze_route_path($path : string) : array
As result returns current route in system in form of array, normalized path, detects module path points to, whether this is API call, administration page, or home page

Example of returned data:
```json
{
    "route"           : [
        "general"
    ],
    "path_normalized" : "admin/System/general",
    "admin_path"      : true,
    "api_path"        : false,
    "cli_path"        : false,
    "current_module"  : "System",
    "home_page"       : false
}
```

<a name="properties" />
###[Up](#) Properties

`$Request` object has next public properties:
* id
* started
* method
* host
* scheme
* secure
* protocol
* path
* uri
* query_string
* remote_addr
* ip
* headers
* query
* data
* files
* data_stream
* cookie
* mirror_index
* path_normalized
* route
* route_path
* route_ids
* admin_path
* api_path
* cli_path
* current_module
* home_page

#### id
Global request id, used by system

#### started
Unix timestamp when request processing started

#### method
Uppercase method, GET by default

#### host
The best guessed host

#### scheme
Schema `http` or `https`

#### secure
Is requested with HTTPS

#### protocol
Protocol, for instance: `HTTP/1.0`, `HTTP/1.1` (default), HTTP/2.0

#### path
Path

#### uri
URI, basically `$path?$query_string` (without `?` is query string is empty), `/` by default

#### query_string
Query string

#### remote_addr
Where request came from, not necessary real IP of client, `127.0.0.1` by default

#### ip
The best guessed IP of client (based on all known headers), `$this->remote_addr` by default

#### headers
Headers are normalized to lowercase keys with hyphen as separator, for instance: `connection`, `referer`, `content-type`, `accept-language`

#### query
Query array, similar to `$_GET`

#### data
Data array, similar to `$_POST`

#### files
Normalized files array

Each file item can be either single file or array of files (in contrast with native PHP arrays where each field like `name` become an array) with keys `name`, `type`, `size`, `tmp_name`, `stream` and `error`.

`name`, `type`, `size` and `error` keys are similar to native PHP fields in `$_FILES`; `tmp_name` might not be temporary file, but file descriptor wrapper like `request-file:///file` instead and `stream` is resource like obtained with `fopen('/tmp/xyz', 'rb')`.

#### data_stream
Data stream resource, similar to `fopen('php://input', 'rb')`

#### cookie
Cookie array, similar to `$_COOKIE`, but also contains un-prefixed keys according to system configuration

#### mirror_index
Current mirror according to configuration

#### path_normalized
Normalized processed representation of relative address, may differ from raw, should be used in most cases

#### route
Contains parsed route of current page url in form of array without module name and prefixes `admin|api`.

For page `admin/System/general/system`:
```json
[
    "general",
    "system"
]
```

#### route_path
Like `$route` property, but excludes numerical items

#### route_ids
Like `$route` property, but only includes numerical items (opposite to route_path property)

#### admin_path
Request to administration section

#### api_path
Request to api section

#### cli_path
Request to cli interface

#### current_module
Current module

#### home_page
Home page

<a name="events" />
###[Up](#) Events

*$Request* object supports next events:
* System/Request/routing_replace/before
* System/Request/routing_replace/after

#### System/Request/routing_replace/before
This event is used by components in order to change current route. Array with one element `rc` is set as parameter for event, is contains reference to string of current route. It may be changed by components, for example, to catch request to some page, that actually doesn't exists, and to make some replacements here.
```
[
    'rc' => &$rc //Reference to string with current route, this string can be changed
]
```

#### System/Request/routing_replace/after
Similar to `System/Request/routing_replace/before`, but happens after language, module, home page and other things are identified and removed from `$rc`.
```
[
    'rc'             => &$rc,              //Reference to string with current route, this string can be changed
    'cli_path'       => (bool)$cli_path,
    'admin_path'     => (bool)$admin_path,
    'api_path'       => (bool)$api_path,
    'regular_path'   => !($cli_path || $admin_path || $api_path),
    'current_module' => $current_module,
    'home_page'      => $home_page
]
```
