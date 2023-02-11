<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_media_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
    
            $table->text('facebook')->nullable();
            $table->text('twitter')->nullable();
            $table->text('linkedin')->nullable(); 
            $table->text('telegram')->nullable(); 
    
            $table->text('publish_settings')->nullable();
    
            $table->timestamps();
        });
    }
};
