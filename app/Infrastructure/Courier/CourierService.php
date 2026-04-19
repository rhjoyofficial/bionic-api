<?php

namespace App\Infrastructure\Courier;

use App\Infrastructure\Courier\Drivers\CarryBeeCourier;
use App\Infrastructure\Courier\Drivers\PathaoCourier;
use App\Infrastructure\Courier\Drivers\RedxCourier;
use App\Infrastructure\Courier\Drivers\SteadfastCourier;
use InvalidArgumentException;

class CourierService
{
    public const DRIVERS = ['pathao', 'steadfast', 'redx', 'carrybee'];

    public function driver(?string $name = null): CourierInterface
    {
        $name = $name ?? config('courier.default', 'pathao');

        return match ($name) {
            'pathao'    => app(PathaoCourier::class),
            'steadfast' => app(SteadfastCourier::class),
            'redx'      => app(RedxCourier::class),
            'carrybee'  => app(CarryBeeCourier::class),
            default     => throw new InvalidArgumentException("Unsupported courier driver: {$name}"),
        };
    }

    public function availableDrivers(): array
    {
        return [
            ['value' => 'pathao',    'label' => 'Pathao',     'needs_location' => true],
            ['value' => 'steadfast', 'label' => 'Steadfast',  'needs_location' => false],
            ['value' => 'redx',      'label' => 'RedX',       'needs_location' => false],
            ['value' => 'carrybee',  'label' => 'CarryBee',   'needs_location' => false],
        ];
    }
}
