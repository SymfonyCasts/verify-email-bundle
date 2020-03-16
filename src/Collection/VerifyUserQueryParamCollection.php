<?php

/*
 * This file is part of the SymfonyCasts BUNDLE_NAME_HERE package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyUser\Collection;

use SymfonyCasts\Bundle\VerifyUser\Model\VerifyUserQueryParam;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 *
 * @internal
 */
final class VerifyUserQueryParamCollection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private $elements = [];

    public function createParam(string $key, string $value): void
    {
        $this->elements[] = new VerifyUserQueryParam($key, $value);
    }

    public function add(VerifyUserQueryParam $queryParam): void
    {
        $this->elements[] = $queryParam;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->elements[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->elements[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->elements[] = $value;

            return;
        }

        $this->elements[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->elements[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->elements);
    }
}
