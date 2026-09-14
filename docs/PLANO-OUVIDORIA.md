# Plano de Desenvolvimento — Módulo Ouvidoria

> Documento de handoff. Uma nova sessão deve ler este arquivo antes de escrever código.
> Última atualização: 2026-09-13 — **Fase 6 (frontend) entregue; Fase 7 validada de ponta a ponta; suíte da API 206/206 verde após correção do vazamento de throttle nos testes.**

## Contexto

`versa_ouvidoria` foi criado a partir da mesma base que `versa_social`
(`C:\Users\Glauber\codes\VersaSocial`). O VersaSocial tem um formulário público
de denúncia (módulo `Social/ChildProtectionCouncils`) acessível **sem autenticação**.

Esse formulário **não existe** no versa_ouvidoria — foi criado só no social, depois do fork.
A busca por `denuncia|complaint` aqui retorna apenas um label em
`modules/Auth/Support/PermissionGroups.php`, sem relação com o form.

**Decisão:** replicar a arquitetura, não copiar o código. O domínio do social
(SIPIA, vítima/agressor, conselho tutelar, deliberação colegiada) não se aplica.

## Domínio

"Denúncia" aqui é apenas **um dos seis tipos** de manifestação. Portanto a entidade
é **Manifestação** (`manifestations`), não `Complaint` — evita um `Complaint` cujo
`type` pode ser "Elogio".

### Campos da manifestação

**Manifestante** (bloco condicional — some quando anônimo):
- Nome Completo ou Razão Social — obrigatório ao se identificar
- E-mail — obrigatório ao se identificar
- Telefone — obrigatório ao se identificar
- CPF / CNPJ — opcional (um campo só; validar por tamanho com as rules `Cpf`/`Cnpj` em `modules/Common/Core/DTOs/Concerns/Rules/`)
- Endereço — opcional

**Manifestação** (sempre obrigatórios):
- Tipo: Reclamação, Denúncia, Elogio, Sugestão, Solicitação, Crítica
- Órgão / Secretaria Destinatária
- Assunto
- Descrição detalhada
- Local da Ocorrência
- Anexos (opcional)

### Anonimato — padrão do VersaSocial (confirmado pelo usuário)

Se o usuário quiser se revelar, solicite os dados; caso contrário, não solicite.
Três partes, replicadas de `ComplaintDenunciatorCard.tsx`:

1. Checkbox `is_anonymous` que, ao marcar, faz `form.setValue("manifestant", null)` — limpa de fato
2. Render condicional `{!form.watch("is_anonymous") && (...)}` — campos nem aparecem
3. Zod `superRefine` — campos `optional()` na base, exigidos só quando `!is_anonymous`

Validar **também no backend** (o social só valida como `array` nullable; aqui o
`ManifestantDTO` deve exigir os campos quando `is_anonymous = false`).

### Status

`recebida → em_analise → respondida → arquivada`

Sem a regra de "vítima identificada" do social (específica do conselho tutelar).

### Resposta ao cidadão — parecer do atendente

**Não há** fluxo de tramitação entre órgãos internos (decisão do usuário).
Não é e-mail nem mensageria: no social, `Notifications/`, `Events/`, `Listeners/`
e `Jobs/` estão todos vazios. É **status + timeline**:

- `manifestation_logs` é o canal de resposta (o cidadão lê como "Histórico de Andamento")
- Campos `parecer`, `respondido_por` (FK users), `respondido_em`
- Ao salvar o parecer → gera log **público** e fecha em `respondida`
- Nota interna → log com `is_public = false`, invisível no endpoint público
- `CreateLog` action (não existe no social) permite ao atendente escrever resposta textual

## Acesso público sem autenticação — como funciona

Três camadas, todas independentes de sessão:

1. **Proxy Next.js** — `ui-boilerplate/proxy.ts`: prefixo no `isPublicPage` escapa do redirect para `/auth/login`
2. **AuthProvider** — `modules/Auth/Providers/AuthProvider.tsx`: early-return no catch, em vez de empurrar para login
3. **Rotas Laravel** — fora do `Route::middleware('auth')`

Precedente local pronto: `/questionnaires` já é público nas três camadas.

**Tenancy sem login:** `InitializeTenancyByRequestData` resolve o tenant pelo header
**`X-Domain`**, não pelo usuário. O `unit_id` vem **do corpo da requisição**
(UUID escolhido num select), nunca de `auth()->user()`.

## Protocolo — é a única via de retorno

`Complaint::generateProtocolNumber()` do social (copiar, trocando o prefixo):

```php
$random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
return "OUV-{$year}-{$random}";
```

`random_bytes(4)` é CSPRNG e os 4 bytes sobrevivem → **32 bits de entropia real**.
Não é sequencial; adivinhar é inviável. Entrega: só a tela de conclusão, com botão
de copiar. O anônimo não informa e-mail nem telefone, então **sem copiar o protocolo
a manifestação fica inacessível para ele** — a tela precisa de aviso explícito.

## Endpoint público deve ser enxuto (correção em relação ao social)

O social expõe `GET /complaints/details/{protocol}` com o
`ComplaintDetailsResource` **completo**: dados do denunciante, CPF/filiação/endereço
da vítima, `deliberation_notes`, `deliberation_votes` e URLs dos anexos. O frontend
mostra 4 campos, mas o JSON carrega tudo (visível no DevTools).

Não é falha de autenticação — o protocolo é um bearer secret legítimo. O problema é
**superexposição depois do acesso**: o token viaja em print, celular compartilhado,
impressão — muitas vezes na casa do acusado.

**Aqui:** `PublicManifestationResource` separado, retornando **apenas** protocolo,
tipo, status, data de envio e logs `is_public = true`. Nunca manifestante, anexos
ou notas internas. Mais `throttle` como higiene de abuso em rota anônima.

