<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DefaultCategoriesService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(DefaultCategoriesService $categories): void
    {
        // ── Usuário ──────────────────────────────────────────────────────
        $user = User::updateOrCreate(
            ['email' => 'teste@finance.dev'],
            [
                'name' => 'Teste',
                'password' => Hash::make('password123'),
                'timezone' => 'America/Sao_Paulo',
            ],
        );

        $this->command->info("Usuário: {$user->email} / senha: password123");

        // ── Categorias padrão (idempotente) ───────────────────────────────
        $created = $categories->seedFor($user);
        $this->command->info("Categorias criadas: {$created}");

        // ── Contas ────────────────────────────────────────────────────────
        $nubank = Account::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Nubank'],
            ['type' => 'checking', 'initial_balance' => '3200.00', 'color' => '#820AD1', 'icon' => 'wallet'],
        );

        $carteira = Account::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Carteira'],
            ['type' => 'cash', 'initial_balance' => '150.00', 'color' => '#10b981', 'icon' => 'banknotes'],
        );

        $inter = Account::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Inter Invest'],
            ['type' => 'investment', 'initial_balance' => '8500.00', 'color' => '#f97316', 'icon' => 'chart-line'],
        );

        $this->command->info('Contas: Nubank, Carteira, Inter Invest');

        // ── Transações de exemplo ─────────────────────────────────────────
        $catByName = $user->categories()->pluck('id', 'name');

        $samples = [
            // Entradas
            ['account' => $nubank,   'occurred_on' => '2026-04-05', 'description' => 'Salário abril',        'amount' => '4800.00', 'direction' => 'in',  'category' => 'Renda Principal'],
            ['account' => $nubank,   'occurred_on' => '2026-04-10', 'description' => 'Freela site cliente',  'amount' => '800.00',  'direction' => 'in',  'category' => 'Renda Extra'],
            // Despesas essenciais
            ['account' => $nubank,   'occurred_on' => '2026-04-01', 'description' => 'Aluguel',              'amount' => '1400.00', 'direction' => 'out', 'category' => 'Moradia'],
            ['account' => $nubank,   'occurred_on' => '2026-04-03', 'description' => 'Mercado Pão de Açúcar','amount' => '320.50',  'direction' => 'out', 'category' => 'Alimentação'],
            ['account' => $nubank,   'occurred_on' => '2026-04-08', 'description' => 'Uber — trabalho',      'amount' => '42.90',   'direction' => 'out', 'category' => 'Transporte'],
            ['account' => $nubank,   'occurred_on' => '2026-04-12', 'description' => 'Farmácia',             'amount' => '85.00',   'direction' => 'out', 'category' => 'Saúde'],
            ['account' => $nubank,   'occurred_on' => '2026-04-15', 'description' => 'Spotify',              'amount' => '21.90',   'direction' => 'out', 'category' => 'Assinaturas'],
            ['account' => $nubank,   'occurred_on' => '2026-04-15', 'description' => 'Netflix',              'amount' => '39.90',   'direction' => 'out', 'category' => 'Assinaturas'],
            ['account' => $nubank,   'occurred_on' => '2026-04-18', 'description' => 'Alura — plano anual',  'amount' => '79.90',   'direction' => 'out', 'category' => 'Educação'],
            // Supérfluos
            ['account' => $carteira, 'occurred_on' => '2026-04-06', 'description' => 'Bar com amigos',       'amount' => '95.00',   'direction' => 'out', 'category' => 'Lazer'],
            ['account' => $nubank,   'occurred_on' => '2026-04-20', 'description' => 'iFood — jantar',       'amount' => '58.70',   'direction' => 'out', 'category' => 'Alimentação'],
            // Investimento
            ['account' => $inter,    'occurred_on' => '2026-04-06', 'description' => 'Aporte CDB Inter',     'amount' => '1000.00', 'direction' => 'in',  'category' => 'Renda Extra'],
        ];

        $inserted = 0;
        foreach ($samples as $s) {
            $hash = Transaction::computeHash(
                $s['occurred_on'],
                $s['amount'],
                $s['direction'],
                $s['description'],
                $s['account']->id,
            );

            $exists = Transaction::withTrashed()
                ->where('user_id', $user->id)
                ->where('dedup_hash', $hash)
                ->exists();

            if ($exists) {
                continue;
            }

            Transaction::create([
                'user_id' => $user->id,
                'account_id' => $s['account']->id,
                'category_id' => $catByName[$s['category']] ?? null,
                'occurred_on' => $s['occurred_on'],
                'description' => $s['description'],
                'amount' => $s['amount'],
                'direction' => $s['direction'],
                'dedup_hash' => $hash,
            ]);

            $inserted++;
        }

        $this->command->info("Transações inseridas: {$inserted}");
        $this->command->line('');
        $this->command->line('  <info>Pronto!</info> Acesse http://localhost:5173/login');
        $this->command->line('  email: <comment>teste@finance.dev</comment>');
        $this->command->line('  senha: <comment>password123</comment>');
    }
}
