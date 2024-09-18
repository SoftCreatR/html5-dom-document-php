<?php

/*
 * HTML5 DOMDocument PHP library (extends DOMDocument)
 * https://github.com/ivopetkov/html5-dom-document-php
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace SoftCreatR\Tests\HTML5DOMDocument;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SoftCreatR\HTML5DOMDocument\HTML5DOMNodeList;

class HTML5DOMNodeListTest extends TestCase
{
    /**
     * Test that setting a property throws a RuntimeException.
     */
    public function testSetThrowsException(): void
    {
        $nodeList = new HTML5DOMNodeList();

        // Expect an exception when trying to set a property
        $this->expectException(RuntimeException::class);
        $nodeList->length = 10;  // Trying to set the 'length' property should throw an exception
    }

    /**
     * Test that isset() returns true only for the 'length' property.
     */
    public function testIsset(): void
    {
        $nodeList = new HTML5DOMNodeList();

        // Test that 'length' is set
        $this->assertTrue(isset($nodeList->length));

        // Test that an undefined property returns false
        $this->assertFalse(isset($nodeList->undefinedProperty));
    }

    /**
     * Test that unsetting a property throws a RuntimeException.
     */
    public function testUnsetThrowsException(): void
    {
        $nodeList = new HTML5DOMNodeList();

        // Expect an exception when trying to unset the 'length' property
        $this->expectException(RuntimeException::class);
        $nodeList->length = null;  // This should throw an exception
    }
}
