<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contract_notifications', function (Blueprint $table) {

            $table->id();

            $table->foreignId('contract_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('channel', [
                'email',
                'sms',
                'whatsapp',
                'system'
            ]);

            $table->enum('notification_type', [
                'renewal_warning',
                'expired',
                'auto_renewal',
                'manual'
            ]);

            $table->integer('days_before')
                ->nullable();

            $table->boolean('success')
                ->default(true);

            $table->text('response')
                ->nullable();

            $table->timestamp('sent_at');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_notifications');
    }
};