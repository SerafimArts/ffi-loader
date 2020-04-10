<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Tests;

use Serafim\FFILoader\Preprocessor;

/**
 * Class PreprocessorTestCase
 */
class PreprocessorTestCase extends TestCase
{
    /**
     * @return array|string[][]
     */
    public function commentsDataProvider(): array
    {
        return [
            '//' => ['//'],
            '// Comment' => ['// Comment'],
            '/**/' => ['/**/'],
            '/* Comment */' => ['/* Comment */'],
        ];
    }

    /**
     * @dataProvider commentsDataProvider
     * @param string $comment
     * @return void
     */
    public function testCommentsProcessing(string $comment): void
    {
        $pre = new Preprocessor();

        // Enabled
        $this->assertSame($comment, $pre->source($comment));

        // Disabled
        $pre->keepComments = false;
        $this->assertSame('', $pre->source($comment));

        // Enable again
        $pre->keepComments = true;
        $this->assertSame($comment, $pre->source($comment));
    }

    /**
     * @return void
     */
    public function testInlineCommentsProcessing(): void
    {
        $pre = new Preprocessor();

        // Enabled
        $this->assertSame('// Comment', $pre->source('// Comment'));

        $pre->keepComments = false;

        $this->assertSame('', $pre->source('// Comment'));
    }

    /**
     * @return void
     */
    public function testExpressionNonDefinedIfdef(): void
    {
        $pre = new Preprocessor();

        $this->assertSame('', $pre->source(<<<'c'
            #ifdef E
                Hell Or Word!
            #endif
        c));
    }

    /**
     * @return void
     */
    public function testExpressionDefinedIfdef(): void
    {
        $pre = new Preprocessor();
        $pre->define('E');

        $this->assertSame('Hell Or Word!', \trim($pre->source(<<<'c'
            #ifdef E
                Hell Or Word!
            #endif
        c)));
    }

    /**
     * @return void
     */
    public function testExpressionNonDefinedIfndef(): void
    {
        $pre = new Preprocessor();

        $this->assertSame('Hell Or Word!', \trim($pre->source(<<<'c'
            #ifndef E
                Hell Or Word!
            #endif
        c)));
    }

    /**
     * @return void
     */
    public function testExpressionDefinedIfndef(): void
    {
        $pre = new Preprocessor();
        $pre->define('E');

        $this->assertSame('', $pre->source(<<<'c'
            #ifndef E
                Hell Or Word!
            #endif
        c));
    }

    /**
     * @return void
     */
    public function testExpressionUnknownFileInclude(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Can not include "kernel32.h"');

        (new Preprocessor())->source('#include <kernel32.h>');
    }

    /**
     * @return void
     */
    public function testExpressionInclude(): void
    {
        $expect = \file_get_contents(__DIR__ . '/../resources/kernel32.h');

        $pre = new Preprocessor();
        $pre->includeFrom(__DIR__ . '/../resources');

        $this->assertSame($expect, $pre->source('#include <kernel32.h>'));
    }

    /**
     * @return void
     */
    public function testPositiveIfComparation(): void
    {
        $pre = new Preprocessor();

        $this->assertSame('extern void example(void);', \trim($pre->source(<<<'c'
            #if 3 > 2
                extern void example(void);
            #endif
        c)));
    }

    /**
     * @return void
     */
    public function testPositiveNestedComparation(): void
    {
        $pre = new Preprocessor();

        $this->assertSame('extern void example(void);', \trim($pre->source(<<<'c'
            #if 3 > 2
                #if 4.3.1 > 1.2.0
                    extern void example(void);
                #endif
            #endif
        c)));
    }

    /**
     * @return void
     */
    public function testNegativeLevel1NestedComparation(): void
    {
        $pre = new Preprocessor();

        $this->assertSame('', \trim($pre->source(<<<'c'
            #if false
                #if true
                    extern void example(void);
                #endif
            #endif
        c)));
    }

    /**
     * @return void
     */
    public function testNegativeLevel2NestedComparation(): void
    {
        $pre = new Preprocessor();

        $this->assertSame('', \trim($pre->source(<<<'c'
            #if true
                #if false
                    extern void example(void);
                #endif
            #endif
        c)));
    }

    /**
     * @return void
     */
    public function testNegativeIfComparation(): void
    {
        $pre = new Preprocessor();

        $this->assertSame('', \trim($pre->source(<<<'c'
            #if 3 == 2
                extern void example(void);
            #endif
        c)));
    }

    /**
     * @return void
     */
    public function testSyntaxTolerantUnclosedStmt(): void
    {
        $pre = new Preprocessor();

        $this->assertSame('', $pre->source('#if 42'));
    }

    /**
     * @return void
     */
    public function testSyntaxUnclosedStmt(): void
    {
        $this->expectExceptionMessage('Missing closing (#endif) directive');

        $pre = new Preprocessor();
        $pre->tolerant = false;

        $pre->source(<<<'c'
            #if 42
        c);
    }

    /**
     * @return void
     */
    public function testSyntaxTolerantExtraStmt(): void
    {
        $pre = new Preprocessor();
        $this->assertSame('', $pre->source('#endif'));
    }

    /**
     * @return void
     */
    public function testSyntaxExtraStmt(): void
    {
        $this->expectExceptionMessage('An extra closing (#endif) directive');

        $pre = new Preprocessor();
        $pre->tolerant = false;

        $pre->source(<<<'c'
            #endif
        c);
    }
}
