`\cs\CRUD` - special system trait, that provides create/read/update/delete methods for faster development

### [Methods](#methods) [Properties](#properties) [Data model](#data-model) [Example](#example)

<a name="methods" />
###[Up](#) Methods

CRUD defines next 4 obvious methods:

* create
* read
* update
* delete
* find_urls
* update_files_tags

#### create(...$arguments : array) : false|int|string
Method is for creation of new items. `$arguments` should contain all elements of `$this->data_model` (except first one if it is autoincrement column in database).
Arguments order in array should be the same as in data model or should be an associative array (in associative array order doesn't matter).
Arguments might be passed as single argument with array or as multiple arguments in the same order as in `$this->data_model`.

#### read($id : int|int[]|string|string[]) : array|false
Method is for reading of items. `$arguments` should contain only one element, `id` (which means first item in `$this->data_model`, might be array of `id`).
Strings in returned data will be casted to integers or floats types if they are specified as integers or floats in `$this->data_model`.

#### update(...$arguments : array) : bool
Method is for updating of items. `$arguments` should contain all elements of `$this->data_model`.
Arguments order in array should be the same as in data model or should be an associative array (in associative array order doesn't matter).
Arguments might be passed as single argument with array or as multiple arguments in the same order as in `$this->data_model`.

#### delete($id : int|int[]|string|string[]) : bool
Method is for deletion of items. `$arguments` should contain only one element, `id` (which means first item in `$this->data_model`, might be array of `id`).

#### find_urls($data : string[]) : string[]
Takes array (even multi-dimensional) and returns found URLs (might be string itself or found inside HTML code)

#### update_files_tags($tag : string, $old_files : string[], $new_files : string[])
Calculated difference between old and new files, drops files tags for old files and add files tags for new files. This allows easily add all necessary tags for uploaded files.

<a name="properties" />
###[Up](#) Properties

CRUD method supports such properties:

* data_model (required)
* table (required)
* data_model_ml_group (optional)
* data_model_files_tag_prefix (optional)

#### data_model
Described data model structure for CRUD operations, details in next section

#### table
String with table name that is used in conjunction with data model

#### data_model_ml_group
String with prefix for multilingual fields, needed for `ml:` prefix, details in next section

#### data_model_files_tag_prefix
Prefix for uploaded files. If present - all fields will be scanned using `::find_urls()` method and found URLs will be tagged using `::update_files_tags()` method.
Just declaring this property will handle tagging of all uploaded files for you.

<a name="data-model" />
###[Up](#) Data model
Data model must be present as `$data_model` property of class that uses `\cs\CRUD` trait.
Data model is an array with one required field (must be first in list and be unique) and any number of optional.
First field might be:
* autoincrement column in DB table, in this case should be skipped when calling `::create()` method since it will be generated automatically
* may be non-autoincrement unique column, in this case you should pass its value explicitly when calling `::create()` method
All items names should coincide with corresponding names of columns in DB table.
Items are keys of array, values are types of keys.

```json
{
    "id"      : "int",
    "title"   : "text",
    "content" : "ml:html"
}
```

There are 7 types, one prefix and special one case:
* int
* float
* text
* html
* set
* json
* callable
* `ml:` prefix and `$this->data_model_ml_group`
* joined tables

#### int, float
These two types can have 3 forms of writing:

* int
* int:{min}
* int:{min}..{max}

`{min}` and `{min}:{max}` allows to limit range of possible values

```json
{
    "id"     : "int",
    "user"   : "int:1",
    "status" : "int:0..1",
    "score"  : "float"
}
```

#### text, html
These two types can have 3 forms of writing:

* text
* text:{length}
* text:{length}:{ending}

`{length}` and `{length}:{ending}` allows to limit maximum possible length of string, and optionally specify ending which will be added after cutting (`...` by default).

`text` and `html` are both processed by `xap()` function, but `$html` argument in first case is `text` and in second `true`.

#### set
This type allows you to enumerate all possible values, and if given argument doesn't match any of mentioned here - first specified value will be taken.
Syntax:

* set:{first value},{second value},{third value}

`{first value}`, `{second value}` and `{third value}` are possible values, you can write as many of them as you need, separated with `,`.

If you'll try to pass `5` here, then as it doesn't match any of mentioned - `{first value}` will be taken as default.

#### json
This type converts field data on creation or update into JSON and converts back on reading.

#### callable
If `callable` was specified - it will be called with single parameter and must return processed value:

```php
<?php
function ($string) {
    return xap($string, false);
}
```

This allows to provide custom, more complex processing of input data.

#### `ml:` prefix and `$this->data_model_ml_group`
It is possible to prefix `text` and `html` types with `ml:` to specify that this field is multilingual.

In such case this field will be automatically by `cs\Text` (about [cs\Text class](/docs/$Text.md)).

To work with this prefix `$this->data_model_ml_group` protected property should be created.

Multilingual group and label are composed in such way:
* `$group = $this->data_model_ml_group.'/'.$primary_key_field` - `$primary_key_field` very often is integer autoincrement `id`, but any others are supported as well
* `$primary_key_field_value` - depending on situation may be integer autoincrement `id` or any other supported

All this multilingual operations works on all methods (creation, reading, updating and deletion) of this trait.

`[prefix]texts` and `[prefix]texts_data` tables are assumed to be present in the same DB returned by `$this->cdb()` method (about `$this->cdb()` in [cs\DB\Accessor trait](/docs/$db.md#accessor-trait)).

#### Joined tables
This is more difficult type, but much more powerful as well.
Joined table is represented in form of array and describes table that is complementary to one from `$this->table` property.
Let's look at example:
```php
<?php
...
    protected $data_model                  = [
        'id'         => 'int',
        'date'       => 'int',
        'category'   => 'int',
        'price'      => 'float',
        'in_stock'   => 'int',
        'soon'       => 'int:0..1',
        'listed'     => 'int:0..1',
        'attributes' => [
            'data_model' => [
                'id'            => 'int',
                'attribute'     => 'int',
                'numeric_value' => 'float',
                'string_value'  => 'text',
                'text_value'    => 'html',
                'lang'          => 'text' // Some attributes are language-dependent, some aren't, so we'll handle that manually
            ]
        ],
        'images'     => [
            'data_model' => [
                'id'    => 'int',
                'image' => 'text'
            ]
        ],
        'videos'     => [
            'data_model' => [
                'id'     => 'int',
                'video'  => 'text',
                'poster' => 'text',
                'type'   => 'text'
            ]
        ],
        'tags'       => [
            'data_model'     => [
                'id'  => 'int',
                'tag' => 'html'
            ],
            'language_field' => 'lang'
        ]
    ];
    protected $table                       = '[prefix]shop_items';
    protected $data_model_files_tag_prefix = 'Shop/items';
...
```
In this case `attributes`, `images`, `videos` and `tags` are not fields of `[prefix]shop_items` table, but additional joined tables:
* `[prefix]shop_items_attributes`
* `[prefix]shop_items_images`
* `[prefix]shop_items_videos`
* `[prefix]shop_items_tags`

So, joined tables are named like keys of `$this->data_model` array, but with main table name as prefix.
Each joined table is represented as array with key `data_model` (required) and `language_field` (optional).

##### Joined table `data_model`
Data model of joined table is similar to main data model with few exceptions:
* further joined tables are not supported
* `ml:` prefix is not supported

First key will be used for connection with main table, you do not need to pass it in during create or update, it will not be returned during read.

If data model consists only from two keys (identifier and another field) - you can pass array of scalar values during create/update or even just scalar value in case of single item, also array of scalar items will be returned on read.

##### Joined table `language_field`
`language_field` is optional and should be used when joined table contains language-specific things (for instance, article tags, they might be different depending on language).
`language_field` key should have field name in joined table as its value, also this field should not be present in `data_model` key, you do not need to pass it in during create or update, it will not be returned during read.

<a name="example" />
###[Up](#) Example

```php
<?php
namespace cs\modules\News;
use
    cs\CRUD,
    cs\Singleton;
class News {
    use
        CRUD,
        Singleton;
    /**
     * News data model
     */
    protected $data_model = [
        'id'      => 'int',
        'title'   => 'text:255',
        'content' => 'html',
        'tags'    => [ // [prefix]news_tags(id, tag, lang)
            'data_model'     => [
                'id'  => 'int',
                'tag' => 'string'
            ],
            'language_field' => 'lang'
        ]
    ];
    /**
     * Table name
     */
    protected $table = '[prefix]news';
    /**
     * Required because \cs\CRUD trait uses trait \cs\DB\Accessor
     */
    protected cdb () {
        return Config::instance()->module('News')->db('news');
    }
    /**
     * @param string   $title
     * @param string   $content
     * @param string[] $tags
     *
     * @return false|int
     */
    function add ($title, $content, $tags) {
        return $this->create([$title, $content, $tags]);
    }
    /**
     * @param int $id
     *
     * @return array
     */
    function get ($id) {
        return $this->read($id);
    }
    /**
     * @param int      $id
     * @param string   $title
     * @param string   $content
     * @param string[] $tags
     *
     * @return bool
     */
    function set ($id, $title, $content, $tags) {
        return $this->update([$id, $title, $content, $tags]);
    }
    /**
     * @param int $id
     *
     * @return bool
     */
    function del ($id) {
        return $this->delete($id);
    }
}
```

`func_get_args()` might be used for `$arguments` argument instead of passing all arguments explicitly.
