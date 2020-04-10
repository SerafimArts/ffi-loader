<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Preprocessor;

/**
 * Class Lexer
 */
final class Lexer extends AbstractLexer
{
    /**
     * @var string[]
     */
    private const LEXEMES = [
        'T_GROUP_COMMENT'  => '\\/\\*.*?\\*\\/',
        'T_COMMENT'        => '^\\h*\\/\\/[^\\n]*',
        'T_LOCAL_INCLUDE'  => '^\\h*#\\h*include\\h+"\\h*(.+?)\\h*"',
        'T_GLOBAL_INCLUDE' => '^\\h*#\\h*include\\h+<\\h*(.+?)\\h*>',
        'T_DEFINE'         => '^\\h*#\\h*define\\h+(\w+\h*(?:\\\\s|\\\\\n|[^\\n])*)$',
        'T_UNDEF'          => '^\\h*#\\h*undef\\h+(\w+)$',
        'T_IFDEF'          => '^\\h*#\\h*ifdef\\h+((?:\\\\s|\\\\\n|[^\\n])+)',
        'T_IFNDEF'         => '^\\h*#\\h*ifndef\\h+((?:\\\\s|\\\\\n|[^\\n])+)',
        'T_ENDIF'          => '^\\h*#\\h*endif\\b',
        'T_IF'             => '^\\h*#\\h*if\\h+((?:\\\\s|[^\\n])+)',
        'T_SOURCE'         => '[^\\n]+',
        'T_NEW_LINE'       => '\\n+',
    ];

    /**
     * Lexer constructor.
     */
    public function __construct()
    {
        $this->pcre = $this->compile(self::LEXEMES);
    }
}
