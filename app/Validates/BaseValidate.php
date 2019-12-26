<?php

declare(strict_types=1);

namespace App\Validates;


use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class BaseValidate
{

    /**
     * @Inject()
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    protected $message;

    public function respond($status, $respond_data, $message)
    {
        return ['status' => $status, 'data' => $respond_data, 'message' => $message];
    }

    public function baseSucceed($respond_data = [], $message = 'Request success!', $status = true)
    {
        return $this->respond($status, $respond_data, $message);
    }

    public function baseFailed($message = 'Request failed!', $respond_data = [], $status = false)
    {
        return $this->respond($status, $respond_data, $message);
    }
}