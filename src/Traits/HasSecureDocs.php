<?php

namespace Asmari\SecureDocs\Traits;

use Asmari\SecureDocs\Models\SecureDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasSecureDocs
{
    /**
     * Relationship to the secure documents.
     */
    public function secureDocuments()
    {
        return $this->morphMany(SecureDocument::class, 'model');
    }

    /**
     * Encrypt and upload a file attached to this model.
     *
     * @param UploadedFile $file The file from the request
     * @param string $disk The disk to store to (default: local)
     * @return SecureDocument
     */
    public function uploadSecureDoc(UploadedFile $file, $disk = 'local')
    {
        // 1. Read & Encrypt
        $content = file_get_contents($file->getRealPath());
        $encrypted = Crypt::encrypt($content);

        // 2. Generate Hash Name
        $hashName = Str::random(40) . '.dat';
        $path = 'private_docs/' . $hashName;

        // 3. Store to Disk
        Storage::disk($disk)->put($path, $encrypted);

        // 4. Save Metadata
        return $this->secureDocuments()->create([
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_path' => $path,
        ]);
    }
}