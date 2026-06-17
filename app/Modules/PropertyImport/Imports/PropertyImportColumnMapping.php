<?php

namespace App\Modules\PropertyImport\Imports;

class PropertyImportColumnMapping
{
    /**
     * Excel header columns mapped to property database columns.
     * Headers in the uploaded file must match these keys exactly.
     *
     * @return list<string>
     */
    public static function columns(): array
    {
        return [
            'property_approval_authority',
            'property_village',
            'property_nearby_location',
            'property_custom_nearby_location',
            'property_district',
            'property_mandal',
            'property_panchayati',
            'property_gvmc',
            'property_vmrda',
            'property_registration_area',
            'property_authority',
            'property_residential_type',
            'property_commercial_type',
            'property_development_type',
            'property_layout_type',
            'property_construction_status',
            'property_construction_type',
            'property_price',
            'property_price_range',
            'property_area',
            'property_area_unit',
            'property_price_per_sqft',
            'property_age',
            'property_facing',
            'property_total_floors',
            'property_floor_number',
            'property_furnishing',
            'property_under',
            'property_lp_no',
            'property_plot_no',
            'property_year',
            'property_bedrooms',
            'property_owner_name',
            'property_owner_phone',
            'property_owner_email',
            'property_other_service_name',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function sampleRow(): array
    {
        return [
            'property_approval_authority' => 'GVMC',
            'property_village' => 'chodavaram',
            'property_nearby_location' => 'Railway Station',
            'property_custom_nearby_location' => 'waterTank',
            'property_district' => 'anakapalli',
            'property_mandal' => 'chodavaram',
            'property_panchayati' => 'chodavaram',
            'property_gvmc' => 'anakapalli',
            'property_vmrda' => 'anakapalli',
            'property_registration_area' => 'near bus stand',
            'property_authority' => 'GVMC',
            'property_residential_type' => 'Plot',
            'property_commercial_type' => 'Industrial Land',
            'property_development_type' => 'Gated Community',
            'property_layout_type' => 'Farm Plots',
            'property_construction_status' => 'Under Construction',
            'property_construction_type' => 'Individual House',
            'property_price' => 2302302,
            'property_price_range' => '16 - 20 Lakhs',
            'property_area' => 1452,
            'property_area_unit' => 'Sq.Yards',
            'property_price_per_sqft' => '1000 - 3000',
            'property_age' => '5-10 Years',
            'property_facing' => 'North-West',
            'property_total_floors' => 6,
            'property_floor_number' => '2nd Floor',
            'property_furnishing' => 'Semi-Furnished',
            'property_under' => 'Government',
            'property_lp_no' => '5',
            'property_plot_no' => '2',
            'property_year' => 2025,
            'property_bedrooms' => 2,
            'property_owner_name' => 'naidu',
            'property_owner_phone' => '7896325410',
            'property_owner_email' => 'sai@gmail.com',
            'property_other_service_name' => 'Encumbrance Certificate (EC)',
        ];
    }

    /**
     * @return list<string>
     */
    public static function allowedExtensions(): array
    {
        return ['xlsx', 'xls', 'csv'];
    }

    /**
     * @return list<string>
     */
    public static function allowedMimeTypes(): array
    {
        return [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv',
            'text/plain',
            'application/csv',
        ];
    }
}
