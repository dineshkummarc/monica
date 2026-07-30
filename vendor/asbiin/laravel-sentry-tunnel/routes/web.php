<?php

use Illuminate\Support\Facades\Route;
use SentryTunnel\Http\Controller\SentryTunnel;

Route::post('', [SentryTunnel::class, 'tunnel']);
