# 06 — Importação de Extratos

## Decisão fundadora

**Usar arquivos de extrato (OFX/CSV) exportados pelo banco, não scraping.**

Por quê:
- APIs não-oficiais (pynubank, etc.) quebram a qualquer momento
- OFX é padrão estável que funciona com qualquer banco brasileiro relevante
- Zero dependência de credenciais armazenadas
- Zero risco legal/zona cinza
- Open source sem problema

Trade-off: não é automático. Mas baixar extrato 1× por semana é 30 segundos.

---

## Formatos suportados (MVP)

### OFX (Open Financial Exchange)

Padrão bancário internacional. Funciona com:
- Nubank (conta + cartão)
- Itaú
- Bradesco
- Santander
- Banco do Brasil
- Inter
- C6
- Caixa
- A maioria dos bancos brasileiros

Vantagem: estruturado, datas e valores parseáveis sem ambiguidade.
Biblioteca: `asgrim/ofxparser` (PHP, estável, ativa).

### CSV Nubank — Conta

Formato exportado pelo app Nubank. Colunas típicas:
```
Data,Valor,Identificador,Descrição
2025-04-15,-23.50,abc-123,"Pagamento Uber"
```

### CSV Nubank — Cartão de crédito

Formato diferente do CSV de conta. Colunas típicas:
```
date,title,amount
2025-04-15,Uber,23.50
```

### CSV Genérico

Para qualquer outro CSV. Usuário mapeia colunas manualmente em uma UI:
- Coluna de data
- Coluna de descrição
- Coluna de valor
- Coluna de tipo (opcional — se ausente, deduz por sinal)

---

## Arquitetura: Importer plugável

```
ImporterRegistry (detecta formato) ──┐
                                      ├─→ ImporterInterface
                                      │       │
                                      │       ├─→ OfxImporter
                                      │       ├─→ NubankCsvImporter
                                      │       ├─→ NubankCardCsvImporter
                                      │       └─→ GenericCsvImporter
                                      │
                                      └─→ retorna array<ParsedTransaction>
```

### `ImporterInterface`

```php
namespace App\Domain\Importers\Contracts;

interface ImporterInterface
{
    public static function id(): string;            // 'ofx', 'nubank_csv', etc.
    public static function displayName(): string;
    public function canHandle(UploadedFile $file): bool;
    public function parse(UploadedFile $file, Account $account): array; // <ParsedTransaction>
}
```

### `ParsedTransaction` DTO

```php
namespace App\Domain\Importers\DTOs;

class ParsedTransaction
{
    public function __construct(
        public readonly string $occurredOn,    // Y-m-d
        public readonly string $description,
        public readonly string $amount,         // sempre positivo, decimal string
        public readonly string $direction,      // 'in' | 'out'
        public readonly ?string $externalId = null, // se OFX traz FITID
    ) {}

    public function dedupHash(int $accountId): string
    {
        return hash('sha256', "{$this->occurredOn}|{$this->amount}|{$this->direction}|{$this->description}|{$accountId}");
    }
}
```

### `ImporterRegistry`

```php
namespace App\Domain\Importers;

class ImporterRegistry
{
    /** @param array<ImporterInterface> $importers */
    public function __construct(private array $importers) {}

    public function detect(UploadedFile $file, ?string $hint = null): ImporterInterface
    {
        if ($hint && $importer = $this->byId($hint)) return $importer;

        foreach ($this->importers as $importer) {
            if ($importer->canHandle($file)) return $importer;
        }

        throw new UnsupportedFileFormatException();
    }
}
```

Registrado em `config/importers.php`:

```php
return [
    'importers' => [
        \App\Domain\Importers\OfxImporter::class,
        \App\Domain\Importers\NubankCsvImporter::class,
        \App\Domain\Importers\NubankCardCsvImporter::class,
        \App\Domain\Importers\GenericCsvImporter::class,
    ],
];
```

`DomainServiceProvider` faz o bind:

```php
$this->app->singleton(ImporterRegistry::class, function ($app) {
    $importers = array_map(fn($cls) => $app->make($cls), config('importers.importers'));
    return new ImporterRegistry($importers);
});
```

---

## Fluxo de importação

```
1. POST /api/v1/imports
   - multipart: file + account_id + importer? (opcional, autodetect)
   - calcula sha256 do arquivo, checa UNIQUE (user_id, file_hash)
   - cria ImportBatch (status=pending)
   - dispatch(ProcessImportJob)
   - retorna 202 com import_batch_id e preview_url
        ↓
2. ProcessImportJob (worker)
   - ImporterRegistry::detect → escolhe importer
   - importer->parse() → array<ParsedTransaction>
   - aplica CategorizationRules → suggested_category_id por linha
   - calcula dedup_hash, marca duplicadas
   - salva preview_payload no batch (jsonb)
   - status=preview_ready
        ↓
3. GET /api/v1/imports/{id}/preview
   - retorna lista pra frontend renderizar tabela editável
        ↓
4. (Frontend) usuário revisa, ajusta categorias, confirma
        ↓
5. POST /api/v1/imports/{id}/confirm
   - payload: {overrides: [{row_index, category_id}]}
   - cria Transactions em transação DB
   - pula duplicadas
   - vincula via import_batch_id
   - status=completed
   - dispara MilestoneDetector (importação concluída pode ser marco)
        ↓
6. (opcional) POST /api/v1/imports/{id}/revert
   - soft-deleta todas as transactions desse batch
   - status=reverted
```

---

## Categorização que aprende

Sistema mantém regras em `categorization_rules`. Aplicadas durante importação:

