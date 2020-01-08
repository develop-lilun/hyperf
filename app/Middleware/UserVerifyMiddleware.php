<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserVerifyMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(ContainerInterface $container, HttpResponse $response)
    {
        $this->container = $container;
        $this->response = $response;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 是否开启user_dug
        if (env('USER_DEBUG')) {
            $userInfo = [
                'userId' => 1,
                'nickname' => '跨境知道',
                'avatar' => 'https://static.ikjzd.com/uploads/index/set_thumb/20190709/d7e47e49e7fa602f35f2743dea5956f9.jpg',
            ];
        } else {

            // 获取认证信息
            $sessionId = $request->getHeader('sessionId');
            $token = $request->getHeader('token');

            // token 不存在
            if (!$sessionId || !$token) {
                return $this->response->json(['code' => '401', 'message' => 'token不存在']);
            }

            // 获取用户信息
            $user = new UserServices();
            $userInfo = $user->getUserInfo($token, $sessionId);

            // 无用户信息
            if (!$userInfo) {
                return $this->response->json(['code' => '401', 'message' => '请重新登录']);
            }

            // 用户是否禁用
            if (!isset($userInfo['status']) || $userInfo['status'] == 0) {
                return $this->response->json(['code' => '403', 'message' => '权限不足']);
            }
        }

        $result = [
            'user_id' => $userInfo['userId'],
            'user_name' => $userInfo['nickname'],
            'face_url' => $userInfo['avatar'],
        ];

        // 将用户信息添加到请求头
        Context::set('user_info', $result);
        return $handler->handle($request);
    }
}