## Fases

- [x] **Fase 1 — Units** ✅ concluída e validada em execução
- [x] **Fase 2 — Órgãos/Secretarias** (`destination_agencies`) ✅ concluída e validada em execução
- [x] **Fase 3 — Manifestações** (migration, models, enums, DTOs, actions, controller, resources, rotas) ✅ concluída e validada em execução
- [x] **Fase 4 — Endpoint público enxuto** (`PublicManifestationResource` + throttle + gerador de protocolo) ✅ concluída — 15/15 testes verdes (101 asserções, 9,5 s) em 2026-09-13, após mover o ambiente para o WSL (ver "Estado da máquina")
- [x] **Fase 5 — Upload público** (signed URL, `throttle:10,1`) ✅ concluída — 9/9 testes verdes
- [x] **Fase 6 — Frontend** (rotas públicas, layout, form, tela de conclusão com protocolo, timeline; telas internas) ✅ entregue em 2026-09-13 — `tsc`/`eslint`/`prettier` limpos, fluxo anônimo validado via curl (ver abaixo)
- [x] **Fase 7 — Liberar acesso** (`proxy.ts` + `AuthProvider.tsx`) ✅ concluída — validada de ponta a ponta com a Fase 6 (ver abaixo)
- [ ] **Fase 8 — Testes** (criação anônima sem token, consulta por protocolo, gestão exigindo auth, regressão de não-vazamento)

### Fase 1 — entregue

Módulo `api-boilerplate/modules/Ouvidoria/`, seguindo o padrão de `Transport`:

- Migration `2026_09_12_100000_create_units_tables.php`: `unit_types`, `units`, `unit_user`
- Models `Unit` (global scope `active-units`) e `UnitType`
- DTOs `CreateUnitDTO`, `UpdateUnitDTO`, `UnitAddressDTO`
- Actions: Create, Fetch, FetchUnitsList, Update, Delete, FetchUnitTypesList
- Controllers `UnitController` (CRUD + `publicIndex`), `UnitTypeController`
- Resources `UnitResource`, `PublicUnitResource` (só `id` + `name`), `UnitTypeResource`
- `Routes/v1.php`, `UnitSeeder` (tipo + unidade "Ouvidoria" `OUV-001`)
- Modificados: `config/modules.php`, `Permissions.php` (5 permissões em 4 pontos),
  `PermissionGroups.php` (grupo `Ouvidoria:Unidades`), `DatabaseSeeder.php`

**Decisão:** a `units` do social **não serve para cópia** — `operator_unit`,
`unit_managers` e `Unit::operators()` referenciam `Modules\Auth\Models\Operator`,
**modelo que não existe aqui** (só há `User`). Também arrasta `municipal_councils`,
`unit_care_plans`, territórios e 20+ actions de domínio da assistência social.
Criada uma versão enxuta com o mesmo formato de chave (`id` + `uuid` + `code`) e as
mesmas convenções — é isso que mantém a porta aberta, não os campos extras.
Vínculo com usuário é `unit_user` (não `operator_unit`).

Hierarquia futura entre setores: `parent_unit_id` é aditivo, não migra dados.

### Fase 2 — entregue

Tabela de domínio, migrável para `units` depois (decisão do usuário):

```
destination_agencies: id, uuid, name, active, order, timestamps, softDeletes, userActions
```

Mesmo formato de chave de `units` (referenciada por UUID na API). Quando virar
unidade: `INSERT ... SELECT` + troca de FK, sem tocar no contrato público —
o front sempre mandou UUID e o select sempre consumiu `{id, name}`.

Criado em `modules/Ouvidoria/`, espelhando o padrão da Fase 1:

- Migration `2026_09_12_110000_create_destination_agencies_table.php`
- Model `DestinationAgency` (global scope `active-destination-agencies`)
- DTOs `CreateDestinationAgencyDTO`, `UpdateDestinationAgencyDTO`
- `DestinationAgencyFilters` (`active`, `created_at`)
- Actions: Create, Fetch, FetchDestinationAgenciesList, Update, Delete
- `DestinationAgencyController` (CRUD + `publicIndex`)
- Resources `DestinationAgencyResource` e `PublicDestinationAgencyResource` (só `id` + `name`)
- `DestinationAgencySeeder` — 9 órgãos baseline, idempotente (`firstOrCreate` por nome),
  `order` em múltiplos de 10 para permitir inserção entre itens sem renumerar
- Modificados: `Routes/v1.php`, `Permissions.php` (5 permissões nos 4 pontos),
  `PermissionGroups.php` (grupo `Ouvidoria:Órgãos Destinatários`), `DatabaseSeeder.php`

**Ordenação:** `FetchDestinationAgenciesList` aplica `orderBy('order')->orderBy('name')`
como padrão. `Datatable::applySort` é no-op quando `sort_field` vem vazio, então o
sort explícito do cliente continua vencendo — o default só vale para o combo público.

**Atenção ao `PermissionGroups::fromPermission`:** o `match` é uma cadeia de
`str_contains` avaliada em ordem. O arm de `destination-agencies` foi colocado
**antes** do de `unit`. Hoje não há colisão entre os dois, mas a ordem importa
para qualquer permissão futura cujo nome contenha o substring de outra.

**Órgãos do seeder são genéricos** (Gabinete + 8 secretarias comuns) — trocar
pelos órgãos reais do município antes de ir a produção.

### Estado da verificação (Fase 2)

Validado em execução real:

- Migration aplicada; tabela conferida no `psql` com todas as colunas, os 4 índices
  (`name`, `active`, `order`, `uuid` unique) e as 3 FKs de `userActions`
