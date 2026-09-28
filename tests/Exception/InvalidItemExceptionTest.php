<?php
declare(strict_types=1);

namespace Paysera\Component\ObjectWrapper\Tests\Exception;

use Paysera\Component\ObjectWrapper\Exception\InvalidItemException;
use Paysera\Component\ObjectWrapper\Exception\InvalidItemTypeException;
use Paysera\Component\ObjectWrapper\Exception\MissingItemException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class InvalidItemExceptionTest extends TestCase
{
    /**
     * @dataProvider exceptionProvider
     */
    public function testExceptionDescribesTheItem(InvalidItemException $exception, array $expected)
    {
        $this->assertSame($expected, [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'key' => $exception->getKey(),
            'expectedType' => $exception instanceof InvalidItemTypeException ? $exception->getExpectedType() : null,
            'givenType' => $exception instanceof InvalidItemTypeException ? $exception->getGivenType() : null,
            'previous' => $exception->getPrevious(),
        ]);
    }

    public static function exceptionProvider(): array
    {
        $previous = new RuntimeException('cause');

        return [
            'invalid item with the key only' => [
                new InvalidItemException('a.b'),
                self::expected(InvalidItemException::class, 'Invalid key "a.b"', 'a.b'),
            ],
            'invalid item with null message and previous' => [
                new InvalidItemException('a.b', null, null),
                self::expected(InvalidItemException::class, 'Invalid key "a.b"', 'a.b'),
            ],
            'invalid item with a message and a previous exception' => [
                new InvalidItemException('a.b', 'Unsupported value', $previous),
                self::expected(InvalidItemException::class, 'Unsupported value', 'a.b', null, null, $previous),
            ],
            'invalid item with a replaced message' => [
                (new InvalidItemException('a.b'))->setMessage('Replaced'),
                self::expected(InvalidItemException::class, 'Replaced', 'a.b'),
            ],
            'invalid item type' => [
                new InvalidItemTypeException('string', 'integer', 'a.b'),
                self::expected(
                    InvalidItemTypeException::class,
                    'Expected string but got integer for key "a.b"',
                    'a.b',
                    'string',
                    'integer'
                ),
            ],
            'invalid item type with a previous exception' => [
                new InvalidItemTypeException('string', 'integer', 'a.b', $previous),
                self::expected(
                    InvalidItemTypeException::class,
                    'Expected string but got integer for key "a.b"',
                    'a.b',
                    'string',
                    'integer',
                    $previous
                ),
            ],
            'missing item' => [
                new MissingItemException('a.b'),
                self::expected(MissingItemException::class, 'Missing required key "a.b"', 'a.b'),
            ],
            'missing item with a previous exception' => [
                new MissingItemException('a.b', $previous),
                self::expected(MissingItemException::class, 'Missing required key "a.b"', 'a.b', null, null, $previous),
            ],
        ];
    }

    private static function expected(
        string $class,
        string $message,
        string $key,
        ?string $expectedType = null,
        ?string $givenType = null,
        ?RuntimeException $previous = null
    ): array {
        return [
            'class' => $class,
            'message' => $message,
            'key' => $key,
            'expectedType' => $expectedType,
            'givenType' => $givenType,
            'previous' => $previous,
        ];
    }
}
