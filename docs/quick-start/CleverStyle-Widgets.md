CleverStyle Framework doesn't have any CSS/UI framework bundled with it, since they are often not flexible enough and cause significant overhead.

Instead, system comes with CleverStyle Widgets, which is set of useful custom elements that work nicely with Shadow DOM, ready for convenient data bindings and doesn't enforce any appearance by default (namely, almost no styling by default, just support for many [CSS mixins](https://www.polymer-project.org/1.0/docs/devguide/styling.html.md#custom-css-mixins), so you can style them the way you need).

CleverStyle Widgets includes following elements:
* `cs-button`
* `cs-dropdown`
* `cs-form`
* `cs-group`
* `cs-icon`
* `cs-input-text`
* `cs-label-button`
* `cs-label-switcher`
* `cs-link-button`
* `cs-modal`
* `cs-notify`
* `cs-pagination`
* `cs-progress`
* `cs-section-switcher`
* `cs-select`
* `cs-tabs`
* `cs-textarea`
* `cs-tooltip`

#### cs-button
Wrapper element for native `button` element.

Attributes (also available as properties, so use whatever is more convenient):
* active - boolean, allows to force `:active` state
* compact - boolean, forces button to have smaller size than usually by reducing padding
* icon-before - string, icon from `cs-icon` element added before button contents, only used on initialization, further changes are not reflected in UI
* icon-after - string, icon from `cs-icon` element added after button contents, only used on initialization, further changes are not reflected in UI
* icon - alias for `icon-before`, only used on initialization, further changes are not reflected in UI
* primary - boolean, allows to apply different styling to button, usually used for some key actions
* tight - boolean, affects styling by removing space after element and placing directly before next element (useful for button groups and combining with inputs)

Properties:
* action - string, method on `bind` object to call on button click (see examples below)
* bind - object, object that contains `action` method to call on button click (see examples below)
* this - object, read-only, `this` of element, useful for data-binding
* tooltip - string, if specified then tooltip with specified content will be shown on hover

Examples:
```html
<cs-button><button type="button">Button</button></cs-button>
<cs-button primary><button type="button">Primary button</button></cs-button>
<cs-button>
    <button on-tap="_enable" compact>
        <cs-icon icon="check"></cs-icon>
        [[L.enable]]
    </button>
</cs-button>
<template is="dom-bind">
    <cs-modal this="{{modal}}">One Two Three</cs-modal>
    <cs-button bind="[[modal]]" action="open"><button type="button">Button</button></cs-button>
</template>
```

#### cs-dropdown
Regular dropdown element with some content inside.

Attributes (also available as properties, so use whatever is more convenient):
* align - string, either `left` (by default) or `right`
* opened - boolean, is set automatically when dropdown is opened

Properties:
* target - object, if `cs-button` object passed, click on corresponding button will toggle dropdown (if dropdown is placed right after button - this property will be filled automatically)

Example:
```html
<cs-button icon="cog"><button type="button" small-button>[[L.settings]]</button></cs-button>
<cs-dropdown>
    <cs-group vertical>
        <cs-button><button type="button" on-tap="_general_settings">[[L.general]]</button></cs-link-button>
        <cs-button><button type="button" on-tap="_change_password">[[L.change_password]]</button></cs-button>
    </cs-group>
</cs-dropdown>
```

#### cs-form
Wrapper element for native `form` element.

Primary purpose of element is to provide simple form styling (see example below).

Example (and expected markup):
```html
<cs-form>
    <form>
        <label>Name</label>
        <cs-input-text><input></cs-input-text>
        <label>Description</label>
        <textarea is="cs-textarea"></textarea>
    </form>
</cs-form>
```

#### cs-group
Creates either horizontal or vertical group of elements (for instance, buttons), where there is no space between them.

Attributes (also available as properties, so use whatever is more convenient):
* vertical - boolean, make vertical group

Example:
```html
<cs-group>
    <cs-button primary><button type="submit">OK</button></cs-button>
    <cs-button><button type="button">Cancel</button></cs-button>
</cs-group>
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
Wrapper element for native `input` element.

Attributes (also available as properties, so use whatever is more convenient):
* compact - boolean, uses automatic width computation, which is more compact that enforced styled width
* full-width - boolean, makes element width 100%
* tight - boolean, affects styling by removing space after element and placing directly before next element (useful for button groups and combining with inputs)

Properties:
* tooltip - string, if specified then tooltip with specified content will be shown on hover

Input element will start firing `value-change` event on any `change` or `input` event, so that it works nicely and more convenient with data bindings

Examples:
```html
<cs-input-text><input></cs-input-text>
<cs-input-text><input type="email" value="{{email}}"></cs-input-text>
<cs-input-text full-size><input></cs-input-text>
<p>
    <cs-input-text tight><input></cs-input-text>
    <cs-button><button>Button right after input</button></cs-button>
