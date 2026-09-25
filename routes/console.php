<?php

use App\Jobs\GenerarOrdenesCobroJob;
use App\Jobs\RecordarVencimientoOrdenCobroJob;
use App\Jobs\SuspenderEmpresasMorosasJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reservas:cancelar-expiradas')->hourly()->withoutOverlapping();
Schedule::job(new GenerarOrdenesCobroJob)->hourly()->withoutOverlapping()->onOneServer();
Schedule::job(new RecordarVencimientoOrdenCobroJob)->hourly()->withoutOverlapping()->onOneServer();
Schedule::job(new SuspenderEmpresasMorosasJob)->hourly()->withoutOverlapping()->onOneServer();
