<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class SyncDatabaseFromRemote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sync
                            {--force : Run without asking for confirmation}
                            {--dry-run : Show the sync plan without running SSH or MySQL commands}
                            {--keep-dump : Keep the downloaded SQL dump after import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace the local database with a dump from the configured remote server database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $remote = config('infraread.remote_restore');
        $local = $this->localDatabaseConfig();

        $validationErrors = $this->validateConfiguration($remote, $local);

        if ($validationErrors !== []) {
            foreach ($validationErrors as $error) {
                $this->error($error);
            }

            $this->line('Check your REMOTE_RESTORE_* and DB_* values in .env, then run php artisan config:clear if config is cached.');

            return self::FAILURE;
        }

        $useGzip = filter_var($remote['use_gzip'], FILTER_VALIDATE_BOOL);
        $timestamp = now()->format('Ymd_His');
        $extension = $useGzip ? 'sql.gz' : 'sql';
        $remoteDumpPath = rtrim($remote['remote_tmp_dir'], '/')."/infraread_{$timestamp}.{$extension}";
        $localDumpPath = $this->localDumpPath($remote['local_tmp_dir'], "infraread_{$timestamp}.{$extension}");

        $this->displayPlan($remote, $local, $remoteDumpPath, $localDumpPath);

        if ($this->option('dry-run')) {
            $this->line('Remote dump uses mysqldump --no-tablespaces to avoid requiring global PROCESS privileges.');
            $this->info('Dry run complete. No SSH, dump, download, or import commands were run.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Replace local database [{$local['database']}] with remote database [{$remote['database']['database']}]?", false)) {
            $this->warn('Database sync cancelled.');

            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($localDumpPath));

        try {
            $this->runProcess(
                $this->sshCommand($remote, $this->remoteDumpCommand($remote, $remoteDumpPath, $useGzip)),
                'Creating remote database dump'
            );

            $this->runProcess(
                $this->scpCommand($remote, $remoteDumpPath, $localDumpPath),
                'Downloading database dump'
            );

            $this->runProcess(
                $this->localImportCommand($local, $localDumpPath, $useGzip),
                'Importing dump into local database',
                ['MYSQL_PWD' => (string) $local['password']]
            );

            $this->info('Database sync completed.');

            return self::SUCCESS;
        } finally {
            $this->runCleanup($remote, $remoteDumpPath, $localDumpPath);
        }
    }

    /**
     * @return array{host: ?string, port: int|string|null, database: ?string, username: ?string, password: ?string, driver: ?string}
     */
    protected function localDatabaseConfig(): array
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}", []);

        return [
            'driver' => $config['driver'] ?? null,
            'host' => $config['host'] ?? null,
            'port' => $config['port'] ?? null,
            'database' => $config['database'] ?? null,
            'username' => $config['username'] ?? null,
            'password' => $config['password'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $remote
     * @param  array<string, mixed>  $local
     * @return array<int, string>
     */
    protected function validateConfiguration(array $remote, array $local): array
    {
        $errors = [];

        foreach ([
            'REMOTE_RESTORE_SSH_HOST' => $remote['ssh']['host'] ?? null,
            'REMOTE_RESTORE_SSH_USER' => $remote['ssh']['user'] ?? null,
            'REMOTE_RESTORE_DB_DATABASE' => $remote['database']['database'] ?? null,
            'REMOTE_RESTORE_DB_USER' => $remote['database']['user'] ?? null,
            'REMOTE_RESTORE_DB_PASSWORD' => $remote['database']['password'] ?? null,
            'DB_HOST' => $local['host'] ?? null,
            'DB_DATABASE' => $local['database'] ?? null,
            'DB_USERNAME' => $local['username'] ?? null,
        ] as $name => $value) {
            if ($value === null || $value === '') {
                $errors[] = "{$name} is not configured.";
            }
        }

        if (($local['driver'] ?? null) !== 'mysql') {
            $errors[] = 'db:sync currently supports a local MySQL database only.';
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $remote
     * @param  array<string, mixed>  $local
     */
    protected function displayPlan(array $remote, array $local, string $remoteDumpPath, string $localDumpPath): void
    {
        $this->table(
            ['Step', 'Target'],
            [
                ['Remote SSH', "{$remote['ssh']['user']}@{$remote['ssh']['host']}:{$remote['ssh']['port']}"],
                ['Remote database', "{$remote['database']['user']}@{$remote['database']['host']}:{$remote['database']['port']}/{$remote['database']['database']}"],
                ['Remote dump', $remoteDumpPath],
                ['Local dump', $localDumpPath],
                ['Local database', "{$local['username']}@{$local['host']}:{$local['port']}/{$local['database']}"],
            ]
        );
    }

    protected function localDumpPath(string $configuredPath, string $filename): string
    {
        $basePath = str_starts_with($configuredPath, DIRECTORY_SEPARATOR)
            ? $configuredPath
            : base_path($configuredPath);

        return rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename;
    }

    /**
     * @param  array<string, mixed>  $remote
     * @return array<int, string>
     */
    protected function sshCommand(array $remote, string $remoteCommand): array
    {
        $command = [
            'ssh',
            '-p',
            (string) $remote['ssh']['port'],
        ];

        if (! empty($remote['ssh']['key_path'])) {
            $command[] = '-i';
            $command[] = $this->expandHomePath($remote['ssh']['key_path']);
        }

        $command[] = "{$remote['ssh']['user']}@{$remote['ssh']['host']}";
        $command[] = $remoteCommand;

        return $command;
    }

    /**
     * @param  array<string, mixed>  $remote
     * @return array<int, string>
     */
    protected function scpCommand(array $remote, string $remoteDumpPath, string $localDumpPath): array
    {
        $command = [
            'scp',
            '-P',
            (string) $remote['ssh']['port'],
        ];

        if (! empty($remote['ssh']['key_path'])) {
            $command[] = '-i';
            $command[] = $this->expandHomePath($remote['ssh']['key_path']);
        }

        $command[] = "{$remote['ssh']['user']}@{$remote['ssh']['host']}:{$remoteDumpPath}";
        $command[] = $localDumpPath;

        return $command;
    }

    /**
     * @param  array<string, mixed>  $remote
     */
    protected function remoteDumpCommand(array $remote, string $remoteDumpPath, bool $useGzip): string
    {
        $database = $remote['database'];

        $mysqldump = implode(' ', [
            'MYSQL_PWD='.escapeshellarg((string) $database['password']),
            'mysqldump',
            '--single-transaction',
            '--quick',
            '--add-drop-table',
            '--no-tablespaces',
            '--host='.escapeshellarg((string) $database['host']),
            '--port='.escapeshellarg((string) $database['port']),
            '--user='.escapeshellarg((string) $database['user']),
            escapeshellarg((string) $database['database']),
        ]);

        if ($useGzip) {
            return 'set -e; '.$mysqldump.' | gzip -c > '.escapeshellarg($remoteDumpPath);
        }

        return 'set -e; '.$mysqldump.' > '.escapeshellarg($remoteDumpPath);
    }

    /**
     * @param  array<string, mixed>  $local
     * @return array<int, string>
     */
    protected function localImportCommand(array $local, string $localDumpPath, bool $useGzip): array
    {
        $mysql = implode(' ', [
            'mysql',
            '--host='.escapeshellarg((string) $local['host']),
            '--port='.escapeshellarg((string) $local['port']),
            '--user='.escapeshellarg((string) $local['username']),
            escapeshellarg((string) $local['database']),
        ]);

        $input = $useGzip
            ? 'gzip -dc '.escapeshellarg($localDumpPath)
            : 'cat '.escapeshellarg($localDumpPath);

        return ['sh', '-c', "{$input} | {$mysql}"];
    }

    /**
     * @param  array<int, string>  $command
     * @param  array<string, string>  $environment
     */
    protected function runProcess(array $command, string $label, array $environment = []): void
    {
        $this->line("{$label}...");

        $process = new Process($command, base_path(), $environment, null, 600);
        $process->mustRun(function (string $type, string $buffer): void {
            if ($type === Process::ERR) {
                $this->error(trim($buffer));

                return;
            }

            $this->line(trim($buffer));
        });
    }

    /**
     * @param  array<string, mixed>  $remote
     */
    protected function runCleanup(array $remote, string $remoteDumpPath, string $localDumpPath): void
    {
        try {
            $this->runProcess(
                $this->sshCommand($remote, 'rm -f '.escapeshellarg($remoteDumpPath)),
                'Removing remote dump'
            );
        } catch (\Throwable $exception) {
            $this->warn("Could not remove remote dump: {$exception->getMessage()}");
        }

        if (! $this->option('keep-dump') && File::exists($localDumpPath)) {
            File::delete($localDumpPath);
        }
    }

    protected function expandHomePath(string $path): string
    {
        if (str_starts_with($path, '~/')) {
            return rtrim((string) getenv('HOME'), DIRECTORY_SEPARATOR).substr($path, 1);
        }

        return $path;
    }
}
