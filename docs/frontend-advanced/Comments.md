First of all, comments are not implemented by system core, but might be provided by some modules with `comments` functionality.

However, since it is crucial feature for many applications, how it should work is specified here in order to maintain cross-compatible modules.

[Backend integration can be found here](/docs/backend-advanced/Comments.md)

Integration on client-side is possible through 2 custom elements that any module that provides this functionality will define:
* `cs-comments` - to show comments list anywhere on page
* `cs-comments-count` - to show comments number anywhere on page

Both elements require 2 attributes to be defined on them:
* `module` - module name to which comments belong
* `item` - an item identifier within module to which comments belong

Example:
```html
<cs-comments module="Blogs" item="12"></cs-comments>
<cs-comments-count module="Blogs" item="12"></cs-comments-count>
```
