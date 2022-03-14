<?php

namespace Dealt\DealtSDK\Services;

use Dealt\DealtSDK\DealtClient;
use Exception;

class DealtServiceFactory
{
    private static $classMap = [
        'offers'   => DealtOffers::class,
        'missions' => DealtMissions::class,
    ];

    private $client;
    private $services;

    public function __construct(DealtClient $client)
    {
        $this->client   = $client;
        $this->services = [];
    }

    protected function getServiceClass(string $name)
    {
        return array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }

    /**
     * Undocumented function.
     *
     * @throws Exception
     */
    public function __get(string $name): AbstractDealtService
    {
        $serviceClass = $this->getServiceClass($name);
        if (null !== $serviceClass) {
            if (!array_key_exists($name, $this->services)) {
                $this->services[$name] = new $serviceClass($this->client);
            }

            return $this->services[$name];
        }

        throw new Exception('unknown service requested');
    }
}