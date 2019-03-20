<?php
declare(strict_types=1);

namespace Paysera\Component\ObjectWrapper\Exception;

class InvalidItemTypeException extends InvalidItemException
{
    private $expectedType;
    private $givenType;

    public function __construct(string $expectedType, string $givenType, string $key, \Exception $previous = null)
    {
        parent::__construct(
            $key,
            sprintf('Expected %s but got %s for key "%s"', $expectedType, $givenType, $key),
            $previous
        );
        $this->expectedType = $expectedType;
        $this->givenType = $givenType;
    }

    public function getExpectedType(): string
    {
        return $this->expectedType;
    }

    public function getGivenType(): string
    {
        return $this->givenType;
    }
}
