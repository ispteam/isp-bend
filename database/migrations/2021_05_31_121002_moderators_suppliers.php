<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModeratorsSuppliers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('moderators_suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger("supplier_id");
            $table->foreign("supplier_id")->references("supplierId")->on("suppliers");
            $table->unsignedBigInteger("moderator_id");
            $table->foreign("moderator_id")->references("moderatorId")->on("moderators");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('moderators_suppliers');
    }
}
