<?php

declare(strict_types=1);

namespace App\Controller\Api;


use App\Controller\AbstractController;

class BaseController extends AbstractController
{

    /**
     * 正常请求返回参数
     */
    public const API_SUCCESS_CODE = 0;


    private const API_CODE_NAME = 'apiCode';

    /**
     * API 错误时返回参数
     *
     * @param array $data
     * @param array $extra
     * @return array
     */
    public function success($data = [], $extra = []): array
    {
        $result = [
            'code' => self::API_SUCCESS_CODE,
            'data' => $data,
            'extra' => $extra,
            'message' => 'success',
            'path' => $this->request->getPathInfo(),
            'timestamp' => time(),
        ];
        return $result;
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
    public function error($code = '-200', $data = [], $extra = [], $message = null)
    {
        $result = [
            'code' => $code,
            'data' => $data,
            'extra' => $extra,
            'message' => $message ?? trans(self::API_CODE_NAME . '.' . $code) ?? 'error',
            'path' => $this->request->path(),
            'timestamp' => time(),
        ];
        return $result;
    }
}