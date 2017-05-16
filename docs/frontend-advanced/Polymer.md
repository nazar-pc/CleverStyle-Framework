Polymer is used heavily for Framework's needs and is included on pages unconditionally unless you disable Web Components in administration interface (alongside with ApplyShim and CustomStyleInterface from ShadyCSS).

However, when using third-party Bower and NPM components they might reference Polymer like following:
```html
<link rel="import" href="../polymer/polymer.html">
<!-- or -->
<link rel="import" href="../@polymer/polymer/polymer.html">
```

This is fine, CleverStyle Framework will capture such calls and will serve an empty file for it (and any relevant file provided by Polymer or ShadyCSS).
However, in order for this to happen, Polymer's and ShadyCSS's files should not be present. So if you have Polymer or ShadyCSS installed via dependencies you should either tweak web server preferences to send this files directly to `index.php` for Framework's handling or simply remove their directories).

NOTE: Composer assets module doesn't require you to remove any files and does everything necessary automatically.
