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
        Schema::create('notices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150);
            $table->longText('body');
            $table->unsignedBigInteger('from_id')->index('from_id');
            $table->unsignedBigInteger('editor_id')->index('editor_id')->nullable();
            $table->longText('viewers_ids')->nullable();
            $table->timestamps();
            $table->foreign(['from_id'], 'notices_ibfk_1')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['editor_id'], 'notices_ibfk_2')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
