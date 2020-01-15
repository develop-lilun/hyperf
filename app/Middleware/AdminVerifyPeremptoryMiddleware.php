<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AdminServices;
use App\Services\Common;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 管理后台接口身份验证-强制
 * Class AdminVerifyPeremptoryMiddleware
 * @package App\Middleware
 */
class AdminVerifyPeremptoryMiddleware implements MiddlewareInterface
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
     * @var AdminServices
     */
    protected $adminServices;

    public function __construct(ContainerInterface $container, AdminServices $adminServices, HttpResponse $response)
    {
        $this->container = $container;
        $this->response = $response;
        $this->adminServices = $adminServices;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 是否开启user_dug
        if (env('USER_DEBUG')) {
            $userInfo = [
                'user_id' => 1,
                'user_name' => '管理员',
            ];
        } else {
            // 获取头信息
            $token = $request->getHeader('Authorization')[0] ?? '';
            $userInfo = $this->adminServices->getOtherUserInfo($token);

        }

        // token不存在或已失效
        if (!$userInfo) {
            return $this->response->json(['code' => '401', 'message' => 'token不存在或已失效']);
        }

        return $handler->handle($request);
    }
}