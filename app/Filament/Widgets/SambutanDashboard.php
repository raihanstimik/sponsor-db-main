<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class SambutanDashboard extends Widget
{
    protected string $view = 'filament.widgets.sambutan-dashboard';

    protected static ?int $sort = -102;

    protected int|string|array $columnSpan = 'full';
}
