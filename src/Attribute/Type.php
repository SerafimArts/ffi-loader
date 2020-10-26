<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Attribute;

use JetBrains\PhpStorm\Immutable;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER)]
final class Type implements \Stringable
{
    /**
     * @readonly
     * @var string
     */
    #[Immutable]
    public string $name;

    /**
     * @readonly
     * @var bool
     */
    #[Immutable]
    public bool $const = false;

    /**
     * @param string $name
     */
    public function __construct(string $name, bool $const = false)
    {
        $this->name = $name;
        $this->const = $const;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return ($this->const ? 'const ' : '') . $this->name;
    }
}