</p>
```

#### cs-label-button
Wrapper element for native `label` element.

Element is intended to replace regular UI of `input[type=checkbox]` and/or `input[type=radio]` while using native semantic as much as possible (see example).

Attributes (also available as properties, so use whatever is more convenient):
* active - boolean, represents current state of element and its input
* first - boolean, whether current label is first in group of labels, typically set automatically
* last - boolean, whether current label is last in group of labels, typically set automatically

Properties:
* tooltip - string, if specified then tooltip with specified content will be shown on hover
* value - string, provides unified interface for value getting and setting on any label from group, will be synchronized automatically with `input`s `checked` attribute, useful for data-binding

Example:
```html
<cs-label-button>
    <label>
        <input checked type="radio" value="0">
        Zero
    </label>
</cs-label-button>
<cs-label-button>
    <label>
        <input checked type="radio" value="1">
        One
    </label>
</cs-label-button>
```

#### cs-label-switcher
Wrapper element for native `label` element.

Completely similar to `cs-label-button` (except missing `first` and `last` attributes), but has checkbox-like UI instead of button-like in `cs-label-button`.
Example:
```html
<cs-label-switcher>
    <label>
        <input checked type="radio" value="0">
        Zero
    </label>
</cs-label-switcher>
<cs-label-switcher>
    <label>
        <input checked type="radio" value="1">
        One
    </label>
</cs-label-switcher>
```

#### cs-link-button
Wrapper element for native `a` element.

Button-like UI of link. Completely similar to `cs-button` (except missing `action` and `bind` properties).

Example:
```html
<cs-link-button>
    <a href="/" icon="home">Home</a>
</cs-link-button>
```

#### cs-modal
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
    <cs-modal this="{{modal}}">One Two Three</cs-modal>
    <cs-button bind="[[modal]]" action="open"><button type="button">Button</button></cs-button>
</template>
<cs-button><button type="button">Button</button></cs-button>
<cs-modal>One Two Three</cs-modal>
<cs-modal auto-open>One Two Three</cs-modal>
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

#### cs-pagination
Generates pagination by specified page number and total number of pages, especially convenient to use with data bindings.

Attributes (also available as properties, so use whatever is more convenient):
* page - number, current page (starting from 1)
* pages - number, total number of pages

Example:
```html
<cs-pagination page="{{page}}" pages="[[total_pages]]"></cs-pagination>
```

#### cs-progress
Wrapper element for native `progress` element.

Attributes (also available as properties, so use whatever is more convenient):
* full-width - boolean, makes element width 100%
* infinite - boolean, infinite progress bar
* primary - boolean, allows to apply different styling to progress
* text-progress - whether show text with percents inside progress bar
* tight - boolean, affects styling by removing space after element and placing directly before next element (useful for button groups and combining with inputs)
* value - number, useful for data binding, otherwise attribute on `progress` element might be used

Properties:
* tooltip - string, if specified then tooltip with specified content will be shown on hover

Example:
```html
<progress is="cs-progress" value="20"></progress>
```

#### cs-section-switcher
Extends native `section` element.

Switcher element, especially useful in conjunction with `cs-tabs` element (see example). If switcher is placed right after `cs-tabs` - tabs will control switcher automatically.

Properties:
* selected - number, current selected content element index (starting from 0)
* this - object, read-only, `this` of element, useful for data-binding

Examples:
```html
<nav is="cs-tabs">
    <cs-button><button type="button">One</button></cs-button>
    <cs-button><button type="button">Two</button></cs-button>
</nav>
<section is="cs-section-switcher">
    <article>One</article>
    <article>Two</article>
</section>
<section is="cs-section-switcher" selected="[[selected]]">
    <article>One</article>
    <article>Two</article>
</section>
<nav is="cs-tabs" selected="{{selected}}">
    <cs-button><button type="button">One</button></cs-button>
    <cs-button><button type="button">Two</button></cs-button>
</nav>
```

#### cs-select
Extends native `select` element.

Attributes (also available as properties, so use whatever is more convenient):
* compact - boolean, uses automatic width computation, which is more compact that enforced styled width
* full-width - boolean, makes element width 100%
* tight - boolean, affects styling by removing space after element and placing directly before next element (useful for button groups and combining with inputs)

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

#### cs-tabs
Tabs functionality, especially useful in conjunction with `cs-section-switcher` element. If next element after tabs is `cs-section-switcher` - tabs will control it automatically.

Properties:
* selected - number, current selected tab index (starting from 0)

Example:
```html
<nav is="cs-tabs">
    <cs-button><button type="button">One</button></cs-button>
    <cs-button><button type="button">Two</button></cs-button>
</nav>
```

#### cs-textarea
Extends native `textarea` element.

Attributes (also available as properties, so use whatever is more convenient):
* autosize - boolean, if specified then height will be adjusted dynamically as contents grow
* compact - boolean, uses automatic width computation, which is more compact that enforced styled width
* full-width - boolean, makes element width 100%

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
<cs-button tooltip="Tooltip contents"><button>Button</button></cs-button>
<cs-button>
    <button tooltip="Tooltip contents">
        Button
        <cs-tooltip></cs-tooltip>
    </button>
</cs-button>
```
