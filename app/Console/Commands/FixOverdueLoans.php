<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use Carbon\Carbon;

class FixOverdueLoans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:fix-overdue {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix overdue loans status and fine calculations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Step 1: Update status for loans that should be overdue
        $this->info('📋 Step 1: Checking for loans that should be marked as overdue...');
        
        $activeOverdueLoans = Loan::where('status', 'active')
                                 ->where('due_date', '<', now())
                                 ->get();

        if ($activeOverdueLoans->count() > 0) {
            $this->info("Found {$activeOverdueLoans->count()} active loans that are overdue:");
            
            foreach ($activeOverdueLoans as $loan) {
                $daysOverdue = (int) $loan->due_date->diffInDays(now(), false);
                $this->line("  - Loan #{$loan->id}: {$daysOverdue} days overdue (due: {$loan->due_date->format('d/m/Y')})");
                
                if (!$dryRun) {
                    $loan->update(['status' => 'overdue']);
                }
            }
            
            if (!$dryRun) {
                $this->info("✅ Updated {$activeOverdueLoans->count()} loans to overdue status");
            }
        } else {
            $this->info('✅ No active loans found that need status update');
        }

        $this->newLine();

        // Step 2: Fix fine calculations for all overdue loans
        $this->info('💰 Step 2: Fixing fine calculations for overdue loans...');
        
        $overdueLoans = Loan::where('status', 'overdue')->get();
        
        if ($overdueLoans->count() > 0) {
            $this->info("Found {$overdueLoans->count()} overdue loans to check:");
            
            $updatedCount = 0;
            
            foreach ($overdueLoans as $loan) {
                $daysOverdue = (int) $loan->due_date->diffInDays(now(), false);
                $correctFine = $daysOverdue * 1000; // Rp 1000 per day
                
                if ($loan->fine_amount != $correctFine) {
                    $this->line("  - Loan #{$loan->id}: {$daysOverdue} days → Rp " . number_format($correctFine, 0, ',', '.') . 
                               " (was: Rp " . number_format($loan->fine_amount, 0, ',', '.') . ")");
                    
                    if (!$dryRun) {
                        $loan->update(['fine_amount' => $correctFine]);
                    }
                    $updatedCount++;
                } else {
                    $this->line("  - Loan #{$loan->id}: {$daysOverdue} days → Rp " . number_format($correctFine, 0, ',', '.') . " ✓");
                }
            }
            
            if ($updatedCount > 0) {
                if (!$dryRun) {
                    $this->info("✅ Updated fine amounts for {$updatedCount} loans");
                } else {
                    $this->info("📝 Would update fine amounts for {$updatedCount} loans");
                }
            } else {
                $this->info('✅ All fine amounts are already correct');
            }
        } else {
            $this->info('✅ No overdue loans found');
        }

        $this->newLine();

        // Step 3: Summary
        $totalOverdue = Loan::where('status', 'overdue')->count();
        $totalFines = Loan::where('status', 'overdue')->sum('fine_amount');
        
        $this->info('📊 Summary:');
        $this->line("  - Total overdue loans: {$totalOverdue}");
        $this->line("  - Total fines: Rp " . number_format($totalFines, 0, ',', '.'));
        
        if ($dryRun) {
            $this->newLine();
            $this->warn('🔄 To apply these changes, run the command without --dry-run flag');
        } else {
            $this->newLine();
            $this->info('🎉 All overdue loans have been fixed!');
        }

        return Command::SUCCESS;
    }
}
