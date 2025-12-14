<?php

namespace Asmari\SecureDocs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class SecureDocument extends Model
{
    protected $table = 'secure_documents';
    protected $guarded = [];

    /**
     * Accessor to get decrypted content on demand.
     * Usage: $doc->decrypted_content
     */
    public function getDecryptedContentAttribute()
    {
        // Ensure we are checking the correct disk (local/private)
        if (!Storage::disk('local')->exists($this->file_path)) {
            return null;
        }

        try {
            $encrypted = Storage::disk('local')->get($this->file_path);
            return Crypt::decrypt($encrypted);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return null; // Or throw custom exception
        }
    }

    /**
     * Get the owning model (User, etc).
     */
    public function model()
    {
        return $this->morphTo();
    }
}