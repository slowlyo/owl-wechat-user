<?php

use Slowlyo\OwlWechatUser\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::resource(
    'users',
    \Slowlyo\OwlWechatUser\OwlWechatUserServiceProvider::setting(
        'controller_class',
        Controllers\OwlWechatUserController::class
    )
);
