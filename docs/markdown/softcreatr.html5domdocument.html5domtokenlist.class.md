# SoftCreatR\HTML5DOMDocument\HTML5DOMTokenList

Represents a set of space-separated tokens of an element attribute.

```php
SoftCreatR\HTML5DOMDocument\HTML5DOMTokenList implements Stringable {

	/* Properties */
	public int $length
	public string $value

	/* Methods */
	public __construct ( DOMElement $element , string $attributeName )
	public void add ( [ string $tokens ] )
	public bool contains ( string $token )
	public ArrayIterator entries ( void )
	public string|null item ( int $index )
	public void remove ( [ string $tokens ] )
	public void replace ( string $old , string $new )
	public bool toggle ( string $token [, bool|null $force ] )

}
```

## Implements

##### [Stringable](http://php.net/manual/en/class.stringable.php)

## Properties

##### public int $length

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The number of tokens.

##### public string $value

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A space-separated list of the tokens.

## Methods

##### public [__construct](softcreatr.html5domdocument.html5domtokenlist.__construct.method.md) ( [DOMElement](http://php.net/manual/en/class.domelement.php) $element , string $attributeName )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Creates a list of space-separated tokens based on the attribute value of an element.

##### public void [add](softcreatr.html5domdocument.html5domtokenlist.add.method.md) ( [ string $tokens ] )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Adds the given tokens to the list.

##### public bool [contains](softcreatr.html5domdocument.html5domtokenlist.contains.method.md) ( string $token )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns true if the list contains the given token, otherwise false.

##### public [ArrayIterator](http://php.net/manual/en/class.arrayiterator.php) [entries](softcreatr.html5domdocument.html5domtokenlist.entries.method.md) ( void )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns an iterator allowing you to go through all tokens contained in the list.

##### public string|null [item](softcreatr.html5domdocument.html5domtokenlist.item.method.md) ( int $index )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns an item in the list by its index (returns null if the index is out of bounds).

##### public void [remove](softcreatr.html5domdocument.html5domtokenlist.remove.method.md) ( [ string $tokens ] )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Removes the specified tokens from the list. If the token does not exist in the list, no error is thrown.

##### public void [replace](softcreatr.html5domdocument.html5domtokenlist.replace.method.md) ( string $old , string $new )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Replaces an existing token with a new token.

##### public bool [toggle](softcreatr.html5domdocument.html5domtokenlist.toggle.method.md) ( string $token [, bool|null $force ] )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Removes a given token from the list and returns false. If the token doesn't exist, it's added and the function returns true.

## Details

Location: ~/src/SoftCreatR/HTML5DOMDocument/HTML5DOMTokenList.php

---

[back to index](index.md)

