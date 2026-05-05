<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('published_at')->nullable();
            $table->boolean('active')->default(1);
            $table->boolean('featured')->default(0);
            $table->string('name');
            $table->integer('image')->nullable();
            $table->longText('text')->nullable();
            $table->json('data')->nullable();
            $table->json('images')->nullable();
            $table->string('external_link')->nullable();
            $table->integer('file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blogs');
    }
}
