<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\BaseComponent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class QueueMonitor extends BaseComponent
{
    public string $tab = 'pending';

    public function retryJob(string $uuid): void
    {
        $this->authorize('manage-settings');

        Artisan::call('queue:retry', ['id' => [$uuid]]);
        $this->notifySuccess('Job pushed back onto the queue.');
    }

    public function retryAll(): void
    {
        $this->authorize('manage-settings');

        Artisan::call('queue:retry', ['id' => ['all']]);
        $this->notifySuccess('All failed jobs have been retried.');
    }

    public function deleteFailed(int $id): void
    {
        DB::table('failed_jobs')->where('id', $id)->delete();
        $this->notifySuccess('Failed job removed.');
    }

    public function flushFailed(): void
    {
        $this->authorize('manage-settings');

        $count = DB::table('failed_jobs')->count();
        DB::table('failed_jobs')->truncate();

        $this->notifySuccess("{$count} failed jobs cleared.");
    }

    public function render()
    {
        $pending = DB::table('jobs')->orderByDesc('id')->limit(50)->get()
            ->map(fn ($job) => (object) [
                'id' => $job->id,
                'queue' => $job->queue,
                'name' => $this->jobName($job->payload),
                'attempts' => $job->attempts,
                'available_at' => \Carbon\Carbon::createFromTimestamp($job->available_at),
                'created_at' => \Carbon\Carbon::createFromTimestamp($job->created_at),
            ]);

        $failed = DB::table('failed_jobs')->orderByDesc('id')->limit(50)->get()
            ->map(fn ($job) => (object) [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'queue' => $job->queue,
                'name' => $this->jobName($job->payload),
                'exception' => \Illuminate\Support\Str::limit((string) $job->exception, 400),
                'failed_at' => \Carbon\Carbon::parse($job->failed_at),
            ]);

        return view('livewire.super.queue-monitor', [
            'pending' => $pending,
            'failed' => $failed,
            'stats' => [
                'pending' => DB::table('jobs')->count(),
                'failed' => DB::table('failed_jobs')->count(),
                'batches' => DB::getSchemaBuilder()->hasTable('job_batches') ? DB::table('job_batches')->count() : 0,
                'driver' => config('queue.default'),
            ],
            'queues' => DB::table('jobs')->selectRaw('queue, COUNT(*) as total')->groupBy('queue')->pluck('total', 'queue'),
        ])->layoutData($this->layoutData('Queue Monitor'));
    }

    private function jobName(string $payload): string
    {
        $decoded = json_decode($payload, true);

        return class_basename($decoded['displayName'] ?? $decoded['job'] ?? 'UnknownJob');
    }
}