- `DestinationAgencySeeder` executou: 9 órgãos com UUID gerado e acentuação correta
- `route:list` mostra as **6 rotas** (5 CRUD autenticadas + 1 pública)
- `GET /api/v1/public/destination-agencies` → **HTTP 200 sem auth**, só `{id, name}`,
  `X-RateLimit-Limit: 60`, ordenado por `order`
- `GET`/`POST /destination-agencies` e `GET /destination-agencies/{uuid}` → **401** sem token
- Órgão desativado (`active = false`) → **sai do endpoint público** (9 → 8);
  linha restaurada depois, zero inativos no banco
- **CRUD autenticado exercitado de ponta a ponta** com JWT do `admin`:
  `GET` lista → **200** com payload completo; `POST` → **201** (default `active: true`
  aplicado pelo DTO); `PUT` parcial → **200** (renomeia e desativa, `order` preservado);
  `GET /{uuid}` de registro **inativo** → **200** (o `FetchDestinationAgency` tira o
  global scope, então a gestão enxerga o que o público não enxerga); `DELETE` → **204**
  e o `GET` seguinte → **404**, com soft delete confirmado no banco
- Registro de teste removido; estado final: 9 órgãos vivos, 0 inativos

### Armadilha encontrada na Fase 2 — 403 por permissão não semeada

A primeira tentativa de CRUD autenticado devolveu **403** (não 401): o JWT autenticava,
mas o `->can('ALL-list-destination-agencies')` barrava. As 5 permissões estavam no enum
`Permissions.php` mas **não no banco** — faltava rodar o `PermissionSeeder`:

```bash
php artisan tenants:seed --class='Database\Seeders\PermissionSeeder'
```

Depois disso, as 5 permissões aparecem em `permissions` e o CRUD passa. Não exigiu
mudança de código: `DefaultRoles::ADMIN` já mapeia para `Permissions::all()`.

**Custo do ambiente:** `POST /auth/login` levou **38-58s** nesta máquina (o `GET`
público responde instantâneo). Não investigado — se atrapalhar, olhar custo do
bcrypt e contenção do container. Use `-m` generoso no curl ao testar login.

**O `LoginDTO` espera o campo `login`** (não `username`) no corpo do POST.

**Não há tela** para órgãos destinatários — o frontend do módulo é a Fase 6.

### Fase 3 — entregue (2026-09-13)

Backend completo da gestão autenticada. O endpoint público de criação e a
consulta por protocolo ficaram para a Fase 4, como o plano prevê — o gerador
de protocolo já está no model e em uso.

Duas tabelas:

```
manifestations:      id, uuid, protocol_number (unique), type, status,
                     destination_agency_id, unit_id, subject, description,
                     occurrence_place, is_anonymous, user_id,
                     manifestant_{name,email,phone,document,address},
                     parecer, responded_by_id, responded_at,
                     timestamps, softDeletes, userActions
manifestation_logs:  id, uuid, manifestation_id, content, is_public, status,
                     author_id, timestamps, softDeletes, userActions
```

- Migration `2026_09_13_120000_create_manifestations_tables.php`
- Enums em `Support/`: `ManifestationType` (6 tipos), `ManifestationStatus` (4 status)
- Models `Manifestation` (HasMedia, `cascadeDeletes: logs`) e `ManifestationLog`
- Rule `Rules/CpfOrCnpj` — delega para `Cpf`/`Cnpj` por contagem de dígitos
- DTOs: `CreateManifestationDTO`, `UpdateManifestationDTO`,
  `RespondManifestationDTO`, `CreateManifestationLogDTO`
- `ManifestationFilters` + `WhereDestinationAgencyIdFilter` / `WhereUnitIdFilter`
- Actions: Create, Fetch, FetchManifestationsList, Update, Delete,
  CreateManifestationLog, RespondManifestation
- Resources `ManifestationResource` (completo), `ManifestationListResource`
  (enxuto para datatable) e `ManifestationLogResource`
- `ManifestationController` — CRUD + `respond` + `storeLog`
- Modificados: `Routes/v1.php` (7 rotas), `Permissions.php` (6 permissões nos
  4 pontos), `PermissionGroups.php` (grupo `Ouvidoria:Manifestações`)

**Manifestante é coluna, não entidade — e por quê.** Não existe módulo de
pessoas neste repositório: a única entidade de pessoa é `Modules\Auth\Models\User`,
que **não serve** como manifestante — `login` é unique, `password` é NOT NULL,
não há auto-registro (as rotas públicas de `Auth` são só login/refresh/reset) e
não há CPF, telefone nem endereço. Criar `users` a partir de um POST anônimo
abriria criação de contas não autenticada no mesmo banco que autentica os
atendentes. Os dados ficam planos em `manifestant_*`: são o registro do que foi
declarado **naquele momento**, que continua correto mesmo se a pessoa mudar de
e-mail depois.

**`user_id` é a porta aberta para o portal do cidadão** (decisão do usuário):
FK nullable já criada e indexada, sempre NULL hoje. Quando o portal existir,
o vínculo é preenchido por e-mail/CPF — sem migração de dados e sem tocar no
contrato público. Aditivo, como o `parent_unit_id` da Fase 1.

**Anonimato validado no backend, não só no front.** O DTO usa
`exclude_if:is_anonymous,true` + `required_if:is_anonymous,false`, e o
`CreateManifestation` ainda anula os campos antes do `save()`. É redundante de
propósito: o endpoint da Fase 4 é alcançável sem passar pelo formulário.

**`parecer` e timeline andam juntos.** `RespondManifestation` grava o parecer,
`responded_by_id`, `responded_at`, move para `respondida` **e** emite o log
`is_public = true` na mesma transação — o cidadão lê a timeline, não a coluna,
então um parecer sem log seria invisível para quem manifestou.
`CreateManifestationLog::write()` é compartilhado pelas duas actions.

