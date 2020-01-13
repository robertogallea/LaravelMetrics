<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('metrics.table.name'), function (Blueprint $table) {
            $table->increments('id');
            $table->string(config('metrics.table.columns.name'));
            $table->string(config('metrics.table.columns.type'));
            $table->float(config('metrics.table.columns.value'))->default(1);
            $table->string(config('metrics.table.columns.resolution'))->nullable();
            $table->json(config('metrics.table.columns.metadata'))->nullable();
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
        Schema::dropIfExists(config('metrics.table.name'));
    }
}
