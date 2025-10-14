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
        };
    }
}
