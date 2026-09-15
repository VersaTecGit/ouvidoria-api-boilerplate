<?php

declare(strict_types=1);

namespace Modules\Auth\Support;

enum Permissions: string
{
    case ACCESS_ADMIN_PANEL = 'ALL-access-admin-panel';

    case GET_VERSA360_CLIENT = 'ALL-get-versa360-client';
    case VIEW_VERSA360_SCOPE_PERMISSION_MAP = 'ALL-view-versa360-scope-permission-map';
    case CREATE_VERSA360_SCOPE_PERMISSION_MAP = 'ALL-create-versa360-scope-permission-map';
    case EDIT_VERSA360_SCOPE_PERMISSION_MAP = 'ALL-edit-versa360-scope-permission-map';
    case DELETE_VERSA360_SCOPE_PERMISSION_MAP = 'ALL-delete-versa360-scope-permission-map';

    case LIST_USERS = 'ALL-list-users';
    case VIEW_USERS = 'ALL-view-users';
    case CREATE_USERS = 'ALL-create-users';
    case EDIT_USERS = 'ALL-edit-users';
    case DELETE_USERS = 'ALL-delete-users';

    case EDIT_USERS_STATUS = 'ALL-edit-users-status';
    case EDIT_USERS_PASSWORDS = 'ALL-edit-users-passwords';

    case INTEGRATE_USERS = 'ALL-integrate-users';

    case LIST_USER_ROLES = 'ALL-list-user-roles';
    case EDIT_USER_ROLES = 'ALL-edit-user-roles';

    case LIST_ROLES = 'ALL-list-roles';
    case VIEW_ROLES = 'ALL-view-roles';
    case CREATE_ROLES = 'ALL-create-roles';
    case EDIT_ROLES = 'ALL-edit-roles';
    case DELETE_ROLES = 'ALL-delete-roles';

    case LIST_ROLE_PERMISSIONS = 'ALL-list-role-permissions';
    case EDIT_ROLE_PERMISSIONS = 'ALL-edit-role-permissions';

    case LIST_PERMISSIONS = 'ALL-list-permissions';

    case IMPERSONATE = 'ALL-impersonate';
    case BE_IMPERSONATED = 'ALL-be-impersonated';

    case VIEW_AUTH_SETTINGS = 'ALL-view-auth-settings';
    case EDIT_AUTH_SETTINGS = 'ALL-edit-auth-settings';

    case DELETE_MEDIA = 'ALL-delete-media';

    case LIST_ADS_ITEMS = 'ALL-list-ads';
    case VIEW_ADS_ITEMS = 'ALL-view-ads';
    case CREATE_ADS_ITEMS = 'ALL-create-ads';
    case EDIT_ADS_ITEMS = 'ALL-edit-ads';
    case DELETE_ADS_ITEMS = 'ALL-delete-ads';

    case LIST_THEMES_ITEMS = 'ALL-list-themes';
    case VIEW_THEMES_ITEMS = 'ALL-view-themes';
    case CREATE_THEMES_ITEMS = 'ALL-create-themes';
    case EDIT_THEMES_ITEMS = 'ALL-edit-themes';
    case DELETE_THEMES_ITEMS = 'ALL-delete-themes';

    case LIST_ACCESS_LOGS = 'ALL-list-access-logs';

    case LIST_QUESTIONNAIRES_GROUPS = 'ALL-list-questionnaires-groups';
    case VIEW_QUESTIONNAIRES_GROUPS = 'ALL-view-questionnaires-groups';
    case CREATE_QUESTIONNAIRES_GROUPS = 'ALL-create-questionnaires-groups';
    case EDIT_QUESTIONNAIRES_GROUPS = 'ALL-edit-questionnaires-groups';
    case DELETE_QUESTIONNAIRES_GROUPS = 'ALL-delete-questionnaires-groups';

    case LIST_QUESTIONNAIRES = 'ALL-list-questionnaires';
    case VIEW_QUESTIONNAIRES = 'ALL-view-questionnaires';
    case CREATE_QUESTIONNAIRES = 'ALL-create-questionnaires';
    case EDIT_QUESTIONNAIRES = 'ALL-edit-questionnaires';
    case DELETE_QUESTIONNAIRES = 'ALL-delete-questionnaires';

