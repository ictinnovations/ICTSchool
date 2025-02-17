<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stdBill', function (Blueprint $table) {
            $table->decimal('adjusted', 18, 2)->default(0.00)->after('paidAmount'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stdBill', function (Blueprint $table) {
            $table->dropColumn('adjusted');
        });
    }
};
