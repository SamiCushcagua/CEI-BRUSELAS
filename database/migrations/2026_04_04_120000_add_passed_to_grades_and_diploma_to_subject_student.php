<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->boolean('passed')->default(false)->after('notes');
        });

        Schema::table('subject_student', function (Blueprint $table) {
            $table->boolean('diploma_delivered')->default(false)->after('period_id');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('passed');
        });

        Schema::table('subject_student', function (Blueprint $table) {
            $table->dropColumn('diploma_delivered');
        });
    }
};
