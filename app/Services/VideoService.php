<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;

class VideoService
{
    protected string $disk = 'r2';

    public function upload(UploadedFile $file, int $courseId): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "videos/course_{$courseId}/{$filename}";

        Storage::disk($this->disk)->put($path, file_get_contents($file));

        return $path;
    }

    /**
     * Generate presigned URL for direct upload to R2
     */
    public function getPresignedUploadUrl(int $courseId, string $filename, string $contentType): array
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $uniqueFilename = Str::uuid() . '.' . $extension;
        $path = "videos/course_{$courseId}/{$uniqueFilename}";

        $client = new S3Client([
            'region' => 'auto',
            'version' => 'latest',
            'endpoint' => config('filesystems.disks.r2.endpoint'),
            'credentials' => [
                'key' => config('filesystems.disks.r2.key'),
                'secret' => config('filesystems.disks.r2.secret'),
            ],
            'use_path_style_endpoint' => true,
        ]);

        $command = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.r2.bucket'),
            'Key' => $path,
            'ContentType' => $contentType,
        ]);

        $presignedRequest = $client->createPresignedRequest($command, '+60 minutes');
        $presignedUrl = (string) $presignedRequest->getUri();

        return [
            'upload_url' => $presignedUrl,
            'path' => $path,
        ];
    }

    /**
     * Get signed URL for video streaming directly from R2
     * Uses presigned URL for direct R2 access (faster than going through server)
     */
    public function getSignedUrl(Video $video, int $expiresInMinutes = 120): string
    {
        // Get file extension to determine content type
        $extension = strtolower(pathinfo($video->video_path, PATHINFO_EXTENSION));
        $contentType = match ($extension) {
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska',
            'm4v' => 'video/x-m4v',
            default => 'video/mp4',
        };

        $client = new S3Client([
            'region' => 'auto',
            'version' => 'latest',
            'endpoint' => config('filesystems.disks.r2.endpoint'),
            'credentials' => [
                'key' => config('filesystems.disks.r2.key'),
                'secret' => config('filesystems.disks.r2.secret'),
            ],
            'use_path_style_endpoint' => true,
        ]);

        $command = $client->getCommand('GetObject', [
            'Bucket' => config('filesystems.disks.r2.bucket'),
            'Key' => $video->video_path,
            'ResponseContentType' => $contentType,
            'ResponseContentDisposition' => 'inline',
        ]);

        $presignedRequest = $client->createPresignedRequest($command, "+{$expiresInMinutes} minutes");

        return (string) $presignedRequest->getUri();
    }

    public function delete(Video $video): bool
    {
        try {
            if (Storage::disk($this->disk)->exists($video->video_path)) {
                return Storage::disk($this->disk)->delete($video->video_path);
            }
        } catch (\Exception $e) {
            // Log the error but don't fail - allow database record to be deleted
            Log::warning('Failed to delete video from storage: ' . $e->getMessage(), [
                'video_id' => $video->id,
                'path' => $video->video_path,
            ]);
        }

        return true;
    }

    public function exists(string $path): bool
    {
        try {
            return Storage::disk($this->disk)->exists($path);
        } catch (\Exception $e) {
            Log::warning('Failed to check video existence: ' . $e->getMessage());
            return false;
        }
    }
}
