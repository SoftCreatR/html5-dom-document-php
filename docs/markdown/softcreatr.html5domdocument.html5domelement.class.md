# SoftCreatR\HTML5DOMDocument\HTML5DOMElement

Represents a live (can be manipulated) representation of an element in a HTML5 document.

```php
SoftCreatR\HTML5DOMDocument\HTML5DOMElement extends DOMElement implements DOMParentNode, DOMChildNode, Stringable {

	/* Properties */
	public HTML5DOMTokenList $classList
	public string $innerHTML
	public string $outerHTML

	/* Methods */
	public string getAttribute ( string $qualifiedName )
	public array<string, getAttributes ( void )
	public string getNodeValue ( void )
	public string getTextContent ( void )
	public self|null querySelector ( string $selector )
	public HTML5DOMNodeList querySelectorAll ( string $selector )

}
```

## Extends

##### [DOMElement](http://php.net/manual/en/class.domelement.php)

## Implements

##### [DOMParentNode](http://php.net/manual/en/class.domparentnode.php)

##### [DOMChildNode](http://php.net/manual/en/class.domchildnode.php)

##### [Stringable](http://php.net/manual/en/class.stringable.php)

## Properties

##### public HTML5DOMTokenList $classList

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A collection of the class attributes of the element.

##### public string $innerHTML

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The HTML code inside the element.

##### public string $outerHTML

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The HTML code for the element including the code inside.

### Inherited from [DOMElement](http://php.net/manual/en/class.domelement.php)

##### public  $childElementCount

##### public  $firstElementChild

##### public  $lastElementChild

##### public  $nextElementSibling

##### public  $previousElementSibling

##### public  $schemaTypeInfo

##### public  $tagName

### Inherited from [DOMNode](http://php.net/manual/en/class.domnode.php)

##### public  $attributes

##### public  $baseURI

##### public  $childNodes

##### public  $firstChild

##### public  $lastChild

##### public  $localName

##### public  $namespaceURI

##### public  $nextSibling

##### public  $nodeName

##### public  $nodeType

##### public  $nodeValue

##### public  $ownerDocument

##### public  $parentNode

##### public  $prefix

##### public  $previousSibling

##### public  $textContent

## Methods

##### public string [getAttribute](softcreatr.html5domdocument.html5domelement.getattribute.method.md) ( string $qualifiedName )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns the value for the attribute name specified.

##### public array<string, [getAttributes](softcreatr.html5domdocument.html5domelement.getattributes.method.md) ( void )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns an array containing all attributes.

##### public string [getNodeValue](softcreatr.html5domdocument.html5domelement.getnodevalue.method.md) ( void )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns the updated nodeValue property.

##### public string [getTextContent](softcreatr.html5domdocument.html5domelement.gettextcontent.method.md) ( void )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns the updated textContent property.

##### public self|null [querySelector](softcreatr.html5domdocument.html5domelement.queryselector.method.md) ( string $selector )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns the first child element matching the selector.

##### public HTML5DOMNodeList [querySelectorAll](softcreatr.html5domdocument.html5domelement.queryselectorall.method.md) ( string $selector )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns a list of children elements matching the selector.

### Inherited from [DOMElement](http://php.net/manual/en/class.domelement.php)

##### public [__construct](http://php.net/manual/en/domelement.construct.php) ( string $qualifiedName [, string|null $value [, string $namespace = '' ]] )

##### public void [after](http://php.net/manual/en/domelement.after.php) ( [  $nodes ] )

##### public void [append](http://php.net/manual/en/domelement.append.php) ( [  $nodes ] )

##### public void [before](http://php.net/manual/en/domelement.before.php) ( [  $nodes ] )

##### public void [getAttributeNS](http://php.net/manual/en/domelement.getattributens.php) ( string|null $namespace , string $localName )

##### public void [getAttributeNode](http://php.net/manual/en/domelement.getattributenode.php) ( string $qualifiedName )

##### public void [getAttributeNodeNS](http://php.net/manual/en/domelement.getattributenodens.php) ( string|null $namespace , string $localName )

##### public void [getElementsByTagName](http://php.net/manual/en/domelement.getelementsbytagname.php) ( string $qualifiedName )

##### public void [getElementsByTagNameNS](http://php.net/manual/en/domelement.getelementsbytagnamens.php) ( string|null $namespace , string $localName )

##### public void [hasAttribute](http://php.net/manual/en/domelement.hasattribute.php) ( string $qualifiedName )

##### public void [hasAttributeNS](http://php.net/manual/en/domelement.hasattributens.php) ( string|null $namespace , string $localName )

