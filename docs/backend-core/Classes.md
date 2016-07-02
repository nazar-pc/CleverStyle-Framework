### Basic classes:

| namespace\class                | Description                                                                                                            |
|--------------------------------|------------------------------------------------------------------------------------------------------------------------|
| `\cs\App`                      | provides functionality of application execution (blocks and module page generation, etc.)          |
| `\cs\Cache`                    | caching interface                                                                                                      |
| `\cs\Cache\Prefix`             | class is used for simplified work with cache, when using common prefix                                                 |
| `\cs\Config`                   | routing, modules defining, and configuration getting/setting                                                           |
| `\cs\Config\Module_Properties` | class for simplified access to properties of modules configuration                                                     |
| `\cs\Core`                     | low level system configuration                                                                                         |
| `\cs\DB`                       | Databases interface                                                                                                    |
| `\cs\Event`                    | events subscribing and dispatching                                                                                     |
| `\cs\ExitException`            | primary exception class for situations where it is necessary to stop further request processing with some status code  |
| `\cs\Group`                    | all work with user groups                                                                                              |
| `\h`                           | interface for html generation (inherits everything from `h\Base` class)                                                |
| `\h\Base`                      | inherits basic functionality from `nazarpc\BananaHTML` and customizes it for CleverStyle Framework                           |
| `\cs\Key`                      | generating/getting/setting/checking of keys, keys may be stored with some attached information                         |
| `\cs\Language`                 | multilingual user interface                                                                                            |
| `\cs\Language\Prefix`          | class is used for simplified work with language translations, when using common prefix                                 |
| `\cs\Mail`                     | mail sending functionality, inherits PHPMailer                                                                         |
| `\cs\Menu`                     | menu class is used in administration for generating second and third level of menu                                     |
| `\cs\Page`                     | resulting page generation                                                                                              |
| `\cs\Page\Meta`                | generation of various meta tags                                                                                        |
| `\cs\Permission`               | all work with permissions                                                                                              |
| `\cs\Request`                  | unified source of information                                                                                          |
| `\cs\Response`                 | unified target for all needed response data                                                                            |
| `\cs\Session`                  | responsible for current user session                                                                                   |
| `\cs\Storage`                  | files storing interface                                                                                                |
| `\cs\Text`                     | multilingual user content                                                                                              |
| `\cs\User`                     | all work with users                                                                                                    |
| `\cs\User\Properties`          | simplified access to user data                                                                                         |

### Supplementary classes:

| Class                          | Description                                                                                                            |
|--------------------------------|------------------------------------------------------------------------------------------------------------------------|
| `\cs\False_class`              | is used in some classes for chaining calls                                                                             |
| `\cs\Page\Includes_processing` | includes few methods used for processing CSS and HTML files before putting into cache                                  |
| `\cs\Request\File_stream`      | stream wrapper for request files                                                                                       |
| `\cs\Request\Psr7_data_stream` | stream wrapper for PSR7-compatible request data stream                                                                 |

### Traits

| Trait                          | Description                                                                                                            |
|--------------------------------|------------------------------------------------------------------------------------------------------------------------|
| `\cs\CRUD`                     | provides powerful wrappers for handling typical operations on items stored in database                                 |
| `\cs\CRUD_helpers`             | additional helper methods for `\cs\CRUD`                                                                               |
| `\cs\DB\Accessor`              | is used for simplifying work with db, provides few convenient wrapper methods                                          |
| `\cs\Singleton`                | is used by other classes in order to realize Singleton pattern                                                         |

`h` class is used as static, other basic classes are used as singletons

Classes in `cs` namespace can be redefined without change by placing class with the same interface into namespace `cs\custom`. This means, that custom class
can even inherit system one. Usually such classes are placed into separate files into `custom` directory, in order to be included before first usage.
