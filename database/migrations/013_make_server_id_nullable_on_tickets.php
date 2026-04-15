<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
            $table->unsignedInteger('server_id')->nullable()->change();
            $table->foreign('server_id')->references('id')->on('servers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
            $table->unsignedInteger('server_id')->nullable(false)->change();
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
        });
    }
};
