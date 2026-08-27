<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\Website\QuotaService;
use App\Support\Navigation;
use Livewire\Component;

/**
 * Shared behaviour for every panel component: layout data, toasts and
 * convenient access to the authenticated user.
 */
abstract class BaseComponent extends Component
{
    public function user(): ?User
    {
        return auth()->user();
    }

    protected function layoutData(string $title = 'Dashboard'): array
    {
        $user = $this->user();

        $data = [
            'title' => $title,
            'navigation' => $user ? Navigation::for($user) : [],
        ];

        if ($user && ! $user->isStaff()) {
            $data['usage'] = app(QuotaService::class)->usage($user);
        }

        return $data;
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('toast', message: $message, type: $type);
    }

    protected function notifySuccess(string $message): void
    {
        $this->toast($message, 'success');
    }

    protected function notifyError(string $message): void
    {
        $this->toast($message, 'error');
    }
}
