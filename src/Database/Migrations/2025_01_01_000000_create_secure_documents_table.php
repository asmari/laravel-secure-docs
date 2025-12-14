<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('secure_documents', function (Blueprint $table) {
            $table->id();
            // Polymorphic relation: allows attaching docs to User, Employee, etc.
            $table->morphs('model'); 
            $table->string('original_name');
            $table->string('mime_type');
            $table->string('file_path'); // Stores the internal path
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('secure_documents');
    }
};