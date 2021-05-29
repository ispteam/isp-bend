<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('models', function (Blueprint $table) {
            $table->bigIncrements("modelId");
            $table->string("partNo")->unique(); //Why part number is unique? To avoid any clash between the parts.
            $table->text("partDescription");
            $table->unsignedBigInteger("brandId");
            $table->unsignedBigInteger("supplierId");
            $table->string("quantity");
            $table->timestamps();
            $table->foreign("brandId")->references("brandId")->on("brands")->onDelete("CASCADE")->onUpdate("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('models');
    }
}
