<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakCorrectionRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('break_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correction_request_id')
                ->constrained('attendance_correction_requests')
                ->cascadeOnDelete()
                ->name('fk_break_corrections_att_correction_req');
            $table->foreignId('break_id')
                ->nullable()
                ->constrained('breaks')
                ->cascadeOnDelete()
                ->name('fk_break_corrections_break');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('break_correction_requests');
    }
}