**`ManifestationListResource` existe para não pagar anexo por linha.** A
listagem omite `description`, `logs`, manifestante completo e anexos — cada
anexo custaria uma URL temporária por registro na datatable.

### Armadilha encontrada na Fase 3 — `->constrained()->index()` colide

Encadear `->index()` depois de `->constrained()` **consome o nome da constraint**:
o Laravel nomeia todas as FKs da tabela como `"1"` e a segunda estoura
`SQLSTATE[42710] Duplicate object: constraint "1" already exists`. Na Fase 1
passou despercebido porque `units` tem uma FK só.

Correto: `->constrained()` sozinho, e `$table->index('coluna', 'nome_idx')` em
linha separada quando o índice for desejado. **O Postgres não indexa FK
automaticamente** — só cria a constraint. Foram criados dois índices explícitos
(`manifestations_agency_idx`, `manifestation_logs_manifestation_idx`); as demais
FKs seguem sem índice, como em `units.unit_type_id` da Fase 1.

### Armadilha do ambiente — Git Bash engole a saída do `docker compose exec`

> Vale para comandos disparados **do Windows** (Git Bash). Desde 2026-09-13 o
> ambiente roda de dentro do WSL Ubuntu (ver "Ambiente"), onde o bash comum
> funciona normalmente. O restante desta seção é histórico do período no NTFS.

Rodar `docker compose exec -T laravel.test php artisan ...` pelo **Bash** devolve
**exit 0 e saída vazia**, mesmo quando o comando falha. Foi isso que escondeu o
erro de FK acima por várias tentativas, simulando um "travamento". Use
**PowerShell** para qualquer artisan cujo resultado importe.

Dois sintomas relacionados, ambos do filesystem 9p do Docker Desktop:
`php artisan` chega a travar em `p9_client_rpc` (visível em `/proc/<pid>/wchan`,
estado `Ds`) e um `exec` pesado já derrubou o container do `pgsql` com exit 137.
Se acontecer: `kill -9` no processo e `docker compose up -d` para reerguer.

**`curl -m` curto dá falso negativo:** um POST que atingiu o timeout do curl
**gravou** no banco mesmo assim; o retry criou registro duplicado. Use `-m 240`
e confira o banco antes de repetir um POST.

### Estado da verificação (Fase 3)

Validado em execução real:

- Migration aplicada; schema conferido no `psql` — 26 colunas em `manifestations`,
  13 em `manifestation_logs`, todas as FKs nomeadas, `ON DELETE SET NULL` em
  `unit_id`/`user_id`/`responded_by_id`/`author_id` e cascade em `manifestation_id`
- `tenants:rollback --step=1` + `migrate` refeitos limpos — o `down()` funciona
- As 6 permissões `ALL-*-manifestations` gravadas em `permissions` pelo `PermissionSeeder`
- `route:list --path=manifestations` mostra as **7 rotas**
- `GET`/`POST /manifestations` → **401** sem token
- **POST identificada** → **201** com `OUV-2026-7D77065E`, status `recebida`
- **POST anônima com dados de manifestante no payload** → **201** e as 5 colunas
  `manifestant_*` gravadas **NULL no banco** (conferido no `psql`): o anonimato
  não depende do frontend
- **POST identificada sem nome/e-mail/telefone** → **422** com os 3 erros
- CPF inválido → **422**; CNPJ válido (14 dígitos) → **201** — `CpfOrCnpj` ok
- `POST /{uuid}/logs` com `is_public: false` + `status` → **201**, autor gravado
  e a manifestação movida para `em_analise`
- `POST /{uuid}/respond` → **200**: `parecer`, `responded_by`, `responded_at`,
  status `respondida` e timeline com **2 logs** (1 interno + 1 público)
- `PUT` parcial de triagem → **200** (status e subject trocados, resto preservado)
- Filtros `status`, `type` e `destination_agency_id` (por UUID) e `search`
  (com `unaccent`) retornam o esperado
- `DELETE` → **204**, `GET` seguinte → **404**, soft delete confirmado no banco
- `ManifestationListResource` conferido no JSON: sem `description`, sem `logs`,
  sem anexos
- Registros de teste removidos; estado final: 0 manifestações, 0 logs,
  Fases 1 e 2 intactas
- **Pint limpo** nos 60 arquivos de `modules/Ouvidoria` + `modules/Auth/Support`
  (corrigido de passagem um `use` não utilizado pré-existente em `DeleteUnit.php`).
  Não há config de PHPStan no projeto

**Não há tela** para manifestações — o frontend do módulo é a Fase 6.

### Fase 6 — entregue (2026-09-13)

Frontend completo em `ui-boilerplate`, branch `feat/fase-6-frontend-ouvidoria`
(sobre `main`, commit `80edd2d`). Módulo novo `modules/Ouvidoria/` espelhando o
padrão de `Transport`; páginas em `app/ouvidoria/` (público) e no route group
`app/(ouvidoria)/` (interno — o parêntese não aparece na URL, então não colide
com o prefixo público do bypass).

**Rotas públicas — prefixo `/ouvidoria`, fixado na Fase 7:**

| URL | O que faz |
|---|---|
| `/ouvidoria` | Formulário de manifestação; ao enviar, vira a **tela de conclusão** no mesmo lugar (protocolo em fonte mono, botão copiar, aviso em vermelho de que não há outra via de acesso, link para acompanhar) |
| `/ouvidoria/consulta` | Campo de protocolo (validado no cliente com a mesma regex da rota Laravel; caixa indiferente) |
| `/ouvidoria/consulta/[protocol]` | Tipo, status, data e **Histórico de Andamento** (só logs públicos — é o que a API devolve). Protocolo malformado nem chega à API |