    case LIST_QUESTIONNAIRE_RESPONSES = 'ALL-list-questionnaire-responses';
    case VIEW_QUESTIONNAIRE_RESPONSES = 'ALL-view-questionnaire-responses';

    case LIST_VEHICLES = 'ALL-list-vehicles';
    case VIEW_VEHICLES = 'ALL-view-vehicles';
    case CREATE_VEHICLES = 'ALL-create-vehicles';
    case EDIT_VEHICLES = 'ALL-edit-vehicles';
    case DELETE_VEHICLES = 'ALL-delete-vehicles';

    case LIST_VEHICLE_REQUESTS = 'ALL-list-vehicle-requests';
    case VIEW_VEHICLE_REQUESTS = 'ALL-view-vehicle-requests';
    case CREATE_VEHICLE_REQUESTS = 'ALL-create-vehicle-requests';
    case EDIT_VEHICLE_REQUESTS = 'ALL-edit-vehicle-requests';
    case DELETE_VEHICLE_REQUESTS = 'ALL-delete-vehicle-requests';

    case LIST_VEHICLE_TRIPS = 'ALL-list-vehicle-trips';
    case VIEW_VEHICLE_TRIPS = 'ALL-view-vehicle-trips';
    case EDIT_VEHICLE_TRIPS = 'ALL-edit-vehicle-trips';
    case DELETE_VEHICLE_TRIPS = 'ALL-delete-vehicle-trips';

    case LIST_UNITS = 'ALL-list-units';
    case VIEW_UNITS = 'ALL-view-units';
    case CREATE_UNITS = 'ALL-create-units';
    case EDIT_UNITS = 'ALL-edit-units';
    case DELETE_UNITS = 'ALL-delete-units';

    case LIST_DESTINATION_AGENCIES = 'ALL-list-destination-agencies';
    case VIEW_DESTINATION_AGENCIES = 'ALL-view-destination-agencies';
    case CREATE_DESTINATION_AGENCIES = 'ALL-create-destination-agencies';
    case EDIT_DESTINATION_AGENCIES = 'ALL-edit-destination-agencies';
    case DELETE_DESTINATION_AGENCIES = 'ALL-delete-destination-agencies';

    case LIST_MANIFESTATIONS = 'ALL-list-manifestations';
    case VIEW_MANIFESTATIONS = 'ALL-view-manifestations';
    case CREATE_MANIFESTATIONS = 'ALL-create-manifestations';
    case EDIT_MANIFESTATIONS = 'ALL-edit-manifestations';
    case DELETE_MANIFESTATIONS = 'ALL-delete-manifestations';
    case RESPOND_MANIFESTATIONS = 'ALL-respond-manifestations';

