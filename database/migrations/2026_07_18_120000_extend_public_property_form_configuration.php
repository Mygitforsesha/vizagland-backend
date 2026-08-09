<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_field_configurations', function (Blueprint $table): void {
            if (! Schema::hasColumn('property_field_configurations', 'property_field_placeholder')) {
                $table->string('property_field_placeholder')->nullable()->after('property_field_label');
            }
            if (! Schema::hasColumn('property_field_configurations', 'property_field_is_readonly')) {
                $table->boolean('property_field_is_readonly')->default(false)->after('property_field_is_required');
            }
            if (! Schema::hasColumn('property_field_configurations', 'property_field_is_searchable')) {
                $table->boolean('property_field_is_searchable')->default(false)->after('property_field_is_readonly');
            }
            if (! Schema::hasColumn('property_field_configurations', 'property_field_is_multiple')) {
                $table->boolean('property_field_is_multiple')->default(false)->after('property_field_is_searchable');
            }
            if (! Schema::hasColumn('property_field_configurations', 'property_field_options')) {
                $table->json('property_field_options')->nullable()->after('property_field_is_multiple');
            }
            if (! Schema::hasColumn('property_field_configurations', 'property_field_options_api')) {
                $table->string('property_field_options_api')->nullable()->after('property_field_options');
            }
            if (! Schema::hasColumn('property_field_configurations', 'property_field_validation')) {
                $table->json('property_field_validation')->nullable()->after('property_field_options_api');
            }
            if (! Schema::hasColumn('property_field_configurations', 'property_field_default_value')) {
                $table->string('property_field_default_value')->nullable()->after('property_field_validation');
            }
            if (! Schema::hasColumn('property_field_configurations', 'property_field_depends_on')) {
                $table->json('property_field_depends_on')->nullable()->after('property_field_default_value');
            }
            if (! Schema::hasColumn('property_field_configurations', 'property_field_public_section')) {
                $table->string('property_field_public_section')->nullable()->after('property_field_depends_on');
            }
            if (! Schema::hasColumn('property_field_configurations', 'property_field_public_order')) {
                $table->unsignedInteger('property_field_public_order')->nullable()->after('property_field_public_section');
            }
        });

        if (! Schema::hasTable('master_dropdowns')) {
            Schema::create('master_dropdowns', function (Blueprint $table): void {
                $table->id('master_dropdown_id');
                $table->string('master_dropdown_key')->unique();
                $table->string('master_dropdown_label');
                $table->boolean('master_dropdown_is_active')->default(true);
                $table->unsignedInteger('master_dropdown_display_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('master_dropdown_options')) {
            Schema::create('master_dropdown_options', function (Blueprint $table): void {
                $table->id('master_dropdown_option_id');
                $table->foreignId('master_dropdown_id')
                    ->constrained('master_dropdowns', 'master_dropdown_id')
                    ->cascadeOnDelete();
                $table->string('master_dropdown_option_value');
                $table->string('master_dropdown_option_label');
                $table->boolean('master_dropdown_option_is_active')->default(true);
                $table->unsignedInteger('master_dropdown_option_display_order')->default(0);
                $table->timestamps();

                $table->unique(
                    ['master_dropdown_id', 'master_dropdown_option_value'],
                    'master_dropdown_option_unique',
                );
            });
        }

        if (! Schema::hasTable('property_category_area_units')) {
            Schema::create('property_category_area_units', function (Blueprint $table): void {
                $table->id('property_category_area_unit_id');
                $table->string('property_category_value');
                $table->string('property_area_unit_value');
                $table->string('property_area_unit_label');
                $table->boolean('property_category_area_unit_is_active')->default(true);
                $table->unsignedInteger('property_category_area_unit_display_order')->default(0);
                $table->timestamps();

                $table->unique(
                    ['property_category_value', 'property_area_unit_value'],
                    'property_category_area_unit_unique',
                );
                $table->index('property_category_value');
            });
        }

        $this->seedMasterDropdowns();
        $this->seedCategoryAreaUnits();
        $this->configurePublicFormFields();
    }

    public function down(): void
    {
        Schema::dropIfExists('property_category_area_units');
        Schema::dropIfExists('master_dropdown_options');
        Schema::dropIfExists('master_dropdowns');

        Schema::table('property_field_configurations', function (Blueprint $table): void {
            foreach ([
                'property_field_placeholder',
                'property_field_is_readonly',
                'property_field_is_searchable',
                'property_field_is_multiple',
                'property_field_options',
                'property_field_options_api',
                'property_field_validation',
                'property_field_default_value',
                'property_field_depends_on',
                'property_field_public_section',
                'property_field_public_order',
            ] as $column) {
                if (Schema::hasColumn('property_field_configurations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function seedMasterDropdowns(): void
    {
        if (DB::table('master_dropdowns')->exists()) {
            return;
        }

        $now = now();
        $dropdowns = [
            'registration_type' => ['Registration Type', [
                ['owner', 'Owner'],
                ['agent', 'Agent'],
                ['realtor', 'Realtor'],
                ['builder', 'Builder'],
                ['developer', 'Developer'],
                ['company', 'Company'],
                ['seller', 'Seller'],
                ['buyer', 'Buyer'],
                ['others', 'Others'],
            ]],
            'property_category' => ['Property Category', [
                ['flats', 'Flats'],
                ['independent_house', 'Independent House'],
                ['villa', 'Villa'],
                ['apartment', 'Apartment'],
                ['land', 'Land'],
                ['plot', 'Plot'],
                ['commercial', 'Commercial'],
                ['factory', 'Factory'],
                ['warehouse', 'Warehouse'],
                ['farm_land', 'Farm Land'],
            ]],
            'facing' => ['Facing', [
                ['east', 'East'],
                ['west', 'West'],
                ['north', 'North'],
                ['south', 'South'],
                ['north_east', 'North East'],
                ['north_west', 'North West'],
                ['south_east', 'South East'],
                ['south_west', 'South West'],
            ]],
            'floor_number' => ['Floor Number', [
                ['ground', 'Ground'],
                ['1', '1'],
                ['2', '2'],
                ['3', '3'],
                ['4', '4'],
                ['5', '5'],
                ['6', '6'],
                ['7', '7'],
                ['8', '8'],
                ['9', '9'],
                ['10', '10'],
                ['10_plus', '10+'],
            ]],
            'price_range' => ['Price Range', [
                ['below_25_lakhs', 'Below 25 Lakhs'],
                ['25_50_lakhs', '25 - 50 Lakhs'],
                ['50_75_lakhs', '50 - 75 Lakhs'],
                ['75_lakhs_1_crore', '75 Lakhs - 1 Crore'],
                ['1_2_crore', '1 - 2 Crore'],
                ['2_5_crore', '2 - 5 Crore'],
                ['above_5_crore', 'Above 5 Crore'],
            ]],
            'property_age' => ['Property Age', [
                ['under_construction', 'Under Construction'],
                ['0_1_years', '0 - 1 Years'],
                ['1_5_years', '1 - 5 Years'],
                ['5_10_years', '5 - 10 Years'],
                ['10_plus_years', '10+ Years'],
            ]],
            'bedrooms' => ['Bedrooms', [
                ['1', '1 BHK'],
                ['2', '2 BHK'],
                ['3', '3 BHK'],
                ['4', '4 BHK'],
                ['5', '5 BHK'],
                ['5_plus', '5+ BHK'],
            ]],
            'furnishing' => ['Furnishing', [
                ['unfurnished', 'Unfurnished'],
                ['semi_furnished', 'Semi Furnished'],
                ['fully_furnished', 'Fully Furnished'],
            ]],
            'approval_authority' => ['Approval Authority', [
                ['gvmc', 'GVMC'],
                ['vmrda', 'VMRDA'],
                ['panchayat', 'Panchayat'],
                ['dtcp', 'DTCP'],
                ['other', 'Other'],
            ]],
            'property_falls_under' => ['Property Falls Under', [
                ['gvmc', 'GVMC'],
                ['vmrda', 'VMRDA'],
                ['panchayat', 'Panchayat'],
                ['municipality', 'Municipality'],
                ['other', 'Other'],
            ]],
            'lp_year' => ['LP Year', $this->yearOptions()],
            'document_year' => ['Document Year', $this->yearOptions()],
            'area_units' => ['Area Units', [
                ['sft', 'SFT'],
                ['sq_yards', 'Sq.Yards'],
                ['acres', 'Acres'],
                ['cents', 'Cents'],
                ['sqft', 'Sq. Ft.'],
                ['sqm', 'Sq. M.'],
            ]],
        ];

        $order = 10;
        foreach ($dropdowns as $key => [$label, $options]) {
            $dropdownId = DB::table('master_dropdowns')->insertGetId([
                'master_dropdown_key' => $key,
                'master_dropdown_label' => $label,
                'master_dropdown_is_active' => true,
                'master_dropdown_display_order' => $order,
                'created_at' => $now,
                'updated_at' => $now,
            ], 'master_dropdown_id');

            $optionOrder = 10;
            $rows = [];
            foreach ($options as [$value, $optionLabel]) {
                $rows[] = [
                    'master_dropdown_id' => $dropdownId,
                    'master_dropdown_option_value' => $value,
                    'master_dropdown_option_label' => $optionLabel,
                    'master_dropdown_option_is_active' => true,
                    'master_dropdown_option_display_order' => $optionOrder,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $optionOrder += 10;
            }
            DB::table('master_dropdown_options')->insert($rows);
            $order += 10;
        }
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function yearOptions(): array
    {
        $years = [];
        $current = (int) date('Y');
        for ($year = $current; $year >= 1980; $year--) {
            $years[] = [(string) $year, (string) $year];
        }

        return $years;
    }

    private function seedCategoryAreaUnits(): void
    {
        if (DB::table('property_category_area_units')->exists()) {
            return;
        }

        $now = now();
        $mappings = [
            'flats' => [['sft', 'SFT']],
            'apartment' => [['sft', 'SFT']],
            'independent_house' => [['sft', 'SFT'], ['sq_yards', 'Sq.Yards']],
            'villa' => [['sft', 'SFT'], ['sq_yards', 'Sq.Yards']],
            'land' => [['sq_yards', 'Sq.Yards'], ['acres', 'Acres'], ['cents', 'Cents']],
            'plot' => [['sq_yards', 'Sq.Yards'], ['acres', 'Acres'], ['cents', 'Cents']],
            'farm_land' => [['sq_yards', 'Sq.Yards'], ['acres', 'Acres'], ['cents', 'Cents']],
            'factory' => [['sft', 'SFT'], ['sq_yards', 'Sq.Yards'], ['acres', 'Acres'], ['cents', 'Cents']],
            'warehouse' => [['sft', 'SFT'], ['sq_yards', 'Sq.Yards'], ['acres', 'Acres'], ['cents', 'Cents']],
            'commercial' => [['sft', 'SFT'], ['sq_yards', 'Sq.Yards']],
        ];

        $rows = [];
        foreach ($mappings as $category => $units) {
            $order = 10;
            foreach ($units as [$value, $label]) {
                $rows[] = [
                    'property_category_value' => $category,
                    'property_area_unit_value' => $value,
                    'property_area_unit_label' => $label,
                    'property_category_area_unit_is_active' => true,
                    'property_category_area_unit_display_order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $order += 10;
            }
        }

        DB::table('property_category_area_units')->insert($rows);
    }

    private function configurePublicFormFields(): void
    {
        $now = now();
        $locationApi = '/api/master/locations/search';
        $masterApi = '/api/public/master-dropdowns';

        $publicFields = [
            // Location
            ['property_village', 'Village', 'Select Village', 'property_location', 'property_location', 10, 'select', true, true, $locationApi, null, null],
            ['property_nearby_location', 'Nearby Location', 'Select Nearby Location', 'property_location', 'property_location', 20, 'select', false, false, $locationApi, null, null],
            ['property_custom_nearby_location', 'Custom Nearby Location', 'Enter Nearby Location', 'property_location', 'property_location', 30, 'text', false, false, null, null, null],
            ['property_district', 'District', 'Enter District', 'property_location', 'property_location', 40, 'text', false, true, $locationApi, null, null],
            ['property_mandal', 'Mandal', 'Enter Mandal', 'property_location', 'property_location', 50, 'text', false, true, $locationApi, null, null],
            ['property_panchayati', 'Panchayati', 'Enter Panchayati / Sachivalayam', 'property_location', 'property_location', 60, 'text', false, true, $locationApi, null, null],
            ['property_gvmc', 'GVMC Zone / Ward', 'Enter GVMC Zone / Ward Number', 'property_location', 'property_location', 70, 'text', false, true, $locationApi, null, null],
            ['property_vmrda', 'VMRDA', 'Enter VMRDA', 'property_location', 'property_location', 80, 'text', false, true, $locationApi, null, null],
            ['property_registration_area', 'Registration Area', 'Enter Register Office Location', 'property_location', 'property_location', 90, 'text', false, true, $locationApi, null, null],
            ['property_authority', 'Authority', 'Enter GVMC / VMRDA', 'property_location', 'property_location', 100, 'text', false, true, $locationApi, null, null],

            // Category
            ['property_category', 'Property Category', 'Select Property Category', 'property_group_and_types', 'property_category', 10, 'select', true, false, $masterApi, null, null],

            // Details
            ['property_project_name', 'Project Name', 'Enter Project / Property Name', 'property_details', 'property_details', 10, 'text', false, false, null, null, null],
            ['property_lp_no', 'LP Number', 'Select LP No / B.P.A No', 'property_details', 'property_details', 20, 'text', false, false, null, null, null],
            ['property_year', 'LP Year', 'Select LP No / B.P.A No Year', 'property_details', 'property_details', 30, 'select', false, false, $masterApi, null, null],
            ['property_total_floors', 'Total Floors', 'Enter Total Floors / Total Plots', 'property_details', 'property_details', 40, 'integer', false, false, null, null, null],
            ['property_block_phase', 'Block / Phase', 'Enter Block No / Phase (1-10)', 'property_details', 'property_details', 50, 'text', false, false, null, null, null],
            ['property_flat_door_no', 'Flat / Door No', 'Enter Plot No / D.No. / Flat No', 'property_details', 'property_details', 60, 'text', false, false, null, null, null],
            ['property_floor_number', 'Floor Number', 'Select Floor No', 'property_details', 'property_details', 70, 'select', false, false, $masterApi, null, null],
            ['property_facing', 'Facing', 'Select Facing', 'property_details', 'property_details', 80, 'select', false, false, $masterApi, null, null],
            ['property_area', 'Area', 'Enter Area', 'property_details', 'property_details', 90, 'number', true, false, null, null, null],
            ['property_area_unit', 'Area Unit', 'Select Area Unit', 'property_details', 'property_details', 100, 'select', true, false, $masterApi, ['field' => 'property_category'], null],
            ['property_price', 'Price', 'Enter Price', 'property_details', 'property_details', 110, 'number', true, false, null, null, null],
            ['property_price_range', 'Price Range', 'Select Price Range', 'property_details', 'property_details', 120, 'select', false, false, $masterApi, null, null],
            ['property_age', 'Property Age', 'Select Property Age', 'property_details', 'property_details', 130, 'select', false, false, $masterApi, null, null],
            ['property_bedrooms', 'Bedrooms', 'Select Bed Room', 'property_details', 'property_details', 140, 'select', false, false, $masterApi, null, null],
            ['property_furnishing', 'Furnishing', 'Select Furnishing', 'property_details', 'property_details', 150, 'select', false, false, $masterApi, null, null],
            ['property_under', 'Property Falls Under', 'Select Property Falls Under', 'property_details', 'property_details', 160, 'select', false, false, $masterApi, null, null],
            ['property_approval_authority', 'Approval Authority', 'Select Approved By', 'property_approval', 'property_details', 170, 'select', false, false, $masterApi, null, null],
            ['property_document_no', 'Document Number', 'Select Document No', 'property_details', 'property_details', 180, 'text', false, false, null, null, null],
            ['property_document_year', 'Document Year', 'Select Document Year', 'property_details', 'property_details', 190, 'select', false, false, $masterApi, null, null],
            ['property_registration_office_area', 'Registration Office Area', 'Enter Registered Office Area', 'property_details', 'property_details', 200, 'text', false, false, null, null, null],

            // Images / Documents
            ['property_images', 'Property Images', 'Drag and drop property photos here, or click to browse', 'property_media', 'property_images', 10, 'file', false, false, null, null, true],
            ['property_documents', 'Property Documents', 'Drag and drop deeds, approvals, or plans here', 'property_media', 'property_documents', 10, 'file', false, false, null, null, true],

            // Owner (kept for admin config; excluded from public form)
            ['property_owner_name', 'Owner Name', 'Enter owner name', 'property_owner', null, null, 'text', false, false, null, null, null],
            ['property_owner_phone', 'Owner Phone', 'Enter owner phone', 'property_owner', null, null, 'text', false, false, null, null, null],

            // Other services
            ['property_youtube_video_link', 'YouTube Video Link', 'Paste YouTube Video Link', 'property_other_services', 'other_services', 10, 'text', false, false, null, null, null],
            ['property_location_link', 'Google Location Link', 'Paste Property Location Link', 'property_other_services', 'other_services', 20, 'text', false, false, null, null, null],

            // Contact numbers repeater
            ['property_contact_numbers', 'Property Contact Numbers', 'Add contact numbers', 'property_owner', 'property_contact_numbers', 10, 'repeater', false, false, $masterApi, null, true],
        ];

        foreach ($publicFields as [
            $key,
            $label,
            $placeholder,
            $adminSection,
            $publicSection,
            $publicOrder,
            $dataType,
            $required,
            $searchable,
            $optionsApi,
            $dependsOn,
            $multiple,
        ]) {
            $exists = DB::table('property_field_configurations')
                ->where('property_field_key', $key)
                ->exists();

            $payload = [
                'property_field_label' => $label,
                'property_field_placeholder' => $placeholder,
                'property_field_section' => $adminSection,
                'property_field_data_type' => $dataType,
                'property_field_is_active' => true,
                'property_field_is_required' => $required,
                'property_field_is_readonly' => false,
                'property_field_is_searchable' => (bool) $searchable,
                'property_field_is_multiple' => (bool) $multiple,
                'property_field_options' => null,
                'property_field_options_api' => $optionsApi,
                'property_field_validation' => $key === 'property_contact_numbers'
                    ? json_encode([
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
                    ], JSON_THROW_ON_ERROR)
                    : null,
                'property_field_default_value' => null,
                'property_field_depends_on' => $dependsOn === null ? null : json_encode($dependsOn, JSON_THROW_ON_ERROR),
                'property_field_public_section' => $publicSection,
                'property_field_public_order' => $publicOrder,
                'property_field_display_order' => $publicOrder,
                'property_field_updated_at' => $now,
            ];

            if ($exists) {
                DB::table('property_field_configurations')
                    ->where('property_field_key', $key)
                    ->update($payload);
            } else {
                DB::table('property_field_configurations')->insert([
                    'property_field_key' => $key,
                    ...$payload,
                    'property_field_created_at' => $now,
                ]);
            }
        }

        // Hide owner email and other-service-name from public config; keep for admin/create compatibility.
        DB::table('property_field_configurations')
            ->whereIn('property_field_key', ['property_owner_email', 'property_other_service_name'])
            ->update([
                'property_field_public_section' => null,
                'property_field_public_order' => null,
                'property_field_updated_at' => $now,
            ]);

        // Hide legacy group/types type fields from public (replaced by property_category).
        DB::table('property_field_configurations')
            ->whereIn('property_field_key', [
                'property_residential_type',
                'property_commercial_type',
                'property_development_type',
                'property_layout_type',
                'property_construction_status',
                'property_construction_type',
                'property_price_per_sqft',
                'property_plot_no',
            ])
            ->update([
                'property_field_public_section' => null,
                'property_field_public_order' => null,
                'property_field_updated_at' => $now,
            ]);
    }
};
