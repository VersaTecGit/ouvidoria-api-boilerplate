<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Support;

use Modules\Questionnaires\DTOs\FileUploadElementDTO;

enum QuestionnaireElementType: string
{
    case TEXT_FIELD = 'TextField';
    case TITLE_FIELD = 'TitleField';
    case SUB_TITLE_FIELD = 'SubTitleField';
    case PARAGRAPH_FIELD = 'ParagraphField';
    case SEPARATOR_FIELD = 'SeparatorField';
    case SPACER_FIELD = 'SpacerField';
    case NUMBER_FIELD = 'NumberField';
    case TEXT_AREA_FIELD = 'TextAreaField';
    case DATE_FIELD = 'DateField';
    case SELECT_FIELD = 'SelectField';
    case CHECKBOX_FIELD = 'CheckboxField';
    case SWITCH_FIELD = 'SwitchField';
    case DATE_RANGE_FIELD = 'DateRangeField';
    case FILE_UPLOAD_FIELD = 'FileUploadField';

    public static function all(): array
    {
        return [
            self::TEXT_FIELD,
            self::TITLE_FIELD,
            self::SUB_TITLE_FIELD,
            self::PARAGRAPH_FIELD,
            self::SEPARATOR_FIELD,
            self::SPACER_FIELD,
            self::NUMBER_FIELD,
            self::TEXT_AREA_FIELD,
            self::DATE_FIELD,
            self::SELECT_FIELD,
            self::CHECKBOX_FIELD,
            self::SWITCH_FIELD,
            self::DATE_RANGE_FIELD,
            self::FILE_UPLOAD_FIELD,
        ];
    }

    public static function toArray(): array
    {
        return array_column(QuestionnaireElementType::cases(), 'value');
    }

    public function description(): string
    {
        return match ($this) {
            self::TEXT_FIELD => 'Texto curto',
            self::TITLE_FIELD => 'Título',
            self::SUB_TITLE_FIELD => 'Subtítulo',
            self::PARAGRAPH_FIELD => 'Parágrafo',
            self::SEPARATOR_FIELD => 'Separador',
            self::SPACER_FIELD => 'Espaçamento',
            self::NUMBER_FIELD => 'Campo numérico',
            self::TEXT_AREA_FIELD => 'Texto longo',
            self::DATE_FIELD => 'Campo de Data',
            self::SELECT_FIELD => 'Campo de Seleção',
            self::CHECKBOX_FIELD => 'Múltipla escolha',
            self::SWITCH_FIELD => 'Veracidade',
            self::DATE_RANGE_FIELD => 'Intervalo de datas',
            self::FILE_UPLOAD_FIELD => 'Upload de arquivo',
        };
    }

    public function validadeElementAnswer(mixed $answer): bool
    {
        if (empty($answer)) {
            return true;
        }

        return match ($this) {
            self::TEXT_FIELD => is_string($answer),
            self::NUMBER_FIELD => is_string($answer) && is_numeric($answer),
            self::TEXT_AREA_FIELD => is_string($answer),
            self::DATE_FIELD => is_string($answer) && (bool) strtotime($answer),
            self::SELECT_FIELD => is_string($answer),
            self::CHECKBOX_FIELD => is_string($answer) && is_array(explode(',', $answer)),
            self::SWITCH_FIELD => is_string($answer) && in_array($answer, ['true', 'false']),
            self::DATE_RANGE_FIELD => is_string($answer) && (function ($answer) {
                $decodedAnswer = json_decode($answer, true);

                return is_array($decodedAnswer) && (bool) strtotime($decodedAnswer['start_date']) && (bool) strtotime($decodedAnswer['end_date']);
            })($answer),
            self::FILE_UPLOAD_FIELD => is_string($answer) && (function ($answer) {
                $decodedAnswer = json_decode($answer, true);

                if (! is_array($decodedAnswer)) {
                    return FileUploadElementDTO::fromArray($decodedAnswer);
                }

                foreach ($decodedAnswer as $fileItem) {
                    if (isset($fileItem['uuid'])) {
                        continue;
                    }
                    FileUploadElementDTO::fromArray($fileItem);
                }

                return true;
            })($answer),
        };
    }
}
