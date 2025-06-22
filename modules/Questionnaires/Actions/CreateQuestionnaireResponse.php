<?php

declare(strict_types=1);

namespace Modules\Questionnaires\Actions;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Common\Core\Exceptions\ApiException;
use Modules\Questionnaires\DTOs\CreateQuestionnaireResponseDTO;
use Modules\Questionnaires\DTOs\FileUploadElementDTO;
use Modules\Questionnaires\Models\QuestionnaireResponse;
use Modules\Questionnaires\Support\QuestionnaireElementType;

final readonly class CreateQuestionnaireResponse
{
    public function __construct(private FetchQuestionnaire $fetchQuestionnaire) {}

    public function handle(CreateQuestionnaireResponseDTO $dto): QuestionnaireResponse
    {
        $questionnaire = $this->fetchQuestionnaire->handle($dto->questionnaire_id);

        $questionnaireResponse = $dto->toModel(QuestionnaireResponse::class);

        try {
            DB::beginTransaction();

            $questionnaireResponse->questionnaire_id = $questionnaire->id;
            $questionnaireResponse->version = $questionnaire->version;

            $questionnaireResponse->answers = $this->handleAnswers($questionnaireResponse, $dto->answers, $questionnaire->elements);

            $questionnaireResponse->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $questionnaireResponse;
    }

    public function handleAnswers(QuestionnaireResponse $questionnaireResponse, array $answers, array $elements): array
    {
        return collect($answers)->mapWithKeys(function ($answer, $elementId) use ($questionnaireResponse, $elements) {
            $element = collect($elements)->firstWhere('id', $elementId);
            $type = QuestionnaireElementType::tryFrom($element['type']);

            if (! $type) {
                throw new ApiException('Tipo de elemento não encontrado');
            }

            if (! $type->validadeElementAnswer($answer)) {
                throw new ApiException("Resposta inválida para o tipo de elemento: {$type->description()}, resposta: {$answer}");
            }

            if ($type === QuestionnaireElementType::FILE_UPLOAD_FIELD) {
                $answer = $this->handleFileUpload($questionnaireResponse, $answer);
            }

            return [$elementId => $answer];
        })->toArray();
    }

    public function handleFileUpload(QuestionnaireResponse $questionnaireResponse, string $answer): string
    {
        $decodedAnswer = json_decode($answer, true);
        $dto = FileUploadElementDTO::fromArray($decodedAnswer);

        $questionnaireResponse->addMediaFromDisk($dto->file->key, 'central')
            ->usingFileName($dto->file->uuid . '.' . $dto->file->extension)
            ->toMediaCollection('attachments');

        return json_encode([
            'fileName' => $dto->fileName,
            'uuid' => $dto->file->uuid,
        ]);
    }
}