    public static function all(): array
    {
        return [
            self::ACCESS_ADMIN_PANEL,

            self::GET_VERSA360_CLIENT,
            self::VIEW_VERSA360_SCOPE_PERMISSION_MAP,
            self::CREATE_VERSA360_SCOPE_PERMISSION_MAP,
            self::EDIT_VERSA360_SCOPE_PERMISSION_MAP,
            self::DELETE_VERSA360_SCOPE_PERMISSION_MAP,

            self::LIST_USERS,
            self::VIEW_USERS,
            self::CREATE_USERS,
            self::EDIT_USERS,
            self::DELETE_USERS,

            self::EDIT_USERS_STATUS,
            self::EDIT_USERS_PASSWORDS,

            self::INTEGRATE_USERS,

            self::LIST_USER_ROLES,
            self::EDIT_USER_ROLES,

            self::LIST_ROLES,
            self::VIEW_ROLES,
            self::CREATE_ROLES,
            self::EDIT_ROLES,
            self::DELETE_ROLES,

            self::LIST_ROLE_PERMISSIONS,
            self::EDIT_ROLE_PERMISSIONS,

            self::LIST_PERMISSIONS,

            self::IMPERSONATE,
            self::BE_IMPERSONATED,

            self::VIEW_AUTH_SETTINGS,
            self::EDIT_AUTH_SETTINGS,

            self::DELETE_MEDIA,

            self::LIST_ADS_ITEMS,
            self::VIEW_ADS_ITEMS,
            self::CREATE_ADS_ITEMS,
            self::EDIT_ADS_ITEMS,
            self::DELETE_ADS_ITEMS,

            self::LIST_THEMES_ITEMS,
            self::VIEW_THEMES_ITEMS,
            self::CREATE_THEMES_ITEMS,
            self::EDIT_THEMES_ITEMS,
            self::DELETE_THEMES_ITEMS,

            self::LIST_ACCESS_LOGS,

            self::LIST_QUESTIONNAIRES_GROUPS,
            self::VIEW_QUESTIONNAIRES_GROUPS,
            self::CREATE_QUESTIONNAIRES_GROUPS,
            self::EDIT_QUESTIONNAIRES_GROUPS,
            self::DELETE_QUESTIONNAIRES_GROUPS,

            self::LIST_QUESTIONNAIRES,
            self::VIEW_QUESTIONNAIRES,
            self::CREATE_QUESTIONNAIRES,
            self::EDIT_QUESTIONNAIRES,
            self::DELETE_QUESTIONNAIRES,

            self::LIST_QUESTIONNAIRE_RESPONSES,
            self::VIEW_QUESTIONNAIRE_RESPONSES,

            self::LIST_VEHICLES,
            self::VIEW_VEHICLES,
            self::CREATE_VEHICLES,
            self::EDIT_VEHICLES,
            self::DELETE_VEHICLES,

            self::LIST_VEHICLE_REQUESTS,
            self::VIEW_VEHICLE_REQUESTS,
            self::CREATE_VEHICLE_REQUESTS,
            self::EDIT_VEHICLE_REQUESTS,
            self::DELETE_VEHICLE_REQUESTS,

            self::LIST_VEHICLE_TRIPS,
            self::VIEW_VEHICLE_TRIPS,
            self::EDIT_VEHICLE_TRIPS,
            self::DELETE_VEHICLE_TRIPS,

            self::LIST_UNITS,
            self::VIEW_UNITS,
            self::CREATE_UNITS,
            self::EDIT_UNITS,
            self::DELETE_UNITS,

            self::LIST_DESTINATION_AGENCIES,
            self::VIEW_DESTINATION_AGENCIES,
            self::CREATE_DESTINATION_AGENCIES,
            self::EDIT_DESTINATION_AGENCIES,
            self::DELETE_DESTINATION_AGENCIES,

            self::LIST_MANIFESTATIONS,
            self::VIEW_MANIFESTATIONS,
            self::CREATE_MANIFESTATIONS,
            self::EDIT_MANIFESTATIONS,
            self::DELETE_MANIFESTATIONS,
            self::RESPOND_MANIFESTATIONS,
        ];
    }

