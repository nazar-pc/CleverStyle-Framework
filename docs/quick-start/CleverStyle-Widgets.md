CleverStyle Framework doesn't have any CSS/UI framework bundled with it, since they are often not flexible enough and cause significant overhead.

Instead, system comes with CleverStyle Widgets, which is set of useful custom elements that work nicely with Shadow DOM, ready for convenient data bindings and doesn't enforce any appearance by default (namely, almost no styling by default, just support for many [CSS mixins](https://www.polymer-project.org/1.0/docs/devguide/styling.html.md#custom-css-mixins), so you can style them the way you need).

CleverStyle Widgets includes following elements:
* `cs-button`
* `cs-form`
* `cs-icon`
* `cs-input-text`
* `cs-label-button`
* `cs-label-switcher`
* `cs-link-button`
* `cs-nav-button-group`
* `cs-nav-dropdown`
* `cs-nav-pagination`
* `cs-nav-tabs`
* `cs-notify`
* `cs-progress`
* `cs-section-modal`
* `cs-section-switcher`
* `cs-select`
* `cs-textarea`
* `cs-tooltip`

#### cs-button
Extends native `button` element.

Attributes (also available as properties, so use whatever is more convenient):
* active - boolean, allows to force `:active` state
* force-fullsize - boolean, forces even empty button to appear as regular
* icon - string, icon from `cs-icon` element added before button contents
* icon-after - string, icon from `cs-icon` element added after button contents
* primary - boolean, allows to apply different styling to button, usually used for some key actions
* tight - boolean, affects button styling by removing space after button and placing directly before next element (useful for button groups and combining with inputs)

Properties:
* action - string, method on `bind` object to call on button click (see examples below)
* bind - object, object that contains `action` method to call on button click (see examples below)
* this - object, read-only, `this` of element, useful for data-binding
* tooltip - string, if specified then tooltip with specified content will be shown on hover

Examples:
```html
<button is="cs-button" type="button">Button</button>
<button is="cs-button" type="button" primary>Primary button</button>
<button is="cs-button" icon="book" type="button"></button>
<button is="cs-button" icon="check" on-tap="_enable" force-compact>[[L.enable]]</button>
<button is="cs-button" type="button" icon="home" force-fullsize></button>
<template is="dom-bind">
    <section is="cs-section-modal" this="{{modal}}">One Two Three</section>
    <button is="cs-button" type="button" bind="[[modal]]" action="open">Button</button>
</template>
```

#### cs-form
Extends native `form` element.

Primary purpose of element is to provide simple form styling (see example below).

Properties:
* this - read-only, `this` of element, useful for data-binding

Example (and expected markup):
```html
<form is="cs-form">
    <label>Name</label>
    <input is="cs-input-text">
    <label>Description</label>
    <textarea is="cs-textarea"></textarea>
</form>
```

