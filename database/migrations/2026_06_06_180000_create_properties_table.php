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
        Schema::create('properties', function (Blueprint $table) {
            $table->id('property_id');
            $table->string('property_code')->nullable()->unique();
            $table->string('property_title')->nullable();
            $table->text('property_description')->nullable();
            $table->string('property_type')->nullable();
            $table->string('property_listing_type')->nullable();
            $table->decimal('property_price', 15, 2)->nullable();
            $table->boolean('property_negotiable')->default(false);
            $table->decimal('property_area_sqft', 10, 2)->nullable();
            $table->decimal('property_area', 12, 2)->nullable();
            $table->string('property_area_unit')->nullable();
            $table->unsignedTinyInteger('property_bedrooms')->nullable();
            $table->unsignedTinyInteger('property_bathrooms')->nullable();
            $table->unsignedTinyInteger('property_parking')->nullable();
            $table->text('property_address')->nullable();
            $table->string('property_locality')->nullable();
            $table->string('property_city')->nullable();
            $table->string('property_state')->nullable();
            $table->string('property_pincode', 10)->nullable();
            $table->string('property_lp_number')->nullable();
            $table->unsignedSmallInteger('property_year')->nullable();
            $table->string('property_plot_number')->nullable();
            $table->string('property_ownership_type')->nullable();
            $table->decimal('property_latitude', 10, 7)->nullable();
            $table->decimal('property_longitude', 10, 7)->nullable();
            $table->string('property_contact_name')->nullable();
            $table->string('property_contact_phone', 20)->nullable();
            $table->string('property_contact_type')->nullable();
            $table->string('property_source')->nullable();
            $table->string('property_status')->default('draft');
            $table->string('property_created_by_type')->nullable();
            $table->unsignedBigInteger('property_created_by_id')->nullable();
            $table->foreignId('property_created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('property_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('property_assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('property_published_at')->nullable();
            $table->timestamps();

            $table->index('property_status');
            $table->index('property_type');
            $table->index('property_listing_type');
            $table->index('property_city');
            $table->index('property_locality');
            $table->index('property_contact_type');
            $table->index('property_source');
            $table->index('property_created_by_type');
            $table->index('property_created_by_id');
            $table->index('property_created_by');
            $table->index('property_reviewed_by');
            $table->index('property_assigned_to');
            $table->index('property_published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
