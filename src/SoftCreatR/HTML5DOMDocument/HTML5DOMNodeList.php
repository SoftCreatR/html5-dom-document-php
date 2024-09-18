<?php

/*
 * HTML5 DOMDocument PHP library (extends DOMDocument)
 * https://github.com/ivopetkov/html5-dom-document-php
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace SoftCreatR\HTML5DOMDocument;

use ArrayObject;
use RuntimeException;

/**
 * Represents a list of DOM nodes.
 *
 * @property-read int $length The list items count
 */
class HTML5DOMNodeList extends ArrayObject
{
    /**
     * Returns the item at the specified index.
     *
     * @param int $index The item index.
     * @return HTML5DOMElement|null The item at the specified index or null if not existent.
     */
    public function item(int $index): ?HTML5DOMElement
    {
        return $this->offsetExists($index) ? $this->offsetGet($index) : null;
    }

    /**
     * Returns the value for the specified property.
     *
     * @param string $name The name of the property.
     * @return int
     * @throws RuntimeException
     */
    public function __get(string $name): int
    {
        return match ($name) {
            'length' => \count($this),
            default => throw new RuntimeException('Undefined property: ' . self::class . '::$' . $name),
        };
    }

    /**
     * Handles attempts to set properties.
     *
     * @param string $name The name of the property.
     * @param mixed $value The value to set.
     * @throws RuntimeException
     */
    public function __set(string $name, mixed $value): void
    {
        throw new RuntimeException('Cannot set read-only property ' . self::class . '::$' . $name);
    }

    /**
     * Checks if a property is set.
     *
     * @param string $name The name of the property.
     * @return bool True if the property is set, false otherwise.
     */
    public function __isset(string $name): bool
    {
        return $name === 'length';
    }

    /**
     * Handles attempts to unset properties.
     *
     * @param string $name The name of the property.
     * @throws RuntimeException
     */
    public function __unset(string $name): void
    {
        throw new RuntimeException('Cannot unset read-only property ' . self::class . '::$' . $name);
    }
}
