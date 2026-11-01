<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class PurgeSpamAccounts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:purge-spam
                            {--name=Test : Name pattern to match (exact, case-insensitive)}
                            {--days=7 : Only consider accounts created within this many days}
                            {--dry-run : List matching accounts without deleting}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     */
    protected $description = 'Delete spam accounts matching a name pattern (e.g. mass-created "Test" bots)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name');
        $days = (int) $this->option('days');

        $query = User::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->where('created_at', '>=', now()->subDays($days))
            ->where(function (Builder $q) {
                // Never touch admin or mod accounts, even if name matches.
                $q->whereNull('role')
                  ->orWhereNotIn('role', ['admin', 'mod']);
            });

        $accounts = $query->get(['id', 'name', 'email', 'created_at']);

        if ($accounts->isEmpty()) {
            $this->info('No matching accounts found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Created'],
            $accounts->map(fn ($u) => [$u->id, $u->name, $u->email, $u->created_at->toDateTimeString()])
        );

        $this->newLine();
        $this->warn("Found {$accounts->count()} account(s) matching \"{$name}\" created in the last {$days} day(s).");

        if ($this->option('dry-run')) {
            $this->info('Dry run — no accounts were deleted.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Permanently delete these accounts?')) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        $deleted = $query->delete();

        $this->info("Deleted {$deleted} spam account(s).");

        return self::SUCCESS;
    }
}