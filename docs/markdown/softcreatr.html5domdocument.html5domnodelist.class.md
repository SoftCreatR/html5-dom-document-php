# SoftCreatR\HTML5DOMDocument\HTML5DOMNodeList

Represents a list of DOM nodes.

```php
SoftCreatR\HTML5DOMDocument\HTML5DOMNodeList extends ArrayObject implements Countable, Serializable, ArrayAccess, Traversable, IteratorAggregate {

	/* Properties */
	public readonly int $length

	/* Methods */
	public HTML5DOMElement|null item ( int $index )

}
```

## Extends

##### [ArrayObject](http://php.net/manual/en/class.arrayobject.php)

## Implements

##### [Countable](http://php.net/manual/en/class.countable.php)

##### [Serializable](http://php.net/manual/en/class.serializable.php)

##### [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php)

##### [Traversable](http://php.net/manual/en/class.traversable.php)

##### [IteratorAggregate](http://php.net/manual/en/class.iteratoraggregate.php)

## Properties

##### public readonly int $length

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The list items count

## Methods

##### public HTML5DOMElement|null [item](softcreatr.html5domdocument.html5domnodelist.item.method.md) ( int $index )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns the item at the specified index.

### Inherited from [ArrayObject](http://php.net/manual/en/class.arrayobject.php)

##### public [__construct](http://php.net/manual/en/arrayobject.construct.php) ( [ object|array $array = [] [, int $flags = 0 [, string $iteratorClass = 'ArrayIterator' ]]] )

##### public void [append](http://php.net/manual/en/arrayobject.append.php) ( mixed $value )

##### public void [asort](http://php.net/manual/en/arrayobject.asort.php) ( [ int $flags = 0 ] )

##### public void [count](http://php.net/manual/en/arrayobject.count.php) ( void )

##### public void [exchangeArray](http://php.net/manual/en/arrayobject.exchangearray.php) ( object|array $array )

##### public void [getArrayCopy](http://php.net/manual/en/arrayobject.getarraycopy.php) ( void )

##### public void [getFlags](http://php.net/manual/en/arrayobject.getflags.php) ( void )

##### public void [getIterator](http://php.net/manual/en/arrayobject.getiterator.php) ( void )

##### public void [getIteratorClass](http://php.net/manual/en/arrayobject.getiteratorclass.php) ( void )

##### public void [ksort](http://php.net/manual/en/arrayobject.ksort.php) ( [ int $flags = 0 ] )

##### public void [natcasesort](http://php.net/manual/en/arrayobject.natcasesort.php) ( void )

##### public void [natsort](http://php.net/manual/en/arrayobject.natsort.php) ( void )

##### public void [serialize](http://php.net/manual/en/arrayobject.serialize.php) ( void )

##### public void [setFlags](http://php.net/manual/en/arrayobject.setflags.php) ( int $flags )

##### public void [setIteratorClass](http://php.net/manual/en/arrayobject.setiteratorclass.php) ( string $iteratorClass )

##### public void [uasort](http://php.net/manual/en/arrayobject.uasort.php) ( callable $callback )

##### public void [uksort](http://php.net/manual/en/arrayobject.uksort.php) ( callable $callback )

##### public void [unserialize](http://php.net/manual/en/arrayobject.unserialize.php) ( string $data )

## Details

Location: ~/src/SoftCreatR/HTML5DOMDocument/HTML5DOMNodeList.php

---

[back to index](index.md)

