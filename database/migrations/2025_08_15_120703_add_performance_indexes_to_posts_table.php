<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexesToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            // Performance indexes for common query patterns

            // For filtering posts by source and reading status
            $table->index(['source_id', 'read'], 'posts_source_read_index');

            // For chronological queries by source
            $table->index(['source_id', 'posted_at'], 'posts_source_date_index');

            // For read/unread queries with date filtering
            $table->index(['read', 'posted_at'], 'posts_read_date_index');

            // Composite index for API filtering (read status + source + category + date)
            $table->index(['read', 'source_id', 'category_id', 'posted_at'], 'posts_api_filter_index');

            // Improve UID uniqueness checks (already unique, but index helps performance)
            // Note: uid already has unique constraint, this just ensures it's indexed efficiently
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            // Remove performance indexes
            $table->dropIndex('posts_source_read_index');
            $table->dropIndex('posts_source_date_index');
            $table->dropIndex('posts_read_date_index');
            $table->dropIndex('posts_api_filter_index');
        });
    }
}
