# SoftCreatR\HTML5DOMDocument\HTML5DOMDocument::modify

Applies the modifications specified to the DOM document.

```php
public void modify ( [ int $modifications = 0 ] )
```

## Parameters

##### modifications

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The modifications to apply. Available values:
- HTML5DOMDocument::FIX_MULTIPLE_TITLES - removes all but the last title elements.
- HTML5DOMDocument::FIX_DUPLICATE_METATAGS - removes all but the last metatags with matching name or property attributes.
- HTML5DOMDocument::FIX_MULTIPLE_HEADS - merges multiple head elements.
- HTML5DOMDocument::FIX_MULTIPLE_BODIES - merges multiple body elements.
- HTML5DOMDocument::OPTIMIZE_HEAD - moves charset metatag and title elements first.
- HTML5DOMDocument::FIX_DUPLICATE_STYLES - removes all but first styles with duplicate content.

## Details

Class: [SoftCreatR\HTML5DOMDocument\HTML5DOMDocument](softcreatr.html5domdocument.html5domdocument.class.md)

Location: ~/src/SoftCreatR/HTML5DOMDocument/HTML5DOMDocument.php

---

[back to index](index.md)

