<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_files', function (Blueprint $table) {

            $table->id();

            $table->foreignId('contract_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('file_name');

            $table->string('file_path');

            $table->unsignedBigInteger('file_size')
                ->nullable();

            $table->string('mime_type', 100)
                ->nullable();

            $table->foreignId('uploaded_by')
                ->constrained('users');

            $table->timestamps();

            $table->index('contract_id');
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_files');
    }
};