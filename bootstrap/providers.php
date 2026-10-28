<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\AnggotaPanelProvider;
use App\Providers\Filament\KuratorPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    KuratorPanelProvider::class,
    AnggotaPanelProvider::class,
];
