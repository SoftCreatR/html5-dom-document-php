# SoftCreatR\HTML5DOMDocument\HTML5DOMTokenList::toggle

Removes a given token from the list and returns false. If the token doesn't exist, it's added and the function returns true.

```php
public bool toggle ( string $token [, bool|null $force ] )
```

## Parameters

##### token

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The token you want to toggle.

##### force

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A Boolean that, if included, turns the toggle into a one-way operation. If set to false, the token will only be removed but not added again. If set to true, the token will only be added but not removed again.

## Returns

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;False if the token is not in the list after the call, or true if the token is in the list after the call.

## Details

Class: [SoftCreatR\HTML5DOMDocument\HTML5DOMTokenList](softcreatr.html5domdocument.html5domtokenlist.class.md)

Location: ~/src/SoftCreatR/HTML5DOMDocument/HTML5DOMTokenList.php

---

[back to index](index.md)

