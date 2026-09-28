<?php
declare(strict_types=1);

namespace Paysera\Component\ObjectWrapper\Tests\Exception;

use Paysera\Component\ObjectWrapper\Exception\InvalidItemException;
use Paysera\Component\ObjectWrapper\Exception\InvalidItemTypeException;
use Paysera\Component\ObjectWrapper\Exception\MissingItemException;
use PHPUnit\Framework\TestCase;

class InvalidItemExceptionTest extends TestCase
{
    /**
     * @dataProvider exceptionProvider
     * @param array<string, mixed> $expected
     */
    public function testExceptionDescribesTheItem(callable $createException, array $expected): void
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
        return [
            'invalid item with null message and previous' => [
                static function () {
                    return new InvalidItemException('a.b', null, null);
                },
                self::expected(InvalidItemException::class, 'Invalid key "a.b"', 'a.b'),
            ],
            'invalid item type with a null previous exception' => [
                static function () {
                    return new InvalidItemTypeException('string', 'integer', 'a.b', null);
                },
                self::expected(
                    InvalidItemTypeException::class,
                    'Expected string but got integer for key "a.b"',
                    'a.b',
                    'string',
                    'integer'
                ),
            ],
            'missing item with a null previous exception' => [
                static function () {
                    return new MissingItemException('a.b', null);
                },
                self::expected(MissingItemException::class, 'Missing required key "a.b"', 'a.b'),
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
        ?string $givenType = null
    ): array {
        return [
            'class' => $class,
            'message' => $message,
            'key' => $key,
            'expectedType' => $expectedType,
            'givenType' => $givenType,
            'previous' => null,
        ];
    }
}
