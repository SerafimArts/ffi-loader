<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Attribute;

/**
 * @internal Reader is an internal library class, please do not use it in your code.
 * @psalm-internal Serafim\FFILoader
 *
 * @psalm-type ReflectionAttributeProvider = \ReflectionClass
 *                                         | \ReflectionFunctionAbstract
 *                                         | \ReflectionParameter
 *                                         | \ReflectionClassConstant
 *                                         | \ReflectionProperty
 */
final class Reader
{
    /**
     * @template T
     * @psalm-param ReflectionAttributeProvider $ref
     * @psalm-param class-string<T>|null $class
     * @psalm-return T|null
     *
     * @param \Reflector $ref
     * @param string|null $class
     * @param int $flags
     * @return object|null
     */
    public static function getAttributeInstance(\Reflector $ref, string $class = null, int $flags = 0): ?object
    {
        if ($attribute = self::getAttribute($ref, $class, $flags)) {
            return $attribute->newInstance();
        }

        return null;
    }

    /**
     * @psalm-param ReflectionAttributeProvider $ref
     *
     * @param \Reflector $ref
     * @param string|null $class
     * @param int $flags
     * @return \ReflectionAttribute|null
     */
    public static function getAttribute(\Reflector $ref, string $class = null, int $flags = 0): ?\ReflectionAttribute
    {
        $attributes = $ref->getAttributes($class, $flags);

        if (\count($attributes) === 0) {
            return null;
        }

        return \reset($attributes);
    }
}
