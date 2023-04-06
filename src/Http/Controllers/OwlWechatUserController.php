<?php

namespace Slowlyo\OwlWechatUser\Http\Controllers;

use Slowlyo\OwlAdmin\Renderers\Form;
use Slowlyo\OwlAdmin\Renderers\Operation;
use Slowlyo\OwlAdmin\Renderers\TableColumn;
use Slowlyo\OwlAdmin\Renderers\TextControl;
use Slowlyo\OwlWechatUser\Services\UserService;
use Slowlyo\OwlAdmin\Controllers\AdminController;
use Slowlyo\OwlWechatUser\OwlWechatUserServiceProvider;

class OwlWechatUserController extends AdminController
{
    protected $serviceName;

    public function __construct()
    {
        $this->serviceName = OwlWechatUserServiceProvider::setting('service_class', UserService::class);

        parent::__construct();
    }

    public function list()
    {
        $crud = $this->baseCRUD()
            ->headerToolbar([
                amis('reload')->align('right'),
                amis('filter-toggler')->align('right'),
            ])
            ->filter(
                $this->baseFilter()->body([
                    TextControl::make()->name('nickname')->label('昵称')->size('md'),
                    TextControl::make()->name('phone')->label('手机号')->size('md'),
                ])
            )
            ->columns([
                TableColumn::make()->name('id')->label('ID')->sortable(true),
                TableColumn::make()
                    ->name('oauth_m_p.avatar')
                    ->label('头像')
                    ->type('image')
                    ->width(50)
                    ->height(50)
                    ->enlargeAble(true),
                TableColumn::make()->name('oauth_m_p.nickname')->label('昵称'),
                TableColumn::make()->name('phone')->label('手机号'),
                TableColumn::make()->name('created_at')->label('创建时间')->type('datetime')->sortable(true),
                TableColumn::make()->name('updated_at')->label('更新时间')->type('datetime')->sortable(true),
                Operation::make()->label(__('admin.actions'))->buttons([
                    $this->rowShowButton(true),
                ]),
            ]);

        return $this->baseList($crud);
    }

    public function form(): Form
    {
        return $this->baseForm()->body([]);
    }

    public function detail(): Form
    {
        return $this->baseDetail()->body([
            TextControl::make()->static(true)->name('id')->label('ID'),

            amis('static-image')->name('oauth_m_p.avatar')->label('头像')->enlargeAble(true),
            TextControl::make()->static(true)->name('oauth_m_p.nickname')->label('昵称'),
            TextControl::make()->static(true)->name('phone')->label('手机号'),

            TextControl::make()->static(true)->name('created_at')->label('创建时间'),
            TextControl::make()->static(true)->name('updated_at')->label('更新时间'),
        ]);
    }
}
