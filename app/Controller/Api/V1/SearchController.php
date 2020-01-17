<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;


use App\Controller\Api\BaseController;
use App\Services\SearchServices;
use App\Validates\Api\SearchValidate;
use Hyperf\Di\Annotation\Inject;

class SearchController extends BaseController
{

    /**
     * @Inject()
     * @var SearchValidate
     */
    private $searchValidate;

    /**
     * @Inject()
     * @var SearchServices
     */
    private $searchServices;

    public function list()
    {
        $params = $this->request->all();

        // 数据验证
        $restValidate = $this->searchValidate->searchValidate($params);
        if ($restValidate['status'] !== true) {
            return $this->error($restValidate['message']);
        }

        $result = $this->searchServices->searchAll($params);

        // 搜索成功事件
//        event(new UserSearchSuccess($params));

        return $this->success($result);
    }

    public function update(){

        $result = $this->searchServices->update();

        // 搜索成功事件
//        event(new UserSearchSuccess($params));

        return $this->success($result);
    }

}