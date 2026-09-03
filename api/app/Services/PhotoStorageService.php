<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
use MongoDB\GridFS\Bucket;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoStorageService
{
    /** GridFS bucket in the same Mongo database as the collections. */
    private function bucket(): Bucket
    {
        $database = DB::connection('mongodb')->getMongoDB();

        return $database->selectGridFSBucket();
    }

    public function store(UploadedFile $file): string
    {
        $bucket = $this->bucket();
        $stream = fopen($file->getRealPath(), 'rb');

        $id = $bucket->uploadFromStream(
            'user-photo-'.uniqid('', true).'.'.$file->getClientOriginalExtension(),
            $stream,
            ['metadata' => ['mime' => $file->getMimeType()]]
        );

        if (is_resource($stream)) {
            fclose($stream);
        }

        return (string) $id;
    }

    public function stream(string $fileId): StreamedResponse
    {
        $bucket = $this->bucket();
        $download = $bucket->openDownloadStream(new ObjectId($fileId));
        $fileDoc = $bucket->findOne(['_id' => new ObjectId($fileId)]);
        $mime = $fileDoc?->metadata->mime ?? 'application/octet-stream';

        return response()->stream(function () use ($download): void {
            while (! feof($download)) {
                echo fread($download, 8192);
            }
            fclose($download);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
        ]);
    }

    public function delete(?string $fileId): void
    {
        if ($fileId === null || $fileId === '') {
            return;
        }

        $this->bucket()->delete(new ObjectId($fileId));
    }
}
