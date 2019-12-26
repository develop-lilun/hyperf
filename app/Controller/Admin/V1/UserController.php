<?php

declare(strict_types=1);

namespace App\Controller\Admin\V1;


use App\Controller\Admin\BaseController;
use App\Validates\IndexValidate;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

/**
 * Class UserController
 * @package App\Controller\Admin\V1
 * @Controller(prefix="user")
 */
class UserController extends BaseController
{
    /**
     * @Inject
     * @var IndexValidate
     */
    private $indexValidate;


    public function foo()
    {
        $params = $this->request->all();

        $restValidate = $this->indexValidate->foo($params);
        if($restValidate['status']  !== true){
            return $this->error($restValidate['message']);
        }

        return $this->success();
    }

    /**
     * @PostMapping(path="/ccc")
     * @return array
     */
    public function ccc()
    {
        $params = $this->request->all();

        $restValidate = $this->indexValidate->foo($params);
        if($restValidate['status']  !== true){
            return $this->error($restValidate['message']);
        }

        return $this->success();
    }
}