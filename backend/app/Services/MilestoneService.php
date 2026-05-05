<?php

namespace App\Services;

use App\Domain\Milestones\Contracts\MilestoneDetector;
use App\Models\Milestone;
use App\Models\User;

class MilestoneService
{
    /** @param array<MilestoneDetector> $detectors */
    public function __construct(private readonly array $detectors) {}

    public function detectFor(User $user): int
    {
        $created = 0;

        foreach ($this->detectors as $detector) {
            foreach ($detector->detect($user) as $dto) {
                $milestone = Milestone::firstOrCreate(
                    ['user_id' => $user->id, 'dedup_key' => $dto->dedupKey],
                    [
                        'type' => $dto->type,
                        'tier' => $dto->tier,
                        'title' => $dto->title,
                        'body' => $dto->body,
                        'payload' => $dto->payload,
                        'achieved_at' => now(),
                        'created_at' => now(),
                    ],
                );
                if ($milestone->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        return $created;
    }
}
