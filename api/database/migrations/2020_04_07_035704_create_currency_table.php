<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyTable extends Migration
{
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('iso_code')->unique();
            $table->text('symbol');
            $table->decimal('exchange_rate', 12, 2);
            $table->timestamps();

			$table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
