<?php

use App\Models\Transaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->uuid('uuid');
            $table->foreignId('user_id');
            $table->foreignId('recipient_id');
            $table->foreignId('source_currency_id');
            $table->foreignId('target_currency_id');
            $table->double('amount');
            $table->string('reference')->unique();
            $table->float('rate');
            $table->float('transfer_fee');
            $table->float('variable_fee');
            $table->float('fixed_fee');
            $table->enum('type', Transaction::TYPE);
            $table->enum('status', Transaction::STATUS)->default(Transaction::STATUS['Pending']);
            $table->json('meta_data')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('transactions');
    }
}
