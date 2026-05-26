<?php

declare(strict_types=1);

namespace Modules\EmendasParlamentares\Commands;

use Illuminate\Console\Command;
use Modules\EmendasParlamentares\Actions\PersistirEmendasImportadasAction;
use Modules\EmendasParlamentares\Services\DecretoDocxParserService;
use Modules\EmendasParlamentares\Services\EmendaTranslatorService;
use Throwable;

/**
 * Artisan Command — Importação de Emendas do DECRETO Nº 134/2026
 *
 * Uso:
 *   php artisan emendas:importar-decreto --tenant=ID
 *   php artisan emendas:importar-decreto --tenant=ID --dry-run
 *   php artisan emendas:importar-decreto --tenant=ID --file=/caminho/personalizado/decreto.docx
 *   php artisan emendas:importar-decreto --tenant=ID --dry-run --verbose
 */
class ImportarEmendasDecretoCommand extends Command
{
    /**
     * Assinatura do comando com opções configuráveis.
     */
    protected $signature = 'emendas:importar-decreto
                            {--tenant= : O ID do tenant para o qual a importação será executada (obrigatório)}
                            {--file= : Caminho do arquivo .docx (padrão: ANEXO DO DECRETO 134 DE 2026.docx na raiz)}
                            {--dry-run : Simula a importação sem gravar no banco de dados}
                            {--force : Força a execução mesmo se houver avisos de CNPJ inválido}';

    protected $description = 'Importa as emendas parlamentares individuais do Decreto nº 134/2026 a partir do arquivo .docx em um tenant específico';

    public function __construct(
        private readonly DecretoDocxParserService        $parser,
        private readonly EmendaTranslatorService          $translator,
        private readonly PersistirEmendasImportadasAction $persistir,
    ) {
        parent::__construct();
    }

    /**
     * Entry point do command.
     */
    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        if (empty($tenantId)) {
            $this->error('  ✖ A opção --tenant é obrigatória. Use: php artisan emendas:importar-decreto --tenant=ID');
            return self::FAILURE;
        }

        if (str_contains($tenantId, ',')) {
            $this->error('  ✖ A importação deve ser executada para apenas um tenant por vez.');
            return self::FAILURE;
        }

        $tenant = tenancy()->find($tenantId);

        if (!$tenant) {
            $this->error("  ✖ Tenant com ID '{$tenantId}' não foi encontrado.");
            return self::FAILURE;
        }

        $dryRun  = $this->option('dry-run');
        $filePath = $this->resolveFilePath();

        $this->printHeader($dryRun, $filePath);

