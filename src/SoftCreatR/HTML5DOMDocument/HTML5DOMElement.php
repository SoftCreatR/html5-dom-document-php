<?php

/*
 * HTML5 DOMDocument PHP library (extends DOMDocument)
 * https://github.com/ivopetkov/html5-dom-document-php
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace SoftCreatR\HTML5DOMDocument;

use DOMElement;
use Exception;
use RuntimeException;
use SoftCreatR\HTML5DOMDocument\Internal\QuerySelectors;

/**
 * Represents a live (can be manipulated) representation of an element in a HTML5 document.
 *
 * @property string $innerHTML The HTML code inside the element.
 * @property string $outerHTML The HTML code for the element including the code inside.
 * @property HTML5DOMTokenList $classList A collection of the class attributes of the element.
 */
class HTML5DOMElement extends DOMElement
{
    use QuerySelectors;

    /**
     * Cache for found entities.
     *
     * @var array{0: array, 1: array}
     */
    private static array $foundEntitiesCache = [[], []];

    /**
     * Cache for new objects.
     *
     * @var array
     */
    private static array $newObjectsCache = [];

    /**
     * A collection of the class attributes of the element.
     *
     * @var HTML5DOMTokenList|string|null
     */
    private HTML5DOMTokenList|string|null $classList = null;

    /**
     * Returns the value for the specified property.
     *
     * @param string $name
     * @return string|HTML5DOMTokenList
     * @throws RuntimeException
     */
    public function __get(string $name): string|HTML5DOMTokenList
    {
        return match ($name) {
            'innerHTML' => $this->getInnerHTML(),
            'outerHTML' => $this->getOuterHTML(),
            'classList' => $this->getClassList(),
            default => throw new RuntimeException('Undefined property: ' . self::class . '::$' . $name),
        };
    }

