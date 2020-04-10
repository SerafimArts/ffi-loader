<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Preprocessor;

use Phplrt\Contracts\Lexer\LexerInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Serafim\FFILoader\Preprocessor;

/**
 * Class Expression
 */
class Expression
{
    /**
     * @var string[]
     */
    private const STATE_OPERAND = [
        'T_VERSION',
        'T_DIGIT',
        'T_BOOL',
        'T_STRING',
    ];

    /**
     * @var string[]
     */
    private const STATE_OPERATOR = [
        'T_GTE',
        'T_GT',
        'T_LTE',
        'T_LT',
        'T_EQ',
        'T_NEQ',
    ];

    /**
     * @var LexerInterface
     */
    private LexerInterface $expr;

    /**
     * @var Preprocessor
     */
    private Preprocessor $pre;

    /**
     * Expression constructor.
     *
     * @param Preprocessor $pre
     */
    public function __construct(Preprocessor $pre)
    {
        $this->expr = new ExpressionLexer();
        $this->pre = $pre;
    }

    /**
     * @param string $expr
     * @return bool
     * @throws \Throwable
     */
    public function eval(string $expr): bool
    {
        $stack = new \SplStack();
        $operator = null;

        try {
            foreach ($this->expr->lex($expr) as $token) {
                $stack->push($token);

                switch ($stack->count()) {
                    case 1:
                    case 3:
                        $this->assertState($stack, self::STATE_OPERAND);
                        continue 2;

                    case 2:
                        $this->assertState($stack, self::STATE_OPERATOR);
                        continue 2;

                    default:
                        $error = 'Unsupported expression type at "' . $token->getValue() . '"';
                        throw new \LogicException($error);
                }
            }

            return $this->reduce($stack);
        } catch (\Throwable $e) {
            if ($this->pre->tolerant === false) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * @param \SplStack $stack
     * @return bool
     */
    private function reduce(\SplStack $stack): bool
    {
        if (\count($stack) === 1) {
            /** @var TokenInterface $top */
            $top = $stack->top();

            return \strtolower($top->getValue()) === 'false'
                ? false
                : (bool)$top->getValue();
        }

        $fn = $this->fn($stack->bottom(), $stack->pop());

        return $this->compare($fn, $stack->top());
    }

    /**
     * @param \SplStack $stack
     * @param array $allowed
     * @return void
     */
    private function assertState(\SplStack $stack, array $allowed): void
    {
        /** @var TokenInterface $last */
        $last = $stack->top();

        if (\in_array($last->getName(), $allowed, true)) {
            return;
        }

        throw new \LogicException('Unexpected token "' . $last->getName() . '"');
    }

    /**
     * @param TokenInterface ...$tokens
     * @return int
     */
    private function fn(TokenInterface ...$tokens): int
    {
        $names = \array_map(fn (TokenInterface $t): string => $t->getName(), $tokens);
        $values = \array_map(fn (TokenInterface $t): string => $t->getValue(), $tokens);

        switch (\count($values)) {
            case 1:
                return $values[0];

            case 2:
                return \in_array('T_VERSION', $names, true)
                    ? \version_compare(...$values)
                    : $values[0] <=> $values[1];

            default:
                return 0;
        }
    }

    /**
     * @param int $result
     * @param TokenInterface $operator
     * @return bool
     */
    private function compare(int $result, TokenInterface $operator): bool
    {
        switch ($operator->getName()) {
            case 'T_GT':
                return $result > 0;

            case 'T_LT':
                return $result < 0;

            case 'T_GTE':
                return $result >= 0;

            case 'T_LTE':
                return $result <= 0;

            case 'T_EQ':
                return $result === 0;

            case 'T_NEQ':
                return $result !== 0;

            default:
                throw new \LogicException('Unsupported operator: ' . $operator->getValue());
        }
    }
}
