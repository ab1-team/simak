<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usaha_id');
            $table->string('api_secret', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->foreign('usaha_id')
                ->references('id')->on('usaha')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
