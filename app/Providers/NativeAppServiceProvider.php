<?php

namespace App\Providers;

use Native\Laravel\Facades\Window;
use Native\Laravel\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        try {
            // Fix: Move DB to writable AppData location (Storage)
            $writableDbPath = storage_path('twinx.sqlite');
            $bundledDbPath = database_path('database.sqlite');

            if (!file_exists($writableDbPath) && file_exists($bundledDbPath)) {
                @copy($bundledDbPath, $writableDbPath);
            }

            if (file_exists($writableDbPath)) {
                config(['database.connections.sqlite.database' => $writableDbPath]);
            }
        } catch (\Throwable $e) {
            // Silently fail to allow the window to at least show an error page
        }

        Window::open()
            ->width(1280)
            ->height(800)
            ->title('Twinx ERP System')
            ->rememberState()
            ->showDevTools(false)
            ->maximize()
            ->kiosk(); // Uncommented for final build
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
