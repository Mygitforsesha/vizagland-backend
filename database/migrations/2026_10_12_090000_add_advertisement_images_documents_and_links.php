<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('advertisements')) {
            Schema::table('advertisements', function (Blueprint $table): void {
                if (! Schema::hasColumn('advertisements', 'advertisement_youtube_video_links')) {
                    $table->json('advertisement_youtube_video_links')->nullable();
                }

                if (! Schema::hasColumn('advertisements', 'advertisement_location_links')) {
                    $table->json('advertisement_location_links')->nullable();
                }
            });
        }

        if (! Schema::hasTable('advertisement_images')) {
            Schema::create('advertisement_images', function (Blueprint $table): void {
                $table->id('advertisement_image_id');
                $table->foreignId('advertisement_id')->constrained('advertisements', 'advertisement_id')->cascadeOnDelete();
                $table->string('advertisement_image_path');
                $table->string('advertisement_image_original_name')->nullable();
                $table->string('advertisement_image_mime_type')->nullable();
                $table->unsignedBigInteger('advertisement_image_size')->nullable();
                $table->unsignedSmallInteger('advertisement_image_sort_order')->default(0);
                $table->timestamps();

                $table->index('advertisement_id', 'ad_images_ad_idx');
            });
        }

        if (! Schema::hasTable('advertisement_documents')) {
            Schema::create('advertisement_documents', function (Blueprint $table): void {
                $table->id('advertisement_document_id');
                $table->foreignId('advertisement_id')->constrained('advertisements', 'advertisement_id')->cascadeOnDelete();
                $table->string('advertisement_document_path');
                $table->string('advertisement_document_original_name')->nullable();
                $table->string('advertisement_document_mime_type')->nullable();
                $table->unsignedBigInteger('advertisement_document_size')->nullable();
                $table->timestamps();

                $table->index('advertisement_id', 'ad_documents_ad_idx');
            });
        }

        if (! Schema::hasTable('advertisements') || ! Schema::hasColumn('advertisements', 'advertisement_image_path')) {
            return;
        }

        $now = now();

        DB::table('advertisements')
            ->select(['advertisement_id', 'advertisement_image_path'])
            ->whereNotNull('advertisement_image_path')
            ->where('advertisement_image_path', '!=', '')
            ->orderBy('advertisement_id')
            ->chunkById(200, function ($advertisements) use ($now): void {
                $rows = [];

                foreach ($advertisements as $advertisement) {
                    $alreadyCopied = DB::table('advertisement_images')
                        ->where('advertisement_id', $advertisement->advertisement_id)
                        ->exists();

                    if ($alreadyCopied) {
                        continue;
                    }

                    $rows[] = [
                        'advertisement_id' => $advertisement->advertisement_id,
                        'advertisement_image_path' => $advertisement->advertisement_image_path,
                        'advertisement_image_original_name' => null,
                        'advertisement_image_mime_type' => null,
                        'advertisement_image_size' => null,
                        'advertisement_image_sort_order' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('advertisement_images')->insert($rows);
                }
            }, 'advertisement_id');
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_documents');
        Schema::dropIfExists('advertisement_images');

        if (Schema::hasTable('advertisements')) {
            Schema::table('advertisements', function (Blueprint $table): void {
                $columns = [];

                if (Schema::hasColumn('advertisements', 'advertisement_youtube_video_links')) {
                    $columns[] = 'advertisement_youtube_video_links';
                }

                if (Schema::hasColumn('advertisements', 'advertisement_location_links')) {
                    $columns[] = 'advertisement_location_links';
                }

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
