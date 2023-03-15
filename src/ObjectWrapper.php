<?php

declare(strict_types=1);

namespace Paysera\Component\ObjectWrapper;

use ArrayIterator;
use RuntimeException;
use stdClass;
use IteratorAggregate;
use ArrayAccess;
use Paysera\Component\ObjectWrapper\Exception\InvalidItemTypeException;
use Paysera\Component\ObjectWrapper\Exception\MissingItemException;

class ObjectWrapper implements ArrayAccess, IteratorAggregate
{
    private stdClass $data;
    private stdClass $originalData;
    private array $path;

    public function __construct(stdClass $data, array $path = [])
    {
        $this->path = $path;
        $this->data = clone $data;
        $this->originalData = $data;

        $this->processData();
    }

    private function processData(): void
    {
        foreach ($this->data as $key => &$item) {
            $item = $this->processItem($item, [$key]);
        }
    }

    private function processItem(mixed $item, array $keys): mixed
    {
        if ($item instanceof stdClass) {
            return new self($item, array_merge($this->path, $keys));
        } elseif (is_array($item)) {
            return $this->processArray($item, $keys);
        }

        return $item;
    }

    private function processArray(array $data, array $keys): array
    {
        foreach ($data as $i => &$item) {
            $item = $this->processItem($item, array_merge($keys, [(string)$i]));
        }

        return $data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data->$offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data->$offset ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Modifying ObjectWrapper is not allowed');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Modifying ObjectWrapper is not allowed');
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @throws MissingItemException
     */
    public function getRequired(string $key): mixed
    {
        if (!isset($this->data->$key)) {
            throw new MissingItemException($this->buildKey($key));
        }

        return $this->data->$key;
    }

    public function getRequiredBool(string $key): bool
    {
        return $this->getRequiredOfType($key, 'boolean');
    }

    public function getBool(string $key, ?bool $default = null): ?bool
    {
        return $this->getOfType($key, 'boolean', $default);
    }

    public function getRequiredFloat(string $key): float
    {
        return $this->getRequiredOfType($key, 'float');
    }

    public function getFloat(string $key, ?float $default = null): ?float
    {
        return $this->getOfType($key, 'float', $default);
    }

    public function getRequiredInt(string $key): int
    {
        return $this->getRequiredOfType($key, 'integer');
    }

    public function getInt(string $key, ?int $default = null): ?int
    {
        return $this->getOfType($key, 'integer', $default);
    }

    public function getRequiredObject(string $key): mixed
    {
        return $this->getRequiredOfType($key, 'object');
    }

    public function getObject(string $key): ?ObjectWrapper
    {
        return $this->getOfType($key, 'object', null);
    }

    public function getRequiredString(string $key): string
    {
        return $this->getRequiredOfType($key, 'string');
    }

    public function getString(string $key, ?string $default = null): ?string
    {
        return $this->getOfType($key, 'string', $default);
    }

    public function getArray(string $key, array $default = []): array
    {
        return $this->getOfType($key, 'array', $default);
    }

    public function getOriginalData(): stdClass
    {
        return $this->originalData;
    }

    public function getDataAsArray(): array
    {
        return $this->recursiveToArray($this);
    }

    private function recursiveToArray(ArrayAccess|array $inputData): array
    {
        $data = [];
        foreach ($inputData as $key => $item) {
            if ($item instanceof ArrayAccess || is_array($item)) {
                $data[$key] = $this->recursiveToArray($item);
            } else {
                $data[$key] = $item;
            }
        }

        return $data;
    }

    private function getRequiredOfType(string $key, string $expectedType): mixed
    {
        $value = $this->getRequired($key);

        return $this->assertValueType($value, $expectedType, $key);
    }

    private function getOfType(string $key, string $expectedType, $default): mixed
    {
        $value = $this[$key];
        if ($value === null) {
            return $default;
        }

        return $this->assertValueType($value, $expectedType, $key);
    }

    /**
     * @param mixed $value
     * @param string $expectedType
     * @param string $key
     *
     * @return mixed
     * @throws InvalidItemTypeException
     */
    private function assertValueType(mixed $value, string $expectedType, string $key): mixed
    {
        $givenType = gettype($value);
        if ($givenType === 'double') {
            $givenType = 'float';
        }

        if ($expectedType === 'boolean') {
            $boolean = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($boolean === null) {
                throw new InvalidItemTypeException($expectedType, $givenType, $this->buildKey($key));
            }

            return $boolean;
        }

        if ($givenType !== $expectedType && !(is_int($value) && $expectedType === 'float')) {
            throw new InvalidItemTypeException($expectedType, $givenType, $this->buildKey($key));
        }

        if ($expectedType === 'float') {
            return (float)$value;
        }

        return $value;
    }

    public function getArrayOfBool(string $key): array
    {
        return $this->getArrayOfType($key, 'boolean');
    }

    public function getArrayOfFloat(string $key): array
    {
        return $this->getArrayOfType($key, 'float');
    }

    public function getArrayOfInt(string $key): array
    {
        return $this->getArrayOfType($key, 'integer');
    }

    public function getArrayOfString(string $key): array
    {
        return $this->getArrayOfType($key, 'string');
    }

    /**
     * @param string $key
     * @return array|ObjectWrapper[]
     */
    public function getArrayOfObject(string $key): array
    {
        return $this->getArrayOfType($key, 'object');
    }

    private function getArrayOfType(string $key, string $expectedType): array
    {
        $value = $this->getArray($key);

        return array_map(function ($item) use ($expectedType, $key) {
            return $this->assertValueType($item, $expectedType, $key);
        }, $value);
    }

    private function buildKey(string $key): string
    {
        return implode('.', array_merge($this->path, [$key]));
    }
}
