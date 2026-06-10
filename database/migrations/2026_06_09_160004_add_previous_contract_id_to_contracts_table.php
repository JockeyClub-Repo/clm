<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {

            $table->unsignedBigInteger('previous_contract_id')
                  ->nullable()
                  ->after('id');

            $table->foreign('previous_contract_id')
                  ->references('id')
                  ->on('contracts')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {

            $table->dropForeign(['previous_contract_id']);
            $table->dropColumn('previous_contract_id');
        });
    }
};