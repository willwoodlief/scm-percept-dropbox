<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('percept_dropbox_access_token')) {
            Schema::create('percept_dropbox_access_token', function (Blueprint $table) {
                $table->id();
                $table->json('token_data')->nullable();
                $table->string('token', 355)->nullable();
                $table->integer('expire_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('percept_dropbox_access_token');
    }
};