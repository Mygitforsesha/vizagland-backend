<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id('support_request_id');
            $table->string('support_request_name');
            $table->string('support_request_email');
            $table->string('support_request_phone', 20)->nullable();
            $table->text('support_request_message');
            $table->string('support_request_status')->default('new');
            $table->timestamps();

            $table->index('support_request_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_requests');
    }
};
