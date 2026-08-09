<?php

namespace App\Modules\PropertyFieldConfiguration\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\PropertyFieldConfiguration\Enums\PropertyFieldDataType;
use App\Modules\PropertyFieldConfiguration\Enums\PropertyFieldSection;
use App\Modules\PropertyFieldConfiguration\Models\PropertyFieldConfiguration;
use App\Modules\PropertyFieldConfiguration\Repositories\PropertyFieldConfigurationRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PropertyFieldConfigurationService
{
    /**
     * @var list<array{key: string, label: string, order: int}>
     */
    // private const PUBLIC_SECTIONS = [
    //     ['key' => 'property_location', 'label' => 'Property Location', 'order' => 10],
    //     ['key' => 'property_category', 'label' => 'Property Category', 'order' => 20],
    //     ['key' => 'property_details', 'label' => 'Property Details', 'order' => 30],
    //     ['key' => 'property_images', 'label' => 'Property Images', 'order' => 40],
    //     ['key' => 'property_documents', 'label' => 'Property Documents', 'order' => 50],
    //     ['key' => 'owner_details', 'label' => 'Owner Details', 'order' => 60],
    //     ['key' => 'other_services', 'label' => 'Other Services', 'order' => 70],
    //     ['key' => 'property_contact_numbers', 'label' => 'Property Contact Numbers', 'order' => 80],
    // ];
private const PUBLIC_SECTIONS = [
    ['key' => 'property_location', 'label' => 'Property Location', 'order' => 10],
    ['key' => 'property_category', 'label' => 'Property Category', 'order' => 20],
    ['key' => 'property_details', 'label' => 'Property Details', 'order' => 30],
    ['key' => 'owner_details', 'label' => 'Owner Details', 'order' => 40],
    ['key' => 'other_services', 'label' => 'Other Services', 'order' => 50],
    ['key' => 'property_contact_numbers', 'label' => 'Property Contact Numbers', 'order' => 60],
    ['key' => 'property_images', 'label' => 'Property Images', 'order' => 70],
    ['key' => 'property_documents', 'label' => 'Property Documents', 'order' => 80],
];
    /**
     * @var Collection<int, PropertyFieldConfiguration>|null
     */
    private ?Collection $cachedConfigurations = null;

    public function __construct(
        private readonly PropertyFieldConfigurationRepository $propertyFieldConfigurationRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * @return Collection<int, PropertyFieldConfiguration>
     */
    public function listAll(): Collection
    {
        return $this->propertyFieldConfigurationRepository->allOrdered();
    }

    public function show(int $propertyFieldConfigurationId): PropertyFieldConfiguration
    {
        $configuration = $this->propertyFieldConfigurationRepository->findById($propertyFieldConfigurationId);

        if ($configuration === null) {
            throw (new ModelNotFoundException)->setModel(
                PropertyFieldConfiguration::class,
                [$propertyFieldConfigurationId],
            );
        }

        return $configuration;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PropertyFieldConfiguration
    {
        $attributes = $this->applyPublicSectionMapping($attributes);

        $configuration = $this->propertyFieldConfigurationRepository->create($attributes);
        $this->resetCache();

        $this->activityLogService->log(
            type: ActivityLogType::System,
            action: 'form_field_added',
            description: "Form field added: {$configuration->property_field_label} ({$configuration->property_field_key})",
            entityType: 'property_field_configuration',
            entityId: $configuration->property_field_configuration_id,
            metadata: ['property_field_key' => $configuration->property_field_key],
        );

        return $configuration;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(int $propertyFieldConfigurationId, array $attributes): PropertyFieldConfiguration
    {
        $configuration = $this->show($propertyFieldConfigurationId);
        $attributes = $this->applyPublicSectionMapping($attributes, $configuration);
        $updatedConfiguration = $this->propertyFieldConfigurationRepository->update($configuration, $attributes);
        $this->resetCache();

        return $updatedConfiguration;
    }

    public function deactivate(int $propertyFieldConfigurationId): PropertyFieldConfiguration
    {
        $configuration = $this->show($propertyFieldConfigurationId);
        $deactivatedConfiguration = $this->propertyFieldConfigurationRepository->deactivate($configuration);
        $this->resetCache();

        $this->activityLogService->log(
            type: ActivityLogType::System,
            action: 'form_field_disabled',
            description: "Form field disabled: {$deactivatedConfiguration->property_field_label} ({$deactivatedConfiguration->property_field_key})",
            entityType: 'property_field_configuration',
            entityId: $deactivatedConfiguration->property_field_configuration_id,
            metadata: ['property_field_key' => $deactivatedConfiguration->property_field_key],
        );

        return $deactivatedConfiguration;
    }

    /**
     * @return array{sections: list<array<string, mixed>>}
     */
    public function publicFormConfig(): array
    {
        $fieldsBySection = [];

        foreach ($this->propertyFieldConfigurationRepository->activePublicFormFields() as $configuration) {
            $sectionKey = $configuration->property_field_public_section;

            if ($sectionKey === null || $sectionKey === '') {
                continue;
            }

            $fieldsBySection[$sectionKey][] = $this->mapPublicField($configuration);
        }

        $sections = [];

        foreach (self::PUBLIC_SECTIONS as $section) {
            $sections[] = [
                'key' => $section['key'],
                'label' => $section['label'],
                'order' => $section['order'],
                'fields' => $fieldsBySection[$section['key']] ?? [],
            ];
        }

        return ['sections' => $sections];
    }

    /**
     * @return array<string, mixed>
     */
    public function publicMasterDropdowns(): array
    {
        $dropdowns = [];

        foreach ($this->propertyFieldConfigurationRepository->activeMasterDropdownsWithOptions() as $dropdown) {
            $dropdowns[$dropdown->master_dropdown_key] = $dropdown->options
                ->map(static fn ($option): array => [
                    'value' => $option->master_dropdown_option_value,
                    'label' => $option->master_dropdown_option_label,
                    'order' => $option->master_dropdown_option_display_order,
                ])
                ->values()
                ->all();
        }

        $categoryAreaUnits = [];

        foreach ($this->propertyFieldConfigurationRepository->activeCategoryAreaUnits() as $mapping) {
            $categoryAreaUnits[$mapping->property_category_value][] = [
                'value' => $mapping->property_area_unit_value,
                'label' => $mapping->property_area_unit_label,
                'order' => $mapping->property_category_area_unit_display_order,
            ];
        }

        $dropdowns['category_area_units'] = $categoryAreaUnits;

        return $dropdowns;
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    public function applyFieldConfigurationToValidationRules(array $rules): array
    {
        foreach ($this->configurations() as $configuration) {
            $validationPath = $this->validationPathFor($configuration);

            if (! $configuration->property_field_is_active) {
                // Soft-ignore: do not reject payload keys for inactive fields.
                // Values are stripped later by filterInactiveFieldAttributes().
                continue;
            }

            if (! $configuration->property_field_is_required || ! array_key_exists($validationPath, $rules)) {
                continue;
            }

            $existingRules = (array) $rules[$validationPath];
            $filteredRules = array_values(array_filter(
                $existingRules,
                static fn (mixed $rule): bool => $rule !== 'nullable',
            ));

            if (! in_array('required', $filteredRules, true)) {
                array_unshift($filteredRules, 'required');
            }

            $rules[$validationPath] = $filteredRules;
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function filterInactiveFieldAttributes(array $attributes): array
    {
        $inactiveKeys = $this->configurations()
            ->filter(static fn (PropertyFieldConfiguration $configuration): bool => ! $configuration->property_field_is_active)
            ->pluck('property_field_key')
            ->all();

        if ($inactiveKeys === []) {
            return $attributes;
        }

        return array_diff_key($attributes, array_flip($inactiveKeys));
    }

    public function validationPathFor(PropertyFieldConfiguration $configuration): string
    {
        if ($configuration->property_field_section === PropertyFieldSection::PropertyMedia->value) {
            return $configuration->property_field_key;
        }

        if (
            $configuration->property_field_section === PropertyFieldSection::PropertyOtherServices->value
            && $configuration->property_field_key === 'property_other_service_name'
        ) {
            return 'property_other_services.property_service_name';
        }

        if (in_array($configuration->property_field_key, ['property_images', 'property_documents', 'property_contact_numbers'], true)) {
            return $configuration->property_field_key;
        }

        return $configuration->property_field_section.'.'.$configuration->property_field_key;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPublicField(PropertyFieldConfiguration $configuration): array
    {
        return [
            'id' => $configuration->property_field_configuration_id,
            'key' => $configuration->property_field_key,
            'label' => $configuration->property_field_label,
            'placeholder' => $configuration->property_field_placeholder,
            'section' => $configuration->property_field_public_section,
            'order' => $configuration->property_field_public_order ?? $configuration->property_field_display_order,
            'type' => in_array($configuration->property_field_key, [
                'property_contact_numbers',
                'property_youtube_video_links',
                'property_location_links',
            ], true) || $configuration->property_field_data_type === PropertyFieldDataType::Repeater
                ? 'repeater'
                : $configuration->property_field_data_type->value,
            'required' => $configuration->property_field_is_required,
            'active' => $configuration->property_field_is_active,
            'readonly' => (bool) $configuration->property_field_is_readonly,
            'searchable' => (bool) $configuration->property_field_is_searchable,
            'multiple' => (bool) $configuration->property_field_is_multiple,
            'options' => $configuration->property_field_options,
            'options_api' => $configuration->property_field_options_api,
            'validation' => $configuration->property_field_validation,
            'default_value' => $configuration->property_field_default_value,
            'depends_on' => $configuration->property_field_depends_on,
        ];
    }

    /**
     * Map admin section → public section so new/updated fields appear in public form-config
     * without changing the public-section gate.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function applyPublicSectionMapping(
        array $attributes,
        ?PropertyFieldConfiguration $existing = null,
    ): array {
        $fieldKey = (string) ($attributes['property_field_key'] ?? $existing?->property_field_key ?? '');
        $adminSection = $attributes['property_field_section'] ?? $existing?->property_field_section;

        $shouldMapSection = $existing === null
            || array_key_exists('property_field_section', $attributes)
            || array_key_exists('property_field_key', $attributes);

        if ($shouldMapSection && is_string($adminSection) && $adminSection !== '') {
            $publicSection = $this->resolvePublicSection($adminSection, $fieldKey);
            $attributes['property_field_public_section'] = $publicSection;
            $attributes['property_field_public_order'] = $publicSection === null
                ? null
                : (int) ($attributes['property_field_display_order']
                    ?? $existing?->property_field_display_order
                    ?? 0);
        } elseif (
            array_key_exists('property_field_display_order', $attributes)
            && ($attributes['property_field_public_section'] ?? $existing?->property_field_public_section) !== null
        ) {
            $attributes['property_field_public_order'] = (int) $attributes['property_field_display_order'];
        }

        return $attributes;
    }

    private function resolvePublicSection(string $adminSection, string $fieldKey): ?string
    {
        if (in_array($fieldKey, ['property_owner_name', 'property_owner_phone'], true)) {
            return null;
        }

        return match ($fieldKey) {
            'property_images' => 'property_images',
            'property_documents' => 'property_documents',
            'property_contact_numbers' => 'property_contact_numbers',
            default => match ($adminSection) {
                PropertyFieldSection::PropertyLocation->value => 'property_location',
                PropertyFieldSection::PropertyGroupAndTypes->value => 'property_category',
                PropertyFieldSection::PropertyDetails->value,
                PropertyFieldSection::PropertyApproval->value => 'property_details',
                PropertyFieldSection::PropertyOwner->value => 'owner_details',
                PropertyFieldSection::PropertyOtherServices->value => 'other_services',
                PropertyFieldSection::PropertyMedia->value => 'property_images',
                default => null,
            },
        };
    }

    /**
     * @return Collection<int, PropertyFieldConfiguration>
     */
    private function configurations(): Collection
    {
        return $this->cachedConfigurations ??= $this->propertyFieldConfigurationRepository->allOrdered();
    }

    private function resetCache(): void
    {
        $this->cachedConfigurations = null;
    }
}
