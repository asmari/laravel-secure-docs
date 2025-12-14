# Laravel Secure Docs

**Laravel Secure Docs** is a robust package for securely encrypting and storing sensitive user documents (ID Cards, Passports, Contracts) in Laravel applications.

It uses **AES-256-CBC** encryption to ensure that files are encrypted *before* they touch the disk. Even if your storage volume is compromised, the files remain unreadable without your application's `APP_KEY`.

## Features

- **Zero-Knowledge Storage:** Files are stored as encrypted blobs (`.dat`).
- **Filename Hashing:** Original filenames are hidden; storage paths are randomized.
- **On-the-Fly Decryption:** Files are decrypted in memory only when requested.
- **Polymorphic:** Attach secure documents to *any* model (`User`, `Employee`, `Company`, etc.).
- **Laravel 10, 11, & 12 Ready.**

## Requirements

- PHP ^8.1
- Laravel ^10.0, ^11.0, or ^12.0

---

## Installation

Run:
```bash
composer require asmari/laravel-secure-docs:dev-main
```

## Setup

1. Run Migrations
Publish the package migration to create the secure_documents table:

```bash 
php artisan migrate
```
2. Add the Trait
Add the HasSecureDocs trait to any model that needs to upload documents (e.g., User, Employee).

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Asmari\SecureDocs\Traits\HasSecureDocs; // <--- Import

class User extends Authenticatable
{
    use HasSecureDocs; // <--- Enable

    // ...
}
```

## Usage
1. Encrypting & Uploading
In your Controller, use the uploadSecureDoc method. This handles encryption, hashing, and database linking automatically.

```php
public function store(Request $request)
{
    $request->validate([
        'id_card' => 'required|file|mimes:jpg,png,pdf|max:2048'
    ]);

    // Automatically encrypts, hashes, and saves metadata
    $user = auth()->user();
    $user->uploadSecureDoc($request->file('id_card'));

    return back()->with('success', 'Document encrypted successfully!');
}
```
2. Decrypting & Viewing
To view a file, use the model to fetch the metadata and stream the decrypted content.

Controller Example:
```php
use Asmari\SecureDocs\Models\SecureDocument;

public function show($id)
{
    $doc = SecureDocument::findOrFail($id);

    // Authorization: Ensure user owns this file (or use a Policy)
    if ($doc->model_id !== auth()->id()) {
        abort(403);
    }

    // 'decrypted_content' is a helper attribute that decrypts on the fly
    return response($doc->decrypted_content)
        ->header('Content-Type', $doc->mime_type)
        ->header('Content-Disposition', 'inline; filename="' . $doc->original_name . '"');
}
```
Blade View Example:
```html
<a href="{{ route('docs.show', $doc->id) }}" target="_blank">
    View Encrypted File
</a>
```
⚠️ Critical Security Notice

This package relies on your Laravel application's APP_KEY (found in .env).

1. Do NOT lose your APP_KEY. If you change or lose this key, all encrypted files will become permanently unreadable (garbage data).

2. Backup your APP_KEY securely.

3. Ensure your storage/app/private_docs folder is not symlinked to public/. It must remain private.