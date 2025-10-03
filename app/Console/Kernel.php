<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $tasks = \App\Models\Task::where('status', 'pending')
                ->whereDate('due_date', now()->addDay())
                ->get();

            $gmail = app(\App\Services\GmailService::class);

            foreach ($tasks as $task) {
                $gmail->sendMail(
                    "Reminder: {$task->title}",
                    "Deadline besok untuk tugas: {$task->description}"
                );
                $task->update(['reminded_at' => now()]);
            }
        })->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
