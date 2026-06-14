<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->refactorPropertiesTable();
        $this->refactorPropertyImagesTable();
        $this->refactorPropertyDocumentsTable();
    }

    public function down(): void
    {
        $this->revertPropertyDocumentsTable();
        $this->revertPropertyImagesTable();
        $this->revertPropertiesTable();
    }

    private function refactorPropertiesTable(): void
    {
        if (! Schema::hasTable('properties')) {
            return;
        }

        if (Schema::hasColumn('properties', 'property_code')) {
            DB::statement('ALTER TABLE properties CHANGE property_code property_reference_id VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('properties', 'property_lp_number')) {
            DB::statement('ALTER TABLE properties CHANGE property_lp_number property_lp_no VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('properties', 'property_plot_number')) {
            DB::statement('ALTER TABLE properties CHANGE property_plot_number property_plot_no VARCHAR(255) NULL');
        }

        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'property_approval_authority')) {
                $table->string('property_approval_authority')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_village')) {
                $table->string('property_village')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_nearby_location')) {
                $table->string('property_nearby_location')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_custom_nearby_location')) {
                $table->string('property_custom_nearby_location')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_district')) {
                $table->string('property_district')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_mandal')) {
                $table->string('property_mandal')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_panchayati')) {
                $table->string('property_panchayati')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_gvmc')) {
                $table->string('property_gvmc')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_vmrda')) {
                $table->string('property_vmrda')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_registration_area')) {
                $table->string('property_registration_area')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_authority')) {
                $table->string('property_authority')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_residential_type')) {
                $table->string('property_residential_type')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_commercial_type')) {
                $table->string('property_commercial_type')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_development_type')) {
                $table->string('property_development_type')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_layout_type')) {
                $table->string('property_layout_type')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_construction_status')) {
                $table->string('property_construction_status')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_construction_type')) {
                $table->string('property_construction_type')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_price_range')) {
                $table->string('property_price_range')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_price_per_sqft')) {
                $table->string('property_price_per_sqft')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_age')) {
                $table->string('property_age')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_facing')) {
                $table->string('property_facing')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_total_floors')) {
                $table->unsignedSmallInteger('property_total_floors')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_floor_number')) {
                $table->string('property_floor_number')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_furnishing')) {
                $table->string('property_furnishing')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_under')) {
                $table->string('property_under')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_owner_name')) {
                $table->string('property_owner_name')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_owner_phone')) {
                $table->string('property_owner_phone', 20)->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_owner_email')) {
                $table->string('property_owner_email')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_verified')) {
                $table->boolean('property_verified')->default(false);
            }

            if (! Schema::hasColumn('properties', 'property_submitted_at')) {
                $table->timestamp('property_submitted_at')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_view_count') && ! Schema::hasColumn('properties', 'property_views')) {
                $table->unsignedInteger('property_view_count')->default(0);
            }

            if (! Schema::hasColumn('properties', 'property_lead_count') && ! Schema::hasColumn('properties', 'property_leads')) {
                $table->unsignedInteger('property_lead_count')->default(0);
            }

            if (! Schema::hasColumn('properties', 'property_approved_at')) {
                $table->timestamp('property_approved_at')->nullable();
            }

            if (! Schema::hasColumn('properties', 'property_approved_by_user_id')) {
                $table->unsignedBigInteger('property_approved_by_user_id')->nullable();
            }
        });

        // property_reference_id is shared between original and vizagland_copy records.
    }

    private function revertPropertiesTable(): void
    {
        if (! Schema::hasTable('properties')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table): void {
            $columns = [
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
                'property_price_range',
                'property_price_per_sqft',
                'property_age',
                'property_facing',
                'property_total_floors',
                'property_floor_number',
                'property_furnishing',
                'property_under',
                'property_owner_name',
                'property_owner_phone',
                'property_owner_email',
                'property_verified',
                'property_submitted_at',
                'property_view_count',
                'property_lead_count',
                'property_approved_at',
                'property_approved_by_user_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('properties', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasColumn('properties', 'property_reference_id')) {
            DB::statement('ALTER TABLE properties CHANGE property_reference_id property_code VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('properties', 'property_lp_no')) {
            DB::statement('ALTER TABLE properties CHANGE property_lp_no property_lp_number VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('properties', 'property_plot_no')) {
            DB::statement('ALTER TABLE properties CHANGE property_plot_no property_plot_number VARCHAR(255) NULL');
        }
    }

    private function refactorPropertyImagesTable(): void
    {
        if (! Schema::hasTable('property_images')) {
            return;
        }

        if (Schema::hasColumn('property_images', 'property_image_name')) {
            DB::statement('ALTER TABLE property_images CHANGE property_image_name property_image_original_name VARCHAR(255) NOT NULL');
        }

        if (! Schema::hasColumn('property_images', 'property_image_mime_type')) {
            Schema::table('property_images', function (Blueprint $table): void {
                $table->string('property_image_mime_type')->nullable()->after('property_image_size');
            });
        }
    }

    private function revertPropertyImagesTable(): void
    {
        if (! Schema::hasTable('property_images')) {
            return;
        }

        if (Schema::hasColumn('property_images', 'property_image_mime_type')) {
            Schema::table('property_images', function (Blueprint $table): void {
                $table->dropColumn('property_image_mime_type');
            });
        }

        if (Schema::hasColumn('property_images', 'property_image_original_name')) {
            DB::statement('ALTER TABLE property_images CHANGE property_image_original_name property_image_name VARCHAR(255) NOT NULL');
        }
    }

    private function refactorPropertyDocumentsTable(): void
    {
        if (! Schema::hasTable('property_documents')) {
            return;
        }

        if (Schema::hasColumn('property_documents', 'property_document_type')) {
            Schema::table('property_documents', function (Blueprint $table): void {
                $table->dropIndex(['property_document_type']);
            });
        }

        if (Schema::hasColumn('property_documents', 'property_document_name')) {
            DB::statement('ALTER TABLE property_documents CHANGE property_document_name property_document_original_name VARCHAR(255) NOT NULL');
        }

        if (Schema::hasColumn('property_documents', 'property_document_type')) {
            Schema::table('property_documents', function (Blueprint $table): void {
                $table->dropColumn('property_document_type');
            });
        }

        if (! Schema::hasColumn('property_documents', 'property_document_mime_type')) {
            Schema::table('property_documents', function (Blueprint $table): void {
                $table->string('property_document_mime_type')->nullable()->after('property_document_size');
            });
        }
    }

    private function revertPropertyDocumentsTable(): void
    {
        if (! Schema::hasTable('property_documents')) {
            return;
        }

        if (Schema::hasColumn('property_documents', 'property_document_mime_type')) {
            Schema::table('property_documents', function (Blueprint $table): void {
                $table->dropColumn('property_document_mime_type');
            });
        }

        if (! Schema::hasColumn('property_documents', 'property_document_type')) {
            Schema::table('property_documents', function (Blueprint $table): void {
                $table->string('property_document_type')->nullable();
            });
        }

        if (Schema::hasColumn('property_documents', 'property_document_original_name')) {
            DB::statement('ALTER TABLE property_documents CHANGE property_document_original_name property_document_name VARCHAR(255) NOT NULL');
        }
    }
};
