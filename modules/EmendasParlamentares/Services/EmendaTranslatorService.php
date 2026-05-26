<?php

declare(strict_types=1);

namespace Modules\EmendasParlamentares\Services;

/**
 * Etapa 3 do pipeline de importação.
 *
 * Responsabilidade: recebe as linhas normalizadas do DecretoDocxParserService
 * e aplica heurísticas para preencher os campos que não existem explicitamente
 * no documento (tipo_objeto, tipo do recebedor, partido normalizado, etc.),
 * retornando um payload estruturado pronto para persistência.
 */
final class EmendaTranslatorService
{
    /**
     * Exercício fiscal fixo extraído do Decreto (Decreto nº 134/2026).
     */
    private const EXERCICIO = '2026';

    /**
     * Município e UF padrão (inferidos do contexto do decreto).
     */
    private const MUNICIPIO_PADRAO = 'Caratinga';
    private const UF_PADRAO        = 'MG';

    /**
     * De/Para de partidos: normaliza variações tipográficas encontradas no documento.
     */
    private const PARTIDO_MAP = [
        'PPR'         => 'PP',
        'PSDR'        => 'PSD',
        'MDR'         => 'MDB',
        'AVAN TER'    => 'AVANTE',
        'AVANTR'      => 'AVANTE',
        'AVANTER'     => 'AVANTE',
        'REPUBLICANO' => 'REPUBLICANOS',
    ];

    /**
     * Padrões Regex → tipo do recebedor.
     * A ordem importa: mais específico primeiro.
     */
    private const TIPO_RECEBEDOR_PATTERNS = [
        '/^SECRETARIA/i'        => 'prefeitura',
        '/^FUNDO\s+MUNICIPAL/i' => 'prefeitura',
        '/^FUNDO/i'             => 'prefeitura',
        '/^FUNDAÇÃO/i'          => 'entidade',
        '/^ASSOCIAÇÃO/i'        => 'entidade',
        '/^HOSPITAL/i'          => 'entidade',
        '/^(LAR\s+DOS|LAR\b)/i' => 'entidade',
        '/^PROJETO/i'           => 'entidade',
        '/^CLUBE/i'             => 'entidade',
        '/^INSTITUTO/i'         => 'entidade',
        '/^CASA\s+(DE|DO)/i'    => 'entidade',
        '/^EBEN[EÉ]ZER/i'       => 'entidade',
        '/^ASSIST[EÊ]NCIA/i'    => 'entidade',
        '/^N[UÚ]CLEO/i'         => 'entidade',
        '/^UNIDADE/i'           => 'prefeitura',
    ];

