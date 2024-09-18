<?php

/*
 * HTML5 DOMDocument PHP library (extends DOMDocument)
 * https://github.com/ivopetkov/html5-dom-document-php
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace SoftCreatR\Tests\HTML5DOMDocument;

use DOMDocument;
use DOMException;
use DOMXPath;
use Exception;
use PHPUnit\Framework\TestCase;
use SoftCreatR\HTML5DOMDocument\HTML5DOMDocument;
use SoftCreatR\HTML5DOMDocument\HTML5DOMElement;
use SoftCreatR\HTML5DOMDocument\HTML5DOMNodeList;

use const LIBXML_HTML_NODEFDTD;
use const LIBXML_HTML_NOIMPLIED;

class HTML5DOMDocumentTest extends TestCase
{
    /**
     * Test saving HTML content loaded from various sources and verify the output.
     *
     * @throws Exception
     */
    public function testSaveHTML(): void
    {
        $testSource = function (string $source, string $expectedSource): void {
            $dom = new HTML5DOMDocument();
            $dom->loadHTML($source);
            self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));
        };

        $bodyContent = '<div>hello</div>';

        // Case 1: Simple HTML structure
        $source = '<!DOCTYPE html><html><body>' . $bodyContent . '</body></html>';
        $testSource($source, $source);

        // Case 2: HTML with empty head tag
        $source = '<!DOCTYPE html><html><head></head><body>' . $bodyContent . '</body></html>';
        $testSource($source, $source);

        // Case 3: HTML with custom attributes
        $source = '<!DOCTYPE html><html custom-attribute="1"><head custom-attribute="2"></head><body custom-attribute="3">' . $bodyContent . '</body></html>';
        $testSource($source, $source);

        // Case 4: Empty DOM, nothing loaded
        $dom = new HTML5DOMDocument();
        self::assertSame('', $dom->saveHTML());
    }

    /**
     * Test omitted elements such as <html>, <head>, and <body>.
     *
     * This ensures that the library can handle omitted elements and generate correct HTML output.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testOmittedElements(): void
    {
        $testSource = function (string $source, string $expectedSource): void {
            $dom = new HTML5DOMDocument();
            $dom->loadHTML($source);
            self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));
        };

        $bodyContent = '<div>hello</div>';

        // Test body content with various levels of omitted elements
        $expectedSource = '<!DOCTYPE html><html><body>' . $bodyContent . '</body></html>';
        $testSource('<!DOCTYPE html><html><body>' . $bodyContent . '</body></html>', $expectedSource);
        $testSource('<html><body>' . $bodyContent . '</body></html>', $expectedSource);
        $testSource('<body>' . $bodyContent . '</body>', $expectedSource);
        $testSource($bodyContent, $expectedSource);

        $headContent = '<script>alert(1);</script>';

        // Test head content with various levels of omitted elements
        $expectedSource = '<!DOCTYPE html><html><head>' . $headContent . '</head></html>';
        $testSource('<!DOCTYPE html><html><head>' . $headContent . '</head></html>', $expectedSource);
        $testSource('<html><head>' . $headContent . '</head></html>', $expectedSource);
        $testSource('<head>' . $headContent . '</head>', $expectedSource);
    }

    /**
     * Test handling of UTF-8 encoded content in the DOM.
     *
     * This ensures that various character sets (including Cyrillic and Chinese) are correctly processed and output.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testUTF(): void
    {
        $bodyContent = '<div>hello</div>'
            . '<div>здравей</div>' // Cyrillic
            . '<div>你好</div>'; // Chinese
        $expectedSource = '<!DOCTYPE html><html><body>' . $bodyContent . '</body></html>';

        $dom = new HTML5DOMDocument();
        $dom->loadHTML($bodyContent);

        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Test handling of non-breaking spaces and whitespace in the DOM.
     *
     * This test checks for accurate handling of &nbsp; and whitespace characters.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testNbspAndWhiteSpace(): void
    {
        $bodyContent = '<div> &nbsp; &nbsp; &nbsp; </div>'
            . '<div> &nbsp;&nbsp;&nbsp; </div>'
            . '<div> &nbsp; <span>&nbsp;</span></div>'
            . '<div>text1 text2 </div>';

        $expectedSource = '<!DOCTYPE html><html><body>' . $bodyContent . '</body></html>';
        $dom = new HTML5DOMDocument();
        $dom->loadHTML($bodyContent);
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Additional test for whitespace issues in forms and labels
        $bodyContentWithLabels = '<label>Label 1</label><input><label>Label 2</label><input>';
        $expectedSourceWithLabels = '<!DOCTYPE html><html><body>' . $bodyContentWithLabels . '</body></html>';
        $dom->loadHTML($bodyContentWithLabels);
        self::assertEquals($expectedSourceWithLabels, $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Test the handling of HTML entities in both element attributes and text nodes.
     *
     * Ensures that the library correctly preserves, decodes, and re-encodes HTML entities.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testHtmlEntities(): void
    {
        $attributeContent = '&quot;&#8595; &amp;';
        $bodyContent = '<div data-value="' . $attributeContent . '"> &#8595; &amp; &quot; &Acirc; &rsaquo;&rsaquo;&Acirc; </div>';
        $expectedSource = '<!DOCTYPE html><html><body>' . $bodyContent . '</body></html>';

        $dom = new HTML5DOMDocument();
        $dom->loadHTML($bodyContent);

        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Check that the attribute value is correctly decoded
        $decodedAttributeContent = \html_entity_decode($attributeContent);
        self::assertSame($decodedAttributeContent, $dom->querySelector('div')->getAttribute('data-value'));

        // Set the attribute again and check if the original encoding is preserved
        $dom->querySelector('div')->setAttribute('data-value', $attributeContent);
        self::assertSame($attributeContent, $dom->querySelector('div')->getAttribute('data-value'));
    }

    /**
     * Test inserting HTML content at different positions within the DOM and verifying the resulting structure.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testInsertHTML(): void
    {
        // Test inserting beforeBodyEnd
        $source = '<!DOCTYPE html><html><body>text1</body></html>';
        $dom = new HTML5DOMDocument();
        $dom->loadHTML($source);
        $dom->insertHTML('<html><head><meta custom="value"></head><body><div>text2</div><div>text3</div></body></html>');
        $expectedSource = '<!DOCTYPE html><html><head><meta custom="value"></head><body>text1<div>text2</div><div>text3</div></body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting afterBodyBegin
        $dom->loadHTML($source);
        $dom->insertHTML('<html><head><meta custom="value"></head><body><div>text2</div><div>text3</div></body></html>', 'afterBodyBegin');
        $expectedSource = '<!DOCTYPE html><html><head><meta custom="value"></head><body><div>text2</div><div>text3</div>text1</body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting content into empty elements
        $source = '<!DOCTYPE html><html><body></body></html>';
        $dom->loadHTML($source);
        $dom->insertHTML('<html><head><meta custom="value"></head><body><div>text1</div><div>text2</div></body></html>', 'afterBodyBegin');
        $expectedSource = '<!DOCTYPE html><html><head><meta custom="value"></head><body><div>text1</div><div>text2</div></body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting content in a specific target
        $source = '<!DOCTYPE html><html><body><div></div><div></div><div></div></body></html>';
        $dom->loadHTML($source);
        $secondDiv = $dom->querySelectorAll('div')->item(1);
        $secondDiv->appendChild($dom->createInsertTarget('name1'));
        $dom->insertHTML('<html><head><meta custom="value"></head><body><div>text1</div><div>text2</div></body></html>', 'name1');
        $expectedSource = '<!DOCTYPE html><html><head><meta custom="value"></head><body><div></div><div><div>text1</div><div>text2</div></div><div></div></body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting into an empty DOM with no source
        $dom = new HTML5DOMDocument();
        $insertTarget = $dom->createInsertTarget('name1');
        $dom->insertHTML('<body></body>');
        $dom->querySelector('body')->appendChild($insertTarget);
        $dom->insertHTML('value1', 'name1');
        $expectedSource = '<!DOCTYPE html><html><body>value1</body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting content with duplicate IDs
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<div>1</div><div id="value1">2</div><div>3</div>');
        $dom->insertHTML('<div id="value1">5</div><div>4</div>');
        $expectedSource = '<!DOCTYPE html><html><body><div>1</div><div id="value1">2</div><div>3</div><div id="value1">5</div><div>4</div></body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test handling of an empty source
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('');
        $dom->insertHTML('<div>text1</div>');
        $expectedSource = '<!DOCTYPE html><html><body><div>text1</div></body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test handling of no source at all
        $dom = new HTML5DOMDocument();
        $dom->insertHTML('<div>text1</div>');
        $expectedSource = '<!DOCTYPE html><html><body><div>text1</div></body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting a script tag
        $dom = new HTML5DOMDocument();
        $dom->insertHTML('<script>alert(1);</script>');
        $expectedSource = '<!DOCTYPE html><html><body><script>alert(1);</script></body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting a custom tag
        $dom = new HTML5DOMDocument();
        $dom->insertHTML('<component></component>');
        $expectedSource = '<!DOCTYPE html><html><body><component></component></body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting an empty string
        $dom = new HTML5DOMDocument();
        $dom->insertHTML('');
        $expectedSource = '<!DOCTYPE html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting an HTML tag with attributes
        $dom = new HTML5DOMDocument();
        $dom->insertHTML('<html data-var1="value1"></html>');
        $expectedSource = '<!DOCTYPE html><html data-var1="value1"></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting a head tag with attributes
        $dom = new HTML5DOMDocument();
        $dom->insertHTML('<head data-var1="value1"></head>');
        $expectedSource = '<!DOCTYPE html><html><head data-var1="value1"></head></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting a body tag with attributes
        $dom = new HTML5DOMDocument();
        $dom->insertHTML('<body data-var1="value1"></body>');
        $expectedSource = '<!DOCTYPE html><html><body data-var1="value1"></body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting empty content into an insert target
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<body></body>');
        $insertTarget = $dom->createInsertTarget('name1');
        $dom->querySelector('body')->appendChild($insertTarget);
        $dom->insertHTML('', 'name1');
        $expectedSource = '<!DOCTYPE html><html><body></body></html>';
        self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Test handling of empty HTML documents and ensuring correct minimal structure is generated.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testEmpty(): void
    {
        $testSource = function (string $source, string $expectedSource): void {
            $dom = new HTML5DOMDocument();
            $dom->loadHTML($source);
            self::assertEquals($expectedSource, $this->removeNewLines($dom->saveHTML()));
        };

        // Testing different variations of empty HTML structures
        $source = '<!DOCTYPE html><html><head></head><body></body></html>';
        $testSource($source, $source);

        $source = '<!DOCTYPE html><html><body></body></html>';
        $testSource($source, $source);

        $source = '<!DOCTYPE html><html><head></head></html>';
        $testSource($source, $source);

        $source = '<!DOCTYPE html><html></html>';
        $testSource($source, $source);

        $source = '<!DOCTYPE html>';
        $testSource($source, $source);

        // Test with an empty source, expecting just the doctype
        $testSource('', '<!DOCTYPE html>');
    }

    /**
     * Test basic querySelector and querySelectorAll functionality to fetch elements by CSS selectors.
     *
     * @throws Exception
     */
    public function testQuerySelector(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body>'
            . '<h1>text0</h1>'
            . '<div id="text1" class="class1">text1</div>'
            . '<div>text2</div>'
            . '<div>'
            . '<div empty-attribute class="text3 class1">text3</div>'
            . '</div>'
            . '<my-custom-element class="text5 class1">text5</my-custom-element>'
            . '<span id="text4" class="class1 class2">text4</span>'
            . '</body></html>');

        // Basic querySelector tests
        self::assertSame('text1', $dom->querySelector('#text1')->innerHTML);

        // QuerySelectorAll tests
        self::assertCount(9, $dom->querySelectorAll('*')); // html + body + 1 h1 + 4 divs + 1 custom element + 1 span
        self::assertSame('text0', $dom->querySelectorAll('h1')->item(0)->innerHTML);
        self::assertCount(4, $dom->querySelectorAll('div')); // 4 divs
        self::assertCount(1, $dom->querySelectorAll('#text1'));
        self::assertSame('text1', $dom->querySelectorAll('#text1')->item(0)->innerHTML);
        self::assertCount(1, $dom->querySelectorAll('.text3'));
        self::assertSame('text3', $dom->querySelectorAll('.text3')->item(0)->innerHTML);
        self::assertSame('text1', $dom->querySelectorAll('div#text1')->item(0)->innerHTML);
        self::assertSame('text4', $dom->querySelectorAll('span#text4')->item(0)->innerHTML);
        self::assertSame('text4', $dom->querySelectorAll('[id="text4"]')->item(0)->innerHTML);
        self::assertSame('text4', $dom->querySelectorAll('span[id="text4"]')->item(0)->innerHTML);
        self::assertSame('text1', $dom->querySelectorAll('[id]')->item(0)->innerHTML);
        self::assertCount(2, $dom->querySelectorAll('[id]'));
        self::assertCount(1, $dom->querySelectorAll('[empty-attribute]'));
        self::assertCount(0, $dom->querySelectorAll('[missing-attribute]'));
        self::assertSame('text4', $dom->querySelectorAll('span[id]')->item(0)->innerHTML);
        self::assertCount(0, $dom->querySelectorAll('span[data-other]'));
        self::assertCount(0, $dom->querySelectorAll('div#text4'));
        self::assertCount(2, $dom->querySelectorAll('div.class1'));
        self::assertCount(4, $dom->querySelectorAll('.class1'));
        self::assertCount(1, $dom->querySelectorAll('.class1.class2'));
        self::assertCount(1, $dom->querySelectorAll('.class2.class1'));
        self::assertCount(0, $dom->querySelectorAll('div.class2'));
        self::assertCount(1, $dom->querySelectorAll('span.class2'));
        self::assertCount(1, $dom->querySelectorAll('my-custom-element'));
        self::assertCount(1, $dom->querySelectorAll('my-custom-element.text5'));
        self::assertSame('text5', $dom->querySelectorAll('my-custom-element.text5')->item(0)->innerHTML);

        // Querying unknown elements
        self::assertCount(0, $dom->querySelectorAll('unknown'));
        self::assertNull($dom->querySelectorAll('unknown')->item(0));
        self::assertCount(0, $dom->querySelectorAll('#unknown'));
        self::assertNull($dom->querySelectorAll('#unknown')->item(0));
        self::assertCount(0, $dom->querySelectorAll('.unknown'));
        self::assertNull($dom->querySelectorAll('.unknown')->item(0));
    }

    /**
     * Test querySelector and querySelectorAll within a specific element.
     *
     * This ensures that the querying is scoped to the element.
     *
     * @throws Exception
     */
    public function testElementQuerySelector(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><head>'
            . '<link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">'
            . '<link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32"></head>'
            . '<body><div id="container">'
            . '<div id="text1" class="class1">text1</div>'
            . '<div>text2</div>'
            . '<div>'
            . '<div class="class3 class1">text3</div>'
            . '</div>'
            . '<my-custom-element class="class5 class1">text5</my-custom-element>'
            . '<span id="text4" class="class1 class2">text4</span>'
            . '</div></body></html>');

        // Basic querySelector tests within the #container
        self::assertSame('text1', $dom->querySelector('#container')->querySelector('#text1')->innerHTML);

        // QuerySelectorAll tests within the #container
        self::assertCount(6, $dom->querySelector('#container')->querySelectorAll('*')); // 4 divs + 1 custom element + 1 span
        self::assertCount(4, $dom->querySelector('#container')->querySelectorAll('div')); // 4 divs
        self::assertCount(1, $dom->querySelector('#container')->querySelectorAll('#text1'));
        self::assertSame('text1', $dom->querySelector('#container')->querySelectorAll('#text1')->item(0)->innerHTML);
        self::assertCount(1, $dom->querySelector('#container')->querySelectorAll('.class3'));
        self::assertSame('text3', $dom->querySelector('#container')->querySelectorAll('.class3')->item(0)->innerHTML);

        // Attribute-based selectors
        self::assertCount(1, $dom->querySelector('#container')->querySelectorAll('[class~="class3"]'));
        self::assertSame('text3', $dom->querySelector('#container')->querySelectorAll('[class~="class3"]')->item(0)->innerHTML);
        self::assertCount(1, $dom->querySelector('#container')->querySelectorAll('[class|="class1"]'));
        self::assertSame('text1', $dom->querySelector('#container')->querySelectorAll('[class|="class1"]')->item(0)->innerHTML);
        self::assertCount(1, $dom->querySelector('#container')->querySelectorAll('[class^="class3"]'));
        self::assertSame('text3', $dom->querySelector('#container')->querySelectorAll('[class^="class3"]')->item(0)->innerHTML);
        self::assertCount(1, $dom->querySelector('#container')->querySelectorAll('[class$="class2"]'));
        self::assertSame('text4', $dom->querySelector('#container')->querySelectorAll('[class$="class2"]')->item(0)->innerHTML);
        self::assertCount(1, $dom->querySelector('#container')->querySelectorAll('[class*="ss3"]'));
        self::assertSame('text3', $dom->querySelector('#container')->querySelectorAll('[class*="ss3"]')->item(0)->innerHTML);

        // Complex selector examples
        self::assertSame('text1', $dom->querySelector('#container')->querySelectorAll('div#text1')->item(0)->innerHTML);
        self::assertSame('text4', $dom->querySelector('#container')->querySelectorAll('span#text4')->item(0)->innerHTML);
        self::assertSame('text4', $dom->querySelector('#container')->querySelectorAll('[id="text4"]')->item(0)->innerHTML);
        self::assertSame('text4', $dom->querySelector('#container')->querySelectorAll('span[id="text4"]')->item(0)->innerHTML);

        // Tests for non-existent elements
        self::assertCount(0, $dom->querySelector('#container')->querySelectorAll('div#text4'));
        self::assertCount(2, $dom->querySelector('#container')->querySelectorAll('div.class1'));
        self::assertCount(4, $dom->querySelector('#container')->querySelectorAll('.class1'));
        self::assertCount(0, $dom->querySelector('#container')->querySelectorAll('div.class2'));
        self::assertCount(1, $dom->querySelector('#container')->querySelectorAll('span.class2'));
        self::assertCount(1, $dom->querySelector('#container')->querySelectorAll('my-custom-element'));
        self::assertCount(1, $dom->querySelector('#container')->querySelectorAll('my-custom-element.class5'));
        self::assertSame('text5', $dom->querySelector('#container')->querySelectorAll('my-custom-element.class5')->item(0)->innerHTML);

        // Querying unknown elements
        self::assertCount(0, $dom->querySelector('#container')->querySelectorAll('unknown'));
        self::assertNull($dom->querySelector('#container')->querySelectorAll('unknown')->item(0));
        self::assertCount(0, $dom->querySelector('#container')->querySelectorAll('#unknown'));
        self::assertNull($dom->querySelector('#container')->querySelectorAll('#unknown')->item(0));
        self::assertCount(0, $dom->querySelector('#container')->querySelectorAll('.unknown'));
        self::assertNull($dom->querySelector('#container')->querySelectorAll('.unknown')->item(0));

        // Tests for link elements in the head
        self::assertSame('/favicon-16x16.png', $dom->querySelectorAll('link[rel="icon"]')->item(0)->getAttribute('href'));
        self::assertSame('/favicon-32x32.png', $dom->querySelectorAll('link[rel="icon"]')->item(1)->getAttribute('href'));
        self::assertSame('/favicon-16x16.png', $dom->querySelectorAll('link[rel="icon"][sizes="16x16"]')->item(0)->getAttribute('href'));
        self::assertNull($dom->querySelectorAll('link[rel="icon"][sizes="16x16"]')->item(1));
    }

    /**
     * Test case-insensitivity in querySelector and querySelectorAll when querying elements by tag name, class, or ID.
     *
     * @throws Exception
     */
    public function testElementQuerySelectorCaseSensitivity(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body>'
            . '<dIV class="claSS1" id="elemeNT1">'
            . '<div class="claSS2 claSS3">'
            . '<spAN>text1</span>'
            . '<A>text2</a>'
            . '</div>'
            . '<a>text3</a>'
            . '<a>text4</a>'
            . '</div>'
            . '</body></html>');

        // Case-insensitive tag names
        self::assertSame('<div class="claSS2 claSS3"><span>text1</span><a>text2</a></div><a>text3</a><a>text4</a>', $dom->querySelector('div')->innerHTML);
        self::assertSame('<div class="claSS2 claSS3"><span>text1</span><a>text2</a></div><a>text3</a><a>text4</a>', $dom->querySelector('Div')->innerHTML);
        self::assertSame('text1', $dom->querySelector('span')->innerHTML);
        self::assertSame('text1', $dom->querySelector('Span')->innerHTML);

        // Case-sensitive class attribute selectors
        self::assertNull($dom->querySelector('div[class="class1"]'));
        self::assertSame('<div class="claSS2 claSS3"><span>text1</span><a>text2</a></div><a>text3</a><a>text4</a>', $dom->querySelector('div[class="claSS1"]')->innerHTML);
        self::assertSame('<div class="claSS2 claSS3"><span>text1</span><a>text2</a></div><a>text3</a><a>text4</a>', $dom->querySelector('Div[Class="claSS1"]')->innerHTML);

        // Case-sensitive ID attribute selectors
        self::assertNull($dom->querySelector('div#element1'));
        self::assertSame('<div class="claSS2 claSS3"><span>text1</span><a>text2</a></div><a>text3</a><a>text4</a>', $dom->querySelector('div#elemeNT1')->innerHTML);
        self::assertNull($dom->querySelector('Div#element1'));
        self::assertSame('<div class="claSS2 claSS3"><span>text1</span><a>text2</a></div><a>text3</a><a>text4</a>', $dom->querySelector('Div#elemeNT1')->innerHTML);
        self::assertNull($dom->querySelector('#element1'));
        self::assertSame('<div class="claSS2 claSS3"><span>text1</span><a>text2</a></div><a>text3</a><a>text4</a>', $dom->querySelector('#elemeNT1')->innerHTML);

        // Case-sensitive class name selectors
        self::assertNull($dom->querySelector('div.class2.class3'));
        self::assertSame('<span>text1</span><a>text2</a>', $dom->querySelector('div.claSS2.claSS3')->innerHTML);
        self::assertNull($dom->querySelector('.class2.class3'));
        self::assertSame('<span>text1</span><a>text2</a>', $dom->querySelector('.claSS2.claSS3')->innerHTML);
    }

    /**
     * Test complex CSS query selectors like descendant, sibling, and attribute selectors.
     *
     * @throws Exception
     */
    public function testComplexQuerySelectors(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body>'
            . '<span>text1</span>'
            . '<span>text2</span>'
            . '<span>text3</span>'
            . '<div><span>text4</span></div>'
            . '<div id="id,1">text5</div>'
            . '<a href="#">text6</a>'
            . '<div><a href="#">text7</a></div>'
            . '</body></html>');

        // Testing multiple selectors: 'span' and 'div'
        self::assertCount(7, $dom->querySelectorAll('span, div')); // 4 spans + 3 divs

        // Testing 'span' and an attribute selector with id containing a comma
        self::assertCount(5, $dom->querySelectorAll('span, [id="id,1"]')); // 4 spans + 1 div

        // Testing 'div' and an attribute selector with id containing a comma
        self::assertCount(3, $dom->querySelectorAll('div, [id="id,1"]')); // 3 divs

        // Descendant selector: divs inside body
        self::assertCount(3, $dom->querySelectorAll('body div'));

        // Descendant selector: links inside body
        self::assertCount(2, $dom->querySelectorAll('body a'));

        // Child combinator: direct children of body
        self::assertCount(1, $dom->querySelectorAll('body > a'));
        self::assertSame('text6', $dom->querySelector('body > a')->innerHTML);

        // Child combinator: direct children of div
        self::assertCount(1, $dom->querySelectorAll('div > a'));
        self::assertSame('text7', $dom->querySelector('div > a')->innerHTML);

        // Adjacent sibling combinator: span followed by span
        self::assertCount(2, $dom->querySelectorAll('span + span'));
        self::assertSame('text2', $dom->querySelectorAll('span + span')[0]->innerHTML);
        self::assertSame('text3', $dom->querySelectorAll('span + span')[1]->innerHTML);

        // General sibling combinator: divs that follow a span
        self::assertCount(3, $dom->querySelectorAll('span ~ div'));
    }

    /**
     * Test query selectors for elements matching multiple classes or conditions (greedy selection).
     *
     * @throws Exception
     */
    public function testComplexQuerySelectors2(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<body>'
            . '<div class="a1">1</div>'
            . '<div class="a2">2</div>'
            . '<div class="a3">3</div>'
            . '</body>');

        // Testing greedy selection for multiple classes
        $elements = $dom->querySelectorAll('.a1, .a2, .a3');
        self::assertCount(3, $elements);
    }

    /**
     * Test complex CSS query selectors with child combinators using class and ID selectors.
     *
     * @throws Exception
     */
    public function testComplexQuerySelectors3(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body>'
            . '<div class="class1" id="element1">'
            . '<div><span>text1</span><a>text2</a></div>'
            . '<a>text3</a><a>text4</a>'
            . '</div></body></html>');

        // Testing child combinators with class and ID selectors
        self::assertSame('text2', $dom->querySelector('div.class1 > div > a')->innerHTML);
        self::assertSame('text3', $dom->querySelector('div.class1 > a')->innerHTML);
        self::assertSame('text2', $dom->querySelector('div[class="class1"] > div > a')->innerHTML);
        self::assertSame('text3', $dom->querySelector('div[class="class1"] > a')->innerHTML);
        self::assertSame('text2', $dom->querySelector('div#element1 > div > a')->innerHTML);
        self::assertSame('text3', $dom->querySelector('div#element1 > a')->innerHTML);
    }

    /**
     * Test setting and getting innerHTML of elements and verify that it works as expected.
     *
     * @throws Exception
     */
    public function testInnerHTML(): void
    {
        // Test innerHTML retrieval
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body>'
            . '<div>text1</div>'
            . '</body></html>');

        self::assertSame('<div>text1</div>', $dom->querySelector('body')->innerHTML);

        // Test setting innerHTML and saving the result
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<div>text1</div>');
        $element = $dom->querySelector('div');
        $element->innerHTML = 'text2';
        self::assertSame('<!DOCTYPE html><html><body><div>text2</div></body></html>', $this->removeNewLines($dom->saveHTML()));

        // Test setting nested innerHTML
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<div>text1</div>');
        $element = $dom->querySelector('div');
        $element->innerHTML = '<div>text1<div>text2</div></div>';
        self::assertSame('<!DOCTYPE html><html><body><div><div>text1<div>text2</div></div></div></body></html>', $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Test outerHTML property of elements and its manipulation.
     *
     * This test checks the ability to retrieve and manipulate the outer HTML of elements in the DOM.
     *
     * @throws Exception
     */
    public function testOuterHTML(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body>'
            . '<div>text1</div><span title="hi"></span><br/>'
            . '</body></html>');

        // Test outerHTML retrieval for various elements
        self::assertSame('<div>text1</div>', $dom->querySelector('div')->outerHTML);
        self::assertSame('<span title="hi"></span>', $dom->querySelector('span')->outerHTML);
        self::assertSame('<br/>', $dom->querySelector('br')->outerHTML);

        // Test outerHTML manipulation by replacing the outer HTML content
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<div>text1</div>');
        $element = $dom->querySelector('div');
        $element->outerHTML = 'text2';
        self::assertSame('<!DOCTYPE html><html><body>text2</body></html>', $this->removeNewLines($dom->saveHTML()));

        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<div>text1</div>');
        $element = $dom->querySelector('div');
        $element->outerHTML = '<div>text2<div>text3</div></div>';
        self::assertSame('<!DOCTYPE html><html><body><div>text2<div>text3</div></div></body></html>', $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Test retrieval and manipulation of element attributes, including special characters.
     *
     * @throws Exception
     */
    public function testGetAttributes(): void
    {
        $dataAttributeValue = '&quot;<>&*;';
        $expectedDataAttributeValue = '"<>&*;';
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body>'
            . '<div class="text1" data-value="' . $dataAttributeValue . '">text1</div>'
            . '</body></html>');

        // Test individual attribute retrieval
        self::assertSame('text1', $dom->querySelector('div')->getAttribute('class'));
        self::assertSame('', $dom->querySelector('div')->getAttribute('unknown'));
        self::assertSame($expectedDataAttributeValue, $dom->querySelector('div')->getAttribute('data-value'));

        // Test retrieving all attributes
        $attributes = $dom->querySelector('div')->getAttributes();
        self::assertCount(2, $attributes);
        self::assertSame('text1', $attributes['class']);
        self::assertSame($expectedDataAttributeValue, $attributes['data-value']);
    }

    /**
     * Test reading and saving HTML from/to files.
     *
     * This test case generates a temporary file, writes HTML content to it, modifies the content,
     * and verifies the result after saving the modified content back to the file.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testFiles(): void
    {
        // Generate a secure random filename
        $filename = \sys_get_temp_dir() . '/html5-dom-document-test-file-' . \bin2hex(\random_bytes(8));

        // Write HTML content to the temporary file
        \file_put_contents($filename, '<!DOCTYPE html><html><body>'
            . '<div>text1</div>'
            . '<div>text2</div>'
            . '</body></html>');

        // Load the HTML content from the file
        $dom = new HTML5DOMDocument();
        $dom->loadHTMLFile($filename);

        // Remove the first div element
        $dom->querySelector('body')->removeChild($dom->querySelector('div'));

        // Save the modified HTML content back to the file
        $dom->saveHTMLFile($filename);

        // Verify the file content matches the expected result
        $this->assertEquals('<!DOCTYPE html><html><body>'
            . '<div>text2</div>'
            . '</body></html>', $this->removeNewLines(\file_get_contents($filename)));
    }

    /**
     * Test handling of duplicate element IDs in the DOM.
     *
     * This test ensures that duplicate IDs can be processed when allowed by using
     * the HTML5DOMDocument::ALLOW_DUPLICATE_IDS flag.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testDuplicateIDs(): void
    {
        // Test with duplicate IDs allowed (using HTML5DOMDocument::ALLOW_DUPLICATE_IDS)
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<!DOCTYPE html><html><head>'
            . '<script id="script1">var script1=1;</script>'
            . '<script id="script1">var script1=2;</script>'
            . '</head><body></body></html>', HTML5DOMDocument::ALLOW_DUPLICATE_IDS);
        $expectedSource = '<!DOCTYPE html><html><head>'
            . '<script id="script1">var script1=1;</script>'
            . '<script id="script1">var script1=2;</script>'
            . '</head><body></body></html>';
        self::assertSame($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting HTML with duplicate IDs
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<!DOCTYPE html><html><head>'
            . '<script id="script1">var script1=1;</script>'
            . '<script id="script2">var script2=1;</script>'
            . '</head><body>'
            . 'hello<div id="text1">text1</div>'
            . '<div id="text2">text2</div>'
            . '<div id="text3">text3</div>'
            . '<div><span id="span1">hi1</span></div>'
            . '<span id="span2">hi2</span>'
            . '</body></html>');
        $dom->insertHTML('<!DOCTYPE html><html><head>'
            . '<script id="script0">var script0=1;</script>'
            . '<script id="script1">var script1=1;</script>'
            . '<script id="script3">var script3=1;</script>'
            . '</head><body>'
            . '<div id="text0">text0</div>'
            . '<div id="text2">text2</div>'
            . '<div id="text4">text4</div>'
            . '<span id="span1">hi11</span>'
            . '<div><span id="span1">hi22</span></div>'
            . '</body></html>');
        $expectedSource = '<!DOCTYPE html><html><head>'
            . '<script id="script1">var script1=1;</script>'
            . '<script id="script2">var script2=1;</script>'
            . '<script id="script0">var script0=1;</script>'
            . '<script id="script1">var script1=1;</script>'
            . '<script id="script3">var script3=1;</script>'
            . '</head><body>'
            . 'hello<div id="text1">text1</div>'
            . '<div id="text2">text2</div>'
            . '<div id="text3">text3</div>'
            . '<div><span id="span1">hi1</span></div>'
            . '<span id="span2">hi2</span>'
            . '<div id="text0">text0</div>'
            . '<div id="text2">text2</div>'
            . '<div id="text4">text4</div>'
            . '<span id="span1">hi11</span>'
            . '<div><span id="span1">hi22</span></div>'
            . '</body></html>';
        self::assertSame($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test nested duplicate IDs
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<!DOCTYPE html><html><body>'
            . '<div id="text1">text1</div>'
            . '</body></html>');
        $dom->insertHTML('<!DOCTYPE html><html><body>'
            . '<div>'
            . '<div id="text1">text1</div>'
            . '<div><div id="text1">text1</div></div>'
            . '<div id="text2">text2</div>'
            . '</div>'
            . '</body></html>');
        $expectedSource = '<!DOCTYPE html><html><body>'
            . '<div id="text1">text1</div>'
            . '<div>'
            . '<div id="text1">text1</div>'
            . '<div><div id="text1">text1</div></div>'
            . '<div id="text2">text2</div>'
            . '</div>'
            . '</body></html>';
        self::assertSame($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test inserting HTML with non-duplicate IDs
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<!DOCTYPE html><html><body>'
            . '<div id="text1">text1</div>'
            . '</body></html>');
        $dom->insertHTML('<!DOCTYPE html><html><body>'
            . '<div>'
            . '<div id="text2">text2</div>'
            . '<div id="text2">text2</div>'
            . '</div>'
            . '</body></html>');
        $expectedSource = '<!DOCTYPE html><html><body>'
            . '<div id="text1">text1</div>'
            . '<div>'
            . '<div id="text2">text2</div>'
            . '<div id="text2">text2</div>'
            . '</div>'
            . '</body></html>';
        self::assertSame($expectedSource, $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Test handling of duplicate <title>, <meta>, and <style> tags.
     *
     * This test covers optimization and fixing of multiple <title>, <meta>, and <style> tags
     * within the head section of the HTML document.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testDuplicateTags(): void
    {
        // Test for duplicate <title> tags
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<!DOCTYPE html><html><head>'
            . '<title>Title1</title>'
            . '</head></html>');
        $dom->insertHTML('<head>'
            . '<title>Title2</title>'
            . '</head>');
        $dom->modify(HTML5DOMDocument::FIX_MULTIPLE_TITLES);

        $expectedSource = '<!DOCTYPE html><html><head>'
            . '<title>Title2</title>'
            . '</head></html>';
        self::assertSame($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test for duplicate <meta> tags with optimization
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<!DOCTYPE html><html><head>'
            . '<meta charset="utf-8">'
            . '<meta content="index,follow" name="robots">'
            . '<meta content="html5" name="keywords">'
            . '<meta content="website" property="og:type">'
            . '</head></html>');
        $dom->insertHTML('<head>'
            . '<meta content="dom" name="keywords">'
            . '<meta charset="us-ascii">'
            . '<meta content="video.movie" property="og:type">'
            . '<title>Title1</title>'
            . '</head>');
        $dom->modify(
            HTML5DOMDocument::FIX_DUPLICATE_METATAGS
            | HTML5DOMDocument::OPTIMIZE_HEAD
        );

        $expectedSource = '<!DOCTYPE html><html><head>'
            . '<meta charset="us-ascii">'
            . '<title>Title1</title>'
            . '<meta content="index,follow" name="robots">'
            . '<meta content="dom" name="keywords">'
            . '<meta content="video.movie" property="og:type">'
            . '</head></html>';
        self::assertSame($expectedSource, $this->removeNewLines($dom->saveHTML()));

        // Test for duplicate <style> tags
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<!DOCTYPE html><html><head>'
            . '<style>body{color:red;}</style>'
            . '<style>body{color:red;}</style>'
            . '<style>div{color:blue;}</style>'
            . '<style>span{color:green;}</style>'
            . '<style>body{color:red;}</style>'
            . '</head></html>');
        $dom->insertHTML('<head>'
            . '<style>div{color:blue;}</style>'
            . '</head>');
        $dom->modify(HTML5DOMDocument::FIX_DUPLICATE_STYLES);

        $expectedSource = '<!DOCTYPE html><html><head>'
            . '<style>body{color:red;}</style>'
            . '<style>div{color:blue;}</style>'
            . '<style>span{color:green;}</style>'
            . '</head></html>';
        self::assertSame($expectedSource, $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Test saving specific HTML nodes rather than the entire document.
     *
     * This test ensures that the `saveHTML` function works correctly when applied to specific nodes
     * like <div>, <body>, or <script> within the document.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testSaveHTMLForNodes(): void
    {
        // Custom HTML tags make the default saveHTML function return more whitespaces
        $html = '<html><head><component><script src="url1"/><script src="url2"/></component></head><body><div><component><ul><li><a href="#">Link 1</a></li><li><a href="#">Link 2</a></li></ul></component></div>';

        $dom = new HTML5DOMDocument();
        $dom->loadHTML($html);

        // Test saving HTML for different nodes
        $expectedOutput = '<div><component><ul><li><a href="#">Link 1</a></li><li><a href="#">Link 2</a></li></ul></component></div>';
        self::assertSame($expectedOutput, $dom->saveHTML($dom->querySelector('div')));

        $expectedOutput = '<body><div><component><ul><li><a href="#">Link 1</a></li><li><a href="#">Link 2</a></li></ul></component></div></body>';
        self::assertSame($expectedOutput, $dom->saveHTML($dom->querySelector('div')->parentNode));

        $expectedOutput = '<html><head><component><script src="url1"></script><script src="url2"></script></component></head><body><div><component><ul><li><a href="#">Link 1</a></li><li><a href="#">Link 2</a></li></ul></component></div></body></html>';
        self::assertSame($expectedOutput, $dom->saveHTML($dom->querySelector('div')->parentNode->parentNode));

        $expectedOutput = '<!DOCTYPE html><html><head><component><script src="url1"></script><script src="url2"></script></component></head><body><div><component><ul><li><a href="#">Link 1</a></li><li><a href="#">Link 2</a></li></ul></component></div></body></html>';
        self::assertSame($expectedOutput, $this->removeNewLines($dom->saveHTML($dom->querySelector('div')->parentNode->parentNode->parentNode)));

        $expectedOutput = '<script src="url1"></script>';
        self::assertSame($expectedOutput, $dom->saveHTML($dom->querySelector('script')));

        $expectedOutput = '<component><script src="url1"></script><script src="url2"></script></component>';
        self::assertSame($expectedOutput, $dom->saveHTML($dom->querySelector('script')->parentNode));

        $expectedOutput = '<head><component><script src="url1"></script><script src="url2"></script></component></head>';
        self::assertSame($expectedOutput, $dom->saveHTML($dom->querySelector('script')->parentNode->parentNode));

        $expectedOutput = '<html><head><component><script src="url1"></script><script src="url2"></script></component></head><body><div><component><ul><li><a href="#">Link 1</a></li><li><a href="#">Link 2</a></li></ul></component></div></body></html>';
        self::assertSame($expectedOutput, $dom->saveHTML($dom->querySelector('script')->parentNode->parentNode->parentNode));

        $expectedOutput = '<!DOCTYPE html><html><head><component><script src="url1"></script><script src="url2"></script></component></head><body><div><component><ul><li><a href="#">Link 1</a></li><li><a href="#">Link 2</a></li></ul></component></div></body></html>';
        self::assertSame($expectedOutput, $this->removeNewLines($dom->saveHTML($dom->querySelector('script')->parentNode->parentNode->parentNode->parentNode)));
    }

    /**
     * Test handling multiple <head> and <body> tags in a document.
     *
     * This test ensures that duplicate <head>, <body>, and <title> elements are correctly merged or fixed.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testMultipleHeadAndBodyTags(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<!DOCTYPE html><html>'
            . '<head>'
            . '<title>Title1</title>'
            . '<meta charset="utf-8">'
            . '</head>'
            . '<head>'
            . '<title>Title2</title>'
            . '<meta content="index,follow" name="robots">'
            . '</head>'
            . '<body>'
            . 'Text1'
            . '<div>TextA</div>'
            . '</body>'
            . '<body>'
            . 'Text2'
            . '<div>TextB</div>'
            . '</body>'
            . '</html>');

        $dom->modify(
            HTML5DOMDocument::FIX_MULTIPLE_HEADS
            | HTML5DOMDocument::FIX_MULTIPLE_BODIES
            | HTML5DOMDocument::FIX_MULTIPLE_TITLES
        );

        $expectedSource = '<!DOCTYPE html><html>'
            . '<head>'
            . '<meta charset="utf-8">'
            . '<title>Title2</title>'
            . '<meta content="index,follow" name="robots">'
            . '</head>'
            . '<body>'
            . 'Text1'
            . '<div>TextA</div>'
            . 'Text2'
            . '<div>TextB</div>'
            . '</body>'
            . '</html>';
        self::assertSame($expectedSource, $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Test inserting HTML while copying attributes from existing elements.
     *
     * This test ensures that attributes from the existing <html>, <head>, and <body> tags are copied
     * and merged with the new attributes.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testInsertHTMLCopyAttributes(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<!DOCTYPE html>'
            . '<html data-html-custom-1="1">'
            . '<head data-head-custom-1="1"></head>'
            . '<body data-body-custom-1="1"></body>'
            . '</html>');
        $dom->insertHTML('<html data-html-custom-1="A" data-html-custom-2="B">'
            . '<head data-head-custom-1="A" data-head-custom-2="B"></head>'
            . '<body data-body-custom-1="A" data-body-custom-2="B"></body>'
            . '</html>');
        $expectedSource = '<!DOCTYPE html>'
            . '<html data-html-custom-1="A" data-html-custom-2="B">'
            . '<head data-head-custom-1="A" data-head-custom-2="B"></head>'
            . '<body data-body-custom-1="A" data-body-custom-2="B"></body>'
            . '</html>';
        self::assertSame($expectedSource, $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Test inserting multiple instances of HTML with insertHTMLMulti.
     *
     * This test ensures that the `insertHTMLMulti` method can insert multiple blocks of HTML
     * and that the results match manual insertions done through loops.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testInsertHTMLMulti(): void
    {
        $html = '';

        for ($i = 0; $i < 5; $i++) {
            $html .= '<div>';
            $html .= '<div id="id' . $i . '"></div>';
            $html .= '<div class="class' . $i . '"></div>';
            $html .= '<div></div>';
            $html .= '<div></div>';
            $html .= '<div></div>';
            $html .= '</div>';
        }

        $dom1 = new HTML5DOMDocument();
        $dom1->loadHTML('<body></body>');

        for ($i = 0; $i < 5; $i++) {
            $dom1->insertHTML($html);
        }

        $result1 = $dom1->saveHTML();

        $dom2 = new HTML5DOMDocument();
        $dom2->loadHTML('<body></body>');

        $data = [];

        for ($i = 0; $i < 5; $i++) {
            $data[] = ['source' => $html];
        }

        $dom2->insertHTMLMulti($data);
        $result2 = $dom2->saveHTML();

        self::assertSame($result1, $result2);
    }

    /**
     * Test invalid arguments and exception handling for missing properties.
     *
     * This test verifies that accessing non-existent properties of DOM elements throws the appropriate exceptions.
     *
     * @throws Exception
     */
    public function testInvalidArguments1(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<!DOCTYPE html><body></body></html>');
        $element = $dom->querySelector('body');
        $this->expectException(Exception::class);  // Expecting a general Exception
        /** @noinspection PhpUndefinedFieldInspection */
        $element->missing; // Accessing a non-existent property should throw an Exception
    }

    /**
     * Test invalid arguments and exception handling for setting missing properties.
     *
     * This test verifies that attempting to set non-existent properties of DOM elements throws the appropriate exceptions.
     *
     * @throws Exception
     */
    public function testInvalidArguments2(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<!DOCTYPE html><body></body></html>');
        $element = $dom->querySelector('body');
        $this->expectException(Exception::class);  // Expecting a general Exception
        /** @noinspection PhpUndefinedFieldInspection */
        $element->missing = 'true'; // Attempting to set a non-existent property should throw an Exception
    }

    /**
     * Test invalid arguments and exception handling for missing properties in a NodeList.
     *
     * This test verifies that accessing non-existent properties of a NodeList throws an Exception.
     */
    public function testInvalidArguments5(): void
    {
        $list = new HTML5DOMNodeList();
        $this->expectException(Exception::class); // Expecting an Exception when accessing a missing property
        $list->missing; // Accessing a non-existent property on a NodeList should throw an Exception
    }

    /**
     * Test the `contains` method of classList.
     *
     * This test ensures that the `classList->contains` method works correctly for detecting whether a class is present in an element.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListContains(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class=" c aaa b  c  "></body></html>');

        $html = $dom->querySelector('html');
        self::assertFalse($html->classList->contains('a')); // 'a' is not in the class list

        $body = $dom->querySelector('body');
        $classList = $body->classList;
        self::assertFalse($classList->contains('a')); // 'a' is not in the class list
        self::assertTrue($classList->contains('aaa')); // 'aaa' is in the class list
        self::assertTrue($classList->contains('b')); // 'b' is in the class list
        self::assertTrue($classList->contains('c')); // 'c' is in the class list (even if duplicated in the string)
        self::assertFalse($classList->contains('d')); // 'd' is not in the class list
    }

    /**
     * Test the `entries` method of classList.
     *
     * This test verifies that the `classList->entries` method iterates correctly over the unique class names of an element.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListEntries(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="  a   b c b a c"></body></html>');

        // Test for <html> element (no class attribute)
        $html = $dom->querySelector('html');
        $text = '';

        foreach ($html->classList->entries() as $class) {
            $text .= "[{$class}]";
        }

        self::assertSame('', $text); // No class attribute in <html>

        // Test for <body> element (with class attribute)
        $body = $dom->querySelector('body');
        $text = '';

        foreach ($body->classList->entries() as $class) {
            $text .= "[{$class}]";
        }

        self::assertSame('[a][b][c]', $text); // Class list should be unique and ordered
    }

    /**
     * Test the `item` method of classList.
     *
     * This test ensures that the `classList->item` method returns the correct class at the specified index.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListItem(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="  a   b c b a c"></body></html>');

        // Test for <html> element (no class attribute)
        $html = $dom->querySelector('html');
        self::assertNull($html->classList->item(0));
        self::assertNull($html->classList->item(1));

        // Test for <body> element (with class attribute)
        $body = $dom->querySelector('body');
        self::assertSame('a', $body->classList->item(0));
        self::assertSame('b', $body->classList->item(1));
        self::assertSame('c', $body->classList->item(2));
        self::assertNull($body->classList->item(3)); // Index out of range
    }

    /**
     * Test the `add` method of classList.
     *
     * This test verifies that classes are correctly added to an element and that duplicates are avoided.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListAdd(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="  a   b c b a c"></body></html>');

        // Test adding class to <html> element (which has no classes)
        $html = $dom->querySelector('html');
        $html->classList->add('abc');
        self::assertSame('abc', $html->getAttribute('class'));

        // Test adding classes to <body> element (with existing classes)
        $body = $dom->querySelector('body');
        $body->classList->add('a', 'd');
        self::assertSame('a b c d', $body->getAttribute('class')); // Ensures 'd' is added and duplicates are avoided
    }

    /**
     * Test the `remove` method of classList.
     *
     * This test verifies that classes are correctly removed from an element's class list.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListRemove(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="  a   b c b a c"></body></html>');

        $html = $dom->querySelector('html');
        $html->classList->remove('a');
        self::assertSame('', $html->getAttribute('class'));

        $body = $dom->querySelector('body');
        $body->classList->remove('a', 'd');
        self::assertSame('b c', $body->getAttribute('class'));
    }

    /**
     * Test the `toggle` method of classList.
     *
     * This test ensures that the `toggle` method correctly adds/removes classes and returns the appropriate boolean.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListToggle(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="  a   b c b a c"></body></html>');

        $html = $dom->querySelector('html');
        $isThere = $html->classList->toggle('a');
        self::assertTrue($isThere);
        self::assertSame('a', $html->getAttribute('class'));

        $body = $dom->querySelector('body');
        $isThere = $body->classList->toggle('a');
        self::assertFalse($isThere);
        self::assertSame('b c', $body->getAttribute('class'));

        $isThere = $body->classList->toggle('d');
        self::assertTrue($isThere);
        self::assertSame('b c d', $body->getAttribute('class'));
    }

    /**
     * Test the `toggle` method of classList with force parameter.
     *
     * This test ensures that the `toggle` method with force parameter adds/removes classes based on the boolean flag.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListToggleForce(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="  a   b c b a c"></body></html>');

        $html = $dom->querySelector('html');
        $isThere = $html->classList->toggle('a', false);
        self::assertFalse($isThere);
        self::assertSame('', $html->getAttribute('class'));

        $isThere = $html->classList->toggle('a', true);
        self::assertTrue($isThere);
        self::assertSame('a', $html->getAttribute('class'));

        $body = $dom->querySelector('body');
        $isThere = $body->classList->toggle('a', false);
        self::assertFalse($isThere);
        self::assertSame('b c', $body->getAttribute('class'));

        $isThere = $body->classList->toggle('b', true);
        self::assertTrue($isThere);
        self::assertSame('b c', $body->getAttribute('class'));
    }

    /**
     * Test the `replace` method of classList.
     *
     * This test ensures that the `replace` method correctly replaces class names in an element's class list.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListReplace(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="  a   b c b a c"></body></html>');

        $html = $dom->querySelector('html');
        $html->classList->replace('a', 'b');
        self::assertSame('', $html->getAttribute('class'));

        $body = $dom->querySelector('body');
        $body->classList->replace('a', 'a');
        self::assertSame('  a   b c b a c', $body->getAttribute('class')); // since no change is made

        $body->classList->replace('a', 'b');
        self::assertSame('b c', $body->getAttribute('class'));

        $body->classList->replace('c', 'd');
        self::assertSame('b d', $body->getAttribute('class'));
    }

    /**
     * Test the `length` property of classList.
     *
     * This test verifies that the `length` property of classList correctly reflects the number of unique classes.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListLength(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="  a   b c b a c"></body></html>');

        $html = $dom->querySelector('html');
        self::assertSame(0, $html->classList->length);

        $body = $dom->querySelector('body');
        self::assertSame(3, $body->classList->length);
    }

    /**
     * Test the `value` property of classList.
     *
     * This test ensures that the `value` property returns the correct string representation of class names.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListValue(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="  a   b c b a c"></body></html>');

        $html = $dom->querySelector('html');
        self::assertSame('', $html->classList->value);

        $body = $dom->querySelector('body');
        self::assertSame('a b c', $body->classList->value);
    }

    /**
     * Test for handling undefined properties in classList.
     *
     * This test verifies that accessing an undefined property in classList throws an Exception.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListUndefinedProperty(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="  a   b c b a c"></body></html>');

        $html = $dom->querySelector('html');
        $this->expectException(Exception::class);
        /** @noinspection PhpUndefinedFieldInspection */
        $html->classList->someProperty;
    }

    /**
     * Test the `toString` method of classList.
     *
     * This test ensures that the `toString` method correctly converts the classList to a string representation.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListToString(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="  a   b c b a c"></body></html>');

        $html = $dom->querySelector('html');
        self::assertSame('', (string)$html->classList);

        $body = $dom->querySelector('body');
        self::assertSame('a b c', (string)$body->classList);
    }

    /**
     * Test overwriting the classList of an element.
     *
     * This test ensures that setting the classList overwrites the existing class names and updates correctly.
     *
     * @group classList
     *
     * @throws Exception
     */
    public function testClassListOverwrite(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body class="a b c"></body></html>');

        $body = $dom->querySelector('body');
        self::assertSame('a b c', (string)$body->classList);
        self::assertSame('a b c', $body->getAttribute('class'));

        $body->setAttribute('class', 'd e f');
        self::assertSame('d e f', (string)$body->classList);
        self::assertSame('d e f', $body->getAttribute('class'));

        $body->classList = 'g h i';
        self::assertSame('g h i', (string)$body->classList);
        self::assertSame('g h i', $body->getAttribute('class'));
    }

    /**
     * Test handling of incorrect charset meta tags.
     *
     * This test ensures that when loading a document with an incorrectly formatted charset meta tag,
     * the saved HTML remains unchanged.
     *
     * @throws Exception
     */
    public function testWrongCharsetMetaTag(): void
    {
        $html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" name="viewport" content="charset=UTF-8; width=device-width; initial-scale=1.0; text/html"></head><body>Hi</body></html>';
        $dom = new HTML5DOMDocument();
        $dom->loadHTML($html);
        $resultHTML = $dom->saveHTML();
        self::assertEquals($html, $this->removeNewLines($resultHTML));
    }

    /**
     * Test LIBXML_HTML_NODEFDTD flag for loading HTML without implied HTML tags.
     *
     * This test ensures that when the LIBXML_HTML_NODEFDTD flag is used, the document
     * generates the implied <html> and <body> tags.
     *
     * @throws Exception
     */
    public function testLIBXML_HTML_NODEFDTD(): void
    {
        $content = '<div>hello</div>';
        $dom = new HTML5DOMDocument();
        $dom->loadHTML($content, LIBXML_HTML_NODEFDTD);
        $expectedContent = '<html><body><div>hello</div></body></html>';
        self::assertEquals($expectedContent, $dom->saveHTML());
    }

    /**
     * Test LIBXML_HTML_NOIMPLIED flag for suppressing implied HTML elements.
     *
     * This test verifies that when the LIBXML_HTML_NOIMPLIED flag is used, no implied
     * <html>, <head>, or <body> tags are added to the document.
     *
     * @throws Exception
     */
    public function testLIBXML_HTML_NOIMPLIED(): void
    {
        $content = '<div>hello</div>';
        $dom = new HTML5DOMDocument();
        $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED);
        self::assertEquals(0, $dom->getElementsByTagName('html')->length);
        self::assertEquals(0, $dom->getElementsByTagName('head')->length);
        self::assertEquals(0, $dom->getElementsByTagName('body')->length);

        $expectedContent = '<!DOCTYPE html><div>hello</div>';
        self::assertEquals($expectedContent, $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Test compatibility with the native DOMDocument class.
     *
     * This test compares HTML5DOMDocument and DOMDocument behavior by verifying that
     * the two classes produce identical output for various content and flags.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testCompatibilityWithDOMDocument(): void
    {
        /**
         * @throws DOMException
         */
        $compareDOMs = static function (HTML5DOMDocument $dom1, DOMDocument $dom2): void {
            self::assertEquals($dom1->getElementsByTagName('html')->length, $dom2->getElementsByTagName('html')->length);
            self::assertEquals($dom1->getElementsByTagName('head')->length, $dom2->getElementsByTagName('head')->length);
            self::assertEquals($dom1->getElementsByTagName('body')->length, $dom2->getElementsByTagName('body')->length);

            $updateNewLines = static function (&$content): void {
                $content = \str_replace(
                    ["\n<head>", "\n<body>", "\n</html>"],
                    ['<head>', '<body>', '</html>'],
                    $content
                );
                $content = \rtrim($content, "\n");
            };

            $result1 = $dom1->saveHTML();
            $result2 = $dom2->saveHTML();
            $result2 = \preg_replace('/<!DOCTYPE(.*?)>/', '<!DOCTYPE html>', $result2);
            $updateNewLines($result1);
            $updateNewLines($result2);
            self::assertEquals($result1, $result2);

            if ($dom1->getElementsByTagName('html')->length > 0 && $dom2->getElementsByTagName('html')->length > 0) {
                $html1 = $dom1->saveHTML($dom1->getElementsByTagName('html')[0]);
                $html2 = $dom2->saveHTML($dom2->getElementsByTagName('html')[0]);
                $updateNewLines($html1);
                $updateNewLines($html2);
                self::assertEquals($html1, $html2);
            }

            if ($dom1->getElementsByTagName('body')->length > 0 && $dom2->getElementsByTagName('body')->length > 0) {
                $body1 = $dom1->saveHTML($dom1->getElementsByTagName('body')[0]);
                $body2 = $dom2->saveHTML($dom2->getElementsByTagName('body')[0]);
                self::assertEquals($body1, $body2);

                if ($dom1->getElementsByTagName('body')[0]->firstChild !== null) {
                    $firstChild1 = $dom1->saveHTML($dom1->getElementsByTagName('body')[0]->firstChild);
                    $firstChild2 = $dom2->saveHTML($dom2->getElementsByTagName('body')[0]->firstChild);
                    self::assertEquals($firstChild1, $firstChild2);
                }
            }
        };

        $compareContent = static function (string $content) use ($compareDOMs): void {
            $dom = new HTML5DOMDocument();
            $dom->loadHTML($content);
            $dom2 = new DOMDocument();
            $dom2->loadHTML($content);
            $compareDOMs($dom, $dom2);
        };

        // Test LIBXML_HTML_NOIMPLIED
        $content = '<div>hello</div>';
        $dom = new HTML5DOMDocument();
        $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED);
        $dom2 = new DOMDocument();
        $dom2->loadHTML($content, LIBXML_HTML_NOIMPLIED);
        $compareDOMs($dom, $dom2);

        // Test LIBXML_HTML_NODEFDTD
        $dom = new HTML5DOMDocument();
        $dom->loadHTML($content, LIBXML_HTML_NODEFDTD);
        $dom2 = new DOMDocument();
        $dom2->loadHTML($content, LIBXML_HTML_NODEFDTD);
        $compareDOMs($dom, $dom2);

        // Test different contents
        $compareContent('<div>hello</div>');
        $compareContent('<body>hello</body>');
        $compareContent('<html><div>hello</div></html>');
        $compareContent('<html><head></head><body><div>hello</div></body></html>');
    }

    /**
     * Test querying for elements with duplicate IDs.
     *
     * This test verifies that when using the ALLOW_DUPLICATE_IDS flag, multiple elements with the same ID
     * can be correctly selected using various query methods.
     *
     * @throws Exception
     */
    public function testDuplicateElementIDsQueries(): void
    {
        $content = '<div id="key1">1</div><div id="key1">2</div><div id="key1">3</div><div id="keyA">A</div>';
        $dom = new HTML5DOMDocument();
        $dom->loadHTML($content, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);

        self::assertEquals('1', $dom->getElementById('key1')->innerHTML);
        self::assertEquals('1', $dom->querySelector('[id="key1"]')->innerHTML);
        self::assertCount(3, $dom->querySelectorAll('[id="key1"]'));
        self::assertEquals('1', $dom->querySelectorAll('[id="key1"]')[0]->innerHTML);
        self::assertEquals('2', $dom->querySelectorAll('[id="key1"]')[1]->innerHTML);
        self::assertEquals('3', $dom->querySelectorAll('[id="key1"]')[2]->innerHTML);
    }

    /**
     * Test exception handling for duplicate element IDs without allowing them.
     *
     * This test ensures that attempting to load HTML with duplicate IDs throws an Exception when
     * the ALLOW_DUPLICATE_IDS flag is not used.
     */
    public function testDuplicateElementIDsException(): void
    {
        $content = '<div id="key1">1</div><div><div id="key1">2</div></div>';
        $dom = new HTML5DOMDocument();

        $this->expectException(Exception::class);
        $dom->loadHTML($content);
    }

    /**
     * Test handling of special characters within script tags.
     *
     * This test verifies that script content with special characters (such as < and >) is correctly handled
     * and does not interfere with HTML parsing.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testSpecialCharsInScriptTags(): void
    {
        $js1 = 'var f1=function(t){
            return t.replace(/</g,"&lt;").replace(/>/g,"&gt;");
        };';
        $js2 = 'var f2=function(t){
            return t.replace(/</g,"&lt;").replace(/>/g,"&gt;");
        };';
        $content = '<html><head><script src="url1"/><script src="url2"></script><script type="text/javascript">' . $js1 . '</script><script>' . $js2 . '</script></head></html>';

        $dom = new HTML5DOMDocument();
        $dom->loadHTML($content);

        $scripts = $dom->querySelectorAll('script');
        self::assertEquals('', $scripts[0]->innerHTML);
        self::assertEquals('', $scripts[1]->innerHTML);
        self::assertEquals($js1, $scripts[2]->innerHTML);
        self::assertEquals($js2, $scripts[3]->innerHTML);

        $expectedHTML = "<!DOCTYPE html>\n" . \str_replace('<script src="url1"/>', '<script src="url1"></script>', $content);
        self::assertEquals($expectedHTML, $dom->saveHTML());
    }

    /**
     * Test loading and saving HTML fragments.
     *
     * This test ensures that when loading HTML fragments (not full documents),
     * the fragment content is preserved and correctly saved without adding extra tags.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testFragments(): void
    {
        $fragments = [
            '<div>text</div>',
            '<p>text</p>',
            '<script type="text/javascript">var a = 1;</script>',
        ];

        foreach ($fragments as $fragment) {
            $dom = new HTML5DOMDocument();
            $dom->loadHTML($fragment, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            self::assertCount(1, $dom->querySelectorAll('*'));
            self::assertEquals($fragment, $dom->saveHTML());
        }
    }

    /**
     * Test script tags containing CDATA sections without an HTML tag.
     *
     * This test verifies that script tags containing content wrapped in CDATA are correctly handled
     * even when the document is missing the <html> tag.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testScriptsCDATA(): void
    {
        // Test case for handling script tags with CDATA content and no <html> tag.
        $html = '<script type="text/template"><div>Hi</div></script>';
        $expectedResult = '<!DOCTYPE html><html><body><script type="text/template"><div>Hi</div></script></body></html>';

        $dom = new HTML5DOMDocument();
        $dom->loadHTML($html);

        self::assertEquals($expectedResult, $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Test handling of internal entities in property getters.
     *
     * This test ensures that internal entities are correctly handled when accessing
     * node properties (nodeValue, textContent) and getters (getNodeValue, getTextContent).
     *
     * @dataProvider propertyGetterTestDataProvider
     *
     * @throws Exception
     */
    public function testInternalEntityFromGetters(string $dom, string $expectedFromProperty, string $expectedFromGetter): void
    {
        $domDoc = new HTML5DOMDocument('1.0', 'utf-8');
        $domDoc->loadHTML($dom);
        $xpath = new DOMXPath($domDoc);

        $xPathNodeList = $xpath->query('//p');

        foreach ($xPathNodeList as $node) {
            static::assertInstanceOf(HTML5DOMElement::class, $node);
            static::assertEquals($expectedFromProperty, $node->nodeValue);
            static::assertEquals($expectedFromGetter, $node->getNodeValue());

            static::assertEquals($expectedFromProperty, $node->textContent);
            static::assertEquals($expectedFromGetter, $node->getTextContent());
        }

        $querySelectorNodeList = $domDoc->querySelectorAll('p');

        foreach ($querySelectorNodeList as $node) {
            static::assertInstanceOf(HTML5DOMElement::class, $node);
            static::assertEquals($expectedFromProperty, $node->nodeValue);
            static::assertEquals($expectedFromGetter, $node->getNodeValue());

            static::assertEquals($expectedFromProperty, $node->textContent);
            static::assertEquals($expectedFromGetter, $node->getTextContent());
        }
    }

    /**
     * Test saving HTML without first loading any HTML content.
     *
     * This test ensures that saving HTML without loading content works as expected
     * when elements are added manually to the document.
     *
     * @throws DOMException
     */
    public function testSaveHTMLWithoutLoadHTML(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->appendChild($dom->createElement('div'));
        $dom->querySelector('*')->innerHTML = 'text';
        self::assertEquals('<div>text</div>', $dom->saveHTML());
    }

    /**
     * Test handling of duplicate IDs when modifying elements.
     *
     * This test ensures that modifying elements with duplicate IDs works correctly
     * when the ALLOW_DUPLICATE_IDS flag is used.
     *
     * @throws DOMException
     * @throws Exception
     */
    public function testAllowDuplicateIDsWhenModifyingElements(): void
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML('<html><body><div id="id1"></div><span id="id1"></span></body></div>', HTML5DOMDocument::ALLOW_DUPLICATE_IDS);

        $body = $dom->querySelector('body');
        $body->innerHTML .= '<strong></strong>';
        $strong = $dom->querySelector('strong');
        $strong->outerHTML = '<strong>text</strong>';

        $expectedResult = '<!DOCTYPE html><html><body><div id="id1"></div><span id="id1"></span><strong>text</strong></body></html>';
        self::assertEquals($expectedResult, $this->removeNewLines($dom->saveHTML()));
    }

    /**
     * Data provider for property getter tests.
     *
     * Provides HTML content, expected property values, and expected getter values
     * for testing various DOM element getters.
     *
     * @return array
     */
    public static function propertyGetterTestDataProvider(): array
    {
        return [
            [
                '<html><body><p><span>Lorem Ipsum</span> &mdash; <span>dolor sit amet,</span></p></body></html>',
                'Lorem Ipsum html5-dom-document-internal-entity1-mdash-end dolor sit amet,',
                'Lorem Ipsum — dolor sit amet,',
            ],
        ];
    }

    /**
     * Utility method to remove new lines from the HTML string.
     *
     * This helper function removes all newlines from a given text.
     *
     * @param string $text
     * @return string
     */
    private function removeNewLines(string $text): string
    {
        return \str_replace("\n", '', $text);
    }
}
