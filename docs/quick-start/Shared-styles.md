CleverStyle Framework doesn't enforce any styling by default at all. This means developers have complete freedom to choose styling they want and do not need to fight with defaults.

However, some defaults are actually present and can be explicitly used if necessary with granular control over what is actually used and where.

These defaults are implemented in form of [Shared styles](https://www.polymer-project.org/1.0/docs/devguide/styling.html.md#style-modules):
* `normalize` provides custom elements-aware version of normalize.css
* `basic-styles-alone` provides custom provide advanced basic styling (including sane typography) on top of `normalize`
* `advanced-styles-alone` contains utility classes (refer to source code for actual contents):
  * `.cs-text-primary`
  * `.cs-text-success`
  * `.cs-text-warning`
  * `.cs-text-error`
  * `.cs-text-center`
  * `.cs-text-left`
  * `.cs-text-right`
  * `.cs-text-lead`
  * `.cs-text-bold`
  * `.cs-text-italic`
  * `.cs-block-primary`
  * `.cs-block-success`
  * `.cs-block-warning`
  * `.cs-block-error`
  * `.cs-margin`
  * `.cs-margin-top`
  * `.cs-margin-bottom`
  * `.cs-margin-left`
  * `.cs-margin-right`
  * `.cs-margin-none`
  * `.cs-padding`
  * `.cs-padding-top`
  * `.cs-padding-bottom`
  * `.cs-padding-left`
  * `.cs-padding-right`
  * `.cs-padding-none`
  * `.cs-cursor-pointer`
  * `.cs-table` (with nested structures and attributes support)
* `basic-styles` is `normalize` and `basic-styles-alone` together
* `advanced-styles` is `basic-styles` and `advanced-styles-alone` together

It is important to mention that using shared styles in main document doesn't affect custom elements and each element should explicitly use these shared styles if necessary.
