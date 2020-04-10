<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Preprocessor;

use Phplrt\Contracts\Lexer\TokenInterface;

/**
 * Class Token
 */
final class Token implements TokenInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $value;

    /**
     * @var int
     */
    private int $offset;

    /**
     * Token constructor.
     *
     * @param string $name
     * @param string $value
     * @param int $offset
     */
    public function __construct(string $name, string $value, int $offset)
    {
        $this->name = $name;
        $this->value = $value;
        $this->offset = $offset;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function getBytes(): int
    {
        return \strlen($this->value);
    }
}
