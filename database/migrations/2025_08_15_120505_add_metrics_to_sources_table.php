<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetricsToSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sources', function (Blueprint $table) {
            // Performance tracking fields
            $table->timestamp('last_fetched_at')->nullable()
                ->comment('When this source was last successfully fetched');

            $table->integer('last_fetch_duration_ms')->nullable()
                ->comment('How long the last fetch took in milliseconds');

            $table->integer('consecutive_failures')->default(0)
                ->comment('Number of consecutive failed fetch attempts');

            $table->timestamp('last_error_at')->nullable()
                ->comment('When the last error occurred');

            $table->text('last_error_message')->nullable()
                ->comment('Details of the last error for debugging');

            $table->enum('status', ['active', 'warning', 'failed'])->default('active')
                ->comment('Health status: active=working, warning=some issues, failed=broken');

            // Add indexes for common queries
            $table->index('status', 'sources_status_index');
            $table->index('last_fetched_at', 'sources_last_fetched_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sources', function (Blueprint $table) {
            // Remove indexes first (Laravel requirement)
            $table->dropIndex('sources_status_index');
            $table->dropIndex('sources_last_fetched_index');

            // Remove the metrics columns
            $table->dropColumn([
                'last_fetched_at',
                'last_fetch_duration_ms',
                'consecutive_failures',
                'last_error_at',
                'last_error_message',
                'status'
            ]);
        });
    }
}
