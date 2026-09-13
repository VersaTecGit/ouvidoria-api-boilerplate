# Plano de Desenvolvimento — Módulo Ouvidoria

> Documento de handoff. Uma nova sessão deve ler este arquivo antes de escrever código.
> Última atualização: 2026-09-13 — **Fase 2 concluída e validada em execução; Fase 7 implementada (inerte até a Fase 6).**

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
- [ ] **Fase 3 — Manifestações** (migration, models, enums, DTOs, actions, controller, resources, rotas)
- [ ] **Fase 4 — Endpoint público enxuto** (`PublicManifestationResource` + throttle + gerador de protocolo)
- [ ] **Fase 5 — Upload público** (signed URL, padrão `PublicQuestionnaireSignedStorageUrlController`, `throttle:10,1`)
- [ ] **Fase 6 — Frontend** (rotas públicas, layout, form, tela de conclusão com protocolo, timeline; telas internas)
- [x] **Fase 7 — Liberar acesso** (`proxy.ts` + `AuthProvider.tsx`) ✅ implementada — **inerte até a Fase 6** (ver abaixo)
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

**Não testável de ponta a ponta ainda:** `app/ouvidoria/` não existe até a Fase 6,
então a mudança é inerte. O teste real do fluxo anônimo acontece na Fase 6/8.

**Ruído de ambiente:** um `npm install` no Windows reescreve `package-lock.json`
removendo blocos `"libc": ["glibc"|"musl"]` de dependências nativas opcionais, sem
que `package.json` mude. Isso **não** deve ser commitado — reverta com
`git checkout -- package-lock.json` antes de fechar um commit no `ui-boilerplate`.

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

**API** — Docker Sail, em `api-boilerplate/`:

```bash
WWWGROUP=1000 WWWUSER=1000 docker compose -f compose.development.yml up -d
docker compose -f compose.development.yml exec laravel.test php artisan <cmd>
```

**UI** — `cd ui-boilerplate && npm run dev`

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
  com Ouvidoria. Baseline atual: **181 passaram, 1 falhou**.

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
