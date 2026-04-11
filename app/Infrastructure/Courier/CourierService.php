<?php

namespace App\Infrastructure\Courier;

use App\Infrastructure\Courier\Drivers\CarryBeeCourier;
use App\Infrastructure\Courier\Drivers\PathaoCourier;
use App\Infrastructure\Courier\Drivers\SteadfastCourier;
use InvalidArgumentException;

class CourierService
{
    /**
     * Supported courier driver names.
     */
    public const DRIVERS = ['pathao', 'steadfast', 'carrybee'];

    /**
     * Resolve a courier driver by name.
     */
    public function driver(?string $name = null): CourierInterface
    {
        $name = $name ?? config('courier.default', 'pathao');

        return match ($name) {
            'pathao'    => app(PathaoCourier::class),
            'steadfast' => app(SteadfastCourier::class),
            'carrybee'  => app(CarryBeeCourier::class),
            default     => throw new InvalidArgumentException("Unsupported courier driver: {$name}"),
        };
    }

    /**
     * Get list of available courier options for admin UI.
     */
    public function availableDrivers(): array
    {
        return [
            ['value' => 'pathao',    'label' => 'Pathao Courier'],
            ['value' => 'steadfast', 'label' => 'Steadfast Delivery'],
            ['value' => 'carrybee',  'label' => 'CarryBee Logistics'],
        ];
    }
}