    public function description(): string
    {
        return match ($this) {
            self::ACCESS_ADMIN_PANEL => 'Acessar painel de administração',

            self::GET_VERSA360_CLIENT => 'Obter cliente do Versa360',
            self::VIEW_VERSA360_SCOPE_PERMISSION_MAP => 'Visualizar mapeamento de escopos e permissões do Versa360',
            self::CREATE_VERSA360_SCOPE_PERMISSION_MAP => 'Criar mapeamento de escopos e permissões do Versa360',
            self::EDIT_VERSA360_SCOPE_PERMISSION_MAP => 'Editar mapeamento de escopos e permissões do Versa360',
            self::DELETE_VERSA360_SCOPE_PERMISSION_MAP => 'Deletar mapeamento de escopos e permissões do Versa360',

            self::LIST_USERS => 'Listar usuários',
            self::VIEW_USERS => 'Visualizar usuários',
            self::CREATE_USERS => 'Criar usuários',
            self::EDIT_USERS => 'Editar usuários',
            self::DELETE_USERS => 'Deletar usuários',

            self::EDIT_USERS_STATUS => 'Editar status de usuários',
            self::EDIT_USERS_PASSWORDS => 'Editar senhas de usuários',

            self::INTEGRATE_USERS => 'Integrar usuários',

            self::LIST_USER_ROLES => 'Listar grupos de usuários',
            self::EDIT_USER_ROLES => 'Editar grupos de usuários',

            self::LIST_ROLES => 'Listar grupos',
            self::VIEW_ROLES => 'Visualizar grupos',
            self::CREATE_ROLES => 'Criar grupos',
            self::EDIT_ROLES => 'Editar grupos',
            self::DELETE_ROLES => 'Deletar grupos',

            self::LIST_ROLE_PERMISSIONS => 'Listar permissões de grupos',
            self::EDIT_ROLE_PERMISSIONS => 'Editar permissões de grupos',

            self::LIST_PERMISSIONS => 'Listar permissões',

            self::IMPERSONATE => 'Impersonar',
            self::BE_IMPERSONATED => 'Ser impersonado',

            self::VIEW_AUTH_SETTINGS => 'Visualizar configurações de autenticação',
            self::EDIT_AUTH_SETTINGS => 'Editar configurações de autenticação',

            self::DELETE_MEDIA => 'Deletar mídias',

            self::LIST_ADS_ITEMS => 'Listar banners de login',
            self::VIEW_ADS_ITEMS => 'Visualizar banners de login',
            self::CREATE_ADS_ITEMS => 'Criar banners de login',
            self::EDIT_ADS_ITEMS => 'Editar banners de login',
            self::DELETE_ADS_ITEMS => 'Deletar banners de login',

            self::LIST_THEMES_ITEMS => 'Listar temas',
            self::VIEW_THEMES_ITEMS => 'Visualizar temas',
            self::CREATE_THEMES_ITEMS => 'Criar temas',
            self::EDIT_THEMES_ITEMS => 'Editar temas',
            self::DELETE_THEMES_ITEMS => 'Deletar temas',

            self::LIST_USERS => 'Listar usuários',
            self::VIEW_USERS => 'Visualizar usuários',
            self::CREATE_USERS => 'Criar usuários',
            self::EDIT_USERS => 'Editar usuários',
            self::DELETE_USERS => 'Deletar usuários',

            self::LIST_ACCESS_LOGS => 'Listar logs de acesso',

            self::LIST_QUESTIONNAIRES_GROUPS => 'Listar grupos de questionários',
            self::VIEW_QUESTIONNAIRES_GROUPS => 'Visualizar grupos de questionários',
            self::CREATE_QUESTIONNAIRES_GROUPS => 'Criar grupos de questionários',
            self::EDIT_QUESTIONNAIRES_GROUPS => 'Editar grupos de questionários',
            self::DELETE_QUESTIONNAIRES_GROUPS => 'Deletar grupos de questionários',

            self::LIST_QUESTIONNAIRES => 'Listar questionários',
            self::VIEW_QUESTIONNAIRES => 'Visualizar questionários',
            self::CREATE_QUESTIONNAIRES => 'Criar questionários',
            self::EDIT_QUESTIONNAIRES => 'Editar questionários',
            self::DELETE_QUESTIONNAIRES => 'Deletar questionários',

            self::LIST_QUESTIONNAIRE_RESPONSES => 'Listar respostas de questionários',
            self::VIEW_QUESTIONNAIRE_RESPONSES => 'Visualizar respostas de questionários',

            self::LIST_VEHICLES => 'Listar veículos',
            self::VIEW_VEHICLES => 'Visualizar veículos',
            self::CREATE_VEHICLES => 'Criar veículos',
            self::EDIT_VEHICLES => 'Editar veículos',
            self::DELETE_VEHICLES => 'Deletar veículos',

            self::LIST_VEHICLE_REQUESTS => 'Listar solicitações de veículos',
            self::VIEW_VEHICLE_REQUESTS => 'Visualizar solicitações de veículos',
            self::CREATE_VEHICLE_REQUESTS => 'Criar solicitações de veículos',
            self::EDIT_VEHICLE_REQUESTS => 'Editar solicitações de veículos',
            self::DELETE_VEHICLE_REQUESTS => 'Deletar solicitações de veículos',

            self::LIST_VEHICLE_TRIPS => 'Listar viagens',
            self::VIEW_VEHICLE_TRIPS => 'Visualizar viagens',
            self::EDIT_VEHICLE_TRIPS => 'Editar viagens',
            self::DELETE_VEHICLE_TRIPS => 'Deletar viagens',

            self::LIST_UNITS => 'Listar unidades',
            self::VIEW_UNITS => 'Visualizar unidades',
            self::CREATE_UNITS => 'Criar unidades',
            self::EDIT_UNITS => 'Editar unidades',
            self::DELETE_UNITS => 'Deletar unidades',

            self::LIST_DESTINATION_AGENCIES => 'Listar órgãos destinatários',
            self::VIEW_DESTINATION_AGENCIES => 'Visualizar órgãos destinatários',
            self::CREATE_DESTINATION_AGENCIES => 'Criar órgãos destinatários',
            self::EDIT_DESTINATION_AGENCIES => 'Editar órgãos destinatários',
            self::DELETE_DESTINATION_AGENCIES => 'Deletar órgãos destinatários',

            self::LIST_MANIFESTATIONS => 'Listar manifestações',
            self::VIEW_MANIFESTATIONS => 'Visualizar manifestações',
            self::CREATE_MANIFESTATIONS => 'Criar manifestações',
            self::EDIT_MANIFESTATIONS => 'Editar manifestações',
            self::DELETE_MANIFESTATIONS => 'Deletar manifestações',
            self::RESPOND_MANIFESTATIONS => 'Responder manifestações',
        };
    }

