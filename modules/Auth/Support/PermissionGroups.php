<?php

declare(strict_types=1);

namespace Modules\Auth\Support;

enum PermissionGroups: string
{
    case ACCESS_USERS = 'Acesso:Usuários';
    case ACCESS_ROLES = 'Acesso:Grupos';
    case ACCESS_PANELS = 'Acesso:Painéis';

    case QUESTIONNAIRES_GROUPS = 'Questionários:Blocos';
    case QUESTIONNAIRES = 'Questionários:Questionários';
    case QUESTIONNAIRE_RESPONSES = 'Questionários:Respostas';

    case ACCESS_LOGS = 'Logs:Logs de Acesso';

    case CONFIG_ADS = 'Configurações:Banners de Login';
    case CONFIG_THEMES = 'Configurações:Temas';
    case CONFIG_VERSA360 = 'Configurações:Versa 360';

    case OUVIDORIA_UNITS = 'Ouvidoria:Unidades';

    case OTHERS_TRANSPORT = 'Outros:Logística e Transporte';
    case OTHERS_MISC = 'Outros:Diversos';

    public static function fromPermission(string $permissionName): self
    {
        return match (true) {
            str_contains($permissionName, 'ads') => self::CONFIG_ADS,
            str_contains($permissionName, 'theme') => self::CONFIG_THEMES,
            str_contains($permissionName, 'versa360') => self::CONFIG_VERSA360,

            str_contains($permissionName, 'questionnaires-group') => self::QUESTIONNAIRES_GROUPS,
            str_contains($permissionName, 'questionnaire-response') => self::QUESTIONNAIRE_RESPONSES,
            str_contains($permissionName, 'questionnaires') => self::QUESTIONNAIRES,

            str_contains($permissionName, 'user') => self::ACCESS_USERS,
            str_contains($permissionName, 'role') || str_contains($permissionName, 'permission') => self::ACCESS_ROLES,
            str_contains($permissionName, 'panel') => self::ACCESS_PANELS,

            str_contains($permissionName, 'logs') => self::ACCESS_LOGS,

            str_contains($permissionName, 'vehicle') => self::OTHERS_TRANSPORT,

            str_contains($permissionName, 'unit') => self::OUVIDORIA_UNITS,

            default => self::OTHERS_MISC,
        };
    }

    public static function modules(): array
    {
        $modules = [];

        foreach (self::cases() as $case) {
            $module = $case->module();
            $group = $case->group();

            if (! isset($modules[$module])) {
                $modules[$module] = [
                    'name' => $module,
                    'description' => $case->moduleDescription(),
                    'groups' => [],
                ];
            }

            $modules[$module]['groups'][] = [
                'name' => $group,
                'description' => $case->groupDescription(),
            ];
        }

        return array_values($modules);
    }

    public function module(): string
    {
        return explode(':', $this->value)[0];
    }

    public function group(): string
    {
        return explode(':', $this->value)[1];
    }

    public function moduleDescription(): string
    {
        return match ($this->module()) {
            'Acesso' => 'Gerencia usuários, permissões, papéis de acesso e painéis administrativos.',
            'Questionários' => 'Configuração, resposta e gestão de questionários utilizados pelo sistema.',
            'Logs' => 'Monitoramento e auditoria de acessos ao sistema.',
            'Configurações' => 'Parâmetros de configuração geral e personalização da plataforma.',
            'Ouvidoria' => 'Gestão das manifestações recebidas pela ouvidoria e suas unidades destinatárias.',
            'Outros' => 'Módulos auxiliares como relatórios, denúncias e transporte.',
            default => 'Módulo do sistema.',
        };
    }

    public function groupDescription(): string
    {
        return match ($this) {
            self::ACCESS_USERS => 'Gerencia usuários, permitindo listagem, criação, edição e remoção.',
            self::ACCESS_ROLES => 'Administra papéis de acesso e permissões atribuídas.',
            self::ACCESS_PANELS => 'Gerencia quais painéis são acessíveis pelos usuários.',

            self::QUESTIONNAIRES_GROUPS => 'Define os blocos e agrupamentos estruturais dos questionários.',
            self::QUESTIONNAIRES => 'Gerencia os questionários disponíveis no sistema.',
            self::QUESTIONNAIRE_RESPONSES => 'Gerencia as respostas enviadas aos questionários.',

            self::ACCESS_LOGS => 'Registros de acesso de usuários ao sistema.',

            self::CONFIG_ADS => 'Gerencia banners exibidos na tela de login.',
            self::CONFIG_THEMES => 'Gerencia temas visuais da plataforma.',
            self::CONFIG_VERSA360 => 'Configurações e controle da integração Versa 360.',

            self::OUVIDORIA_UNITS => 'Gerencia as unidades da ouvidoria, destinatárias das manifestações.',

            self::OTHERS_TRANSPORT => 'Gerencia logística e transporte.',
            self::OTHERS_MISC => 'Funcionalidades diversas complementares.',

            default => 'Grupo funcional do sistema.',
        };
    }
}
