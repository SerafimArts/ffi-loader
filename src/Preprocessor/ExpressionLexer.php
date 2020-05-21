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
 * Class ExpressionLexer
 */
class ExpressionLexer extends AbstractLexer
{
    /**
     * @var string[]
     */
    private const LEXEMES = [
        // Operands
        'T_VERSION'    => '\d+(?:\.\d+)+',
        'T_DIGIT'      => '\d+',
        'T_BOOL'       => 'true|false',
        'T_STRING'       => 'true|false',
        // Operators
        'T_GTE'        => '>=',
        'T_GT'         => '>',
        'T_LTE'        => '<=',
        'T_LT'         => '<',
        'T_EQ'         => '==',
        'T_NEQ'        => '!=',
        // Other
        'T_WHITESPACE' => '\s+',
        'T_UNKNOWN'    => '.+?',
    ];

    /**
     * ExpressionLexer constructor.
     */
    public function __construct()
    {
        $this->pcre = $this->compile(self::LEXEMES);
    }

    /**
     * @param string $source
     * @param int $offset
     * @return iterable|TokenInterface[]
     */
    public function lex($source, int $offset = 0): iterable
    {
        foreach (parent::lex($source, $offset) as $token) {
            switch ($token->getName()) {
                case 'T_WHITESPACE':
                    continue 2;

                case 'T_UNKNOWN':
                    throw new \LogicException('Unexpected lexeme "' . $token->getValue() . '"');

                default:
                    yield $token;
            }
        }
    }
}
