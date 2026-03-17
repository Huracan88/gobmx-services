<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastSyncUpToSentreRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sentre_records', function (Blueprint $table) {
            $table->timestamp('last_sync_up')->nullable()->default(null)->after('caracter_documental');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sentre_records', function (Blueprint $table) {
            $table->dropColumn('last_sync_up');
        });
    }
}
