# HTML5DOMDocument

HTML5DOMDocument extends the native [DOMDocument](http://php.net/manual/en/class.domdocument.php) class in PHP. It fixes some long-standing issues and introduces additional, modern functionality for handling HTML5 documents.

[![Latest Stable Version](https://poser.pugx.org/softcreatr/html5-dom-document-php/v/stable)](https://packagist.org/packages/softcreatr/html5-dom-document-php)
[![License](https://poser.pugx.org/softcreatr/html5-dom-document-php/license)](https://packagist.org/packages/softcreatr/html5-dom-document-php)

## Why Use HTML5DOMDocument?

- **Preserves HTML entities** that the native DOMDocument does not handle properly.
- **Preserves void tags** like `<input>` or `<br>`, which DOMDocument tends to mishandle.
- Allows **inserting HTML code** correctly into the document, ensuring that head and body elements are placed in their respective sections.
- Enables **CSS-style selectors** for querying the DOM:
    - Supported selectors include `*`, `tagname`, `tagname#id`, `#id`, `tagname.classname`, `.classname`, multiple class selectors, attribute selectors, and complex selectors like `div p`, `div > p`, `div + p`, `p ~ ul`, and `div, p`.
- **Element manipulation** with:
    - `element->classList` for manipulating classes
    - `element->innerHTML` for working with the contents of an element
    - `element->outerHTML` for manipulating an entire element as a string
- **Efficient handling of duplicate elements and IDs** with advanced HTML insertion and validation.

## Installation via Composer

Install via [Composer](https://getcomposer.org/) with the following command:

```shell
composer require "softcreatr/html5-dom-document-php:3.*"
```

## Features & Improvements

- **ClassList Support**: The library adds support for manipulating classes of elements through the `classList` property, making it easier to work with CSS class attributes.
- **Enhanced Query Selectors**: Use advanced CSS-like selectors for querying elements from the DOM, such as:
    - `div > p` (direct child)
    - `div + p` (adjacent sibling)
    - `p ~ ul` (general sibling)
    - `[attribute]`, `[attribute=value]`, etc.
- **HTML Insertion**: It allows inserting fragments of HTML into the document, intelligently placing them in the correct location (e.g., scripts into `<head>`, content into `<body>`).
- **Custom Insert Targets**: Define custom targets within the document for precise HTML insertion.

## Documentation

Comprehensive [documentation](https://github.com/softcreatr/html5-dom-document-php/blob/main/docs/markdown/index.md) is available in the repository.

## Examples

### Basic Usage

Use HTML5DOMDocument just like the native DOMDocument:

```php
<?php
require 'vendor/autoload.php';

$dom = new \SoftCreatR\HTML5DOMDocument\HTML5DOMDocument();
$dom->loadHTML('<!DOCTYPE html><html><body>Hello World!</body></html>');
echo $dom->saveHTML();
```

### Querying with CSS Selectors

```php
$dom = new \SoftCreatR\HTML5DOMDocument\HTML5DOMDocument();
$dom->loadHTML('<!DOCTYPE html><html><body><h1>Hello</h1><div class="content">This is some text</div></body></html>');

echo $dom->querySelector('h1')->innerHTML;  // Outputs: Hello
echo $dom->querySelector('.content')->outerHTML;  // Outputs: <div class="content">This is some text</div>
```

### Inserting HTML Code

```php
$dom = new \SoftCreatR\HTML5DOMDocument\HTML5DOMDocument();
$dom->loadHTML('
    <!DOCTYPE html>
    <html>
        <head><style>body { color: red; }</style></head>
        <body><h1>Hello</h1></body>
    </html>
');

$dom->insertHTML('
    <html>
        <head><script>alert("JS Script")</script></head>
        <body><div>This is some text</div></body>
    </html>
');

echo $dom->saveHTML();
// Properly merges new elements into the existing head and body.
```

### Manipulating Class Attribute

```php
$dom = new \SoftCreatR\HTML5DOMDocument\HTML5DOMDocument();
$dom->loadHTML('<div class="class1"></div>');

$div = $dom->querySelector('div');
$div->classList->add('class2');
$div->classList->remove('class1');

echo $div->getAttribute('class');  // Outputs: "class2"
```

### Custom Insert Targets

```php
$dom = new \SoftCreatR\HTML5DOMDocument\HTML5DOMDocument();
$dom->loadHTML('<html><body><div id="main"></div></body></html>');

$mainDiv = $dom->querySelector('#main');
$mainDiv->appendChild($dom->createInsertTarget('name1'));

$dom->insertHTML('<div id="new-content">New content</div>', 'name1');
echo $dom->saveHTML();
```

## License

This project is licensed under the MIT License. See the [license file](https://github.com/softcreatr/html5-dom-document-php/blob/main/LICENSE) for more details.

## Contributing

Contributions are welcome! Feel free to open issues or submit pull requests to improve this library. Let's collaborate to make it even better.

## Authors

- Original library by [Ivo Petkov](https://github.com/ivopetkov/).
- V3 and ongoing maintenance by [Sascha Greuel](https://github.com/softcreatr/).
