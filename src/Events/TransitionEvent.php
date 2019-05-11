<?php

namespace Resiliency\Events;

use Symfony\Component\EventDispatcher\Event;

class TransitionEvent extends Event
{
    /**
     * @var string the Transition name
     */
    private $eventName;

    /**
     * @var string the Service URI
     */
    private $service;

    /**
     * @var array the Service parameters
     */
    private $parameters;

    /**
     * @param string $eventName the transition name
     * @param string $service the Service URI
     * @param array $parameters the Service parameters
     */
    public function __construct($eventName, $service, array $parameters)
    {
        $this->eventName = $eventName;
        $this->service = $service;
        $this->parameters = $parameters;
    }

    /**
     * @return string the Transition name
     */
    public function getEvent(): string
    {
        return $this->eventName;
    }

    /**
     * @return string the Service URI
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * @return array the Service parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
