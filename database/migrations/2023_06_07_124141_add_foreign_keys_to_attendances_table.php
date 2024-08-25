<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign(['in_location_id'], 'fk_in_location_id_attendances_area_id')->references(['id'])->on('areas')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['out_location_id'], 'fk_out_location_id_attendances_area_id')->references(['id'])->on('areas')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['worker_id'], 'fk_worker_id_users_id')->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign('fk_in_location_id_attendances_area_id');
            $table->dropForeign('fk_out_location_id_attendances_area_id');
            $table->dropForeign('fk_worker_id_users_id');
        });
    }
};
