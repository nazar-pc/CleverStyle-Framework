### Page title
Part of page title is generated automatically in form: `Site name [| Administration] | Module name`.

Where `|` is page title separator, and `Administration` is added only on administration pages.

Before final page rendering all parts of title are stored in form of array and available as `$Page->Title`, also `$Page->title('add something to title')` can be used to add new title element to the end (to avoid manual title array editing).

### Page content
Page content may be added in few ways:
* using method `$Page->content()`
* using method `$Page->json()` (is used for API, it is possible to call method specifying array, and page will output JSON representation)
* manually editing `$Page->Content` property
* manually editing `$Reponse->body` property (not really recommended)
* writing to stream `$Reponse->body_stream` (for large amounts of data)
