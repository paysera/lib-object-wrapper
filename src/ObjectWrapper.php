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
use Traversable;

class ObjectWrapper implements ArrayAccess, IteratorAggregate
{
    private $data;
    private $originalData;
    private $path;

    public function __construct(stdClass $data, $path = [])
    {
        $this->path = $path;
        $this->data = clone $data;
        $this->originalData = $data;

        $this->processData();
    }

    private function processData()
    {
        foreach ($this->data as $key => &$item) {
            $item = $this->processItem($item, [$key]);
        }
    }

    private function processItem($item, array $keys)
    {
        if ($item instanceof stdClass) {
            return new self($item, array_merge($this->path, $keys));
        } elseif (is_array($item)) {
            return $this->processArray($item, $keys);
        }

        return $item;
    }

    private function processArray(array $data, array $keys)
    {
        foreach ($data as $i => &$item) {
            $item = $this->processItem($item, array_merge($keys, [(string)$i]));
        }

        return $data;
    }

    public function offsetExists($key): bool
    {
        return isset($this->data->$key);
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return isset($this->data->$key) ? $this->data->$key : null;
    }

    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('Modifying ObjectWrapper is not allowed');
    }

    public function offsetUnset($offset): void
    {
        throw new RuntimeException('Modifying ObjectWrapper is not allowed');
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function getRequired(string $key)
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

    /**
     * @param string $key
     * @param bool|null $default
     * @return bool|null
     */
    public function getBool(string $key, bool $default = null)
    {
        return $this->getOfType($key, 'boolean', $default);
    }

    public function getRequiredFloat(string $key): float
    {
        return $this->getRequiredOfType($key, 'float');
    }

    /**
     * @param string $key
     * @param float|null $default
     * @return float|null
     */
    public function getFloat(string $key, float $default = null)
    {
        return $this->getOfType($key, 'float', $default);
    }

    public function getRequiredInt(string $key): int
    {
        return $this->getRequiredOfType($key, 'integer');
    }

    /**
     * @param string $key
     * @param int|null $default
     * @return int|null
     */
    public function getInt(string $key, int $default = null)
    {
        return $this->getOfType($key, 'integer', $default);
    }

    public function getRequiredObject(string $key): self
    {
        return $this->getRequiredOfType($key, 'object');
    }

    /**
     * @param string $key
     * @return ObjectWrapper|null
     */
    public function getObject(string $key)
    {
        return $this->getOfType($key, 'object', null);
    }

    public function getRequiredString(string $key): string
    {
        return $this->getRequiredOfType($key, 'string');
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function getString(string $key, string $default = null)
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
        return $this->getObjectWrapperAsArray($this);
    }

    private function getObjectWrapperAsArray(ObjectWrapper $objectWrapper)
    {
        $data = [];
        foreach ($objectWrapper as $key => $item) {
            if ($item instanceof ObjectWrapper) {
                $data[$key] = $this->getObjectWrapperAsArray($item);
                continue;
            } elseif (is_array($item)) {
                $data[$key] = $this->getObjectWrapperFromArray($item);
            } else {
                $data[$key] = $item;
            }
        }

        return $data;
    }

    private function getObjectWrapperFromArray(array $list)
    {
        $data = [];
        foreach ($list as $key => $item) {
            if ($item instanceof ObjectWrapper) {
                $data[$key] = $this->getObjectWrapperAsArray($item);
            } elseif (is_array($item)) {
                $data[$key] = $this->getObjectWrapperFromArray($item);
            } else {
                $data[$key] = $item;
            }
        }

        return $data;
    }

    private function getRequiredOfType(string $key, string $expectedType)
    {
        $value = $this->getRequired($key);

        return $this->assertValueType($value, $expectedType, $key);
    }

    private function getOfType(string $key, string $expectedType, $default)
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
     * @return mixed
     * @throws InvalidItemTypeException
     */
    private function assertValueType($value, string $expectedType, string $key)
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

    private function getArrayOfType(string $key, string $expectedType)
    {
        $value = $this->getArray($key);

        return array_map(function ($item) use ($expectedType, $key) {
            return $this->assertValueType($item, $expectedType, $key);
        }, $value);
    }

    private function buildKey(string $key)
    {
        return implode('.', array_merge($this->path, [$key]));
    }
}