    /**
     * Recebe as linhas brutas do Parser e retorna payload estruturado.
     *
     * @param  array<int, array<string, mixed>> $parsedRows Saída do DecretoDocxParserService
     * @return array<int, array{
     *   _meta: array<string, mixed>,
     *   emenda: array<string, mixed>,
     *   concedente: array<string, mixed>,
     *   recebedor: array<string, mixed>,
     *   partido_vereador: string,
     * }>
     */
    public function translate(array $parsedRows): array
    {
        $translated = [];

        foreach ($parsedRows as $row) {
            $recebedor  = $this->buildRecebedor($row);
            $emenda     = $this->buildEmenda($row, $recebedor['tipo']);
            $concedente = $this->buildConcedente($row);

            $translated[] = [
                '_meta'            => $row['_meta'],
                'emenda'           => $emenda,
                'concedente'       => $concedente,
                'recebedor'        => $recebedor,
                'partido_vereador' => $concedente['partido'],
            ];
        }

        return $translated;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Builders por entidade
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Monta o array de dados para a tabela `emendas`.
     *
     * @param array<string, mixed> $row
     * @param string               $recebedorTipo
     * @return array<string, mixed>
     */
    private function buildEmenda(array $row, string $recebedorTipo): array
    {
        $modalidadeId = 4; // Outros
        if ($recebedorTipo === 'entidade') {
            $modalidadeId = 2; // Transferência entidade
        } elseif ($recebedorTipo === 'prefeitura' || $recebedorTipo === 'estado') {
            $modalidadeId = 3; // Transferência governamental
        }

        $tipoObjeto = $this->inferTipoObjeto($row['justificativa_raw'] ?? '');
        $gnd = 'outro';
        if ($tipoObjeto === 'saude' || $tipoObjeto === 'assistencia_social') {
            $gnd = 'gnd3';
        } elseif ($tipoObjeto === 'infraestrutura') {
            $gnd = 'gnd4';
        }

        return [
            // Campos explícitos do documento
            'numero'          => $this->formatNumero($row['numero'] ?? null, self::EXERCICIO),
            'exercicio'       => self::EXERCICIO,
            'responsavel'     => 'Não informado',
            'descricao_objeto' => $this->normalizeDescricao($row['justificativa_raw'] ?? ''),
            'valor'           => $row['valor'],

            // Campos inferidos por heurística
            'tipo_objeto'     => $tipoObjeto,
            'tipo_origem'     => 'Municipal',

            // Valores fixos/padrão para este decreto
            'status'          => 'pendente',
            'rascunho'        => false,
            'anuencia_sus'    => false,
            'gnd'             => $gnd,
            'modalidade_id'   => $modalidadeId,

            // FKs resolvidas na etapa de persistência
            'concedente_id'   => null,          // preenchido pelo PersistirAction
            'recebedor_id'    => null,          // preenchido pelo PersistirAction
        ];
    }

    /**
     * Monta o array de dados para a tabela `concedentes`.
     * O concedente é o vereador autor da emenda.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function buildConcedente(array $row): array
    {
        $vereadorNome = $this->normalizeName($row['vereador_raw'] ?? '');
        $partido      = $this->normalizePartido($row['partido_raw'] ?? '');

        return [
            '_resolved_by' => 'row_context',
            'nome'         => $vereadorNome,
            'tipo'         => 'Vereador',
            'partido'      => $partido,
            'descricao'    => "Vereador(a) autor(a) de Emenda Impositiva Decreto nº 134/2026",
        ];
    }

    /**
     * Monta o array de dados para a tabela `recebedores`.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function buildRecebedor(array $row): array
    {
        $razaoSocial = $this->normalizeName($row['destinacao_raw'] ?? '');
        $cnpj        = $row['cnpj'];
        $cnpjDigits  = preg_replace('/\D/', '', $cnpj ?? '');

        return [
            '_resolved_by' => 'cnpj_lookup',
            'razao_social' => $razaoSocial,
            'cnpj'         => $cnpjDigits !== '' ? $cnpjDigits : null,
            'cnpj_valido'  => ($cnpjDigits !== '' && strlen($cnpjDigits) === 14),
            'tipo'         => $this->inferTipoRecebedor($razaoSocial),
            'municipio'    => self::MUNICIPIO_PADRAO,
            'uf'           => self::UF_PADRAO,
            'codigo_ibge'  => 3113404, // Código IBGE de Caratinga/MG
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Heurísticas
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Formata o número da emenda no padrão esperado pelo Model: AAAA/NNN
     * Ex: numero=1, exercicio=2026 → "2026/1"
     */
    private function formatNumero(?int $numero, string $exercicio): ?string
    {
        if ($numero === null) {
            return null;
        }
        return "{$exercicio}/{$numero}";
    }

    /**
     * Infere o tipo do objeto a partir do prefixo da justificativa.
     *
     * Regra:
     *  - "CUSTEIO..."    → 'saude'         (custeio de serviços → geralmente saúde/social)
     *  - "INVESTIMENTO..." → 'infraestrutura' (obras/equipamentos)
     *  - Fallback          → 'assistencia_social'
     *
     * Nota: o campo tipo_objeto no sistema aceita: saude | educacao | infraestrutura | assistencia_social
     */
    private function inferTipoObjeto(string $justificativa): string
    {
        $upper = mb_strtoupper(trim($justificativa), 'UTF-8');

        if (str_starts_with($upper, 'CUSTEIO')) {
            return 'saude';
        }

        if (str_starts_with($upper, 'INVESTIMENTO')) {
            return 'infraestrutura';
        }

        return 'assistencia_social';
    }

    /**
     * Classifica o tipo do recebedor a partir do nome da razão social.
     * Usa os padrões regex em TIPO_RECEBEDOR_PATTERNS, do mais específico ao mais geral.
     */
    private function inferTipoRecebedor(string $razaoSocial): string
    {
        foreach (self::TIPO_RECEBEDOR_PATTERNS as $pattern => $tipo) {
            if (preg_match($pattern, $razaoSocial)) {
                return $tipo;
            }
        }
        return 'outro'; // fallback
    }

    /**
     * Normaliza o nome do partido corrigindo variações tipográficas do documento.
     */
    private function normalizePartido(string $partido): string
    {
        $upper = mb_strtoupper(trim($partido), 'UTF-8');
        return self::PARTIDO_MAP[$upper] ?? $upper;
    }

    /**
     * Normaliza nomes de pessoas e entidades:
     *  - Remove espaços duplos
     *  - Converte para Title Case preservando acentos
     *  - Trata partículas comuns (de, da, do, dos, das)
     */
    private function normalizeName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? $name);

        // Converte para Title Case via mb_convert_case
        $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');

        // Mantém partículas em minúsculo
        $particles = ['De', 'Da', 'Do', 'Dos', 'Das', 'E', 'Em', 'Para', 'Por', 'Com', 'Ao', 'Aos'];
        foreach ($particles as $particle) {
            $name = preg_replace(
                '/\b' . preg_quote($particle, '/') . '\b/',
                mb_strtolower($particle, 'UTF-8'),
                $name
            ) ?? $name;
        }

        // Garante que a primeira letra seja maiúscula mesmo após substituição
        return ucfirst($name);
    }

    /**
     * Normaliza a descrição do objeto:
     *  - Remove espaços duplos
     *  - Converte de UPPER_CASE para Sentence case
     */
    private function normalizeDescricao(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
        if ($text === '') {
            return '';
        }

        // Converte toda a string para minúsculo e capitaliza apenas a primeira letra
        $lower = mb_strtolower($text, 'UTF-8');
        return mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8')
            . mb_substr($lower, 1, null, 'UTF-8');
    }
}
