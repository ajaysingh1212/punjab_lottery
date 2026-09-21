<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ticket_types') && !Schema::hasColumn('ticket_types', 'image')) {
            Schema::table('ticket_types', function (Blueprint $table) {
                $table->string('image')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ticket_types') && Schema::hasColumn('ticket_types', 'image')) {
            Schema::table('ticket_types', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};
