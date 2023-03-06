<?php

namespace Slowlyo\OwlWechatUser\Define;

interface UserOAuthDefine
{
    /** @var string 用户来源: 微信小程序 */
    const SOURCE_WECHAT_MP = 'WECHAT_MP';

    /** @var string 用户来源: 微信公众号 */
    const SOURCE_WECHAT_OFFICIAL = 'WECHAT_OFFICIAL';

}
