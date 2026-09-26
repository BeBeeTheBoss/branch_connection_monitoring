<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitored_hosts', function (Blueprint $table) {
            $table->dropUnique('monitored_hosts_ip_address_unique');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('monitored_hosts', function (Blueprint $table) {
            $table->dropIndex('monitored_hosts_ip_address_index');
            $table->unique('ip_address');
        });
    }
};
