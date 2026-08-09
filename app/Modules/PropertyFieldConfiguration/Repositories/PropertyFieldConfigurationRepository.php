<?php

namespace App\Modules\PropertyFieldConfiguration\Repositories;

use App\Modules\PropertyFieldConfiguration\Models\MasterDropdown;
use App\Modules\PropertyFieldConfiguration\Models\PropertyCategoryAreaUnit;
use App\Modules\PropertyFieldConfiguration\Models\PropertyFieldConfiguration;
use Illuminate\Database\Eloquent\Collection;

class PropertyFieldConfigurationRepository
{
    /**
     * @return Collection<int, PropertyFieldConfiguration>
     */
    public function allOrdered(): Collection
    {
        return PropertyFieldConfiguration::query()
            ->orderBy('property_field_section')
            ->orderBy('property_field_display_order')
            ->orderBy('property_field_configuration_id')
            ->get();
    }

    /**
     * @return Collection<int, PropertyFieldConfiguration>
     */
    public function activeOrdered(): Collection
    {
        return PropertyFieldConfiguration::query()
            ->where('property_field_is_active', true)
            ->orderBy('property_field_section')
            ->orderBy('property_field_display_order')
            ->orderBy('property_field_configuration_id')
            ->get();
    }

    /**
     * @return Collection<int, PropertyFieldConfiguration>
     */
    public function activePublicFormFields(): Collection
    {
        return PropertyFieldConfiguration::query()
            ->where('property_field_is_active', true)
            ->whereNotNull('property_field_public_section')
            ->orderBy('property_field_public_order')
            ->orderBy('property_field_configuration_id')
            ->get();
    }

    public function findById(int $propertyFieldConfigurationId): ?PropertyFieldConfiguration
    {
        return PropertyFieldConfiguration::query()
            ->where('property_field_configuration_id', $propertyFieldConfigurationId)
            ->first();
    }

    public function findByKey(string $propertyFieldKey): ?PropertyFieldConfiguration
    {
        return PropertyFieldConfiguration::query()
            ->where('property_field_key', $propertyFieldKey)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PropertyFieldConfiguration
    {
        return PropertyFieldConfiguration::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(PropertyFieldConfiguration $configuration, array $attributes): PropertyFieldConfiguration
    {
        $configuration->update($attributes);

        return $configuration->fresh();
    }

    public function deactivate(PropertyFieldConfiguration $configuration): PropertyFieldConfiguration
    {
        $configuration->update(['property_field_is_active' => false]);

        return $configuration->fresh();
    }

    /**
     * @return Collection<int, MasterDropdown>
     */
    public function activeMasterDropdownsWithOptions(): Collection
    {
        return MasterDropdown::query()
            ->where('master_dropdown_is_active', true)
            ->with(['options' => function ($query): void {
                $query->where('master_dropdown_option_is_active', true)
                    ->orderBy('master_dropdown_option_display_order')
                    ->orderBy('master_dropdown_option_id');
            }])
            ->orderBy('master_dropdown_display_order')
            ->orderBy('master_dropdown_id')
            ->get();
    }

    /**
     * @return Collection<int, PropertyCategoryAreaUnit>
     */
    public function activeCategoryAreaUnits(): Collection
    {
        return PropertyCategoryAreaUnit::query()
            ->where('property_category_area_unit_is_active', true)
            ->orderBy('property_category_value')
            ->orderBy('property_category_area_unit_display_order')
            ->orderBy('property_category_area_unit_id')
            ->get();
    }
}
