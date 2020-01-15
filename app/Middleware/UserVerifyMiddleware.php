<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\UserServices;
use function Couchbase\defaultDecoder;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 身份认证中间件-不强制
 * Class UserVerifyMiddleware
 * @package App\Middleware
 */
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

    /**
     * @Inject()
     * @var UserServices
     */
    protected $userServices;

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
            $sessionId = $request->getHeader('sessionId')[0] ?? '';
            $token = $request->getHeader('token')[0] ?? '';

            if ($sessionId && $token) {
                // 查询用户信息
                $userInfo = $this->userServices->getUserInfo($token, $sessionId);
            }
        }

        $result = [
            'user_id' => $userInfo['userId'] ?? 0,
            'user_name' => $userInfo['nickname'] ?? '',
            'face_url' => $userInfo['avatar'] ?? '',
        ];

        // 将用户信息添加到协程上下文
        Context::set('user_info', $result);
        var_dump($result);
        return $handler->handle($request);
    }
}