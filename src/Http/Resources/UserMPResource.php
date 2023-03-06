<?php

namespace Slowlyo\OwlWechatUser\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Slowlyo\OwlWechatUser\Models\User */
class UserMPResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'phone'      => $this->phone,
            'avatar'     => $this->oauthMP->avatar,
            'nickname'   => $this->oauthMP->nickname,
            'created_at' => $this->oauthMP->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
