Microsoft's browsers constantly lacking something, so polyfills for them should not affect other users and will be loaded only for corresponding versions of their browsers.

Sub-directories contain only version number, where 11 means IE and 12+ means Edge.
Everything from later versions automatically assumed to be applied for all older versions.
As we'll drop support for old versions we'll just remove corresponding directories.
