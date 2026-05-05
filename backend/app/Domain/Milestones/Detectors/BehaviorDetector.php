<?php

namespace App\Domain\Milestones\Detectors;

use App\Domain\Milestones\Contracts\MilestoneDetector;
use App\Domain\Milestones\DTOs\MilestoneDTO;
use App\Models\CategorizationRule;
use App\Models\Goal;
use App\Models\ImportBatch;
use App\Models\Milestone;
use App\Models\Transaction;
use App\Models\User;

class BehaviorDetector implements MilestoneDetector
{
    public function detect(User $user): array
    {
        $existing = Milestone::where('user_id', $user->id)
            ->where('type', 'like', 'behavior_%')
            ->pluck('dedup_key')
            ->all();

        $milestones = [];

        // First transaction
        if (!in_array('behavior_first_transaction', $existing, true)
            && Transaction::where('user_id', $user->id)->exists()) {
            $milestones[] = new MilestoneDTO(
                type: 'behavior_first_transaction',
                tier: 'small',
                title: 'Primeira transação registrada',
                body: 'Bem-vindo. A jornada começa aqui.',
                dedupKey: 'behavior_first_transaction',
            );
        }

        // First import completed
        if (!in_array('behavior_first_import', $existing, true)
            && ImportBatch::where('user_id', $user->id)->where('status', 'completed')->exists()) {
            $milestones[] = new MilestoneDTO(
                type: 'behavior_first_import',
                tier: 'small',
                title: 'Primeira importação concluída',
                body: 'Você acabou de automatizar grande parte do trabalho manual.',
                dedupKey: 'behavior_first_import',
            );
        }

        // First categorization rule learned
        if (!in_array('behavior_first_rule', $existing, true)
            && CategorizationRule::where('user_id', $user->id)->where('auto_learned', true)->exists()) {
            $milestones[] = new MilestoneDTO(
                type: 'behavior_first_rule',
                tier: 'small',
                title: 'Primeira regra aprendida',
                body: 'O app começou a aprender com suas decisões.',
                dedupKey: 'behavior_first_rule',
            );
        }

        // First goal created
        if (!in_array('behavior_first_goal', $existing, true)
            && Goal::where('user_id', $user->id)->exists()) {
            $milestones[] = new MilestoneDTO(
                type: 'behavior_first_goal',
                tier: 'small',
                title: 'Primeiro objetivo criado',
                body: 'Definir alvos é metade do caminho.',
                dedupKey: 'behavior_first_goal',
            );
        }

        // First goal achieved
        if (!in_array('behavior_first_goal_achieved', $existing, true)
            && Goal::where('user_id', $user->id)->whereNotNull('achieved_at')->exists()) {
            $milestones[] = new MilestoneDTO(
                type: 'behavior_first_goal_achieved',
                tier: 'large',
                title: 'Primeira meta batida!',
                body: 'Você bateu sua primeira meta. Esse padrão tende a repetir.',
                dedupKey: 'behavior_first_goal_achieved',
            );
        }

        return $milestones;
    }
}
