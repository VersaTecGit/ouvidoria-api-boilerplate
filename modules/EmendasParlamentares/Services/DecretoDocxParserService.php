<?php

declare(strict_types=1);

namespace Modules\EmendasParlamentares\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Row;
use PhpOffice\PhpWord\Element\Cell;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Text;
use RuntimeException;

/**
 * Etapa 1 e 2 do pipeline de importação.
 *
 * Responsabilidades:
 *  - Ler o arquivo .docx usando PhpWord e extrair as tabelas
 *  - Detectar seções por linhas com células mescladas (ex: "ÁREA DA SAÚDE")
 *  - Normalizar e sanitizar cada célula (trim, encoding, parse monetário, CNPJ)
 *  - Retornar array de linhas brutas enriquecidas com contexto de seção
 */
final class DecretoDocxParserService
{
    /**
     * Palavras-chave para identificar cabeçalho de coluna (ignorar essas linhas).
     */
    private const COLUMN_HEADER_KEYWORDS = ['EMENDA', 'VEREADOR', 'PARTIDO', 'VALOR', 'DESTINAÇÃO', 'CNPJ', 'JUSTIFICATIVA'];

    /**
     * Palavras-chave para identificar linhas do ANEXO II (Quadro Consolidado — ignorar).
     */
    private const ANEXO_II_KEYWORDS = ['Quadro I', 'Quadro II', 'Valor Global', 'VEREADORSAÚDE', 'SAÚDE(MÍNIMO'];

    /**
     * Mapeamento de palavras-chave de seção para slug interno.
     */
    private const SECTION_KEYWORD_MAP = [
        'SAÚDE'    => 'saude',
        'SAUDE'    => 'saude',
        'OBRAS'    => 'obras',
        'DIVERSAS' => 'diversas',
    ];

    /**
     * Lê o arquivo .docx e retorna um array de linhas normalizadas.
     *
     * @param  string $filePath Caminho absoluto para o arquivo .docx
     * @return array<int, array{
     *   _meta: array{source_row: int, section: string|null, area_raw: string|null},
     *   numero_raw: string,
     *   vereador_raw: string,
     *   partido_raw: string,
     *   valor_raw: string,
     *   destinacao_raw: string,
     *   cnpj_raw: string,
     *   justificativa_raw: string,
     *   numero: int|null,
     *   valor: float|null,
     *   cnpj: string|null,
     * }>
     * @throws RuntimeException se o arquivo não existir ou não tiver tabelas
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Arquivo não encontrado: {$filePath}");
        }

        $phpWord = IOFactory::load($filePath);
        $results  = [];
        $rowIndex  = 0;
        $currentSection    = null;
        $currentSectionRaw = null;
        // Buffer de texto acumulado dos TextRun/Text anteriores à próxima Table
        $pendingText = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                // ── Captura texto de elementos não-tabela (títulos de seção) ──
                if (!($element instanceof Table)) {
                    $pendingText .= ' ' . $this->elementToText($element);
                    continue;
                }

                // ── Detecta seção a partir do texto acumulado antes desta Table ──
                if ($pendingText !== '') {
                    $trimmed  = trim(preg_replace('/\s+/', ' ', $pendingText) ?? '');
                    $detected = $this->detectSection($trimmed);
                    if ($detected !== null) {
                        $currentSection    = $detected;
                        $currentSectionRaw = $trimmed;
                    }
                    // Detecta fim do documento (ANEXO II) nos textos entre tabelas
                    if ($this->isAnexoII($trimmed)) {
                        break 2; // sai dos loops de seção e elementos
                    }
                    $pendingText = '';
                }

                foreach ($element->getRows() as $row) {
                    $rowIndex++;
                    $cells     = $row->getCells();
                    $cellCount = count($cells);
                    $cellTexts = array_map([$this, 'extractCellText'], $cells);

                    // ── Detectar linha de seção mesclada dentro da tabela (fallback) ──
                    if ($cellCount < 7) {
                        $joined = implode(' ', $cellTexts);

                        if ($this->isAnexoII($joined)) {
                            break 3;
                        }

                        $detected = $this->detectSection($joined);
                        if ($detected !== null) {
                            $currentSection    = $detected;
                            $currentSectionRaw = trim($joined);
                        }
                        continue;
                    }

                    // ── Ignorar cabeçalho de coluna ──
                    if ($this->isColumnHeader($cellTexts)) {
                        continue;
                    }

                    // ── Ignorar linhas vazias ──
                    $numero = trim($cellTexts[0] ?? '');
                    if ($numero === '') {
                        continue;
                    }

                    // ── Normalização dos campos ──
                    $valorRaw      = trim($cellTexts[3] ?? '');
                    $cnpjRaw       = trim($cellTexts[5] ?? '');
                    $justificativa = $this->sanitizeString($cellTexts[6] ?? '');

                    $results[] = [
                        '_meta' => [
                            'source_row'  => $rowIndex,
                            'section'     => $currentSection,
                            'area_raw'    => $currentSectionRaw,
                        ],
                        // Raw — preservados para debug/log
                        'numero_raw'        => $numero,
                        'vereador_raw'      => trim($cellTexts[1] ?? ''),
                        'partido_raw'       => trim($cellTexts[2] ?? ''),
                        'valor_raw'         => $valorRaw,
                        'destinacao_raw'    => trim($cellTexts[4] ?? ''),
                        'cnpj_raw'          => $cnpjRaw,
                        'justificativa_raw' => $justificativa,
                        // Normalizados
                        'numero'  => $this->parseNumero($numero),
                        'valor'   => $this->parseValor($valorRaw),
                        'cnpj'    => $this->parseCnpj($cnpjRaw),
                    ];
                }
            }
        }

        return $results;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Helpers de extração e sanitização
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Extrai o texto puro de uma Cell do PhpWord (suporta TextRun, Text e strings aninhadas).
     */
    private function extractCellText(Cell $cell): string
    {
        $parts = [];
        foreach ($cell->getElements() as $element) {
            $parts[] = $this->elementToText($element);
        }
        return trim(implode(' ', array_filter($parts)));
    }