```php
namespace App\Domain\Categorization;

class CategorizationRuleApplier
{
    public function suggest(string $description, User $user): ?int // category_id
    {
        $rules = CategorizationRule::where('user_id', $user->id)
            ->orderByDesc('priority')
            ->orderByDesc('hits')
            ->get();

        foreach ($rules as $rule) {
            if ($this->matches($description, $rule)) {
                $rule->increment('hits');
                return $rule->category_id;
            }
        }

        return null;
    }

    private function matches(string $description, CategorizationRule $rule): bool
    {
        $haystack = mb_strtolower($description);
        $needle = mb_strtolower($rule->pattern);

        return match ($rule->match_type) {
            'contains' => str_contains($haystack, $needle),
            'starts_with' => str_starts_with($haystack, $needle),
            'exact' => $haystack === $needle,
            'regex' => (bool) preg_match("/{$rule->pattern}/i", $description),
            default => false,
        };
    }
}
```

### Aprendizado

Quando usuário categoriza manualmente uma transação que não tinha categoria, frontend oferece:

> "Aplicar a todas as transações com 'IFOOD' como Alimentação?"

Se sim:
1. Cria `CategorizationRule` com `auto_learned = true`, `match_type = 'contains'`, `pattern = 'IFOOD'`
2. POST `/categorization-rules/{id}/apply-to-existing` aplica retroativamente

Próxima importação já categoriza sozinha.

---

## Deduplicação

Hash forte: `sha256(occurred_on + amount + direction + description + account_id)`.

Constraint `UNIQUE(user_id, dedup_hash)` em `transactions` garante que mesma transação não entra 2x mesmo se reimportar arquivo (ex: extrato com período sobreposto).

`file_hash` no `import_batches` previne reupload do mesmo arquivo (UX: avisa "esse arquivo já foi importado em DD/MM").

---

## Reverter importação

Quando o user percebe que a importação ficou ruim (categoria errada em massa, conta errada, etc.):

```
POST /api/v1/imports/{id}/revert
```

Soft-deleta todas as transactions com aquele `import_batch_id`. Preserva o batch (status=reverted) pra histórico/auditoria.

User pode então reimportar o arquivo (não está mais bloqueado pelo file_hash do batch revertido — checagem só conta batches em `completed`).

---

## Detalhes de implementação por importer

### OfxImporter

```php
use OfxParser\Parser;

public function parse(UploadedFile $file, Account $account): array
{
    $ofx = (new Parser())->loadFromFile($file->getPathname());
    $bankAccount = $ofx->bankAccounts[0] ?? $ofx->creditAccount ?? null;
    if (!$bankAccount) throw new InvalidOfxException();

    $result = [];
    foreach ($bankAccount->statement->transactions as $t) {
        $result[] = new ParsedTransaction(
            occurredOn: $t->date->format('Y-m-d'),
            description: trim($t->memo ?: $t->name),
            amount: number_format(abs((float)$t->amount), 2, '.', ''),
            direction: ((float)$t->amount) < 0 ? 'out' : 'in',
            externalId: $t->uniqueId,
        );
    }
    return $result;
}
```

### NubankCsvImporter (conta)

```php
public function parse(UploadedFile $file, Account $account): array
{
    $rows = $this->readCsv($file); // assume header
    $result = [];
    foreach ($rows as $row) {
        $valor = (float) str_replace(',', '.', $row['Valor']);
        $result[] = new ParsedTransaction(
            occurredOn: Carbon::parse($row['Data'])->format('Y-m-d'),
            description: trim($row['Descrição']),
            amount: number_format(abs($valor), 2, '.', ''),
            direction: $valor < 0 ? 'out' : 'in',
            externalId: $row['Identificador'] ?? null,
        );
    }
    return $result;
}
```

### NubankCardCsvImporter

Praticamente igual ao de conta, mas todas transações são `out` (cartão de crédito) e colunas têm nomes diferentes (`date`, `title`, `amount`).

### GenericCsvImporter

Recebe um mapeamento de colunas via metadata do upload:

```
file + account_id + importer=generic_csv + mapping={"date":"Data","description":"Descricao","amount":"Valor"}
```

---

## Edge cases tratados

- **Arquivo corrompido** → `status=failed` + `error_message` legível
- **Arquivo vazio** → erro com mensagem clara
- **Datas em formato esquisito** → tenta múltiplos formatos (`d/m/Y`, `Y-m-d`, `d-m-Y`)
- **Encoding** → tenta UTF-8, senão Latin-1 (extratos antigos de banco BR usam Latin-1)
- **Linhas vazias** → ignora silenciosamente
- **Valores com R$, vírgula, ponto** → normaliza ("R$ 1.234,56" → "1234.56")
- **Cartão de crédito como conta** → todas as transações são `out`; pagamento da fatura entra como transferência (futuro) ou como `in` manual

---

## Telas frontend envolvidas

- `/imports/upload` — `FileDropzone` + select de conta + select opcional de formato
- `/imports/{id}/preview` — `ImportPreviewTable` editável: cada linha tem date, description, amount, direction, category_select. Linhas duplicadas vêm marcadas e desabilitadas (com tooltip "já existe")
- `/imports` — histórico paginado com botão "reverter" pra batches completed

---

## v1.5+: Auto-categorização inteligente

Após volume de dados acumulado, considerar:
- Sugestão de categoria baseada em similaridade de descrição (Levenshtein, embeddings simples)
- Detecção automática de assinaturas (mesmo valor, mesma descrição, mensal)

Não fazer no MVP — regras + aprendizado manual já resolvem 80%.
