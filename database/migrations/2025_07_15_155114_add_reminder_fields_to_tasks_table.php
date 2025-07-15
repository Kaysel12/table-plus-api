<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dateTime('reminder_time')->nullable()->after('status'); // cuándo se debe enviar el recordatorio
            $table->integer('reminder_before')->nullable()->after('reminder_time'); // cantidad antes de la tarea
            $table->string('reminder_unit')->nullable()->after('reminder_before'); // unidad: minutes, hours, days
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['reminder_time', 'reminder_before', 'reminder_unit']);
        });
    }

};
