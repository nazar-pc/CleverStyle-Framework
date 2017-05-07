First of all, content enhancement is not implemented by system core, but might be provided by multiple modules.

However, since it is crucial feature for many applications, how it should work is specified here in order to maintain cross-compatible modules.

Content enhancement is a way to customize content with additional features. For instance, `Prism` module can enhance content by highlighting syntax of code snippets in different languages.

In order for content enhancement to work consumer should fire and content enhancer should subscribe to an event `System/content_enhancement`.
Content enhancer should be ready that target content might be under Shadow DOM.

Event signature:
```
System/content_enhancement
{
    element: dom_element
}
```

Example of consumer in Blogs module (LiveScript):
```livescript
Polymer(
...
    ready: !->
    ...
        cs.Event.fire('System/content_enhancement', {element: @$.content})
...
)
```

Example of enhancer in Prism module (LiveScript):
```livescript
...
cs.Event.on('System/content_enhancement', ({element}) !->
    Prism.highlightAll(true, ->, element)
    if !element.querySelector('custom-style > style[include=cs-prism-styles]')
        element.insertAdjacentHTML(
            'beforeend',
            """<custom-style><style include="cs-prism-styles"></style></custom-style>"""
        )
)
```
