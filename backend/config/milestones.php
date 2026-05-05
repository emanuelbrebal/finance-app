<?php

return [
    'detectors' => [
        \App\Domain\Milestones\Detectors\NetWorthDetector::class,
        \App\Domain\Milestones\Detectors\BehaviorDetector::class,
        \App\Domain\Milestones\Detectors\FinancialHealthDetector::class,
        \App\Domain\Milestones\Detectors\ResistanceDetector::class,
        \App\Domain\Milestones\Detectors\JourneyTransitionDetector::class,
    ],
];
