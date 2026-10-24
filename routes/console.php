<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
Artisan::command('about-loanengine', function () {
    $this->comment('LoanEngine Login/Register starter');
})->purpose('Show a short LoanEngine starter message');

