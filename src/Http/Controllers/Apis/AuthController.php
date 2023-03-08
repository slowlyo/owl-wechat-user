<?php

namespace Slowlyo\OwlWechatUser\Http\Controllers\Apis;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Slowlyo\OwlWechatUser\Services\UserService;
use Slowlyo\OwlWechatUser\Events\UserUpdatedEvent;
use Slowlyo\OwlWechatUser\Events\UserLoggedInEvent;
use Slowlyo\OwlWechatUser\Events\UserRegisteredEvent;
use Slowlyo\OwlWechatUser\OwlWechatUserServiceProvider;
use Slowlyo\OwlWechatUser\Http\Resources\UserMPResource;

class AuthController extends Controller
{
    /**
     * 微信小程序登录
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function miniProgramLogin(Request $request)
    {
        $service = $this->userService()::make();

        // 解密用户信息
        $data = $service->decryptMP($request->code);

        if ($service->hasError()) {
            $this->fail($service->getError());
        }

        // 获取用户信息
        $user = $service->getUserByWechatMP($data['openid']);

        if (!$user) {
            // 用户注册
            $user = $service->wechatMPRegister($request, $data);

            if ($service->hasError()) {
                $this->fail($service->getError());
            }

            // 用户注册事件
            event(new UserRegisteredEvent($user, $request));
        }

        $user->load('oauthMP');
        $user->refresh();

        // 用户登录事件
        event(new UserLoggedInEvent($user, $request));

        return $this->success([
            'token' => $user->createToken($request->code)->plainTextToken,
            'user'  => $this->resourceClass()::make($user),
        ]);
    }

    /**
     * 微信小程序静默登录
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function miniProgramSilentLogin(Request $request)
    {
        $service = $this->userService()::make();

        // 解密用户信息
        $data = $service->decryptMP($request->code);

        if ($service->hasError()) {
            $this->fail($service->getError());
        }

        // 获取用户信息
        $user = $service->getUserByWechatMP($data['openid']);

        if (!$user) {
            $this->errorNotFound('用户不存在');
        }

        $user->load('oauthMP');
        $user->refresh();

        // 用户登录事件
        event(new UserLoggedInEvent($user, $request));

        return $this->success([
            'token' => $user->createToken($request->code)->plainTextToken,
            'user'  => $this->resourceClass()::make($user),
        ]);
    }

    /**
     * 获取用户信息
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function user(Request $request)
    {
        return $this->success($this->resourceClass()::make($request->user()));
    }

    /**
     * 保存用户信息
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function saveInfo(Request $request)
    {
        $data = $request->validate([
            'nickname' => 'required|string',
            'avatar'   => 'required|string',
        ], [
            'nickname.required' => '昵称不能为空',
            'avatar.required'   => '头像不能为空',
        ]);

        $service = $this->userService()::make();

        $service->saveInfo($request->user(), $data);

        if ($service->hasError()) {
            $this->fail($service->getError());
        }

        $user = $request->user()->load('oauthMP')->refresh();

        // 修改用户信息事件
        event(new UserUpdatedEvent($user, $request));

        return $this->success([
            'user' => $this->resourceClass()::make($user),
        ], '保存成功');
    }

    protected function userService()
    {
        return OwlWechatUserServiceProvider::setting('service_class', UserService::class);
    }

    protected function resourceClass()
    {
        return OwlWechatUserServiceProvider::setting('resource_class', UserMPResource::class);
    }
}