Layout próprio (`Components/Public/PublicLayout.tsx`): header com logo, alternância
de tema e dois botões (nova / consultar), sem sidebar nem menu de usuário — o
`RootLayout` autenticado ficaria no loader para sempre sem `userData`. Mesmo
desenho do `QuestionnaireResponseLayout`.

**Rotas internas (dentro do `RootLayout`, permissão em cada página):**

| URL | Permissão | Tela |
|---|---|---|
| `/manifestations` | `list-manifestations` | Datatable (protocolo, tipo, status, assunto, órgão, manifestante/Anônima, data); filtros status, tipo, anônima, protocolo; busca |
| `/manifestations/create` | `create-manifestations` | Registro pelo atendente (presencial/telefone) — mesmos campos do form público, upload pela rota autenticada genérica |
| `/manifestations/[id]/view` | `view-manifestations` | Detalhe: relato, triagem, anexos (URL temporária de 2 h), manifestante (ou "anônima"), parecer, timeline com notas internas marcadas; botões **Registrar andamento** e **Responder** (`respond-manifestations`) e **Triagem** (`edit-manifestations`) |
| `/manifestations/[id]/edit` | `edit-manifestations` | Só triagem: tipo, status, órgão, unidade, assunto, local. O relato do cidadão não é editável |
| `/destination-agencies` (+ create/edit/view) | `*-destination-agencies` | CRUD: nome, ordem, ativo. Ordenação padrão por `order` |
| `/units` (+ create/edit/view) | `*-units` | CRUD: nome, código, tipo (combo de `unit-types`), CNPJ, horários, descrição, endereço opcional, contatos (`ContactsTable` do Common) |

Menu: grupo **Ouvidoria** (ícone `Megaphone`) em `RootNavItems.tsx`, antes de
"Logística e Transporte".

**Anonimato, como o plano pede** (`Forms/_partials/ManifestationFormFields.tsx`):
o checkbox faz `form.setValue("manifestant", null)`; o bloco só renderiza com
`!watch("is_anonymous")`; o Zod tem os campos `optional()` na base e um
`superRefine` exige nome/e-mail/telefone só quando identificado. O payload
(`Lib/toManifestationPayload.ts`) achata para `manifestant_*` e, quando anônimo,
**não envia nada** da pessoa — nem string vazia. CPF/CNPJ: um campo, máscara
dupla, validado por contagem de dígitos com os mesmos `cpfRefine`/`cnpjRefine`
do Common, enviado só com dígitos.

**Anexos:** `pdf/jpg/png`, 5 arquivos, 10 MB — as constantes em
`Constants/PublicAttachments.ts` espelham a allow-list da Fase 5. Fluxo público
(`Actions/publicUploadFiles.ts`): server action pede a signed URL em
`public/manifestations/uploads/signed-storage-url`, o browser faz o `PUT`, e o
create recebe `{uuid, bucket, key, extension}` com `extension` **tirada da key**
(a API cruza os dois). O registro interno usa o `storeFile` genérico do Common.

**Decisões miúdas que valem registro:**
- Combo de unidade só aparece quando há **mais de uma** unidade ativa; com uma só
  ela é aplicada em silêncio no payload (hoje há só "Ouvidoria").
- Erros 422 da API chegam como `manifestant_name` etc.; o form público remapeia
  para `manifestant.name` antes do `setError`.
- 429 (throttle por IP) vira mensagem própria ("Muitas tentativas…"), não erro genérico.
- Combos internos (`Containers/useManifestationCombos.ts`) usam as listas
  autenticadas com `per_page=all` e filtro `active=true`.
- Timeline (`Manifestation/ManifestationTimeline.tsx`) é **um** componente para
  cidadão e atendente: só mostra `is_public`/autor quando a entrada os traz, então
  nunca inventa campo na visão anônima.

**Verificação (2026-09-13):** `npx tsc --noEmit` **0 erros**; `eslint` **0** nos
arquivos novos; `prettier --write` aplicado. `package-lock.json` intacto (não houve
`npm install`). Contrato da API exercitado por `curl` com `X-Domain: localhost`
e sem token: `public/destination-agencies?per_page=all` → 9 órgãos;
`public/units?per_page=all` → 1; `POST public/manifestations` anônima com o
payload exato do `toManifestationPayload` → **201**; `GET` por protocolo → **200**;
identificada sem nome/e-mail/telefone → **422** com os 3 erros. Os dois registros
de teste criados (`[teste fase 6]`) foram apagados do tenant `localhost`.

**Como testar sem navegador** (não há Chromium/Playwright na máquina): as server
actions do Next respondem a `POST` na URL da página com o header
`Next-Action: <id>`, `Accept: text/x-component` e body JSON com os argumentos.
O `<id>` está no chunk do cliente que importa a action — baixe o HTML da página,
siga os `src` de `_next/static/chunks/` e procure
`"<40 hex>":"nomeDaAction"`. Foi assim que o fluxo anônimo foi validado
atravessando o Next de verdade.

**O que não foi testável localmente:** o `PUT` do anexo no storage — o disco
`central` é S3 e o `.env` está sem credenciais AWS, então a signed URL não é
gerada fora dos testes (que mockam o serviço). Interações de tela (máscaras,
combobox, dropzone) também não — só compilação e contrato. **Fase 8** deve cobrir:
fluxo anônimo com anexo em ambiente com S3/minio, login e clique nas telas
internas, e regressão de não-vazamento no `PublicManifestationResource`.

### Fase 7 — entregue (2026-09-13)

Feita **fora de ordem**, antes das Fases 3–6, por ser isolada e estar em outro
repositório (`ui-boilerplate`), sem colidir com a Fase 2. Branch:
`feat/fase-7-acesso-publico-ouvidoria`, commit `681dc51` (2 arquivos, 4 linhas).

