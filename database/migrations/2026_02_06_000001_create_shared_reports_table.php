<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->string('property_id');
            $table->string('widget_type');
            $table->string('date_range')->default('30days');
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_reports');
    }
};
