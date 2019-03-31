<?php

namespace Resiliency;

use Resiliency\Contracts\Place;
use Resiliency\Contracts\Client;
use Resiliency\Systems\MainSystem;
use Resiliency\Storages\SimpleArray;
use Resiliency\Exceptions\UnavailableService;

/**
 * Main implementation of Circuit Breaker.
 */
final class SimpleCircuitBreaker extends PartialCircuitBreaker
{
    public function __construct(
        Place $openPlace,
        Place $halfOpenPlace,
        Place $closedPlace,
        Client $client
    ) {
        $system = new MainSystem($closedPlace, $halfOpenPlace, $openPlace);

        parent::__construct($system, $client, new SimpleArray());
    }

    /**
     * {@inheritdoc}
     */
    public function call($service, callable $fallback, $serviceParameters = [])
    {
        $transaction = $this->initTransaction($service);

        try {
            if ($this->isOpened()) {
                if ($this->canAccessService($transaction)) {
                    $this->moveStateTo(States::HALF_OPEN_STATE, $service);
                }

                return \call_user_func($fallback);
            }

            $response = $this->request($service);
            $this->moveStateTo(States::CLOSED_STATE, $service);

            return $response;
        } catch (UnavailableService $exception) {
            $transaction->incrementFailures();
            $this->storage->saveTransaction($service, $transaction);

            if (!$this->isAllowedToRetry($transaction)) {
                $this->moveStateTo(States::OPEN_STATE, $service);

                return \call_user_func($fallback);
            }

            return $this->call($service, $fallback, $serviceParameters);
        }
    }
}