    public function detail(): string
    {
        return match ($this) {
            self::ACCESS_ADMIN_PANEL => 'Permite que o usuário acesse o painel de administração do sistema.',

            self::GET_VERSA360_CLIENT => 'Permite que o usuário obtenha o cliente do Versa360 para integração OAuth2. Permissão necessária para integrações com o Versa360.',
            self::VIEW_VERSA360_SCOPE_PERMISSION_MAP => 'Permite que o usuário visualize o mapeamento entre escopos do Versa360 e permissões do sistema.',
            self::CREATE_VERSA360_SCOPE_PERMISSION_MAP => 'Permite que o usuário crie novos mapeamentos entre escopos do Versa360 e permissões do sistema.',
            self::EDIT_VERSA360_SCOPE_PERMISSION_MAP => 'Permite que o usuário edite mapeamentos existentes entre escopos do Versa360 e permissões do sistema.',
            self::DELETE_VERSA360_SCOPE_PERMISSION_MAP => 'Permite que o usuário delete mapeamentos entre escopos do Versa360 e permissões do sistema.',

            self::LIST_USERS => 'Permite que o usuário liste outros usuárioes do sistema.',
            self::VIEW_USERS => 'Permite que o usuário visualize detalhes de outros usuárioes do sistema.',
            self::CREATE_USERS => 'Permite que o usuário crie novos usuárioes no sistema.',
            self::EDIT_USERS => 'Permite que o usuário edite informações de outros usuárioes no sistema.',
            self::DELETE_USERS => 'Permite que o usuário delete outros usuárioes do sistema.',

            self::EDIT_USERS_STATUS => 'Permite que o usuário altere o status (ativo/inativo) de outros usuárioes no sistema.',
            self::EDIT_USERS_PASSWORDS => 'Permite que o usuário altere as senhas de outros usuárioes no sistema.',

            self::LIST_USER_ROLES => 'Permite que o usuário liste os grupos atribuídos aos usuárioes.',
            self::EDIT_USER_ROLES => 'Permite que o usuário edite os grupos atribuídos aos usuárioes.',

            self::LIST_ROLES => 'Permite que o usuário liste os grupos de permissões do sistema.',
            self::VIEW_ROLES => 'Permite que o usuário visualize detalhes dos grupos de permissões do sistema.',
            self::CREATE_ROLES => 'Permite que o usuário crie novos grupos de permissões no sistema.',
            self::EDIT_ROLES => 'Permite que o usuário edite grupos de permissões existentes no sistema.',
            self::DELETE_ROLES => 'Permite que o usuário delete grupos de permissões do sistema.',

            self::LIST_ROLE_PERMISSIONS => 'Permite que o usuário liste as permissões atribuídas aos grupos.',
            self::EDIT_ROLE_PERMISSIONS => 'Permite que o usuário edite as permissões atribuídas aos grupos.',

            self::LIST_PERMISSIONS => 'Permite que o usuário liste todas as permissões disponíveis no sistema.',

            self::IMPERSONATE => 'Permite que o usuário assuma a identidade de outro usuário para fins de suporte ou administração.',
            self::BE_IMPERSONATED => 'Permite que o usuário seja assumido por outro usuário para fins de suporte ou administração.',

            self::VIEW_AUTH_SETTINGS => 'Permite que o usuário visualize as configurações de autenticação do sistema.',
            self::EDIT_AUTH_SETTINGS => 'Permite que o usuário edite as configurações de autenticação do sistema.',

            self::DELETE_MEDIA => 'Permite que o usuário delete arquivos de mídia (imagens, vídeos, etc.) do sistema.',

            self::LIST_ADS_ITEMS => 'Permite que o usuário liste os banners exibidos na tela de login.',
            self::VIEW_ADS_ITEMS => 'Permite que o usuário visualize detalhes dos banners exibidos na tela de login.',
            self::CREATE_ADS_ITEMS => 'Permite que o usuário crie novos banners para serem exibidos na tela de login.',
            self::EDIT_ADS_ITEMS => 'Permite que o usuário edite os banners exibidos na tela de login.',
            self::DELETE_ADS_ITEMS => 'Permite que o usuário delete os banners exibidos na tela de login.',

            self::LIST_THEMES_ITEMS => 'Permite que o usuário liste os temas disponíveis no sistema.',
            self::VIEW_THEMES_ITEMS => 'Permite que o usuário visualize detalhes dos temas disponíveis no sistema.',
            self::CREATE_THEMES_ITEMS => 'Permite que o usuário crie novos temas no sistema.',
            self::EDIT_THEMES_ITEMS => 'Permite que o usuário edite os temas disponíveis no sistema.',
            self::DELETE_THEMES_ITEMS => 'Permite que o usuário delete os temas disponíveis no sistema.',

            self::LIST_ACCESS_LOGS => 'Permite que o usuário liste os logs de acesso ao sistema.',

            self::LIST_QUESTIONNAIRES_GROUPS => 'Permite que o usuário liste os blocos de questionários disponíveis no sistema.',
            self::VIEW_QUESTIONNAIRES_GROUPS => 'Permite que o usuário visualize detalhes dos blocos de questionários disponíveis no sistema.',
            self::CREATE_QUESTIONNAIRES_GROUPS => 'Permite que o usuário crie novos blocos de questionários no sistema.',
            self::EDIT_QUESTIONNAIRES_GROUPS => 'Permite que o usuário edite os blocos de questionários disponíveis no sistema.',
            self::DELETE_QUESTIONNAIRES_GROUPS => 'Permite que o usuário delete os blocos de questionários disponíveis no sistema.',

            self::LIST_QUESTIONNAIRES => 'Permite que o usuário liste os questionários disponíveis no sistema.',
            self::VIEW_QUESTIONNAIRES => 'Permite que o usuário visualize detalhes dos questionários disponíveis no sistema.',
            self::CREATE_QUESTIONNAIRES => 'Permite que o usuário crie novos questionários no sistema.',
            self::EDIT_QUESTIONNAIRES => 'Permite que o usuário edite os questionários disponíveis no sistema.',
            self::DELETE_QUESTIONNAIRES => 'Permite que o usuário delete os questionários disponíveis no sistema.',

            self::LIST_QUESTIONNAIRE_RESPONSES => 'Permite que o usuário liste as respostas de questionários submetidas.',
            self::VIEW_QUESTIONNAIRE_RESPONSES => 'Permite que o usuário visualize detalhes das respostas de questionários submetidas.',

            self::LIST_VEHICLES => 'Permite que o usuário liste os veículos cadastrados no sistema.',
            self::VIEW_VEHICLES => 'Permite que o usuário visualize detalhes dos veículos cadastrados no sistema.',
            self::CREATE_VEHICLES => 'Permite que o usuário crie novos veículos no sistema.',
            self::EDIT_VEHICLES => 'Permite que o usuário edite os veículos cadastrados no sistema.',
            self::DELETE_VEHICLES => 'Permite que o usuário delete os veículos cadastrados no sistema.',

            self::LIST_VEHICLE_REQUESTS => 'Permite que o usuário liste as solicitações de veículos feitas no sistema.',
            self::VIEW_VEHICLE_REQUESTS => 'Permite que o usuário visualize detalhes das solicitações de veículos feitas no sistema.',
            self::CREATE_VEHICLE_REQUESTS => 'Permite que o usuário crie novas solicitações de veículos no sistema.',
            self::EDIT_VEHICLE_REQUESTS => 'Permite que o usuário edite as solicitações de veículos feitas no sistema.',
            self::DELETE_VEHICLE_REQUESTS => 'Permite que o usuário delete as solicitações de veículos feitas no sistema.',

            self::LIST_VEHICLE_TRIPS => 'Permite que o usuário liste as viagens realizadas pelos veículos cadastrados no sistema.',
            self::VIEW_VEHICLE_TRIPS => 'Permite que o usuário visualize detalhes das viagens realizadas pelos veículos cadastrados no sistema.',
            self::EDIT_VEHICLE_TRIPS => 'Permite que o usuário edite as viagens realizadas pelos veículos cadastrados no sistema.',
            self::DELETE_VEHICLE_TRIPS => 'Permite que o usuário delete as viagens realizadas pelos veículos cadastrados no sistema.',

            self::LIST_UNITS => 'Permite que o usuário liste as unidades cadastradas no sistema.',
            self::VIEW_UNITS => 'Permite que o usuário visualize os detalhes das unidades cadastradas no sistema.',
            self::CREATE_UNITS => 'Permite que o usuário cadastre novas unidades no sistema.',
            self::EDIT_UNITS => 'Permite que o usuário edite as unidades cadastradas no sistema.',
            self::DELETE_UNITS => 'Permite que o usuário delete as unidades cadastradas no sistema.',

            self::LIST_DESTINATION_AGENCIES => 'Permite que o usuário liste os órgãos e secretarias destinatários das manifestações.',
            self::VIEW_DESTINATION_AGENCIES => 'Permite que o usuário visualize os detalhes dos órgãos e secretarias destinatários das manifestações.',
            self::CREATE_DESTINATION_AGENCIES => 'Permite que o usuário cadastre novos órgãos e secretarias destinatários das manifestações.',
            self::EDIT_DESTINATION_AGENCIES => 'Permite que o usuário edite os órgãos e secretarias destinatários das manifestações.',
            self::DELETE_DESTINATION_AGENCIES => 'Permite que o usuário delete os órgãos e secretarias destinatários das manifestações.',

            self::LIST_MANIFESTATIONS => 'Permite que o usuário liste as manifestações recebidas pela ouvidoria.',
            self::VIEW_MANIFESTATIONS => 'Permite que o usuário visualize os detalhes de uma manifestação, incluindo os dados do manifestante identificado e as notas internas.',
            self::CREATE_MANIFESTATIONS => 'Permite que o usuário registre manifestações pelo painel interno, por exemplo as recebidas presencialmente ou por telefone.',
            self::EDIT_MANIFESTATIONS => 'Permite que o usuário edite os dados de triagem de uma manifestação, como tipo, status e órgão destinatário.',
            self::DELETE_MANIFESTATIONS => 'Permite que o usuário delete manifestações.',
            self::RESPOND_MANIFESTATIONS => 'Permite que o usuário escreva o parecer e os andamentos que o cidadão lê pelo protocolo, além das notas internas.',

            default => 'Detalhes não disponíveis para esta permissão.',
        };
    }
}
