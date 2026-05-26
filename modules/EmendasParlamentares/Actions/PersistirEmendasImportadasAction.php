<?php

declare(strict_types=1);

namespace Modules\EmendasParlamentares\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\EmendasParlamentares\Models\Concedente;
use Modules\EmendasParlamentares\Models\Emenda;
use Modules\EmendasParlamentares\Models\Recebedor;
use Throwable;

/**
 * Etapa 4 do pipeline de importação.
 *
 * Responsabilidades:
 *  - Resolver (firstOrCreate) o Concedente único do decreto
 *  - Resolver (updateOrCreate) cada Recebedor pela chave de negócio CNPJ
 *  - Inserir/Atualizar cada Emenda pela chave de negócio (numero + exercicio)
 *  - Garantir idempotência: re-executar o command não cria duplicatas
 *  - Encapsular tudo em DB::transaction com rollback automático em exceção
 *  - Retornar relatório detalhado de importação
 */
final class PersistirEmendasImportadasAction
{
    /**
     * @param  array<int, array<string, mixed>> $translatedRows Saída do EmendaTranslatorService
     * @param  bool                             $dryRun         Se true, não persiste, apenas simula
     * @return array{
     *   total: int,
     *   emendas_criadas: int,
     *   emendas_atualizadas: int,
     *   recebedores_criados: int,
     *   recebedores_existentes: int,
     *   erros: array<int, array{row: int, numero: string|null, error: string}>,
     *   avisos: array<int, string>,
     * }
     */
    public function handle(array $translatedRows, bool $dryRun = false): array
    {
        $report = [
            'total'                  => count($translatedRows),
            'emendas_criadas'        => 0,
            'emendas_atualizadas'    => 0,
            'recebedores_criados'    => 0,
            'recebedores_existentes' => 0,
            'erros'                  => [],
            'avisos'                 => [],
        ];

        if ($dryRun) {
            $this->runDryRun($translatedRows, $report);
            return $report;
        }

        try {
            DB::transaction(function () use ($translatedRows, &$report) {
                // ── Itera cada emenda ───────────────────────────────────────
                foreach ($translatedRows as $row) {
                    $this->persistRow($row, $report);
                }
            });
        } catch (Throwable $e) {
            Log::error('[ImportarEmendas] Falha crítica na transação', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            // Re-lança para o Command reportar ao operador
            throw $e;
        }

        return $report;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Persistência de linha individual
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * @param array<string, mixed>    $row
     * @param array<string, mixed>    $report por referência
     */
    private function persistRow(array $row, array &$report): void
    {
        $meta     = $row['_meta'];
        $emendaData = $row['emenda'];
        $recData    = $row['recebedor'];
        $concedenteData = $row['concedente'] ?? null;

        try {
            if ($concedenteData === null) {
                throw new \RuntimeException('Dados do concedente não informados nesta linha.');
            }

            // Resolve Concedente específico desta linha (vereador)
            $concedente = Concedente::updateOrCreate(
                ['nome' => $concedenteData['nome']],
                [
                    'tipo'      => $concedenteData['tipo'],
                    'partido'   => $concedenteData['partido'],
                    'descricao' => $concedenteData['descricao'],
                ]
            );
            // ── Aviso: CNPJ inválido (não rejeita, importa com aviso) ──
            if (isset($recData['cnpj_valido']) && $recData['cnpj_valido'] === false) {
                $report['avisos'][] = sprintf(
                    'Row %d | Emenda %s: CNPJ inválido "%s" — importado com aviso.',
                    $meta['source_row'],
                    $emendaData['numero'] ?? '?',
                    $recData['cnpj'] ?? ''
                );
            }

            // ── 2a. Recebedor: chave de negócio = CNPJ ────────────────────
            /** @var Recebedor $recebedor */
            [$recebedor, $recebedorCriado] = $this->upsertRecebedor($recData);

            if ($recebedorCriado) {
                $report['recebedores_criados']++;
            } else {
                $report['recebedores_existentes']++;
            }

            // ── 2b. Emenda: chave de negócio = (numero + exercicio) ───────
            $emendaPayload = array_filter([
                'exercicio'       => $emendaData['exercicio'],
                'tipo_origem'     => $emendaData['tipo_origem'],
                'concedente_id'   => $concedente->id,
                'recebedor_id'    => $recebedor->id,
                'modalidade_id'   => $emendaData['modalidade_id'],
                'rascunho'        => $emendaData['rascunho'],
                'tipo_objeto'     => $emendaData['tipo_objeto'],
                'status'          => $emendaData['status'],
                'gnd'             => $emendaData['gnd'],
                'descricao_objeto' => $emendaData['descricao_objeto'],
                'valor'           => $emendaData['valor'],
                'responsavel'     => $emendaData['responsavel'],
                'anuencia_sus'    => $emendaData['anuencia_sus'],
            ], fn($v) => $v !== null);

            // Garante que campos boolean não sejam filtrados pelo array_filter
            $emendaPayload['rascunho']    = $emendaData['rascunho'];
            $emendaPayload['anuencia_sus'] = $emendaData['anuencia_sus'];

            $existing = Emenda::where('numero', $emendaData['numero'])
                ->where('exercicio', $emendaData['exercicio'])
                ->first();

            if ($existing) {
                $existing->update($emendaPayload);
                $report['emendas_atualizadas']++;

                Log::info('[ImportarEmendas] Emenda atualizada', [
                    'numero'   => $emendaData['numero'],
                    'exercicio' => $emendaData['exercicio'],
                ]);
            } else {
                Emenda::create(array_merge($emendaPayload, [
                    'numero' => $emendaData['numero'],
                ]));
                $report['emendas_criadas']++;

                Log::info('[ImportarEmendas] Emenda criada', [
                    'numero'   => $emendaData['numero'],
                    'exercicio' => $emendaData['exercicio'],
                ]);
            }
        } catch (Throwable $e) {
            $report['erros'][] = [
                'row'    => $meta['source_row'],
                'numero' => $emendaData['numero'] ?? null,
                'error'  => $e->getMessage(),
            ];

            Log::warning('[ImportarEmendas] Erro ao persistir linha', [
                'row'    => $meta['source_row'],
                'numero' => $emendaData['numero'] ?? null,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    /**
     * Faz upsert do Recebedor pela chave de negócio CNPJ.
     *
     * @param  array<string, mixed>   $data
     * @return array{Recebedor, bool} [instância, foi_criado]
     */
    private function upsertRecebedor(array $data): array
    {
        $cnpj = $data['cnpj'];

        // Se o CNPJ for nulo ou inválido, tenta buscar pelo nome exato
        if ($cnpj === null) {
            $existing = Recebedor::where('razao_social', $data['razao_social'])->first();
            if ($existing) {
                return [$existing, false];
            }
        } else {
            $existing = Recebedor::where('cnpj', $cnpj)->first();
            if ($existing) {
                // Atualiza razão social caso tenha mudado (p.ex. erros tipográficos no doc)
                $existing->update(['razao_social' => $data['razao_social']]);
                return [$existing, false];
            }
        }

        // Cria novo recebedor
        $recebedor = Recebedor::create([
            'razao_social' => $data['razao_social'],
            'cnpj'         => $cnpj ?? '',
            'tipo'         => $data['tipo'],
            'municipio'    => $data['municipio'],
            'uf'           => $data['uf'],
            'codigo_ibge'  => $data['codigo_ibge'] ?? 3113404,
        ]);

        return [$recebedor, true];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Dry-run
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Simula a importação sem gravar no banco.
     * Valida CNPJ, detecta duplicatas e lista o que seria feito.
     *
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, mixed>             $report por referência
     */
    private function runDryRun(array $rows, array &$report): void
    {
        $seenNumeros = [];

        foreach ($rows as $row) {
            $emendaData = $row['emenda'];
            $recData    = $row['recebedor'];
            $meta       = $row['_meta'];

            // Aviso: CNPJ inválido
            if (isset($recData['cnpj_valido']) && $recData['cnpj_valido'] === false) {
                $report['avisos'][] = sprintf(
                    '[DRY-RUN] Row %d | Emenda %s: CNPJ inválido "%s"',
                    $meta['source_row'],
                    $emendaData['numero'] ?? '?',
                    $recData['cnpj'] ?? ''
                );
            }

            // Detecta duplicatas no próprio documento
            $key = ($emendaData['numero'] ?? '') . '/' . ($emendaData['exercicio'] ?? '');
            if (isset($seenNumeros[$key])) {
                $report['avisos'][] = sprintf(
                    '[DRY-RUN] Emenda duplicada no documento: %s (rows %d e %d)',
                    $key,
                    $seenNumeros[$key],
                    $meta['source_row']
                );
            }
            $seenNumeros[$key] = $meta['source_row'];

            // Simula criação vs. atualização consultando o banco (sem escrever)
            $existeEmenda = Emenda::where('numero', $emendaData['numero'])
                ->where('exercicio', $emendaData['exercicio'])
                ->exists();

            $existeRecebedor = $recData['cnpj'] !== null
                ? Recebedor::where('cnpj', $recData['cnpj'])->exists()
                : false;

            if ($existeEmenda) {
                $report['emendas_atualizadas']++;
            } else {
                $report['emendas_criadas']++;
            }

            if ($existeRecebedor) {
                $report['recebedores_existentes']++;
            } else {
                $report['recebedores_criados']++;
            }
        }
    }
}
