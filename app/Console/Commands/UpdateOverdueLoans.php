<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use Carbon\Carbon;

class UpdateOverdueLoans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:update-overdue {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update overdue loan status and calculate fines automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Starting overdue loans update...');
        
        // Find active loans that are past due date
        $overdueLoans = Loan::where('status', 'active')
                           ->where('due_date', '<', now())
                           ->with(['book', 'member'])
                           ->get();

        if ($overdueLoans->isEmpty()) {
            $this->info('No overdue loans found.');
            return 0;
        }

        $this->info("Found {$overdueLoans->count()} overdue loans.");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->table(
                ['ID', 'Book', 'Member', 'Due Date', 'Days Overdue', 'Current Fine', 'New Fine'],
                $overdueLoans->map(function ($loan) {
                    $daysOverdue = now()->diffInDays($loan->due_date);
                    $newFine = $daysOverdue * 1000; // Rp 1000 per day
                    
                    return [
                        $loan->id,
                        $loan->book->title,
                        $loan->member->name,
                        $loan->due_date->format('d/m/Y'),
                        $daysOverdue,
                        'Rp ' . number_format($loan->fine_amount, 0, ',', '.'),
                        'Rp ' . number_format($newFine, 0, ',', '.'),
                    ];
                })
            );
            return 0;
        }

        $updatedCount = 0;
        $totalFineAdded = 0;

        foreach ($overdueLoans as $loan) {
            $daysOverdue = now()->diffInDays($loan->due_date);
            $newFine = $daysOverdue * 1000; // Rp 1000 per day

            $loan->update([
                'status' => 'overdue',
                'fine_amount' => $newFine,
            ]);

            $updatedCount++;
            $totalFineAdded += $newFine;

            $this->line("Updated loan #{$loan->id}: {$loan->book->title} - {$loan->member->name} (Rp " . number_format($newFine, 0, ',', '.') . ")");
        }

        $this->info("Successfully updated {$updatedCount} overdue loans.");
        $this->info("Total fines calculated: Rp " . number_format($totalFineAdded, 0, ',', '.'));

        return 0;
    }
}
