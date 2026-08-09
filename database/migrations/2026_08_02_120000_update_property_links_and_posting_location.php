<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'property_youtube_video_links')) {
                $table->json('property_youtube_video_links')->nullable()->after('property_youtube_video_link');
            }

            if (! Schema::hasColumn('properties', 'property_location_links')) {
                $table->json('property_location_links')->nullable()->after('property_location_link');
            }

            if (! Schema::hasColumn('properties', 'property_posting_location')) {
                $table->json('property_posting_location')->nullable()->after('property_location_links');
            }
        });

        $this->widenLegacyLinkColumns();
        $this->updateFormFieldConfigurations();
    }

    public function down(): void
    {
        $now = now();

        $this->revertFormFieldConfigurations($now);

        Schema::table('properties', function (Blueprint $table): void {
            foreach (['property_posting_location', 'property_location_links', 'property_youtube_video_links'] as $column) {
                if (Schema::hasColumn('properties', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function widenLegacyLinkColumns(): void
    {
        // Validation allows URLs up to 2048 chars; legacy columns were varchar(255).
        // SQLite used in tests treats string affinity loosely, so skip ALTER there.
        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        if (Schema::hasColumn('properties', 'property_youtube_video_link')) {
            DB::statement('ALTER TABLE properties MODIFY property_youtube_video_link TEXT NULL');
        }

        if (Schema::hasColumn('properties', 'property_location_link')) {
            DB::statement('ALTER TABLE properties MODIFY property_location_link TEXT NULL');
        }
    }

    private function updateFormFieldConfigurations(): void
    {
        $now = now();

        $this->upsertLinkRepeaterField(
            oldKey: 'property_youtube_video_link',
            newKey: 'property_youtube_video_links',
            label: 'YouTube Video Links',
            placeholder: 'Paste YouTube Video Link',
            urlLabel: 'YouTube URL',
            order: 10,
            now: $now,
        );

        $this->upsertLinkRepeaterField(
            oldKey: 'property_location_link',
            newKey: 'property_location_links',
            label: 'Google Location Links',
            placeholder: 'Paste Property Location Link',
            urlLabel: 'Location URL',
            order: 20,
            now: $now,
        );

        // Keep other-service-name out of the public form; accept optionally on create for BC.
        DB::table('property_field_configurations')
            ->where('property_field_key', 'property_other_service_name')
            ->update([
                'property_field_public_section' => null,
                'property_field_public_order' => null,
                'property_field_is_required' => false,
                'property_field_updated_at' => $now,
            ]);
    }

    private function upsertLinkRepeaterField(
        string $oldKey,
        string $newKey,
        string $label,
        string $placeholder,
        string $urlLabel,
        int $order,
        mixed $now,
    ): void {
        $validation = json_encode([
            'fields' => [
                [
                    'key' => 'url',
                    'label' => $urlLabel,
                    'placeholder' => $placeholder,
                    'type' => 'text',
                    'required' => false,
                ],
            ],
            'unlimited' => true,
        ], JSON_THROW_ON_ERROR);

        $payload = [
            'property_field_label' => $label,
            'property_field_placeholder' => $placeholder,
            'property_field_section' => 'property_other_services',
            'property_field_data_type' => 'repeater',
            'property_field_is_active' => true,
            'property_field_is_required' => false,
            'property_field_is_readonly' => false,
            'property_field_is_searchable' => false,
            'property_field_is_multiple' => true,
            'property_field_options' => null,
            'property_field_options_api' => null,
            'property_field_validation' => $validation,
            'property_field_default_value' => null,
            'property_field_depends_on' => null,
            'property_field_public_section' => 'other_services',
            'property_field_public_order' => $order,
            'property_field_display_order' => $order,
            'property_field_updated_at' => $now,
        ];

        $existingNew = DB::table('property_field_configurations')
            ->where('property_field_key', $newKey)
            ->exists();

        if ($existingNew) {
            DB::table('property_field_configurations')
                ->where('property_field_key', $newKey)
                ->update($payload);

            DB::table('property_field_configurations')
                ->where('property_field_key', $oldKey)
                ->delete();

            return;
        }

        $updated = DB::table('property_field_configurations')
            ->where('property_field_key', $oldKey)
            ->update([
                'property_field_key' => $newKey,
                ...$payload,
            ]);

        if ($updated === 0) {
            DB::table('property_field_configurations')->insert([
                'property_field_key' => $newKey,
                ...$payload,
                'property_field_created_at' => $now,
            ]);
        }
    }

    private function revertFormFieldConfigurations(mixed $now): void
    {
        foreach ([
            'property_youtube_video_links' => ['property_youtube_video_link', 'YouTube Video Link', 'Paste YouTube Video Link', 10],
            'property_location_links' => ['property_location_link', 'Google Location Link', 'Paste Property Location Link', 20],
        ] as $newKey => [$oldKey, $label, $placeholder, $order]) {
            DB::table('property_field_configurations')
                ->where('property_field_key', $newKey)
                ->update([
                    'property_field_key' => $oldKey,
                    'property_field_label' => $label,
                    'property_field_placeholder' => $placeholder,
                    'property_field_data_type' => 'text',
                    'property_field_is_multiple' => false,
                    'property_field_validation' => null,
                    'property_field_public_section' => 'other_services',
                    'property_field_public_order' => $order,
                    'property_field_display_order' => $order,
                    'property_field_updated_at' => $now,
                ]);
        }
    }
};
