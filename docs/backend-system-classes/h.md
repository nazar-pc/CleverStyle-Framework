*h* class is used for HTML generation (mostly) in accordance with the standards of HTML5 and with useful syntax extensions for simpler usage.

It is based on [BananaHTML](https://github.com/nazar-pc/BananaHTML), so you can refer to its documentation, only extensions for BananaHTML are described here.

### [Pseudo-tags](#pseudo-tags) [Constants](#constants)

<a name="pseudo-tags" />
###[Up](#) Pseudo tags

Such tags in fact are shortcuts for more complicated specific structures:
* icon
* info
* checkbox
* radio

#### icon
Renders icon with `span` tag from set of UIkit icons:
```php
h::icon(
    'plus',
    [
        'tooltip' => 'Plus'
    ]
)
```
results in
```html
<cs-icon icon="plus" tooltip="Plus"></cs-icon>
```
As you can see, it is possible to use other attributes here, for example, `data-title`.

#### info
Special pseudo-tag, is used by system. It uses translations as source of information for content, and takes into account system configuration option *Show tooltips*:
```php
h::info('section_path')
```
results in
```html
<span tooltip="Section path info">
    Section path<cs-tooltip></cs-tooltip>
</span>
```
Where *Section path* from `$L->section_path` and *Section path info* from `$L->section_path_info`.

#### checkbox
Convenient simplified interface for `cs-label-switcher > label > input[type=checkbox]`:
```php
h::checkbox(
    [
        'name'    => 'active',
        'checked' => 1,
        'value'   => 1,
        'in'      => 'Active'
    ]
).
h::checkbox(
    [
        'name'    => 'inactive',
        'checked' => 0,
        'value'   => 1,
        'in'      => 'Inactive'
    ]
)
```
results in:
```html
<cs-label-switcher>
    <label>
        <input checked name="active" type="checkbox" value="1"> Active
    </label>
</cs-label-switcher>
<cs-label-switcher>
    <label>
        <input name="inactive" type="checkbox" value="1"> Inactive
    </label>
</cs-label-switcher>
```

#### radio
Convenient simplified interface for `cs-label-button > label > input[type=radio]`:
```php
h::radio(
    [
        'name'    => 'active',
        'checked' => 1,
        'value'   => [0, 1],
        'in'      => ['Off', 'On']
    ]
)
```
results in:
```html
<cs-label-button>
    <label>
        <input name="active" tag="input" type="radio" value="0"> Off
    </label>
</cs-label-button>
<cs-label-button>
    <label>
        <input checked name="active" tag="input" type="radio" value="1"> On
    </label>
</cs-label-button>
```
