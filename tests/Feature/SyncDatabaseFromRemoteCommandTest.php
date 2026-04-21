<?php

namespace Tests\Feature;

use Tests\TestCase;

class SyncDatabaseFromRemoteCommandTest extends TestCase
{
    public function test_command_fails_when_remote_restore_configuration_is_missing(): void
    {
        config([
            'infraread.remote_restore.ssh.host' => null,
            'infraread.remote_restore.ssh.user' => null,
            'infraread.remote_restore.database.database' => null,
            'infraread.remote_restore.database.user' => null,
            'infraread.remote_restore.database.password' => null,
            'database.default' => 'mysql',
            'database.connections.mysql.driver' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.database' => 'infraread',
            'database.connections.mysql.username' => 'infraread',
        ]);

        $this->artisan('db:sync --dry-run')
            ->expectsOutput('REMOTE_RESTORE_SSH_HOST is not configured.')
            ->expectsOutput('REMOTE_RESTORE_SSH_USER is not configured.')
            ->expectsOutput('REMOTE_RESTORE_DB_DATABASE is not configured.')
            ->expectsOutput('REMOTE_RESTORE_DB_USER is not configured.')
            ->expectsOutput('REMOTE_RESTORE_DB_PASSWORD is not configured.')
            ->assertExitCode(1);
    }

    public function test_dry_run_displays_plan_without_running_external_commands(): void
    {
        config([
            'infraread.remote_restore' => [
                'ssh' => [
                    'host' => '64.225.77.139',
                    'port' => 22,
                    'user' => 'stayyef',
                    'key_path' => '~/.ssh/id_ed25519',
                ],
                'database' => [
                    'host' => '127.0.0.1',
                    'port' => 3306,
                    'database' => 'infraread',
                    'user' => 'infraread',
                    'password' => 'secret',
                ],
                'remote_tmp_dir' => '/tmp',
                'local_tmp_dir' => 'storage/app/restores',
                'use_gzip' => true,
            ],
            'database.default' => 'mysql',
            'database.connections.mysql.driver' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.database' => 'infraread',
            'database.connections.mysql.username' => 'infraread',
            'database.connections.mysql.password' => 'local-secret',
        ]);

        $this->artisan('db:sync --dry-run')
            ->expectsOutputToContain('stayyef@64.225.77.139:22')
            ->expectsOutputToContain('infraread@127.0.0.1:3306/infraread')
            ->expectsOutputToContain('/tmp/infraread_')
            ->expectsOutputToContain('storage/app/restores/infraread_')
            ->expectsOutput('Remote dump uses mysqldump --no-tablespaces to avoid requiring global PROCESS privileges.')
            ->expectsOutput('Dry run complete. No SSH, dump, download, or import commands were run.')
            ->assertExitCode(0);
    }
}