    /**
     * Sets the value for the specified property.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     *
     * @throws RuntimeException
     * @throws Exception
     */
    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'innerHTML' => $this->setInnerHTML((string)$value),
            'outerHTML' => $this->setOuterHTML((string)$value),
            'classList' => $this->setAttribute('class', (string)$value),
            default => throw new RuntimeException('Undefined property: ' . self::class . '::$' . $name),
        };
    }

    /**
     * Checks if a property is set.
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return \in_array($name, ['innerHTML', 'outerHTML', 'classList'], true);
    }

    /**
     * Handles attempts to unset properties.
     *
     * @param string $name
     * @return void
     * @throws RuntimeException
     */
    public function __unset(string $name): void
    {
        throw new RuntimeException('Cannot unset property ' . self::class . '::$' . $name);
    }

    /**
     * Gets the inner HTML of the element.
     *
     * @return string
     */
    private function getInnerHTML(): string
    {
        if ($this->firstChild === null) {
            return '';
        }

        $html = $this->ownerDocument->saveHTML($this);
        $nodeName = $this->nodeName;

        return \preg_replace('@^<' . $nodeName . '[^>]*>|</' . $nodeName . '>$@', '', $html) ?? '';
    }

    /**
     * Sets the inner HTML of the element.
     *
     * @param string $value
     * @return void
     * @throws Exception
     */
    private function setInnerHTML(string $value): void
    {
        while ($this->hasChildNodes()) {
            $this->removeChild($this->firstChild);
        }

        $tmpDoc = $this->getNewHTML5DOMDocument();
        $tmpDoc->loadHTML('<body>' . $value . '</body>', HTML5DOMDocument::ALLOW_DUPLICATE_IDS);

        $body = $tmpDoc->getElementsByTagName('body')->item(0);
        if ($body !== null) {
            foreach ($body->childNodes as $node) {
                $importedNode = $this->ownerDocument->importNode($node, true);
                $this->appendChild($importedNode);
            }
        }
    }

    /**
     * Gets the outer HTML of the element.
     *
     * @return string
     */
    private function getOuterHTML(): string
    {
        if ($this->firstChild === null) {
            $nodeName = $this->nodeName;
            $attributes = $this->getAttributes();
            $result = '<' . $nodeName;

            foreach ($attributes as $attributeName => $value) {
                $result .= ' ' . $attributeName . '="' . \htmlentities($value) . '"';
            }

            if (!\in_array($nodeName, [
                'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img',
                'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr',
            ], true)) {
                $result .= '></' . $nodeName . '>';
            } else {
                $result .= '/>';
            }

            return $result;
        }

        return $this->ownerDocument->saveHTML($this);
    }

    /**
     * Sets the outer HTML of the element.
     *
     * @param string $value
     * @return void
     * @throws Exception
     */
    private function setOuterHTML(string $value): void
    {
        $tmpDoc = $this->getNewHTML5DOMDocument();
        $tmpDoc->loadHTML('<body>' . $value . '</body>', HTML5DOMDocument::ALLOW_DUPLICATE_IDS);

        $body = $tmpDoc->getElementsByTagName('body')->item(0);
        if ($body !== null) {
            foreach ($body->childNodes as $node) {
                $importedNode = $this->ownerDocument->importNode($node, true);
                $this->parentNode?->insertBefore($importedNode, $this);
            }
        }

        $this->parentNode?->removeChild($this);
    }

    /**
     * Gets the classList of the element.
     *
     * @return HTML5DOMTokenList
     */
    private function getClassList(): HTML5DOMTokenList
    {
        if ($this->classList === null) {
            $this->classList = new HTML5DOMTokenList($this, 'class');
        }

        return $this->classList;
    }

    /**
     * Returns a new instance of HTML5DOMDocument.
     *
     * @return HTML5DOMDocument
     */
    private function getNewHTML5DOMDocument(): HTML5DOMDocument
    {
        if (!isset(self::$newObjectsCache['html5domdocument'])) {
            self::$newObjectsCache['html5domdocument'] = new HTML5DOMDocument();
        }

        return clone self::$newObjectsCache['html5domdocument'];
    }

    /**
     * Updates the result value before returning it.
     *
     * @param string $value
     * @return string The updated value
     */
    private function updateResult(string $value): string
    {
        $value = \str_replace(self::$foundEntitiesCache[0], self::$foundEntitiesCache[1], $value);

        if (\str_contains($value, 'html5-dom-document-internal-entity')) {
            $search = [];
            $replace = [];
            $matches = [];

            \preg_match_all('/html5-dom-document-internal-entity([12])-(.*?)-end/', $value, $matches);

            if (!empty($matches[0])) {
                $matches[0] = \array_unique($matches[0]);

                foreach ($matches[0] as $i => $match) {
                    $search[] = $match;
                    $entity = ($matches[1][$i] === '1' ? '&' : '&#') . $matches[2][$i] . ';';
                    $replace[] = \html_entity_decode($entity);
                }

                $value = \str_replace($search, $replace, $value);

                self::$foundEntitiesCache[0] = \array_merge(self::$foundEntitiesCache[0], $search);
                self::$foundEntitiesCache[1] = \array_merge(self::$foundEntitiesCache[1], $replace);
            }
        }

        return $value;
    }

    /**
     * Returns the updated nodeValue property.
     *
     * @return string
     */
    public function getNodeValue(): string
    {
        return $this->updateResult($this->nodeValue ?? '');
    }

    /**
     * Returns the updated textContent property.
     *
     * @return string
     */
    public function getTextContent(): string
    {
        return $this->updateResult($this->textContent ?? '');
    }

    /**
     * Returns the value for the attribute name specified.
     *
     * @param string $qualifiedName The attribute name.
     * @return string The attribute value.
     */
    public function getAttribute(string $qualifiedName): string
    {
        if ($this->attributes->length === 0) {
            return '';
        }

        $value = parent::getAttribute($qualifiedName);

        return ($value !== '' && \str_contains($value, 'html5-dom-document-internal-entity'))
            ? $this->updateResult($value)
            : $value;
    }

    /**
     * Returns an array containing all attributes.
     *
     * @return array<string, string> An associative array containing all attributes.
     */
    public function getAttributes(): array
    {
        $attributes = [];

        foreach ($this->attributes as $attributeName => $attribute) {
            $value = $attribute->value;
            $attributes[$attributeName] = ($value !== '' && \str_contains($value, 'html5-dom-document-internal-entity'))
                ? $this->updateResult($value)
                : $value;
        }

        return $attributes;
    }

    /**
     * Returns the element outerHTML.
     *
     * @return string The element outerHTML.
     */
    public function __toString(): string
    {
        return $this->getOuterHTML();
    }

    /**
     * Returns the first child element matching the selector.
     *
     * @param string $selector A CSS query selector.
     * @return ?self The result DOMElement or null if not found.
     */
    public function querySelector(string $selector): ?self
    {
        return $this->internalQuerySelector($selector);
    }

    /**
     * Returns a list of children elements matching the selector.
     *
     * @param string $selector A CSS query selector.
     * @return HTML5DOMNodeList Returns a list of DOMElements matching the criteria.
     */
    public function querySelectorAll(string $selector): HTML5DOMNodeList
    {
        return $this->internalQuerySelectorAll($selector);
    }
}