        // ── Etapa 1 e 2: Leitura e Normalização ──────────────────────────────
        $this->info('  ⏳ Etapa 1/3 — Lendo e normalizando o arquivo .docx...');
        try {
            $parsedRows = $this->parser->parse($filePath);
        } catch (Throwable $e) {
            $this->error("  ✖ Falha na leitura do arquivo: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->line("  ✔ <fg=green>{$this->count($parsedRows)} linhas de dados extraídas do documento.</>");

        // ── Etapa 3: Tradução e Mapeamento Heurístico ─────────────────────────
        $this->info('  ⏳ Etapa 2/3 — Aplicando heurísticas de tradução...');
        $translatedRows = $this->translator->translate($parsedRows);
        $this->line('  ✔ <fg=green>Tradução concluída.</>');

        if ($this->option('verbose')) {
            $this->printTranslationPreview($translatedRows);
        }

        $allSuccessful = true;

        $this->newLine();
        $this->line('┌─────────────────────────────────────────────────────────┐');
        $this->line(sprintf('│  <fg=cyan;options=bold>Processando Tenant ID: %s</>', str_pad((string) $tenant->getTenantKey(), 30)));
        $this->line('└─────────────────────────────────────────────────────────┘');
        $this->newLine();

        // Inicializa a tenancy
        tenancy()->initialize($tenant);

        try {
            // Verificação de CNPJs inválidos antes de persistir
            $cnpjInvalidos = array_filter($translatedRows, fn($r) => ($r['recebedor']['cnpj_valido'] ?? true) === false);
            if (count($cnpjInvalidos) > 0 && !$this->option('force') && !$dryRun) {
                $this->error(sprintf(
                    '  ✖  %d emenda(s) com CNPJ inválido. Importação abortada. Use --force para importar mesmo assim.',
                    count($cnpjInvalidos)
                ));
                foreach ($cnpjInvalidos as $r) {
                    $this->line(sprintf(
                        '     → Row %d | Emenda %s | CNPJ: %s | Recebedor: %s',
                        $r['_meta']['source_row'],
                        $r['emenda']['numero'] ?? '?',
                        $r['recebedor']['cnpj'] ?? 'nulo',
                        $r['recebedor']['razao_social'] ?? '?'
                    ));
                }
                $allSuccessful = false;
            } else {
                // Persistência
                $mode = $dryRun ? '<fg=yellow>DRY-RUN</>' : '<fg=cyan>LIVE</>';
                $this->info("  ⏳ Etapa 3/3 — Persistindo no banco [{$mode}]...");

                $report = $this->persistir->handle($translatedRows, $dryRun);

                // Relatório final deste tenant
                $this->printReport($report, $dryRun);
            }
        } catch (Throwable $e) {
            $this->error(sprintf('  ✖ Falha crítica na persistência do Tenant %s: %s', $tenant->getTenantKey(), $e->getMessage()));
            $this->error('  A transação foi revertida para este tenant.');
            $allSuccessful = false;
        } finally {
            // Encerra a tenancy
            tenancy()->end();
        }

        return $allSuccessful ? self::SUCCESS : self::FAILURE;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Helpers de output
    // ──────────────────────────────────────────────────────────────────────────

    private function resolveFilePath(): string
    {
        $option = $this->option('file');
        if ($option !== null && $option !== '') {
            return $option;
        }
        return base_path('ANEXO DO DECRETO 134 DE 2026.docx');
    }

    private function printHeader(bool $dryRun, string $filePath): void
    {
        $this->newLine();
        $this->line('┌─────────────────────────────────────────────────────────┐');
        $this->line('│    <fg=cyan;options=bold>Importador de Emendas — Decreto nº 134/2026</>          │');
        $this->line('└─────────────────────────────────────────────────────────┘');
        $this->newLine();
        $this->line("  📂 Arquivo : <fg=white>{$filePath}</>");
        $this->line('  🗓  Exercício: <fg=white>2026</>');
        $this->line('  🏛  Decreto  : <fg=white>Nº 134/2026 — Câmara Municipal de Caratinga</>');
        if ($dryRun) {
            $this->line('  🔍 Modo     : <fg=yellow;options=bold>DRY-RUN — nenhum dado será gravado</>');
        }
        $this->newLine();
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function printTranslationPreview(array $rows): void
    {
        $this->newLine();
        $this->line('  <options=bold>Prévia das primeiras 5 linhas traduzidas:</>');
        $this->newLine();

        $headers = ['Número', 'Vereador', 'Recebedor', 'CNPJ', 'Valor', 'Área', 'Tipo Obj.'];
        $tableRows = [];

        foreach (array_slice($rows, 0, 5) as $row) {
            $tableRows[] = [
                $row['emenda']['numero'] ?? '-',
                mb_strimwidth($row['concedente']['nome'] ?? '-', 0, 25, '…'),
                mb_strimwidth($row['recebedor']['razao_social'] ?? '-', 0, 30, '…'),
                $row['recebedor']['cnpj'] ?? '-',
                'R$ ' . number_format((float) ($row['emenda']['valor'] ?? 0), 2, ',', '.'),
                $row['_meta']['section'] ?? '-',
                $row['emenda']['tipo_objeto'] ?? '-',
            ];
        }

        $this->table($headers, $tableRows);
    }

    /**
     * @param array<string, mixed> $report
     */
    private function printReport(array $report, bool $dryRun): void
    {
        $prefix = $dryRun ? '[DRY-RUN] ' : '';

        $this->newLine();
        $this->line('┌─────────────────────────── Relatório ───────────────────────────┐');
        $this->line(sprintf('│  Total de linhas processadas  : <fg=white>%d</>', $report['total']));
        $this->line(sprintf('│  Emendas criadas              : <fg=green>%d</>', $report['emendas_criadas']));
        $this->line(sprintf('│  Emendas atualizadas          : <fg=yellow>%d</>', $report['emendas_atualizadas']));
        $this->line(sprintf('│  Recebedores novos            : <fg=green>%d</>', $report['recebedores_criados']));
        $this->line(sprintf('│  Recebedores já existentes    : <fg=blue>%d</>', $report['recebedores_existentes']));
        $this->line(sprintf('│  Avisos                       : <fg=yellow>%d</>', count($report['avisos'])));
        $this->line(sprintf('│  Erros                        : <fg=red>%d</>', count($report['erros'])));
        $this->line('└──────────────────────────────────────────────────────────────────┘');

        // Avisos
        if (!empty($report['avisos'])) {
            $this->newLine();
            $this->warn('  ⚠  Avisos:');
            foreach ($report['avisos'] as $aviso) {
                $this->line("     → {$aviso}");
            }
        }

        // Erros
        if (!empty($report['erros'])) {
            $this->newLine();
            $this->error('  ✖ Erros encontrados:');
            foreach ($report['erros'] as $erro) {
                $this->line(sprintf(
                    '     → Row %d | Emenda %s: %s',
                    $erro['row'],
                    $erro['numero'] ?? '?',
                    $erro['error']
                ));
            }
        }

        $this->newLine();
        if (count($report['erros']) === 0) {
            $icon = $dryRun ? '🔍' : '✅';
            $msg  = $dryRun
                ? 'Simulação concluída com sucesso. Nenhum dado foi gravado.'
                : 'Importação concluída com sucesso!';
            $this->line("  {$icon} <fg=green;options=bold>{$msg}</>");
        } else {
            $this->line('  ⚠  <fg=yellow>Importação concluída com erros. Verifique os logs do Laravel.</>');
        }
        $this->newLine();
    }

    /**
     * @param array<mixed> $rows
     */
    private function count(array $rows): int
    {
        return count($rows);
    }
}
