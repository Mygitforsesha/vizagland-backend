<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_enquiries', function (Blueprint $table) {
            $table->id('contact_enquiry_id');
            $table->string('contact_enquiry_full_name');
            $table->string('contact_enquiry_phone', 20);
            $table->string('contact_enquiry_email');
            $table->string('contact_enquiry_subject');
            $table->string('contact_enquiry_property_reference_id')->nullable();
            $table->string('contact_enquiry_district')->nullable();
            $table->text('contact_enquiry_message');
            $table->boolean('contact_enquiry_consent')->default(false);
            $table->string('contact_enquiry_status')->default('new');
            $table->timestamps();

            $table->index('contact_enquiry_status');
            $table->index('contact_enquiry_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_enquiries');
    }
};
