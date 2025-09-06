<?php

namespace App\Http\Responses\Concerns;

use App\Exceptions\CustomException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

trait RedirectWithFeedback
{
    private function sendSuccessRedirect(string $message, $route = null): RedirectResponse
    {
        $feedback = [
            'type' => 'success',
            'message' => $message,
        ];

        $route = $route ?: route('backoffice.dashboard');

        return redirect($route)->with('feedback', $feedback);
    }

    private function sendErrorRedirect(string $message, \Throwable $throwable): RedirectResponse
    {

        if (config('app.debug')) {

            activity()->log($throwable->getMessage());

            if (app()->environment('testing', 'local')) {

                Log::error($throwable->getMessage());
            }
        }

        $localsMessage = $throwable instanceof CustomException
            ? $throwable->getMessage()
            : $message;

        $feedback = [
            'type' => 'error',
            'message' => $localsMessage,
            // 'message' => $throwable->getMessage(),
        ];

        return redirect()->back()->with('feedback', $feedback);
    }
}