**O prefixo público escolhido é `/ouvidoria`.** A Fase 6 **deve** criar
`ui-boilerplate/app/ouvidoria/` — não `app/manifestacoes/` nem `app/manifestations/`.
Se o diretório usar outro nome, o bypass não casa e o visitante anônimo será
redirecionado para `/auth/login`. Optou-se pelo nome do serviço (reconhecível pelo
cidadão, cobre form e consulta) em vez do nome da entidade; as rotas internas seguem
em inglês, mas esta é URL pública.

As três camadas, todas independentes de sessão:

1. `ui-boilerplate/proxy.ts` — `/ouvidoria` somado ao `isPublicPage`, junto de
   `/questionnaires`
2. `ui-boilerplate/modules/Auth/Providers/AuthProvider.tsx` — early-return no catch
   do `refreshUserData`
3. Rotas Laravel — **já estavam prontas**: `Route::prefix('public')` em
   `modules/Ouvidoria/Routes/v1.php` fica fora do `Route::middleware('auth')`, e o
   `ModuleServiceProvider` aplica só `tenant`/`api`/`InitializeTenancyByRequestData`

**Validada de ponta a ponta em 2026-09-13, junto com a Fase 6.** Com o dev server
na 3001 e **sem cookie**: `GET /ouvidoria`, `/ouvidoria/consulta` e
`/ouvidoria/consulta/{protocolo}` → **200**; `GET /manifestations` e `/units` →
**307 → /auth/login** (o bypass abre só o prefixo público). As server actions
públicas foram disparadas pelo Next via `curl` com o header `Next-Action`
(ver "Como testar sem navegador" na Fase 6): `fetchPublicDestinationAgencies` e
`fetchPublicUnits` → `success: true`; `createPublicManifestation` anônima →
**201** com protocolo; `fetchPublicManifestation` → **200** com timeline, e
`notFound: true` para protocolo inexistente. Isso prova as três camadas juntas:
proxy, `X-Domain` derivado do `host` (`localhost:3001` → `localhost`) e rota
Laravel sem `auth`.

**Ajuste que a Fase 6 trouxe para esta camada:** `createHttpClient`
(`modules/Common/Services/http.service.ts`) só envia `Authorization` quando há
token. Antes, o visitante anônimo mandava `Bearer null` em toda requisição —
inofensivo (a rota pública ignora), mas ruído que confundia leitura de log.

**Ruído de ambiente:** um `npm install` no Windows reescreve `package-lock.json`
removendo blocos `"libc": ["glibc"|"musl"]` de dependências nativas opcionais, sem
que `package.json` mude. Isso **não** deve ser commitado — reverta com
`git checkout -- package-lock.json` antes de fechar um commit no `ui-boilerplate`.

### Desenho das rotas públicas — fixado em 2026-09-13

Fixado **antes** de disparar as Fases 4 e 5, que foram implementadas em paralelo
por agentes distintos na branch `feature/ouvidoria-fase-4-5` (criada sobre
`feature/ouvidoria-fase-3`, commit `2ad281c`). Tudo em `Route::prefix('public')`,
fora do `auth`, throttle por IP (não há usuário para chavear):

| Método | Rota | Throttle | Controller |
|---|---|---|---|
| `POST` | `public/manifestations/uploads/signed-storage-url` | 10/min | `PublicManifestationSignedStorageUrlController@store` |
| `POST` | `public/manifestations` | 10/min | `PublicManifestationController@store` |
| `GET` | `public/manifestations/{protocol}` | 30/min | `PublicManifestationController@show` |

`{protocol}` é restrito por `->where('protocol', 'OUV-\d{4}-[A-F0-9]{8}')` — lixo
nem chega à query (404 direto da rota).

**Contrato entre as duas fases — a key do anexo.** `CreateManifestation` faz
`addMediaFromDisk($attachment->key, 'central')` com a key vinda do cliente. Na
rota autenticada é aceitável; na anônima, uma key arbitrária anexaria qualquer
objeto do bucket a uma manifestação que o atendente depois abre. Por isso:

- A Fase 5 **emite** keys exatamente na forma `public-manifestations/<uuid v4>.(pdf|jpg|png)`,
  com a extensão derivada de um mapa fixo por `content_type` — **nunca** do
  `file_name` enviado.
- A Fase 4 **só aceita** anexos cuja key case com essa regex, cujo `uuid` e
  `extension` sejam os embutidos na key, e cujo `bucket` seja o do disco `central`.
  Qualquer divergência → 422.

### Fase 5 — entregue (2026-09-13)

- `modules/Common/Core/Support/SignedStorageUrlService.php` — o
  `PublicQuestionnaireSignedStorageUrlService` **promovido para Common** (era
  genérico; a Ouvidoria não deve depender de `Questionnaires`, e duplicar o
  presign seria pior). Classe **não-final** de propósito: Mockery não mocka
  `final`, e os testes dos dois módulos precisam mocká-la. O action dos
  questionários passou a injetar a versão do Common; o arquivo antigo foi removido.
- `Actions/CreatePublicManifestationSignedStorageUrl` — `ALLOWED_CONTENT_TYPES`
  (`application/pdf→pdf`, `image/jpeg→jpg`, `image/png→png`), `MAX_FILE_SIZE_BYTES`
  = 10 MB; 422 `Tipo de arquivo não permitido.` / `Arquivo excede o tamanho máximo permitido.`
- `DTOs/CreatePublicManifestationSignedStorageUrlDTO` (`content_type`, `file_name` ≤255, `file_size` ≥1)
- `Controllers/PublicManifestationSignedStorageUrlController` — 201 com `{uuid, bucket, key, url, headers}`
- Testes: `tests/Feature/Ouvidoria/PublicManifestationUploadsApiTest.php` (8) e
  `tests/Feature/Questionnaires/PublicQuestionnaireSignedStorageUrlApiTest.php` (1,
  prova que o endpoint dos questionários sobreviveu à promoção). **9/9 verdes**, Pint limpo.

