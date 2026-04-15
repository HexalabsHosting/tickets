<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger')->default('ticket_created');
            $table->json('conditions');  // [{field, operator, value}]
            $table->json('actions');     // [{type, value}]
            $table->boolean('active')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_automation_rules');
    }
};
