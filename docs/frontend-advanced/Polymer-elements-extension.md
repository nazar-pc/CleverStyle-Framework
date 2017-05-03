Before getting into details here, make sure you are familiar with [Web Components](http://webcomponents.org/) in general, [Polymer](https://www.polymer-project.org/) itself and with [behaviors](https://www.polymer-project.org/1.0/docs/devguide/behaviors) in particular.

This features only work when using legacy syntax `Polymer()` and doesn't work with class-based declarations.

Though, sometimes you may need to extend existing custom element while preserving original name.
For instance, you have module with pages build with Polymer elements and want to change that page. But, obviously, you do not want to edit module to keep updating possibility, consequently, you can't change names of custom elements and thus modify them in convenient way.

This is where patched Polymer included in CleverStyle Framework helps. In contrast to extending existing elements you can extend element itself (kind of that actually).

Example (JS extension):
```html
<script>
    Polymer({
        is      : "test-element",
        extends : "test-element",
        ready   : function () {
            this.parent_method();
        }
    });
</script>
<dom-module id="test-element">
    <template></template>
    <script>
        Polymer({
            is            : "test-element",
            parent_method : function () {
                alert("I'm your parent!");
            }
        });
    </script>
</dom-module>
```

Under the hood when `is == extends` element registration will be delayed. When original declaration will be found - it will be converted to behavior and registration of first element will finish.
So, this allows you to kind of extend original element, while this is not entirely true.

Basically, there are few cases with elements extension: you can either extend or override completely original JS declaration, also you can either override original DOM module declaration with template and styles or reuse existing, extending is not supported.

Example (JS override):
```html
<script>
    Polymer({
        is        : "test-element",
        overrides : "test-element",
        ready     : function () {
            alert('parent_method' in this);
        }
    });
</script>
<dom-module id="test-element">
    <template></template>
    <script>
        Polymer({
            is            : "test-element",
            parent_method : function () {
                alert("I'm your parent!");
            }
        });
    </script>
</dom-module>
```

Example (DOM module override):
```html
<dom-module id="test-element" overrides="test-element">
    <template>
        XYZ
    </template>
</dom-module>
<dom-module id="test-element">
    <template></template>
    <script>
        Polymer({
            is : "test-element"
        });
    </script>
</dom-module>
```

You can leverage power of [Reverse dependencies](/docs/backend-advanced/Components-dependencies-and-conflicts.md#reverse-dependencies) to patch page elements of other components.
