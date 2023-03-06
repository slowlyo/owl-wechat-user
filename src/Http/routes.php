<?php

use Slowlyo\OwlWechatUser\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::resource('users', Controllers\OwlWechatUserController::class);
