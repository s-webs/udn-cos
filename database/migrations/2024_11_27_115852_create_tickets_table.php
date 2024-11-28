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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('waiting');
            $table->foreignId('assigned_table_id')->nullable()->constrained('tables')->onDelete('set null');
            $table->date('created_date');
            $table->integer('ticket_number');
            $table->timestamps();

            $table->index(['created_date', 'category_id']);
            $table->unique(['category_id', 'created_date', 'ticket_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
