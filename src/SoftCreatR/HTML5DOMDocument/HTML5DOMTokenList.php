<?php

/*
 * HTML5 DOMDocument PHP library (extends DOMDocument)
 * https://github.com/ivopetkov/html5-dom-document-php
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace SoftCreatR\HTML5DOMDocument;

use ArrayIterator;
use DOMElement;
use InvalidArgumentException;
use RuntimeException;

/**
 * Represents a set of space-separated tokens of an element attribute.
 *
 * @property int $length The number of tokens.
 * @property string $value A space-separated list of the tokens.
 */
class HTML5DOMTokenList
{
    /**
     * The name of the attribute.
     *
     * @var string
     */
    private readonly string $attributeName;

    /**
     * The DOM element.
     *
     * @var DOMElement
     */
    private readonly DOMElement $element;

    /**
     * The list of tokens.
     *
     * @var string[]
     */
    private array $tokens = [];

    /**
     * Creates a list of space-separated tokens based on the attribute value of an element.
     *
     * @param DOMElement $element The DOM element.
     * @param string $attributeName The name of the attribute.
     */
    public function __construct(DOMElement $element, string $attributeName)
    {
        $this->element = $element;
        $this->attributeName = $attributeName;
        $this->tokenize();
    }

    /**
     * Adds the given tokens to the list.
     *
     * @param string ...$tokens The tokens you want to add to the list.
     * @return void
     */
    public function add(string ...$tokens): void
    {
        if (\count($tokens) === 0) {
            return;
        }

        $this->tokenize();

        foreach ($tokens as $token) {
            if (!\in_array($token, $this->tokens, true)) {
                $this->tokens[] = $token;
            }
        }

        $this->setAttributeValue();
    }

    /**
     * Removes the specified tokens from the list. If the token does not exist in the list, no error is thrown.
     *
     * @param string ...$tokens The tokens you want to remove from the list.
     * @return void
     */
    public function remove(string ...$tokens): void
    {
        if (\count($tokens) === 0) {
            return;
        }

        $this->tokenize();

        $this->tokens = \array_values(\array_diff($this->tokens, $tokens));
        $this->setAttributeValue();
    }

    /**
     * Returns an item in the list by its index (returns null if the index is out of bounds).
     *
     * @param int $index The zero-based index of the item you want to return.
     * @return ?string
     */
    public function item(int $index): ?string
    {
        $this->tokenize();

        return $this->tokens[$index] ?? null;
    }

    /**
     * Removes a given token from the list and returns false. If the token doesn't exist, it's added and the function returns true.
     *
     * @param string $token The token you want to toggle.
     * @param bool|null $force A Boolean that, if included, turns the toggle into a one-way operation. If set to false, the token will only be removed but not added again. If set to true, the token will only be added but not removed again.
     * @return bool False if the token is not in the list after the call, or true if the token is in the list after the call.
     */
    public function toggle(string $token, ?bool $force = null): bool
    {
        $this->tokenize();
        $exists = \in_array($token, $this->tokens, true);

        if ($force === null) {
            if ($exists) {
                $this->tokens = \array_values(\array_diff($this->tokens, [$token]));
            } else {
                $this->tokens[] = $token;
            }
        } elseif ($force) {
            if (!$exists) {
                $this->tokens[] = $token;
            }
        } elseif ($exists) {
            $this->tokens = \array_values(\array_diff($this->tokens, [$token]));
        }

        $this->setAttributeValue();

        // Return true if the token is now in the list
        return \in_array($token, $this->tokens, true);
    }

    /**
     * Returns true if the list contains the given token, otherwise false.
     *
     * @param string $token The token you want to check for the existence of in the list.
     * @return bool True if the list contains the given token, otherwise false.
     */
    public function contains(string $token): bool
    {
        $this->tokenize();

        return \in_array($token, $this->tokens, true);
    }

    /**
     * Replaces an existing token with a new token.
     *
     * @param string $old The token you want to replace.
     * @param string $new The token you want to replace $old with.
     * @return void
     */
    public function replace(string $old, string $new): void
    {
        if ($old === $new) {
            return;
        }

        $this->tokenize();
        $indexOld = \array_search($old, $this->tokens, true);

        if ($indexOld !== false) {
            if (!\in_array($new, $this->tokens, true)) {
                $this->tokens[$indexOld] = $new;
            } else {
                // Remove the old token
                \array_splice($this->tokens, $indexOld, 1);
            }

            $this->setAttributeValue();
        }
    }

    /**
     * Returns the tokens as a space-separated string.
     *
     * @return string
     */
    public function __toString(): string
    {
        $this->tokenize();

        return \implode(' ', $this->tokens);
    }

    /**
     * Returns an iterator allowing you to go through all tokens contained in the list.
     *
     * @return ArrayIterator
     */
    public function entries(): ArrayIterator
    {
        $this->tokenize();

        return new ArrayIterator($this->tokens);
    }

    /**
     * Returns the value for the specified property.
     *
     * @param string $name The name of the property.
     * @return string|int The value of the specified property.
     * @throws RuntimeException
     */
    public function __get(string $name): string|int
    {
        return match ($name) {
            'length' => \count($this->tokens),
            'value' => $this->__toString(),
            default => throw new RuntimeException('Undefined property: ' . self::class . '::$' . $name),
        };
    }

    /**
     * Sets the value of a property.
     *
     * @param string $name The name of the property.
     * @param mixed $value The value to set.
     * @return void
     * @throws RuntimeException
     */
    public function __set(string $name, mixed $value): void
    {
        if ($name === 'value') {
            if (!\is_string($value)) {
                throw new InvalidArgumentException('Value must be a string.');
            }

            $this->setValue($value);
        } else {
            throw new RuntimeException('Cannot set undefined or read-only property ' . self::class . '::$' . $name);
        }
    }

    /**
     * Checks if a property is set.
     *
     * @param string $name The name of the property.
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return \in_array($name, ['length', 'value'], true);
    }

    /**
     * Handles attempts to unset properties.
     *
     * @param string $name The name of the property.
     * @return void
     * @throws RuntimeException
     */
    public function __unset(string $name): void
    {
        throw new RuntimeException('Cannot unset property ' . self::class . '::$' . $name);
    }

    /**
     * Sets the value of the token list.
     *
     * @param string $value The new value.
     * @return void
     */
    private function setValue(string $value): void
    {
        $this->tokens = \array_values(\array_unique(\array_filter(
            \explode(' ', $value),
            static fn($token) => $token !== ''
        )));

        $this->setAttributeValue();
    }

    /**
     * Updates the tokens array based on the current attribute value.
     *
     * @return void
     */
    private function tokenize(): void
    {
        $current = $this->element->getAttribute($this->attributeName);

        // Always tokenize to ensure tokens are in sync with the attribute value
        $this->tokens = \array_values(\array_unique(\array_filter(\explode(' ', $current), static fn($token) => $token !== '')));
    }

    /**
     * Sets the attribute value based on the tokens array.
     *
     * @return void
     */
    private function setAttributeValue(): void
    {
        $value = \implode(' ', $this->tokens);
        $this->element->setAttribute($this->attributeName, $value);
    }
}