#### cs-icon
Icon element, uses [FontAwesome](http://fontawesome.io/) icons set.

Attributes (also available as properties, so use whatever is more convenient):
* icon - string, icon (from FontAwesome icons set), if 2 icons separated by coma used - first icon is used as background with inverse color
* flip-x - boolean, mirror icon horizontally
* flip-xy - boolean, mirror icon vertically
* mono - boolean, fixed width icons
* rotate - number, rotate icon, can have value 0, 90, 180 or 270
* spin - boolean, makes icon spinning
* spin-step - boolean, makes spinning step-wise with 8 steps, not smooth

Properties:
* this - object, read-only, `this` of element, useful for data-binding
* tooltip - string, if specified then tooltip with specified content will be shown on hover

Examples:
```html
<cs-icon icon="home"></cs-icon>
<cs-icon icon="home" flip-x></cs-icon>
<cs-icon icon="circle home"></cs-icon>
<cs-icon icon="circle home"></cs-icon>
<cs-icon icon="spinner" spin></cs-icon>
```

#### cs-input-text
Extends native `input` element.

Attributes (also available as properties, so use whatever is more convenient):
* compact - boolean, uses automatic width computation, which is more compact that enforced styled width
* fullWidth - boolean, makes element width 100%
* tight - boolean, affects button styling by removing space after button and placing directly before next element (useful for button groups and combining with inputs)

Properties:
* this - object, read-only, `this` of element, useful for data-binding
* tooltip - string, if specified then tooltip with specified content will be shown on hover
* value - string, actually, native property, but now fires `value-change` event on any `change` or `input` event, so works nicely and more convenient with data bindings

Examples:
```html
<input is="cs-input-text">
<input is="cs-input-text" type="email" value="{{email}}">
<input is="cs-input-text" full-size>
<p>
    <input is="cs-input-text" tight>
    <button is="cs-button" icon="check">Button right after input</button>
</p>
```

#### cs-label-button
Extends native `label` element.

Element is intended to replace regular UI of `input[type=checkbox]` and/or `input[type=radio]` while using native semantic as much as possible (see example).

Attributes (also available as properties, so use whatever is more convenient):
* active - boolean, represents current state of element and its input
* first - boolean, whether current label is first in group of labels
* last - boolean, whether current label is last in group of labels

Properties:
* this - object, read-only, `this` of element, useful for data-binding
* tooltip - string, if specified then tooltip with specified content will be shown on hover
* value - string, provides unified interface for value getting and setting on any label from group, will be synchronized automatically with `input`s `checked` attribute, useful for data-binding

Example:
```html
<label is="cs-label-button">
    <input checked type="radio" value="0">
    Zero
</label>
<label is="cs-label-button">
    <input checked type="radio" value="1">
    One
</label>
```

#### cs-label-switcher
Extends native `label` element.

Completely similar to `cs-label-button` (except missing `first` and `last` attributes), but has checkbox-like UI instead of button-like in `cs-label-button`.
Example:
```html
<label is="cs-label-switcher">
    <input checked type="radio" value="0">
    Zero
</label>
<label is="cs-label-switcher">
    <input checked type="radio" value="1">
    One
</label>
```

#### cs-link-button
Extends native `a` element.

Button-like UI of link. Completely similar to `cs-button`.

Example:
```html
<a is="cs-link-button" href="/" icon="home">Home</a>
```

#### cs-nav-button-group
Extends native `nav` element.

Creates group of buttons (either horizontal or vertical), where there is no space between buttons.

Attributes (also available as properties, so use whatever is more convenient):
* vertical - boolean, make vertical button group

Properties:
* this - object, read-only, `this` of element, useful for data-binding

Example:
```html
<nav is="cs-nav-button-group">
    <button is="cs-button" primary type="submit") OK
    <button is="cs-button" type="button") Cancel
</nav>
```

#### cs-nav-dropdown
Extends native `nav` element.

Regular dropdown element with some content inside.

Attributes (also available as properties, so use whatever is more convenient):
* align - string, either `left` (by default) or `right`
* opened - boolean, automatically when dropdown is opened

Properties:
* target - object, if `cs-button` object passed, click on corresponding button will toggle dropdown (if dropdown is placed right after button - this property will be filled automatically)
* this - object, read-only, `this` of element, useful for data-binding

Example:
```html
<nav is="cs-nav-dropdown" align="right">
    <nav is="cs-nav-button-group" vertical>
        <a is="cs-link-button" on-tap="_general_settings">[[L.general]]</a>
        <button is="cs-button" type="button" on-tap="_change_password">[[L.change_password]]</button>
    </nav>
</nav>
```

#### cs-nav-pagination
Extends native `nav` element.

Generates pagination by specified page number and total number of pages, especially convenient to use with data bindings.

Attributes (also available as properties, so use whatever is more convenient):
* page - number, current page (starting from 1)
* pages - number, total number of pages

Properties:
* this - object, read-only, `this` of element, useful for data-binding

Example:
```html
<nav is="cs-nav-pagination" page="{{page}}" pages="[[total_pages]]"></nav>
```

#### cs-nav-tabs
Extends native `nav` element.

Tabs functionality, especially useful in conjunction with `cs-section-switcher` element. If next element after tabs is `cs-section-switcher` - tabs will control it automatically.

Properties:
* selected - number, current selected tab index (starting from 0)
* this - object, read-only, `this` of element, useful for data-binding

Example:
```html
<nav is="cs-nav-tabs">
    <button is="cs-button" type="button">One</button>
    <button is="cs-button" type="button">Two</button>
</nav>
```

#### cs-notify
Notification element, on creation will move itself to to `document.documentElement` if not there already.

Attributes (also available as properties, so use whatever is more convenient):
* bottom - boolean, whether element is located at bottom of the page
* error - boolean, apply error appearance
* left - boolean, place notification on the left
* no-icon - boolean, allows to remove close button (shown by default)
* right - boolean, place notification on the right
* success - boolean, apply success appearance
* timeout - number, if specified then will be closed after specified number of seconds (0 by default, which means never close automatically)
* top - boolean, whether element is located at top of the page (used by default if `bottom` not specified)
* warning - boolean, warning success appearance

Properties:
* content - string, notification content (will set `this.innerHTML`, so DOM elements can be inserted into element explicitly instead)
* this - object, read-only, `this` of element, useful for data-binding

Examples:
```html
<cs-notify>Hello</cs-notify>
<cs-notify success left>Hello</cs-notify>
<cs-notify error bottom right>Hello</cs-notify>
```

#### cs-progress
Extends native `progress` element.

Progress bar.

Attributes (also available as properties, so use whatever is more convenient):
* infinite - boolean, infinite progress bar
* text-progress - whether show text with percents inside progress bar (Chromium only)
* value - number, attribute only (not working as property!), current progress 0..100%

Properties:
* this - object, read-only, `this` of element, useful for data-binding
* tooltip - string, if specified then tooltip with specified content will be shown on hover

Example:
```html
<progress is="cs-progress" value="20"></progress>
```

#### cs-section-modal
Extends native `section` element.

Modal dialog.

If placed right after `cs-button` element, then button will be automatically used for modal opening.

Attributes (also available as properties, so use whatever is more convenient):
* as-is - boolean, if specified then there will be no background in modal
* auto-destroy - boolean, whether to destroy modal on closing
* auto-open - boolean, whether to open modal automatically on creation
* manual-close - boolean, if specified then `Esc` key and click outside of modal will not work and explicit calling `this.close()` will is needed

Properties:
* this - object, read-only, `this` of element, useful for data-binding

Examples:
```html
<template is="dom-bind">
    <section is="cs-section-modal" this="{{modal}}">One Two Three</section>
    <button is="cs-button" type="button" bind="[[modal]]" action="open">Button</button>
</template>
<button is="cs-button" type="button">Button</button>
<section is="cs-section-modal">One Two Three</section>
<section is="cs-section-modal" auto-open>One Two Three</section>
```

#### cs-section-switcher
Extends native `section` element.

Switcher element, especially useful in conjunction with `cs-nav-tabs` element (see example). If switcher is placed right after `cs-nav-tabs` - tabs will control switcher automatically.

Properties:
* selected - number, current selected content element index (starting from 0)
* this - object, read-only, `this` of element, useful for data-binding

Examples:
```html
<nav is="cs-nav-tabs">
    <button is="cs-button" type="button">One</button>
    <button is="cs-button" type="button">Two</button>
</nav>
<section is="cs-section-switcher">
    <article>One</article>
    <article>Two</article>
</section>
<section is="cs-section-switcher" selected="[[selected]]">
    <article>One</article>
    <article>Two</article>
</section>
<nav is="cs-nav-tabs" selected="{{selected}}">
    <button is="cs-button" type="button">One</button>
    <button is="cs-button" type="button">Two</button>
</nav>
```

#### cs-select
Extends native `select` element.

Attributes (also available as properties, so use whatever is more convenient):
* compact - boolean, uses automatic width computation, which is more compact that enforced styled width
* fullWidth - boolean, makes element width 100%
* tight - boolean, affects button styling by removing space after button and placing directly before next element (useful for button groups and combining with inputs)

Properties:
* selected - string or array, depending on `multiple` attribute might contain single value or array of values (extremely convenient for two-way data-bindings), it is recommended to use it instead of native `value`
* this - object, read-only, `this` of element, useful for data-binding
* tooltip - string, if specified then tooltip with specified content will be shown on hover

Examples:
```html
<select is="cs-select">
    <option>One</option>
    <option>Two</option>
    <option>Three</option>
</select>
<select is="cs-select" selected="{{user_data.language}}" size="5">
    <template is="dom-repeat" items="[[languages]]" as="language">
        <option value="[[language.clanguage]]">[[language.description]]</option>
    </template>
</select>
```

#### cs-textarea
Extends native `textarea` element.

Attributes (also available as properties, so use whatever is more convenient):
* autosize - boolean, if specified then height will be adjusted dynamically as contents grow
* compact - boolean, uses automatic width computation, which is more compact that enforced styled width
* fullWidth - boolean, makes element width 100%
* tight - boolean, affects button styling by removing space after button and placing directly before next element (useful for button groups and combining with inputs)

Properties:
* this - object, read-only, `this` of element, useful for data-binding
* tooltip - string, if specified, tooltip with specified content will be shown on hover
* value - string, actually, native property, but now fires `value-change` event on any `change` or `input` event, so works nicely and more convenient with data bindings

Examples:
```html
<textarea is="cs-textarea"></textarea>
<textarea is="cs-textarea" autosize full-width rows="5" value="{{content}}"></textarea>
```

#### cs-tooltip
Tooltip element. `cs-*` elements from this page which have `tooltip` property support will create `cs-tooltip` element automatically, explicit usage needed only for third-party or native elements.

Tooltip is applied to element by placing `cs-tooltip` element inside (see examples), it will be removed from DOM after initialization.

Examples:
```html
<button is="cs-button" tooltip="Tooltip contents">Button</button>
<button tooltip="Tooltip contents">
    Button
    <cs-tooltip></cs-tooltip>
</button>
```
