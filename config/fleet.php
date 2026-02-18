<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fleet vehicle types (value stored in DB => label shown in UI)
    | Used for: Transport Store/Receiver (vehicle intake), Unit Head (Area
    | Controller), CD, OC Workshop, Staff Officer T&L, CC T&L (fleet request
    | create – New Vehicle), and anywhere else vehicle type is chosen.
    |--------------------------------------------------------------------------
    */
    'vehicle_types' => [
        'SEDAN' => 'Sedan (Saloon)',
        'SUV' => 'SUV',
        'HATCHBACK' => 'Hatchback',
        'COUPE' => 'Coupe',
        'CONVERTIBLE' => 'Convertible',
        'PICKUP' => 'Pickup Truck',
        'VAN' => 'Van',
        'MINIVAN' => 'Minivan (MPV)',
        'BUS' => 'Bus',
        'TRUCK' => 'Truck (Lorry)',
        'WAGON' => 'Wagon (Estate)',
        'CROSSOVER' => 'Crossover (CUV)',
        'JEEP' => 'Jeep',
        'LIMOUSINE' => 'Limousine',
        'MOTORCYCLE' => 'Motorcycle',
        'SCOOTER' => 'Scooter',
        'EV' => 'Electric Vehicle (EV)',
        'HYBRID' => 'Hybrid Vehicle',
    ],

];