**Para a Fase 6:** o front faz `POST .../uploads/signed-storage-url` com
`{content_type, file_name, file_size}`, recebe `{uuid, bucket, key, url, headers}`,
faz `PUT` dos bytes em `url` com `headers`, e envia no create
`attachments[] = {uuid, bucket, key, extension}` com `extension` igual ao sufixo da key.

### Fase 4 — concluída (2026-09-13)

- `Controllers/PublicManifestationController` (`store`, `show`) — usa **só**
  `PublicManifestationResource`; docblock proíbe `ManifestationResource` ali.
- `DTOs/CreatePublicManifestationDTO extends CreateManifestationDTO` — herda
  anonimato/CPF/órgão e endurece `attachments.*` (regex da key, `bucket` do disco
  `central`, `extension` na allow-list, e `after()` cruzando uuid/extensão com a key).
  Reaproveita `CreateManifestation::handle()` sem alterá-lo.
- `Actions/FetchPublicManifestation` — por `protocol_number`, carrega só `publicLogs`
  (ordem cronológica), `firstOrFail`. Separado do `FetchManifestation`, que carrega
  relações internas.
- `Resources/PublicManifestationResource` → `{protocol_number, type, status, created_at, logs[]}`;
  `PublicManifestationLogResource` → `{content, status, created_at}`. Nada mais — o
  docblock diz "não adicione campos aqui".
- Testes: `tests/Feature/Ouvidoria/PublicManifestationsApiTest.php` (15) + helpers
  `UnitsHelper`, `DestinationAgenciesHelper`, `ManifestationsHelper`. Última rodada
  (sob colisão de banco com a Fase 5): 13 passaram; 1 falha era a colisão e 1 era
  fixture (`created_at` não-fillable no helper de log), já corrigida. Rodada limpa
  em 2026-09-13, já no WSL: **15 passaram, 101 asserções, 9,53 s** (11 s de parede).

### Estado da máquina — causa raiz da lentidão dos testes (fechada em 2026-09-13)

**Causa raiz provada:** o bind mount `.:/var/www/html` servido por **9p (drvfs)** a
partir do NTFS (`C:\Users\Glauber\codes\...`). Cada `stat`/`open` do autoloader
atravessa o servidor de arquivos do lado Windows; a pressão de memória do host
(15,7 GB, 0,2–0,4 GB livres) só amplifica. Não é PHP, não é o teste, não é o fato
de a UI rodar fora do Docker.

A/B no **mesmo container, mesmo PHP, mesmo código**, mudando só o disco:

| Operação | Disco nativo (ext4/overlayfs) | Bind mount 9p (NTFS) |
|---|---|---|
| `php artisan --version` | 0,4 s | 15,8 s (oscilou até 1m54s) |
| PHPUnit sem nenhum teste | 0,5 s | 32,1 s |
| CPU consumida (user) | ~0,3 s | ~0,3 s |
| `find vendor` (15.680 arquivos) | instantâneo | 60 s |
| `PublicManifestationsApiTest` (15 testes) | **11 s** | ~4 min para **1** teste |

Descartados com evidência: Xdebug (carregado, `XDEBUG_MODE=off`) e OPcache CLI
(off) valem igual nas duas colunas; o VersaSocial usa a **mesma** arquitetura
(NTFS + 9p, 15.496 arquivos em vendor), então "compose idêntico ao VersaSocial,
que não sofre" não era comparação válida. O diagnóstico anterior (só memória do
host) explicava a oscilação, não o piso de 15–30 s por bootstrap.

**Solução aplicada (2026-09-13):** código movido para a distro **Ubuntu** do WSL2
(ext4), compose executado de dentro dela, UI com Node 22 via nvm no Ubuntu. O
`.wslconfig` (`memory=6GB`, `swap=2GB`) continua valendo. A cópia em
`C:\Users\Glauber\codes\versa_ouvidoria` ficou como **backup congelado**: não
edite nem rode `docker compose` a partir dela (mesmo nome de projeto → o `up`
recriaria o container apontando o mount de volta para o NTFS).

**Testes nunca em paralelo.** `RefreshDatabaseWithTenant` faz `DROP DATABASE` no
tenant `foo` e o `RefreshDatabase` roda `migrate:fresh` no `testing` central: duas
suítes simultâneas derrubam uma à outra (`Tenant could not be identified on domain foo`).
Uma rodada por vez, sempre.

## Convenções do projeto (seguir)

- Módulos em `api-boilerplate/modules/<Nome>/`, registrados em `config/modules.php`
- Rotas em `modules/<Nome>/Routes/v1.php`, prefixadas com `api/v1` pelo `ModuleServiceProvider`
- Models estendem `Modules\Common\Core\Models\Model` (uuid, softDeletes, userActions, Filterable automáticos)
- DTOs usam `WendellAdriel\ValidatedDTO` com `rules()`/`defaults()`/`casts()`
- FK por UUID: `fn (string $p, mixed $v) => Model::findByUuid($v)->id`
- Actions `final readonly`, uma responsabilidade, transação onde houver escrita múltipla
- Listagens via `Datatable::applyFilter/applySort/applyPagination` + `Filters`
- `Permissions::description()` é `match` **exaustivo sem default** — faltar um caso quebra em runtime.
  Registrar toda permissão nova em 4 pontos: enum, `all()`, `description()`, `detail()`
