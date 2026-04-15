<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_category_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('ticket_categories')->cascadeOnDelete();
            $table->string('label');
            $table->string('key');
            $table->string('type')->default('text'); // text, number, textarea, select, toggle
            $table->json('options')->nullable();      // for select type: [{label, value}]
            $table->boolean('required')->default(false);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_category_fields');
    }
};
