<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_field_configurations', function (Blueprint $table) {
            $table->id('property_field_configuration_id');
            $table->string('property_field_key')->unique();
            $table->string('property_field_label');
            $table->string('property_field_section');
            $table->string('property_field_data_type');
            $table->boolean('property_field_is_active')->default(true);
            $table->boolean('property_field_is_required')->default(false);
            $table->unsignedInteger('property_field_display_order')->default(0);
            $table->timestamp('property_field_created_at')->useCurrent();
            $table->timestamp('property_field_updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('property_field_section');
            $table->index('property_field_is_active');
            $table->index('property_field_display_order');
        });

        $now = now();

        $defaultFields = [
            ['property_approval_authority', 'Approval Authority', 'property_approval', 'text', 10],
            ['property_village', 'Village', 'property_location', 'text', 10],
            ['property_nearby_location', 'Nearby Location', 'property_location', 'text', 20],
            ['property_custom_nearby_location', 'Custom Nearby Location', 'property_location', 'text', 30],
            ['property_district', 'District', 'property_location', 'text', 40],
            ['property_mandal', 'Mandal', 'property_location', 'text', 50],
            ['property_panchayati', 'Panchayati', 'property_location', 'text', 60],
            ['property_gvmc', 'GVMC', 'property_location', 'text', 70],
            ['property_vmrda', 'VMRDA', 'property_location', 'text', 80],
            ['property_registration_area', 'Registration Area', 'property_location', 'text', 90],
            ['property_authority', 'Authority', 'property_location', 'text', 100],
            ['property_residential_type', 'Residential Type', 'property_group_and_types', 'text', 10],
            ['property_commercial_type', 'Commercial Type', 'property_group_and_types', 'text', 20],
            ['property_development_type', 'Development Type', 'property_group_and_types', 'text', 30],
            ['property_layout_type', 'Layout Type', 'property_group_and_types', 'text', 40],
            ['property_construction_status', 'Construction Status', 'property_group_and_types', 'text', 50],
            ['property_construction_type', 'Construction Type', 'property_group_and_types', 'text', 60],
            ['property_price', 'Price', 'property_details', 'number', 10],
            ['property_price_range', 'Price Range', 'property_details', 'text', 20],
            ['property_area', 'Area', 'property_details', 'number', 30],
            ['property_area_unit', 'Area Unit', 'property_details', 'text', 40],
            ['property_price_per_sqft', 'Price Per Sqft', 'property_details', 'text', 50],
            ['property_age', 'Age', 'property_details', 'text', 60],
            ['property_facing', 'Facing', 'property_details', 'text', 70],
            ['property_total_floors', 'Total Floors', 'property_details', 'integer', 80],
            ['property_floor_number', 'Floor Number', 'property_details', 'text', 90],
            ['property_furnishing', 'Furnishing', 'property_details', 'text', 100],
            ['property_under', 'Under', 'property_details', 'text', 110],
            ['property_lp_no', 'LP Number', 'property_details', 'text', 120],
            ['property_plot_no', 'Plot Number', 'property_details', 'text', 130],
            ['property_year', 'Year', 'property_details', 'integer', 140],
            ['property_bedrooms', 'Bedrooms', 'property_details', 'integer', 150],
            ['property_owner_name', 'Owner Name', 'property_owner', 'text', 10],
            ['property_owner_phone', 'Owner Phone', 'property_owner', 'text', 20],
            ['property_owner_email', 'Owner Email', 'property_owner', 'email', 30],
            ['property_other_service_name', 'Other Service', 'property_other_services', 'text', 10],
            ['property_images', 'Property Images', 'property_media', 'file', 10],
            ['property_documents', 'Property Documents', 'property_media', 'file', 20],
        ];

        $rows = [];

        foreach ($defaultFields as [$key, $label, $section, $dataType, $displayOrder]) {
            $rows[] = [
                'property_field_key' => $key,
                'property_field_label' => $label,
                'property_field_section' => $section,
                'property_field_data_type' => $dataType,
                'property_field_is_active' => true,
                'property_field_is_required' => false,
                'property_field_display_order' => $displayOrder,
                'property_field_created_at' => $now,
                'property_field_updated_at' => $now,
            ];
        }

        DB::table('property_field_configurations')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('property_field_configurations');
    }
};
