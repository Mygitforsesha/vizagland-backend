<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $masterApi = '/api/public/master-dropdowns';

        $placeholders = [
            'property_village' => 'Select Village',
            'property_nearby_location' => 'Select Nearby Location',
            'property_custom_nearby_location' => 'Enter Nearby Location',
            'property_district' => 'Enter District',
            'property_mandal' => 'Enter Mandal',
            'property_panchayati' => 'Enter Panchayati / Sachivalayam',
            'property_gvmc' => 'Enter GVMC Zone / Ward Number',
            'property_vmrda' => 'Enter VMRDA',
            'property_registration_area' => 'Enter Register Office Location',
            'property_authority' => 'Enter GVMC / VMRDA',
            'property_category' => 'Select Property Category',
            'property_project_name' => 'Enter Project / Property Name',
            'property_lp_no' => 'Select LP No / B.P.A No',
            'property_year' => 'Select LP No / B.P.A No Year',
            'property_total_floors' => 'Enter Total Floors / Total Plots',
            'property_block_phase' => 'Enter Block No / Phase (1-10)',
            'property_flat_door_no' => 'Enter Plot No / D.No. / Flat No',
            'property_floor_number' => 'Select Floor No',
            'property_facing' => 'Select Facing',
            'property_area' => 'Enter Area',
            'property_area_unit' => 'Select Area Unit',
            'property_price' => 'Enter Price',
            'property_price_range' => 'Select Price Range',
            'property_age' => 'Select Property Age',
            'property_bedrooms' => 'Select Bed Room',
            'property_furnishing' => 'Select Furnishing',
            'property_under' => 'Select Property Falls Under',
            'property_approval_authority' => 'Select Approved By',
            'property_document_no' => 'Select Document No',
            'property_document_year' => 'Select Document Year',
            'property_registration_office_area' => 'Enter Registered Office Area',
            'property_youtube_video_link' => 'Paste YouTube Video Link',
            'property_location_link' => 'Paste Property Location Link',
            'property_images' => 'Drag and drop property photos here, or click to browse',
            'property_documents' => 'Drag and drop deeds, approvals, or plans here',
        ];

        foreach ($placeholders as $key => $placeholder) {
            DB::table('property_field_configurations')
                ->where('property_field_key', $key)
                ->update([
                    'property_field_placeholder' => $placeholder,
                    'property_field_updated_at' => $now,
                ]);
        }

        DB::table('property_field_configurations')
            ->where('property_field_key', 'property_contact_numbers')
            ->update([
                'property_field_validation' => json_encode([
                    'fields' => [
                        [
                            'key' => 'registration_type',
                            'label' => 'Registration Type',
                            'placeholder' => 'Select Registration Type',
                            'type' => 'select',
                            'required' => true,
                            'options_api' => $masterApi,
                        ],
                        [
                            'key' => 'phone_number',
                            'label' => 'Phone Number',
                            'placeholder' => 'Enter 10-digit Mobile Number',
                            'type' => 'text',
                            'required' => true,
                        ],
                    ],
                    'unlimited' => true,
                ], JSON_THROW_ON_ERROR),
                'property_field_updated_at' => $now,
            ]);

        DB::table('property_field_configurations')
            ->whereIn('property_field_key', ['property_owner_name', 'property_owner_phone'])
            ->update([
                'property_field_public_section' => null,
                'property_field_public_order' => null,
                'property_field_is_required' => false,
                'property_field_updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // Intentionally left empty — placeholder copy is not reverted.
    }
};