- **Registrar no enum não basta:** as permissões só passam a valer depois de rodar o
  `PermissionSeeder` (`php artisan tenants:seed --class='Database\Seeders\PermissionSeeder'`),
  que grava em `permissions` e sincroniza com os papéis. Sem esse passo a rota autenticada
  responde **403** (autentica, mas o `->can()` barra) — sintoma fácil de confundir com bug
  de código. Não é preciso mexer em `DefaultRoles`: `ADMIN` mapeia para `Permissions::all()`.
- Rota pública: prefixo `/public`, sempre com `throttle`, sempre com Resource próprio enxuto

## Ambiente (já configurado)

**Onde o código vive (desde 2026-09-13):** distro **Ubuntu** do WSL2, em
`/home/glauber/codes/versa_ouvidoria/` (`api-boilerplate/` e `ui-boilerplate/`).
Do Windows: `\\wsl.localhost\Ubuntu\home\glauber\codes\versa_ouvidoria`. Abrir no
VS Code com **Remote-WSL** (`code .` de dentro do Ubuntu). Todos os comandos abaixo
rodam **dentro do Ubuntu** (`wsl -d Ubuntu`), nunca do PowerShell/Git Bash do Windows.

**API** — Docker Sail, em `~/codes/versa_ouvidoria/api-boilerplate/`:

```bash
WWWGROUP=1000 WWWUSER=1000 docker compose -f compose.development.yml up -d
docker compose -f compose.development.yml exec laravel.test php artisan <cmd>
docker compose -f compose.development.yml exec -T laravel.test php artisan test tests/Feature/<Modulo>/<Arquivo>Test.php
```

Volumes `api-boilerplate_sail-pgsql` e `api-boilerplate_sail-redis` são os mesmos de
antes (dados preservados). uid/gid 1000 do Ubuntu = `sail` no container, então a
armadilha do `chown storage/` deixou de existir.

**UI** — `cd ~/codes/versa_ouvidoria/ui-boilerplate && npm run dev` (Node 22 via nvm,
já instalado no Ubuntu; `node_modules` foi reinstalado com `npm ci` no Linux).

| Item | Valor |
|---|---|
| API | http://localhost:8090/api/v1 |
| Frontend | http://localhost:3001 (3000 estava ocupada) |
| Login | `admin` / `password` |
| Tenant | `localhost` (header `X-Domain`, derivado do host pelo front) |
| Banco | `tenant_localhost_versa_boilerplate` (PostgreSQL 15) |

**Portas remapeadas** por conflito com outro projeto (`financia-frontend` ocupava a 5173):
`APP_PORT=8090`, `VITE_PORT=5174`, `REVERB_PORT=8081`. Estão no `.env`
(fora do git) — o `.env.example` segue com as originais.

### Armadilhas já encontradas

- **`JWT_SECRET` vem vazio** no `.env.example` → `php artisan jwt:secret`. Sem isso o seed quebra.
- **`TenantSeeder` não é idempotente**: `db:seed` repetido estoura chave única — foi o que
  mascarou o erro do JWT, deixando o tenant criado **sem** o registro em `domains`
  (resulta em "Tenant could not be identified"). Vale um `firstOrCreate`.
- **Permissões de `storage/`**: `chown -R sail:sail storage bootstrap/cache` no container.
- **PHP do host (Herd-lite) não tem `pdo_pgsql`/`ext-sodium`** e não tem diretório de extensões.
  Instalar com `--ignore-platform-req=ext-sodium`; a app roda no container, que tem tudo.
- **`ApplicationUpTest` é flaky e pré-existente**: compara `datetime` da resposta com
  `Carbon::now()` reavaliado na asserção; quebra quando o segundo vira. Não tem relação
  com Ouvidoria. Baseline atual (2026-09-13, após a correção do throttle abaixo):
  **206 passaram, 0 falharam** (1766 asserções, ~2 min no WSL).
- **Vazamento de throttle entre testes — corrigido em 2026-09-13.** Sintoma: com a
  suíte inteira, 9 testes das rotas públicas (Ouvidoria e Questionnaires) falhavam com
  **429** onde esperavam 201/422; cada arquivo isolado passava. Causa: em
  `tests/Traits/RefreshDatabaseWithTenant.php`, `initializeTenant()` roda **antes** das
  transações, então `$connectionsToTransact = [null, 'tenant']` acaba envolvendo duas
  vezes a conexão do tenant — a conexão **central**, onde o store de cache `database`
  grava o contador do rate limiter, nunca entra em transação e o bucket é commitado de
  vez (as chaves `tenant_foo_<sha1>` e `:timer` ficavam na tabela `cache` do banco
  `testing` depois da suíte). Correção: `Cache::flush()` no trait, logo após
  `initializeTenant()`. **Não** use `CACHE_STORE=array` nos testes: o `stancl/tenancy`
  rejeita esse driver (`CacheTenancyBootstrapper.php:123`) e derruba a suíte inteira.
  `phpunit.xml` segue com `CACHE_STORE=database` (e um `CACHE_DRIVER=file` morto na
  linha anterior — o Laravel 12 lê só `CACHE_STORE`).

## Estado da verificação (Fase 1)

Validado em execução real, não só estaticamente:

- Migrations criaram as 3 tabelas com todas as FKs, índices e soft deletes (conferido no `psql`)
- `UnitSeeder` executou: unidade "Ouvidoria" (`OUV-001`) com UUID gerado
- As 5 permissões `ALL-*-units` gravadas e mapeadas para `Ouvidoria:Unidades`
- `GET /api/v1/public/units` → **HTTP 200 sem auth**, só `{id, name}`, `X-RateLimit-Limit: 60`
- `GET`/`POST /units` e `GET /unit-types` → **401** sem token
- Unidade inativa criada → **não aparece** no endpoint público (removida depois)
- Login devolve JWT; `GET /units` autenticado traz payload completo com `unit_type` aninhado

**Não há tela** para unidades — o frontend do módulo é a Fase 6.
