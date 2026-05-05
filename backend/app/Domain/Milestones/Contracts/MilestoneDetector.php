<?php

namespace App\Domain\Milestones\Contracts;

use App\Models\User;

interface MilestoneDetector
{
    /** @return array<\App\Domain\Milestones\DTOs\MilestoneDTO> */
    public function detect(User $user): array;
}
