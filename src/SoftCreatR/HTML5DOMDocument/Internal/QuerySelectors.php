<?php

/*
 * HTML5 DOMDocument PHP library (extends DOMDocument)
 * https://github.com/ivopetkov/html5-dom-document-php
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace SoftCreatR\HTML5DOMDocument\Internal;

use DOMDocument;
use DOMElement;
use DOMNode;
use InvalidArgumentException;
use RuntimeException;
use SoftCreatR\HTML5DOMDocument\HTML5DOMElement;
use SoftCreatR\HTML5DOMDocument\HTML5DOMNodeList;

trait QuerySelectors
{
    /**
     * Returns the first element matching the selector.
     *
     * @param string $selector A CSS query selector. Available values: *, tagname, tagname#id, #id, tagname.classname, .classname, tagname[attribute-selector] and [attribute-selector].
     * @return HTML5DOMElement|null The result DOMElement or null if not found
     */
    private function internalQuerySelector(string $selector): ?HTML5DOMElement
    {
        return $this->internalQuerySelectorAll($selector, 1)->item(0);
    }

    /**
     * Returns a list of document elements matching the selector.
     *
     * @param string $selector A CSS query selector. Available values: *, tagname, tagname#id, #id, tagname.classname, .classname, tagname[attribute-selector] and [attribute-selector].
     * @param int|null $preferredLimit Preferred maximum number of elements to return.
     * @return HTML5DOMNodeList Returns a list of DOMElements matching the criteria.
     * @throws InvalidArgumentException
     */
    private function internalQuerySelectorAll(string $selector, ?int $preferredLimit = null): HTML5DOMNodeList
    {
        $selector = \trim($selector);
        $cache = [];

        $walkChildren = function (DOMNode|DOMElement $context, ?array $tagNames, callable $callback) use (&$cache): bool|null {
            $children = [];

            if (!empty($tagNames)) {
                foreach ($tagNames as $tagName) {
                    $elements = $context->getElementsByTagName($tagName);
                    foreach ($elements as $element) {
                        $children[] = $element;
                    }
                }
            } else {
                $getChildren = static function (DOMNode $node): array {
                    $result = [];

                    $process = static function (DOMNode $n) use (&$process, &$result): void {
                        foreach ($n->childNodes as $child) {
                            if ($child instanceof DOMElement) {
                                $result[] = $child;
                                $process($child);
                            }
                        }
                    };

                    $process($node);

                    return $result;
                };

                $cacheKey = 'walk_children';
                $children = $this === $context ? ($cache[$cacheKey] ??= $getChildren($context)) : $getChildren($context);
            }

            foreach ($children as $child) {
                if ($callback($child) === true) {
                    return true;
                }
            }

            return null;
        };

        $getElementById = static function (
            DOMNode $context,
            string $id,
            ?string $tagName
        ) use (&$walkChildren): ?DOMElement {
            if ($context instanceof DOMDocument) {
                $element = $context->getElementById($id);

                return $element && ($tagName === null || $element->tagName === $tagName) ? $element : null;
            }

            $foundElement = null;

            $walkChildren($context, $tagName ? [$tagName] : null, static function (
                DOMElement $element
            ) use ($id, &$foundElement): bool {
                if ($element->hasAttribute('id') && $element->getAttribute('id') === $id) {
                    $foundElement = $element;

                    return true;
                }

                return false;
            });

            return $foundElement;
        };

        $simpleSelectors = [];

        // All elements selector ('*')
        $simpleSelectors['\*'] = static function (
            string $mode,
            array $matches,
            DOMNode $context,
            ?callable $add = null
        ) use ($walkChildren): bool|null {
            if ($mode === 'validate') {
                return true;
            }

            $walkChildren($context, [], static fn($element) => $add && $add($element));

            return null;
        };

        // Tag name selector ('[a-zA-Z0-9\-]+')
        $simpleSelectors['[a-zA-Z0-9\-]+'] = static function (
            string $mode,
            array $matches,
            DOMNode|DOMElement $context,
            ?callable $add = null
        ) use ($walkChildren): bool|null {
            $tagNames = \array_map(static fn($match) => \strtolower($match[0]), $matches);

            if ($mode === 'validate') {
                return \in_array($context->tagName, $tagNames, true);
            }

            $walkChildren($context, $tagNames, static fn($element) => $add && $add($element));

            return null;
        };

        // Attribute selector (tagname[attr], [attr], attr operators)
        $simpleSelectors['(?:[a-zA-Z0-9\-]*)(?:\[.+?\])'] = static function (
            string $mode,
            array $matches,
            DOMNode|DOMElement $context,
            ?callable $add = null
        ) use ($walkChildren): bool|null {
            $run = static function ($match) use ($mode, $context, $add, $walkChildren): bool|null {
                $attributeSelectors = \array_map(static function (string $selector): array {
                    $pattern = '/^(.+?)(=|~=|\|=|\^=|\$=|\*=)"(.+?)"$/';

                    return \preg_match($pattern, $selector, $matches)
                        ? ['name' => \strtolower($matches[1]), 'value' => $matches[3], 'operator' => $matches[2]]
                        : ['name' => \strtolower($selector)];
                }, \explode('][', \substr($match[2], 1, -1)));

                $tagName = $match[1] !== '' ? \strtolower($match[1]) : null;

                $check = static function (DOMElement $element) use ($attributeSelectors): bool {
                    if ($element->attributes->length === 0) {
                        return false;
                    }

                    foreach ($attributeSelectors as $selector) {
                        $attrName = $selector['name'];
                        $isMatch = isset($selector['value']) ? match ($selector['operator']) {
                            '=' => $element->getAttribute($attrName) === $selector['value'],
                            '~=' => \in_array($selector['value'], \preg_split('/\s+/', $element->getAttribute($attrName)), true),
                            '|=' => $element->getAttribute($attrName) === $selector['value'] || \str_starts_with($element->getAttribute($attrName), $selector['value'] . '-'),
                            '^=' => \str_starts_with($element->getAttribute($attrName), $selector['value']),
                            '$=' => \str_ends_with($element->getAttribute($attrName), $selector['value']),
                            '*=' => \str_contains($element->getAttribute($attrName), $selector['value']),
                            default => false
                        } : $element->hasAttribute($attrName);

                        if (!$isMatch) {
                            return false;
                        }
                    }

                    return true;
                };

                if ($mode === 'validate') {
                    return ($tagName === null || $context->tagName === $tagName) && $check($context);
                }

                $walkChildren(
                    $context,
                    $tagName ? [$tagName] : null,
                    static fn($element) => $check($element) && $add && $add($element)
                );

                return null;
            };

            foreach ($matches as $match) {
                if ($mode === 'validate' && $run($match)) {
                    return true;
                }

                $run($match);
            }

            return $mode === 'validate' ? false : null;
        };

        // ID Selector (tagname#id or #id)
        $simpleSelectors['(?:[a-zA-Z0-9\-]*)#(?:[a-zA-Z0-9\-\_]+?)'] = static function (
            string $mode,
            array $matches,
            DOMNode|DOMElement $context,
            ?callable $add = null
        ) use ($getElementById): bool|null {
            $run = static function (array $match) use ($mode, $context, $add, $getElementById): bool|null {
                $tagName = $match[1] !== '' ? \strtolower($match[1]) : null;
                $id = $match[2];

                if ($mode === 'validate') {
                    return ($tagName === null || $context->tagName === $tagName)
                        && $context->getAttribute('id') === $id;
                }

                $element = $getElementById($context, $id, $tagName);

                if ($element && $add) {
                    $add($element);
                }

                return null;
            };

            foreach ($matches as $match) {
                if ($mode === 'validate' && $run($match)) {
                    return true;
                }

                $run($match);
            }

            return $mode === 'validate' ? false : null;
        };

        // Class Selector (tagname.classname or .classname)
        $simpleSelectors['(?:[a-zA-Z0-9\-]*)\.(?:[a-zA-Z0-9\-\_\.]+?)'] = static function (
            string $mode,
            array $matches,
            DOMNode|DOMElement $context,
            ?callable $add = null
        ) use ($walkChildren): bool|null {
            $rawData = []; // Array containing [tagName, classNames]
            $tagNames = [];

            // Process matches and build the $rawData and $tagNames arrays
            foreach ($matches as $match) {
                $tagName = $match[1] !== '' ? $match[1] : null;
                $classes = \explode('.', $match[2]);

                if (empty($classes)) {
                    continue;
                }

                $rawData[] = [$tagName, $classes];

                if ($tagName !== null) {
                    $tagNames[] = $tagName;
                }
            }

            // Function to check if an element matches the tag name and class names
            $check = static function (DOMElement $element) use ($rawData): bool {
                if (!$element->hasAttribute('class')) {
                    return false;
                }

                // Add spaces for precise matching
                $classAttribute = ' ' . $element->getAttribute('class') . ' ';
                $tagName = $element->tagName;

                foreach ($rawData as [$expectedTagName, $expectedClasses]) {
                    if ($expectedTagName !== null && $tagName !== $expectedTagName) {
                        continue; // Skip if tag name does not match
                    }

                    // Check if all expected classes are present
                    $allClassesFound = true;

                    foreach ($expectedClasses as $class) {
                        if (!\str_contains($classAttribute, ' ' . $class . ' ')) {
                            $allClassesFound = false;

                            break; // Stop checking further if any class is missing
                        }
                    }

                    if ($allClassesFound) {
                        return true;
                    }
                }

                return false;
            };

            // Handle validate mode
            if ($mode === 'validate') {
                return $check($context);
            }

            // Handle traversal mode using $walkChildren
            $walkChildren($context, $tagNames, static function (DOMElement $element) use ($check, $add): bool|null {
                if ($add && $check($element)) {
                    return $add($element);
                }

                return null;
            });

            return null;
        };

        $isMatchingElement = static function (DOMNode $context, string $selector) use ($simpleSelectors): bool|null {
            foreach ($simpleSelectors as $simpleSelector => $callback) {
                if (\preg_match(
                    '/^' . \str_replace('?:', '', $simpleSelector) . '$/',
                    $selector,
                    $match
                )) {
                    return $callback('validate', [$match], $context);
                }
            }

            return null;
        };

        $complexSelectors = [];

        $getMatchingElements = static function (
            DOMNode $context,
            string $selector,
            $preferredLimit = null
        ) use (&$simpleSelectors, &$complexSelectors): array {
            $processSelector = static function (
                string $mode,
                string $selector,
                $operator = null
            ) use (&$processSelector, $simpleSelectors, $complexSelectors, $context, $preferredLimit): array|bool {
                // Supported simple selectors
                $supportedSimpleSelectors = \array_keys($simpleSelectors);
                $supportedSimpleSelectorsExpression = '(?:(?:' . \implode(')|(?:', $supportedSimpleSelectors) . '))';
                $supportedSelectors = $supportedSimpleSelectors;

                // Supported complex operators
                $supportedComplexOperators = \array_keys($complexSelectors);

                // If no operator is provided, use ',' as the default and construct the selector expression
                if ($operator === null) {
                    $operator = ',';

                    foreach ($supportedComplexOperators as $complexOperator) {
                        \array_unshift(
                            $supportedSelectors,
                            '(?:(?:(?:' . $supportedSimpleSelectorsExpression . '\s*\\' . $complexOperator . '\s*))+' . $supportedSimpleSelectorsExpression . ')'
                        );
                    }
                }

                $supportedSelectorsExpression = '(?:(?:' . \implode(')|(?:', $supportedSelectors) . '))';
                $validationExpression = '/^(?:(?:' . $supportedSelectorsExpression . '\s*\\' . $operator . '\s*))*' . $supportedSelectorsExpression . '$/';

                // Validate the selector
                if (\preg_match($validationExpression, $selector) !== 1) {
                    return false;
                }

                // Add operator at the end for easier parsing
                $selector .= $operator;

                $result = [];

                // Define the $add function for 'execute' mode
                if ($mode === 'execute') {
                    $add = static function ($element) use ($preferredLimit, &$result): bool {
                        if (!\in_array($element, $result, true)) {
                            $result[] = $element;

                            // Limit the number of results if $preferredLimit is set
                            if ($preferredLimit !== null && \count($result) >= $preferredLimit) {
                                return true;
                            }
                        }

                        return false;
                    };
                }

                $selectorsToCall = [];

                // Function to group selectors
                $addSelectorToCall = static function ($type, $selector, $argument) use (&$selectorsToCall): void {
                    $previousIndex = \count($selectorsToCall) - 1;

                    if (
                        $type === 1
                        && isset($selectorsToCall[$previousIndex])
                        && $selectorsToCall[$previousIndex][0] === $type
                        && $selectorsToCall[$previousIndex][1] === $selector
                    ) {
                        $selectorsToCall[$previousIndex][2][] = $argument;
                    } else {
                        $selectorsToCall[] = [$type, $selector, [$argument]];
                    }
                };

                for ($i = 0; $i < 100000; $i++) {
                    // Match the next sub-selector
                    if (!\preg_match(
                        '/^(?<subselector>' . $supportedSelectorsExpression . ')\s*\\' . $operator . '\s*/',
                        $selector,
                        $matches
                    )) {
                        break;
                    }

                    $subSelector = $matches['subselector'];
                    $selectorFound = false;

                    // Check if the sub-selector matches a simple selector
                    foreach ($simpleSelectors as $simpleSelector => $callback) {
                        $match = null;

                        if (\preg_match(
                            '/^' . \str_replace('?:', '', $simpleSelector) . '$/',
                            $subSelector,
                            $match
                        )) {
                            if ($mode === 'parse') {
                                $result[] = $match[0];
                            } else {
                                $addSelectorToCall(1, $simpleSelector, $match);
                            }

                            $selectorFound = true;
                            break;
                        }
                    }

                    // Check if the sub-selector matches a complex selector
                    if (!$selectorFound) {
                        foreach ($complexSelectors as $complexOperator => $callback) {
                            $subSelectorParts = $processSelector('parse', $subSelector, $complexOperator);

                            if ($subSelectorParts !== false) {
                                $addSelectorToCall(2, $complexOperator, $subSelectorParts);
                                $selectorFound = true;

                                break;
                            }
                        }
                    }

                    // If no match is found, throw an exception
                    if (!$selectorFound) {
                        throw new RuntimeException('Internal error for selector "' . $selector . '"!');
                    }

                    // Remove the matched sub-selector and continue parsing
                    $selector = \substr($selector, \strlen($matches[0]));

                    if ($selector === '') {
                        break;
                    }
                }

                // Execute the collected selectors
                foreach ($selectorsToCall as $selectorToCall) {
                    if (!isset($add)) {
                        continue;
                    }

                    if ($selectorToCall[0] === 1) { // Simple selector
                        \call_user_func(
                            $simpleSelectors[$selectorToCall[1]],
                            'execute',
                            $selectorToCall[2],
                            $context,
                            $add
                        );
                    } else { // Complex selector
                        \call_user_func(
                            $complexSelectors[$selectorToCall[1]],
                            $selectorToCall[2][0],
                            $context,
                            $add
                        );
                    }
                }

                return $result;
            };

            return $processSelector('execute', $selector);
        };

        // Descendant selector (space between elements)
        $complexSelectors[' '] = static function (
            array $parts,
            DOMNode $context,
            ?callable $add = null
        ) use (&$getMatchingElements): void {
            $elements = null;

            foreach ($parts as $part) {
                $elements = $elements === null
                    ? $getMatchingElements($context, $part)
                    : \array_reduce($elements, static fn($acc, $element) => \array_merge(
                        $acc,
                        $getMatchingElements($element, $part)
                    ), []);
            }

            foreach ($elements as $element) {
                if ($add) {
                    $add($element);
                }
            }
        };

        // Child selector ('>')
        $complexSelectors['>'] = static function (
            array $parts,
            DOMNode $context,
            ?callable $add = null
        ) use (&$getMatchingElements, &$isMatchingElement): void {
            $elements = null;

            foreach ($parts as $part) {
                $elements = $elements === null
                    ? $getMatchingElements($context, $part)
                    : \array_reduce($elements, static fn($acc, DOMElement $element) => \array_merge(
                        $acc,
                        \array_filter(
                            \iterator_to_array($element->childNodes),
                            static fn($child) => $child instanceof DOMElement && $isMatchingElement($child, $part)
                        )
                    ), []);
            }

            foreach ($elements as $element) {
                if ($add) {
                    $add($element);
                }
            }
        };

        // Adjacent sibling selector ('+')
        $complexSelectors['+'] = static function (
            array $parts,
            DOMNode $context,
            ?callable $add = null
        ) use (&$getMatchingElements, &$isMatchingElement): void {
            $elements = null;

            foreach ($parts as $part) {
                $elements = $elements === null
                    ? $getMatchingElements($context, $part)
                    : \array_filter(
                        \array_map(
                            static function (DOMNode $element) use ($part, $isMatchingElement): ?DOMNode {
                                $nextSibling = $element->nextSibling;

                                while ($nextSibling && !$nextSibling instanceof DOMElement) {
                                    $nextSibling = $nextSibling->nextSibling;
                                }

                                return $nextSibling && $isMatchingElement($nextSibling, $part) ? $nextSibling : null;
                            },
                            $elements
                        )
                    );
            }

            foreach ($elements as $element) {
                if ($add) {
                    $add($element);
                }
            }
        };

        // General sibling selector ('~')
        $complexSelectors['~'] = static function (
            array $parts,
            DOMNode $context,
            ?callable $add = null
        ) use (&$getMatchingElements, &$isMatchingElement): void {
            $elements = null;

            foreach ($parts as $part) {
                $elements = $elements === null
                    ? $getMatchingElements($context, $part)
                    : \array_reduce(
                        $elements,
                        static function (array $acc, DOMNode $element) use ($part, $isMatchingElement): array {
                            $nextSibling = $element->nextSibling;

                            while ($nextSibling) {
                                if ($nextSibling instanceof DOMElement && $isMatchingElement($nextSibling, $part)) {
                                    $acc[] = $nextSibling;
                                }

                                $nextSibling = $nextSibling->nextSibling;
                            }

                            return $acc;
                        },
                        []
                    );
            }

            foreach ($elements as $element) {
                if ($add) {
                    $add($element);
                }
            }
        };

        $result = $getMatchingElements($this, $selector, $preferredLimit);

        if ($result === false) {
            throw new InvalidArgumentException('Unsupported selector (' . $selector . ')');
        }

        return new HTML5DOMNodeList($result);
    }
}
