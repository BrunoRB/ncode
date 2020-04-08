<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('from_id')->unsigned();
            $table->bigInteger('to_id')->unsigned();
            $table->text('details')->nullable();

            $table->text('amount');
            $table->bigInteger('from_currency_id');
            $table->foreign('from_currency_id')->references('id')->on('currencies')->onDelete('cascade');

            $table->text('to_amount');
            $table->bigInteger('to_currency_id');
            $table->foreign('to_currency_id')->references('id')->on('currencies')->onDelete('cascade');

            $table->timestamps();
			$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
