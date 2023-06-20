<?php
namespace Pimcorecasts\Bundle\HashCash\Event;

use Symfony\Contracts\EventDispatcher\Event;

class HashCashInvalidEvent extends Event
{
    protected string $errorMessage;
    
    public function __construct(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    

}
