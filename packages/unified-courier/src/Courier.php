<?php

namespace Refatbd\Courier;

use Refatbd\Courier\Contracts\CourierAdapterInterface;
use Refatbd\Courier\Adapters\SteadfastAdapter;
use Refatbd\Courier\Adapters\RedXAdapter;
use Refatbd\Courier\Adapters\PathaoAdapter;
use Refatbd\Courier\Adapters\PaperflyAdapter;
use Refatbd\Courier\Adapters\CarrybeeAdapter;
use Refatbd\Courier\Adapters\EcourierAdapter;
use InvalidArgumentException;

class Courier
{
    /**
     * Factory create a concrete courier adapter instance.
     *
     * @param string $provider (steadfast, redx, pathao, paperfly, carrybee, ecourier)
     * @param array $config
     * @return CourierAdapterInterface
     */
    public static function provider(string $provider, array $config = []): CourierAdapterInterface
    {
        $normalized = strtolower(trim($provider));

        switch ($normalized) {
            case 'steadfast':
                return new SteadfastAdapter($config);

            case 'redx':
                return new RedXAdapter($config);

            case 'pathao':
                return new PathaoAdapter($config);

            case 'paperfly':
                return new PaperflyAdapter($config);

            case 'carrybee':
                return new CarrybeeAdapter($config);

            case 'ecourier':
                return new EcourierAdapter($config);

            default:
                throw new InvalidArgumentException("Unsupported courier provider: '{$provider}'. Supported providers: steadfast, redx, pathao, paperfly, carrybee, ecourier.");
        }
    }

    /**
     * Alias for provider().
     *
     * @param string $provider
     * @param array $config
     * @return CourierAdapterInterface
     */
    public static function make(string $provider, array $config = []): CourierAdapterInterface
    {
        return self::provider($provider, $config);
    }
}
