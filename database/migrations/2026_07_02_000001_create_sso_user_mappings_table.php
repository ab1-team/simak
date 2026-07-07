<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sso_user_mappings', function (Blueprint $table) {
            $table->id();
            // Identity as known by Holding. Either of these can be the lookup key.
            $table->string('holding_email')->unique();
            $table->unsignedBigInteger('holding_lid')->nullable();
            // Local user this maps to. NOTE: no FK constraint because the
            // existing `users` table is MyISAM/latin1 and does not support
            // referential integrity. Enforce manually in SsoController.
            $table->unsignedBigInteger('local_user_id');
            $table->timestamps();

            $table->index('local_user_id');
            $table->index('holding_lid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_user_mappings');
    }
};