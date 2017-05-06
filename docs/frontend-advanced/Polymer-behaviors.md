Before getting into details here, make sure you are familiar with [Web Components](http://webcomponents.org/) in general, [Polymer](https://www.polymer-project.org/) itself and with [behaviors](https://www.polymer-project.org/1.0/docs/devguide/behaviors.html.md) in particular.

Since CleverStyle Framework uses custom elements heavily, it also provides some convenient behaviors for elements building out of the box:
* cs.Polymer.behaviors.Language

#### cs.Polymer.behaviors.Language
Powerful behavior to provide multilingual features for custom elements.

Basic usage is just including behavior, so that all translations become available for data binding:
```html
<dom-module id="test-element">
    <template>
        <p>[[Language.settings]]</p>
        <p>[[L.settings]]</p>
    </template>
    <script>
        Polymer({
            is        : 'test-element',
            behaviors : [cs.Polymer.behaviors.Language]
        })
    </script>
</dom-module>
```
`L` here is just shortcut for `Language`

A bit more advanced usage is to use formatted translations using exposed `__()` method:
```html
<dom-module id="test-element">
    <template>
        <p>[[__('permissions_for_user', username)]]</p>
    </template>
    <script>
        Polymer({
            is         : 'test-element',
            behaviors  : [cs.Polymer.behaviors.Language],
            properties : {
                username : 'Nazar Mokrynskyi'
            }
        })
    </script>
</dom-module>
```
`__('permissions_for_user', username)` is equivalent to standalone call `cs.Language.format('permissions_for_user', username)` or  `cs.Language.permissions_for_user(username)`.

 The last feature is using prefixes, which is useful for translations from components:
 ```html
 <dom-module id="test-element">
     <template>
         <p>[[__('for_user', username)]]</p>
     </template>
     <script>
         Polymer({
             is         : 'test-element',
             behaviors  : [cs.Polymer.behaviors.Language('permissions_')],
             properties : {
                 username : 'Nazar Mokrynskyi'
             }
         })
     </script>
 </dom-module>
 ```
The result will be exactly the same af in previous.

#### cs.Polymer.behaviors.computed_bindings
Behavior provides a few trivial, but useful computed bindings methods:

* `if(condition, then [, otherwise [, prefix [, postfix]]])`
* `join(array [, separator = ','])`
* `concat(thing [, another [, ...]])`
* `and(x, y [, z [,...]])`
* `or(x, y [, z [,...]])`
* `xor(x, y [, z [,...]])`
* `equal(a, b, strict = false)`
