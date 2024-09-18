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
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use SoftCreatR\HTML5DOMDocument\HTML5DOMTokenList;

class HTML5DOMTokenListTest extends TestCase
{
    /**
     * Sets up a DOM element with a class attribute for use in tests.
     *
     * @return array A list containing a DOMDocument, DOMElement, and the HTML5DOMTokenList instance.
     *
     * @throws DOMException
     */
    private function setupDOMElementWithClass(): array
    {
        $dom = new DOMDocument();
        $element = $dom->createElement('div');
        $element->setAttribute('class', 'foo bar');

        $tokenList = new HTML5DOMTokenList($element, 'class');

        return [$dom, $element, $tokenList];
    }

    /**
     * Test setting the "value" property via __set().
     *
     * @throws DOMException
     */
    public function testSetValue(): void
    {
        [, $element, $tokenList] = $this->setupDOMElementWithClass();

        // Set the value to a new class list
        $tokenList->value = 'baz qux';

        // Ensure that the value is updated in the DOM element's attribute
        $this->assertEquals('baz qux', $element->getAttribute('class'));

        // Ensure that the tokens are updated within the tokenList
        $this->assertEquals('baz qux', (string)$tokenList);
    }

    /**
     * Test setting the value property throws an exception if the value is not a string.
     *
     * @throws DOMException
     */
    public function testSetValueThrowsExceptionOnInvalidType(): void
    {
        [,, $tokenList] = $this->setupDOMElementWithClass();

        // Expect an InvalidArgumentException when trying to set a non-string value
        $this->expectException(InvalidArgumentException::class);
        $tokenList->value = 123; // Invalid, should throw an exception
    }

    /**
     * Test __isset() returns true for 'length' and 'value' properties.
     *
     * @throws DOMException
     */
    public function testIsset(): void
    {
        [,, $tokenList] = $this->setupDOMElementWithClass();

        // Test isset for 'value' and 'length'
        $this->assertTrue(isset($tokenList->value));
        $this->assertTrue(isset($tokenList->length));

        // Test that an undefined property returns false
        $this->assertFalse(isset($tokenList->undefinedProperty));
    }

    /**
     * Test __unset() throws a RuntimeException, as properties cannot be unset.
     *
     * @throws DOMException
     */
    public function testUnsetThrowsException(): void
    {
        [,, $tokenList] = $this->setupDOMElementWithClass();

        // Expect an exception when trying to unset a property
        $this->expectException(InvalidArgumentException::class);
        $tokenList->value = null; // This should throw a RuntimeException
    }

    /**
     * Test the setValue method directly updates the token list.
     *
     * @throws ReflectionException
     * @throws DOMException
     */
    public function testSetValueMethod(): void
    {
        [, $element, $tokenList] = $this->setupDOMElementWithClass();

        // Call the setValue method directly
        $reflection = new ReflectionClass($tokenList);
        $method = $reflection->getMethod('setValue');
        // Make the private method accessible
        $method->invoke($tokenList, 'new-token another-token');

        // Verify that the tokens were updated in the element
        $this->assertEquals('new-token another-token', $element->getAttribute('class'));
    }

    /**
     * Test __get for 'length' and 'value' properties.
     *
     * @throws DOMException
     */
    public function testGetProperties(): void
    {
        [,, $tokenList] = $this->setupDOMElementWithClass();

        // Check the 'length' property
        $this->assertEquals(2, $tokenList->length);

        // Check the 'value' property
        $this->assertEquals('foo bar', $tokenList->value);
    }

    /**
     * Test __get throws an exception for undefined properties.
     *
     * @throws DOMException
     */
    public function testGetThrowsExceptionForUndefinedProperties(): void
    {
        [,, $tokenList] = $this->setupDOMElementWithClass();

        // Expect an exception when trying to access an undefined property
        $this->expectException(RuntimeException::class);
        $undefined = $tokenList->undefinedProperty;
    }

    /**
     * Test add() method does nothing when no tokens are passed.
     *
     * @throws DOMException
     */
    public function testAddDoesNothingWhenNoTokensPassed(): void
    {
        [,, $tokenList] = $this->setupDOMElementWithClass();

        // Capture the initial value
        $initialValue = (string)$tokenList;

        // Call add() with no arguments
        $tokenList->add();

        // Ensure the value hasn't changed
        $this->assertEquals($initialValue, (string)$tokenList);
    }

    /**
     * Test remove() method does nothing when no tokens are passed.
     *
     * @throws DOMException
     */
    public function testRemoveDoesNothingWhenNoTokensPassed(): void
    {
        [,, $tokenList] = $this->setupDOMElementWithClass();

        // Capture the initial value
        $initialValue = (string)$tokenList;

        // Call remove() with no arguments
        $tokenList->remove();

        // Ensure the value hasn't changed
        $this->assertEquals($initialValue, (string)$tokenList);
    }

    /**
     * Test __set throws a RuntimeException when attempting to set undefined or read-only properties.
     *
     * @throws DOMException
     */
    public function testSetThrowsExceptionForUndefinedProperties(): void
    {
        [,, $tokenList] = $this->setupDOMElementWithClass();

        // Expect a RuntimeException when trying to set an undefined property
        $this->expectException(RuntimeException::class);
        $tokenList->undefinedProperty = 'invalid'; // This should throw a RuntimeException
    }
}
