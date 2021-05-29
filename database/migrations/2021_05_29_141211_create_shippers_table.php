<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shippers', function (Blueprint $table) {
            $table->bigIncrements("companyId");
            $table->string("companyName")->nullable();  //Because user might register his name in arabic not in english. That's why it's nullable (can be null)
            $table->string("companyNameArabic")->nullable();  //Because user might register his name in arabic not in english. That's why it's nullable (can be null)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shippers');
    }
}