    /**
     * Converte recursivamente elementos PhpWord em string.
     */
    private function elementToText(mixed $element): string
    {
        if ($element instanceof Text) {
            return $element->getText();
        }
        if ($element instanceof TextRun) {
            $parts = [];
            foreach ($element->getElements() as $child) {
                $parts[] = $this->elementToText($child);
            }
            return implode('', $parts);
        }
        // Outros elementos (imagens, etc.) são ignorados
        return '';
    }

    /**
     * Sanitiza string: remove espaços múltiplos, garante UTF-8 correto.
     */
    private function sanitizeString(string $value): string
    {
        // Garante UTF-8 válido
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        // Remove espaços múltiplos e tabs
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        return trim($value);
    }

    /**
     * Converte número da emenda para int (remove zeros à esquerda, lida com strings como "010").
     */
    private function parseNumero(string $raw): ?int
    {
        $cleaned = preg_replace('/\D/', '', $raw);
        if ($cleaned === '' || $cleaned === null) {
            return null;
        }
        return (int) $cleaned;
    }

    /**
     * Converte valor monetário brasileiro para float.
     * Ex: "R$ 120.000,00" → 120000.00
     *     "R$8.528.706,37" → 8528706.37
     */
    private function parseValor(string $raw): ?float
    {
        // Remove "R$", espaços e pontos de milhar
        $cleaned = preg_replace('/R\$\s*/', '', $raw);
        $cleaned = preg_replace('/\.(?=\d{3}[,\d])/', '', $cleaned ?? '');
        // Troca vírgula decimal por ponto
        $cleaned = str_replace(',', '.', $cleaned ?? '');
        $cleaned = trim($cleaned ?? '');

        if (!is_numeric($cleaned)) {
            return null;
        }
        return (float) $cleaned;
    }

    /**
     * Normaliza CNPJ para formato XX.XXX.XXX/XXXX-XX.
     * Mantém apenas dígitos e reformata.
     */
    private function parseCnpj(string $raw): ?string
    {
        $digits = preg_replace('/\D/', '', $raw);

        if ($digits === null || strlen($digits) < 14) {
            // CNPJ com dígitos insuficientes — retorna os dígitos brutos para log posterior
            return $digits !== '' ? $digits : null;
        }

        // Trunca para 14 dígitos caso haja mais (improvável mas defensivo)
        $digits = substr($digits, 0, 14);

        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($digits, 0, 2),
            substr($digits, 2, 3),
            substr($digits, 5, 3),
            substr($digits, 8, 4),
            substr($digits, 12, 2)
        );
    }

    /**
     * Verifica se a linha pertence ao ANEXO II (Quadro Consolidado).
     */
    private function isAnexoII(string $text): bool
    {
        foreach (self::ANEXO_II_KEYWORDS as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica se a linha é um cabeçalho de coluna (EMENDA|VEREADOR|...).
     *
     * @param array<string> $cellTexts
     */
    private function isColumnHeader(array $cellTexts): bool
    {
        foreach (self::COLUMN_HEADER_KEYWORDS as $keyword) {
            foreach ($cellTexts as $cell) {
                if (stripos($cell, $keyword) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Detecta a seção atual a partir do texto de uma linha de cabeçalho.
     * Retorna slug ('saude', 'obras', 'diversas') ou null se não reconhecido.
     */
    private function detectSection(string $text): ?string
    {
        $normalized = mb_strtoupper($text, 'UTF-8');
        // Remove acentos para comparação mais robusta
        $normalized = strtr($normalized, [
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A',
            'É' => 'E', 'Ê' => 'E',
            'Í' => 'I',
            'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ú' => 'U', 'Ü' => 'U',
            'Ç' => 'C',
        ]);

        foreach (self::SECTION_KEYWORD_MAP as $keyword => $slug) {
            $keyNorm = strtr($keyword, [
                'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'É' => 'E',
                'Ó' => 'O', 'Ú' => 'U', 'Ç' => 'C',
            ]);
            if (str_contains($normalized, $keyNorm)) {
                return $slug;
            }
        }

        return null;
    }
}
