In CleverStyle Framework WYSIWYG editor is optional and provided by third party component. However, interface for that is defined and doesn't depend on exact component used.

There are 4 wrapper elements that convert underlying `textarea` or other block element into WYSIWYG editor:
* `cs-editor`
* `cs-editor-simple`
* `cs-editor-inline`
* `cs-editor-simple-inline`

#### cs-editor
Editor with complete set of features:
```html
<cs-editor>
    <textarea></textarea>
</cs-editor>
```

#### cs-editor-simple
Simplified version of editor without some secondary features:
```html
<cs-editor-simple>
    <textarea></textarea>
</cs-editor-simple>
```

#### cs-editor
Editor with complete set of features, but for inline use, namely, for `div`, not `textarea`:
```html
<cs-editor-inline>
    <div></div>
</cs-editor-inline>
```

#### cs-editor
Simplified version of inline editor, similar to `cs-editor-simple`:
```html
<cs-editor-simple-inline>
    <div></div>
</cs-editor-simple-inline>
```

### Data-bindings
If you want to use data bindings (especially with convenient cs-textarea), because of Polymer issue you'll need to apply data binding to editor's value property as well:
```html
<cs-editor value="{{content}}">
    <textarea is="cs-textarea" value="{{content}}"></textarea>
</cs-editor>
```
Since there is nothing more special to do in order to use wrappers, removing WYSIWYG component will not break anything, `textarea` or `div` will be left as is.
