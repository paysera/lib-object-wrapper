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
     * @param array<string, mixed> $expected
     */
    public function testExceptionDescribesTheItem(callable $createException, array $expected)
    {
        $exception = $createException();

        $this->assertSame($expected, [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'key' => $exception->getKey(),
            'expectedType' => $exception instanceof InvalidItemTypeException ? $exception->getExpectedType() : null,
            'givenType' => $exception instanceof InvalidItemTypeException ? $exception->getGivenType() : null,
            'previous' => $exception->getPrevious(),
        ]);
    }

    /**
     * @return array<string, array{callable, array<string, mixed>}>
     */
    public static function exceptionProvider(): array
    {
        $previous = new RuntimeException('cause');

        return [
            'invalid item with the key only' => [
                static function () {
                    return new InvalidItemException('a.b');
                },
                self::expected(InvalidItemException::class, 'Invalid key "a.b"', 'a.b'),
            ],
            'invalid item with null message and previous' => [
                static function () {
                    return new InvalidItemException('a.b', null, null);
                },
                self::expected(InvalidItemException::class, 'Invalid key "a.b"', 'a.b'),
            ],
            'invalid item with a message and a previous exception' => [
                static function () use ($previous) {
                    return new InvalidItemException('a.b', 'Unsupported value', $previous);
                },
                self::expected(InvalidItemException::class, 'Unsupported value', 'a.b', null, null, $previous),
            ],
            'invalid item with a replaced message' => [
                static function () {
                    return (new InvalidItemException('a.b'))->setMessage('Replaced');
                },
                self::expected(InvalidItemException::class, 'Replaced', 'a.b'),
            ],
            'invalid item type' => [
                static function () {
                    return new InvalidItemTypeException('string', 'integer', 'a.b');
                },
                self::expected(
                    InvalidItemTypeException::class,
                    'Expected string but got integer for key "a.b"',
                    'a.b',
                    'string',
                    'integer'
                ),
            ],
            'invalid item type with a previous exception' => [
                static function () use ($previous) {
                    return new InvalidItemTypeException('string', 'integer', 'a.b', $previous);
                },
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
                static function () {
                    return new MissingItemException('a.b');
                },
                self::expected(MissingItemException::class, 'Missing required key "a.b"', 'a.b'),
            ],
            'missing item with a previous exception' => [
                static function () use ($previous) {
                    return new MissingItemException('a.b', $previous);
                },
                self::expected(MissingItemException::class, 'Missing required key "a.b"', 'a.b', null, null, $previous),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
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
