<?php

namespace App\Modules\PropertyFieldConfiguration\Services;

use App\Modules\ActivityLog\Enums\ActivityLogType;
use App\Modules\ActivityLog\Services\ActivityLogService;
use App\Modules\PropertyFieldConfiguration\Enums\PropertyFieldSection;
use App\Modules\PropertyFieldConfiguration\Models\PropertyFieldConfiguration;
use App\Modules\PropertyFieldConfiguration\Repositories\PropertyFieldConfigurationRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PropertyFieldConfigurationService
{
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
     * @return array<string, list<array{
     *     property_field_key: string,
     *     property_field_label: string,
     *     property_field_data_type: string,
     *     property_field_is_required: bool
     * }>>
     */
    public function publicFormConfig(): array
    {
        $grouped = [];

        foreach ($this->propertyFieldConfigurationRepository->activeOrdered() as $configuration) {
            $section = $configuration->property_field_section;

            $grouped[$section][] = [
                'property_field_key' => $configuration->property_field_key,
                'property_field_label' => $configuration->property_field_label,
                'property_field_data_type' => $configuration->property_field_data_type->value,
                'property_field_is_required' => $configuration->property_field_is_required,
            ];
        }

        return $grouped;
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
                $rules[$validationPath] = ['prohibited'];

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

        return $configuration->property_field_section.'.'.$configuration->property_field_key;
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
