<?php
declare(strict_types=1);

namespace Paysera\Component\ObjectWrapper\Exception;

class InvalidItemException extends \Exception
{
    private $key;

    public function __construct(string $key, string $message = null, \Exception $previous = null)
    {
        parent::__construct($message ?? sprintf('Invalid key "%s"', $key), 0, $previous);
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message): self
    {
        $this->message = $message;

        return $this;
    }
}
