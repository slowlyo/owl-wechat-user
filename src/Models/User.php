<?php

namespace Slowlyo\OwlWechatUser\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Slowlyo\OwlWechatUser\Define\UserOAuthDefine;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Slowlyo\OwlWechatUser\OwlWechatUserServiceProvider;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format($this->getDateFormat());
    }

    protected function userOAuthModelClass()
    {
        return OwlWechatUserServiceProvider::setting('user_oauth_model_class', UserOAuth::class);
    }

    public function oauth()
    {
        return $this->hasMany($this->userOAuthModelClass());
    }

    public function oauthMP()
    {
        return $this->hasOne($this->userOAuthModelClass())->where('source', UserOAuthDefine::SOURCE_WECHAT_MP);
    }
}
