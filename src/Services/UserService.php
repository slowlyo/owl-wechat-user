<?php

namespace Slowlyo\OwlWechatUser\Services;

use Illuminate\Support\Arr;
use Slowlyo\OwlWechatUser\Models\User;
use Slowlyo\OwlAdmin\Traits\ErrorTrait;
use Slowlyo\OwlWechatUser\Models\UserOAuth;
use Slowlyo\OwlAdmin\Services\AdminService;
use Slowlyo\OwlWechatUser\Define\UserOAuthDefine;
use Slowlyo\OwlWechatUser\OwlWechatUserServiceProvider;

class UserService extends AdminService
{
    use ErrorTrait;

    protected $app;

    protected $modelName;

    public function __construct()
    {
        $this->app       = app('wechat.mini_program');
        $this->modelName = $this->userModel();
    }

    protected function userModel()
    {
        return OwlWechatUserServiceProvider::setting('user_model_class', User::class);
    }

    protected function userOAuthModel()
    {
        return OwlWechatUserServiceProvider::setting('user_oauth_model_class', UserOAuth::class);
    }

    /**
     * @param $code
     *
     * @return bool|array
     */
    public function decryptMP($code)
    {
        if (!$code) {
            return $this->setError('code不能为空！');
        }

        try {
            $data = $this->app->auth->session($code);
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }

        if (!isset($data['openid']) || !isset($data['session_key'])) {
            return $this->setError('登录失败！');
        }

        return $data;
    }

    /**
     * @param $sessionKey
     * @param $iv
     * @param $encryptedData
     *
     * @return bool
     */
    protected function decryptMPPhone($sessionKey, $iv, $encryptedData)
    {
        try {
            $phone_data = $this->app->encryptor->decryptData(
                $sessionKey,
                $iv,
                $encryptedData
            );

            if (!$phone_data || !isset($phone_data['purePhoneNumber'])) {
                return $this->setError('用户登录失败, 请检查手机号授权信息是否正确！');
            }
        } catch (\Exception $e) {
            return $this->setError($e->getMessage());
        }

        return $phone_data['purePhoneNumber'];
    }

    /**
     * 根据用户来源和uuid获取用户
     *
     * @param $source
     * @param $uuid
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getUserBySource($source, $uuid)
    {
        return $this->userModel()::with('oauth')
            ->whereHas('oauth', fn($query) => $query->where('source', $source)->where('uuid', $uuid))
            ->first();
    }

    public function getUserByPhone($phone)
    {
        return $this->userModel()::where('phone', $phone)->first();
    }

    /**
     * 根据微信小程序的openid获取用户
     *
     * @param $openid
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getUserByWechatMP($openid)
    {
        return $this->getUserBySource(UserOAuthDefine::SOURCE_WECHAT_MP, $openid);
    }

    /**
     * 微信小程序注册
     *
     * @param $request
     * @param $data
     *
     * @return mixed
     */
    public function wechatMPRegister($request, $data)
    {
        $phone = $this->decryptMPPhone($data['session_key'], $request->iv, $request->encryptedData);

        if ($this->hasError()) {
            return false;
        }

        $user = $this->getUserByPhone($phone);

        if (!$user) {
            $user = new User();

            $user->phone = $phone;
            $user->save();
        }

        $arr = ['openid', 'unionid'];

        foreach ($arr as $key) {
            if (!Arr::get($data, $key)) {
                continue;
            }

            if ($this->userOAuthModel()::where('uuid', $data[$key])->exists()) {
                continue;
            }

            $oauth = new UserOAuth();

            $oauth->user_id  = $user->id;
            $oauth->uuid     = $data[$key];
            $oauth->source   = UserOAuthDefine::SOURCE_WECHAT_MP;
            $oauth->nickname = '用户' . substr($data[$key], -4);

            $oauth->save();
        }

        return $user;
    }

    /**
     * 保存用户信息
     *
     * @param $user
     * @param $data
     *
     * @return bool
     */
    public function saveInfo($user, $data)
    {
        $oauth = $this->userOAuthModel()::where('user_id', $user->id)
            ->where('source', UserOAuthDefine::SOURCE_WECHAT_MP)
            ->get();

        if ($oauth->isEmpty()) {
            return $this->setError('用户未绑定微信小程序！');
        }
        $oauth->each(function ($item) use ($data) {
            $item->nickname = $data['nickname'];
            $item->avatar   = $data['avatar'];
            $item->save();
        });

        return true;
    }

    public function listQuery()
    {
        $nickname = request()->input('nickname');
        $phone    = request()->input('phone');

        $model = $this->getModel();

        return $this->query()->with('oauthMP')
            ->when($phone, fn($query) => $query->where('phone', 'like', "%{$phone}%"))
            ->when($nickname, function ($query) use ($nickname, $model) {
                return $query->whereHas('oauthMP', fn($query) => $query->where('nickname', 'like', "%{$nickname}%"));
            })
            ->orderByDesc($model->getUpdatedAtColumn() ?? $model->getKeyName());
    }

    public function getDetail($id)
    {
        return $this->query()->with('oauthMP')->find($id);
    }
}
