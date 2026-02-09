<?php

declare(strict_types=1);

namespace Modules\DocumentAI\Support;

use Google\Cloud\DocumentAI\V1\Client\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\Document;
use Google\Cloud\DocumentAI\V1\ProcessRequest;
use Google\Cloud\DocumentAI\V1\RawDocument;
use Google\Protobuf\RepeatedField;
use Illuminate\Support\Facades\Storage;
use Modules\Common\Core\DTOs\UploadedFileDTO;
use Modules\Common\Core\Exceptions\ApiException;
use Throwable;

readonly class DocumentAI
{
    public static function processDocument(UploadedFileDTO $dto, string $processorId): ?Document
    {
        $projectId = env('DOCUMENT_AI_PROJECT_ID');
        $location = env('DOCUMENT_AI_LOCATION');

        $name = "projects/{$projectId}/locations/{$location}/processors/{$processorId}";
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . base_path() . '/' . env('GOOGLE_APPLICATION_CREDENTIALS'));

        $tempDir = storage_path('app/tmp');
        $fileExtension = $dto->extension;
        $tempPath = $tempDir . '/' . $dto->uuid . '.' . $fileExtension;

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $document = null;

        try {
            $fileContents = Storage::disk('central')->get($dto->key);
            file_put_contents($tempPath, $fileContents);

            if (! file_exists($tempPath)) {
                throw new ApiException("File could not be downloaded: {$tempPath}");
            }

            $fileType = mime_content_type($tempPath);

            $client = new DocumentProcessorServiceClient();

            $rawDocument = new RawDocument();
            $rawDocument->setContent(file_get_contents($tempPath));
            $rawDocument->setMimeType($fileType);

            $testRequest = new ProcessRequest([
                'name' => $name,
                'skip_human_review' => true,
                'raw_document' => $rawDocument,
            ]);

            $response = $client->processDocument($testRequest);
            $document = $response->getDocument();
        } catch (Throwable $e) {
            throw $e;
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        return $document;
    }

    public static function repeatedFieldToArray(RepeatedField $repeatedField): array
    {
        $array = [];
        foreach ($repeatedField as $item) {
            $array[] = $item;
        }

        return $array;
    }

    public static function getValueByType(array $entities, string $type): ?string
    {
        foreach ($entities as $entity) {
            if ($entity->getType() === $type) {
                if (! empty($entity)) {
                    return $entity->getMentionText();
                }

                return null;

            }
        }

        return null;
    }
}
