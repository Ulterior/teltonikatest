<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSyslogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      // Create the `Posts` table
      Schema::create('syslogs', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->timestamp('recorded_on');
          $table->string('details');
          $table->index(['recorded_on']);
      });

      DB::statement("ALTER TABLE syslogs MODIFY recorded_on TIMESTAMP(3)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('syslogs');
    }
}
