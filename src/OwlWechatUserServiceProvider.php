<?php

namespace Slowlyo\OwlWechatUser;

use Slowlyo\OwlAdmin\Renderers\Flex;
use Illuminate\Support\Facades\Route;
use Slowlyo\OwlAdmin\Renderers\TextControl;
use Slowlyo\OwlAdmin\Extend\ServiceProvider;
use Slowlyo\OwlAdmin\Renderers\VanillaAction;
use Slowlyo\OwlWechatUser\Http\Controllers\Apis\AuthController;

class OwlWechatUserServiceProvider extends ServiceProvider
{
    protected $menu = [
        [
            'title' => '会员管理',
            'url'   => '/users',
            'icon'  => 'ph:user-list',
        ],
    ];

    public function register()
    {
        parent::register();

        $this->loadRoute();
    }

    public function uninstall()
    {
        $this->flushMenu();
        $this->unpublishable();
        // $this->runMigrations(true);
        \Slowlyo\OwlAdmin\Models\Extension::query()->where('name', $this->getName())->delete();
    }

    public function loadRoute()
    {
        Route::any('/api/wechat/mini_program_login', [AuthController::class, 'miniProgramLogin']);
        Route::any('/api/wechat/mini_program_silent_login', [AuthController::class, 'miniProgramSilentLogin']);
        Route::middleware('auth:sanctum')->any('/api/wechat/user', [AuthController::class, 'user']);
        Route::middleware('auth:sanctum')->post('/api/wechat/user', [AuthController::class, 'saveInfo']);
    }

    public function settingForm()
    {
        $default = [
            'extension'              => $this->getName(),
            'service_class'          => 'Slowlyo\OwlWechatUser\Services\UserService',
            'user_model_class'       => 'Slowlyo\OwlWechatUser\Models\User',
            'user_oauth_model_class' => 'Slowlyo\OwlWechatUser\Models\UserOAuth',
            'resource_class'         => 'Slowlyo\OwlWechatUser\Http\Resources\UserMPResource',
            'controller_class'       => 'Slowlyo\OwlWechatUser\Http\Controllers\OwlWechatUserController',
        ];

        $resetBtn = VanillaAction::make()
            ->label('恢复默认值')
            ->level('success')
            ->confirmText('确认恢复为默认值?')
            ->onEvent([
                'click' => [
                    'actions' => [
                        [
                            'actionType'  => 'setValue',
                            'componentId' => 'wechat_user_setting_form',
                            'args'        => ['value' => $default],
                        ],
                        ['actionType' => 'submit', 'componentId' => 'wechat_user_setting_form',],
                    ],
                ],
            ]);

        return $this->baseSettingForm()->id('wechat_user_setting_form')->data($default)->body([
            Flex::make()->justify('end')->items([$resetBtn]),
            TextControl::make()->name('service_class')->label('Service Class')->required(true),
            TextControl::make()->name('user_model_class')->label('User Model Class')->required(true),
            TextControl::make()->name('user_oauth_model_class')->label('User OAuth Model Class')->required(true),
            TextControl::make()->name('resource_class')->label('Resource Class')->required(true),
            TextControl::make()->name('controller_class')->label('Controller Class')->required(true),
        ]);
    }
}
