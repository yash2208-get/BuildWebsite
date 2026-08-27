<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function ($model) use ($event) {
                if (! app()->runningInConsole() || app()->runningUnitTests() === false) {
                    $model->recordActivity($event);
                }
            });
        }
    }

    public function recordActivity(string $event, array $properties = []): void
    {
        if (! config('platform.activity_log.enabled', true)) {
            return;
        }

        $label = class_basename($this);
        $name = $this->activityTitle();

        ActivityLog::record(
            event: $event,
            description: trim("{$label} \"{$name}\" was {$event}"),
            subject: $this,
            properties: $properties ?: array_intersect_key(
                $this->getChanges(),
                array_flip($this->activityLoggable())
            ),
        );
    }

    protected function activityTitle(): string
    {
        foreach (['name', 'title', 'subject', 'question', 'code'] as $attr) {
            if (filled($this->{$attr} ?? null)) {
                return (string) $this->{$attr};
            }
        }

        return (string) $this->getKey();
    }

    protected function activityLoggable(): array
    {
        return property_exists($this, 'activityAttributes')
            ? $this->activityAttributes
            : array_slice($this->getFillable(), 0, 8);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
