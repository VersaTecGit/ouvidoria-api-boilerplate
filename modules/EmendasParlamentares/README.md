# Módulo de Emendas Parlamentares — Importador de Decretos

Este módulo possui ferramentas para importação e tratamento de dados de Emendas Parlamentares oriundas de documentos não estruturados (arquivos `.docx` de Decretos).

---

## 🚀 Comando CLI de Importação

Foi disponibilizado o comando Artisan para automatizar a importação das emendas do **Decreto nº 134/2026**.

```bash
php artisan emendas:importar-decreto [opções]
```

### 📋 Opções e Parâmetros

| Opção | Descrição | Valor Padrão |
| :--- | :--- | :--- |
| `--tenant=ID` | O ID do tenant no qual o decreto será importado. **(Obrigatório)** | - |
| `--dry-run` | Simula toda a execução, valida dados, CNPJs e duplicidades sem gravar no banco de dados. | `false` |
| `--file=caminho` | Caminho personalizado para o arquivo `.docx`. | `ANEXO DO DECRETO 134 DE 2026.docx` na raiz do projeto |
| `--force` | Ignora o bloqueio de CNPJs inválidos e força a persistência dessas linhas com um aviso. | `false` |
| `--verbose` | Exibe uma tabela com prévia detalhada das primeiras 5 linhas lidas e traduzidas em memória. | `false` |

---

## 💡 Exemplos de Uso

### 1. Importação para um Tenant Específico (Obrigatório e Exclusivo)
Para rodar a importação em um tenant (ex: `localhost`), execute:
```bash
php artisan emendas:importar-decreto --tenant=localhost --force
```

### 2. Simulação Completa em um Tenant (Recomendado)
Sempre execute uma simulação em modo verboso antes de persistir os dados reais para conferir se o mapeamento lógico e a tradução estão de acordo com o esperado:
```bash
php artisan emendas:importar-decreto --tenant=localhost --dry-run --verbose
```

### 3. Forçando Importação mesmo com CNPJs Inválidos
Por padrão, se houver registros com CNPJs fora do padrão estrutural de 14 dígitos (como a emenda 109, que contém erro de digitação no anexo), o script emitirá alertas e bloqueará a importação para garantir a integridade. Você pode forçar a importação com:
```bash
php artisan emendas:importar-decreto --tenant=localhost --force
```

### 4. Executando um Arquivo em Outro Diretório
```bash
php artisan emendas:importar-decreto --tenant=localhost --file="/caminho/para/outro/decreto_anexo.docx" --force
```

---

## 🏗️ Arquitetura do Pipeline de Importação

A importação é dividida em 4 etapas bem definidas para garantir a separação de responsabilidades e resiliência:

```
[ .docx File ]
      │
      ▼
┌──────────────────────────────────────┐
│ 1. DecretoDocxParserService          │ ◄── Leitura XML (PhpWord)
│    (Parsing & Normalização)          │ ◄── Sanitização e formatação bruta de valores/CNPJ
└──────────────────┬───────────────────┘
                   │  (Array bruto normalizado)
                   ▼
┌──────────────────────────────────────┐
│ 2. EmendaTranslatorService           │ ◄── Classificação de tipo do objeto (custeio/investimento)
│    (Tradução & Heurísticas)          │ ◄── Classificação de tipo do recebedor (prefeitura/entidade/outro)
└──────────────────┬───────────────────┘ ◄── Normalização de nomes e partidos (De/Para)
                   │  (Payload DTO-like estruturado)
                   ▼
┌──────────────────────────────────────┐
│ 3. PersistirEmendasImportadasAction  │ ◄── DB::transaction (Atomicidade)
│    (Persistência & Idempotência)     │ ◄── Upsert Concedente (Câmara Municipal)
└──────────────────┬───────────────────┘ ◄── Upsert Recebedores (CNPJ) e Emendas (Número/Exercício)
                   │
                   ▼
[ Relatório no Terminal ]
```

### 🛠️ Mapeamento de Tipos de Recebedores (Heurística)
O `EmendaTranslatorService` infere os tipos de recebedores no banco de dados (`prefeitura`, `entidade`, `estado`, `outro`) através das seguintes expressões regulares:
- **`prefeitura`**: Mapeado para qualquer razão social contendo termos como `SECRETARIA`, `FUNDO MUNICIPAL`, `FUNDO` ou `UNIDADE`.
- **`entidade`**: Mapeado para entidades do terceiro setor e filantrópicas contendo `FUNDAÇÃO`, `ASSOCIAÇÃO`, `HOSPITAL`, `LAR`, `PROJETO`, `CLUBE`, `INSTITUTO`, `CASA`, `EBENÉZER`, `ASSISTÊNCIA` ou `NÚCLEO`.
- **`outro`**: Fallback para nomes que não correspondam aos padrões acima.