##### public void [prepend](http://php.net/manual/en/domelement.prepend.php) ( [  $nodes ] )

##### public void [remove](http://php.net/manual/en/domelement.remove.php) ( void )

##### public void [removeAttribute](http://php.net/manual/en/domelement.removeattribute.php) ( string $qualifiedName )

##### public void [removeAttributeNS](http://php.net/manual/en/domelement.removeattributens.php) ( string|null $namespace , string $localName )

##### public void [removeAttributeNode](http://php.net/manual/en/domelement.removeattributenode.php) ( [DOMAttr](http://php.net/manual/en/class.domattr.php) $attr )

##### public void [replaceWith](http://php.net/manual/en/domelement.replacewith.php) ( [  $nodes ] )

##### public void [setAttribute](http://php.net/manual/en/domelement.setattribute.php) ( string $qualifiedName , string $value )

##### public void [setAttributeNS](http://php.net/manual/en/domelement.setattributens.php) ( string|null $namespace , string $qualifiedName , string $value )

##### public void [setAttributeNode](http://php.net/manual/en/domelement.setattributenode.php) ( [DOMAttr](http://php.net/manual/en/class.domattr.php) $attr )

##### public void [setAttributeNodeNS](http://php.net/manual/en/domelement.setattributenodens.php) ( [DOMAttr](http://php.net/manual/en/class.domattr.php) $attr )

##### public void [setIdAttribute](http://php.net/manual/en/domelement.setidattribute.php) ( string $qualifiedName , bool $isId )

##### public void [setIdAttributeNS](http://php.net/manual/en/domelement.setidattributens.php) ( string $namespace , string $qualifiedName , bool $isId )

##### public void [setIdAttributeNode](http://php.net/manual/en/domelement.setidattributenode.php) ( [DOMAttr](http://php.net/manual/en/class.domattr.php) $attr , bool $isId )

### Inherited from [DOMNode](http://php.net/manual/en/class.domnode.php)

##### public void [C14N](http://php.net/manual/en/domnode.c14n.php) ( [ bool $exclusive = false [, bool $withComments = false [, array|null $xpath [, array|null $nsPrefixes ]]]] )

##### public void [C14NFile](http://php.net/manual/en/domnode.c14nfile.php) ( string $uri [, bool $exclusive = false [, bool $withComments = false [, array|null $xpath [, array|null $nsPrefixes ]]]] )

##### public void [appendChild](http://php.net/manual/en/domnode.appendchild.php) ( [DOMNode](http://php.net/manual/en/class.domnode.php) $node )

##### public void [cloneNode](http://php.net/manual/en/domnode.clonenode.php) ( [ bool $deep = false ] )

##### public void [getLineNo](http://php.net/manual/en/domnode.getlineno.php) ( void )

##### public void [getNodePath](http://php.net/manual/en/domnode.getnodepath.php) ( void )

##### public void [hasAttributes](http://php.net/manual/en/domnode.hasattributes.php) ( void )

##### public void [hasChildNodes](http://php.net/manual/en/domnode.haschildnodes.php) ( void )

##### public void [insertBefore](http://php.net/manual/en/domnode.insertbefore.php) ( [DOMNode](http://php.net/manual/en/class.domnode.php) $node [, [DOMNode](http://php.net/manual/en/class.domnode.php)|null $child ] )

##### public void [isDefaultNamespace](http://php.net/manual/en/domnode.isdefaultnamespace.php) ( string $namespace )

##### public void [isSameNode](http://php.net/manual/en/domnode.issamenode.php) ( [DOMNode](http://php.net/manual/en/class.domnode.php) $otherNode )

##### public void [isSupported](http://php.net/manual/en/domnode.issupported.php) ( string $feature , string $version )

##### public void [lookupNamespaceURI](http://php.net/manual/en/domnode.lookupnamespaceuri.php) ( string|null $prefix )

##### public void [lookupPrefix](http://php.net/manual/en/domnode.lookupprefix.php) ( string $namespace )

##### public void [normalize](http://php.net/manual/en/domnode.normalize.php) ( void )

##### public void [removeChild](http://php.net/manual/en/domnode.removechild.php) ( [DOMNode](http://php.net/manual/en/class.domnode.php) $child )

##### public void [replaceChild](http://php.net/manual/en/domnode.replacechild.php) ( [DOMNode](http://php.net/manual/en/class.domnode.php) $node , [DOMNode](http://php.net/manual/en/class.domnode.php) $child )

## Details

Location: ~/src/SoftCreatR/HTML5DOMDocument/HTML5DOMElement.php

---

[back to index](index.md)

