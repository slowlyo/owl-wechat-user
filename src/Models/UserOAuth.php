<?php

namespace Slowlyo\OwlWechatUser\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Slowlyo\OwlWechatUser\OwlWechatUserServiceProvider;

class UserOAuth extends Model
{
    protected $table = 'user_oauth';

    protected function userClass()
    {
        return OwlWechatUserServiceProvider::setting('user_model_class', User::class);
    }

    public function user()
    {
        return $this->belongsTo($this->userClass());
    }

    public function avatar(): Attribute
    {
        return Attribute::get(fn($value) => $value ?? url('extensions/slowlyo/owl-wechat-user/images/default-avatar.jpg'));
    }

    public function scopeSource($query, $source)
    {
        return $query->where('source', $source)->limit(1);
    }
}
