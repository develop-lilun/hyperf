<?php

declare(strict_types=1);

namespace App\Controller\Admin;


use App\Controller\AbstractController;

class BaseController extends AbstractController
{
    /**
     * 正常请求返回参数
     */
    private const ADMIN_SUCCESS_CODE = 0;

    /**
     * 后台状态码地址
     */
    private const ADMIN_CODE_NAME = 'adminCode';

    /**
     * API 错误时返回参数
     *
     * @param array $data
     * @param array $extra
     * @return array
     */
    public function success($data = [], $extra = []): array
    {
        return [
            'code' => self::ADMIN_SUCCESS_CODE,
            'data' => $data,
            'extra' => $extra,
            'message' => 'success',
            'path' => $this->request->getPathInfo(),
            'timestamp' => time(),
        ];
    }

    /**
     * API 错误时返回
     *
     * @param string $code
     * @param string $message
     * @param array $data
     * @param array $extra
     *
     * @return array
     */
    public function error($code = '-201', $data = [], $extra = [], $message = null): array
    {
        return [
            'code' => $code,
            'data' => $data,
            'extra' => $extra,
            'message' => $message ?? trans(self::ADMIN_CODE_NAME . '.' . $code) ?? 'error',
            'path' => $this->request->getPathInfo(),
            'timestamp' => time(),
        ];
    }
}