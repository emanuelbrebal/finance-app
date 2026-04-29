<?php

return [
    'checkpoints' => [
        \App\Domain\Wishlist\Checkpoints\QuarantineCheckpoint::class,
        \App\Domain\Wishlist\Checkpoints\EmergencyFundCheckpoint::class,
        \App\Domain\Wishlist\Checkpoints\PositiveSavingsRateCheckpoint::class,
        \App\Domain\Wishlist\Checkpoints\GoalImpactCheckpoint::class,
        \App\Domain\Wishlist\Checkpoints\StillWantedCheckpoint::class,
    ],
];
