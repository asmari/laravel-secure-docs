<?php

namespace Asmari\SecureDocs\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
// Note: We don't import Spatie classes here directly to prevent
// crashing if the user hasn't installed Spatie. 
// We rely on the user implementing HasMedia interface in their model.

trait HasSecureSpatieMedia
{
    // The user MUST invoke 'use InteractsWithMedia' in their model themselves,
    // or we can strictly check for it.

    public function addSecureMedia(UploadedFile $file, string $collectionName = 'default')
    {
        if (!method_exists($this, 'addMediaFromString')) {
            throw new \Exception("Please install 'spatie/laravel-medialibrary' and use the 'InteractsWithMedia' trait to use this feature.");
        }

        $encryptedContent = Crypt::encrypt(file_get_contents($file->getRealPath()));
        $secureFilename = Str::random(40) . '.dat';

        return $this->addMediaFromString($encryptedContent)
            ->usingFileName($secureFilename)
            ->withCustomProperties([
                'is_encrypted' => true,
                'original_mime_type' => $file->getMimeType(),
                'original_filename' => $file->getClientOriginalName(),
            ])
            ->toMediaCollection($collectionName);
    }

    public function downloadSecureMedia($media)
    {
        // $media should be an instance of Spatie\MediaLibrary\MediaCollections\Models\Media
        
        if (!$media->getCustomProperty('is_encrypted')) {
            abort(500, 'File is not encrypted.');
        }

        $decrypted = Crypt::decrypt(stream_get_contents($media->stream()));

        return response($decrypted)
            ->header('Content-Type', $media->getCustomProperty('original_mime_type'))
            ->header('Content-Disposition', 'attachment; filename="' . $media->getCustomProperty('original_filename') . '"');
    }
}