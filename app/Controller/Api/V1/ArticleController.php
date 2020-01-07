<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;


use App\Controller\Api\BaseController;
use App\Services\ArticleCategoryServices;
use App\Services\ArticleServices;
use App\Validates\Api\ArticleValidate;
use Hyperf\Di\Annotation\Inject;

/**
 * Class ArticleController
 * @package App\Controller\Api\V1
 */
class ArticleController extends BaseController
{
    /**
     * 文章模块验证服务
     *
     * @Inject()
     * @var ArticleValidate
     */
    protected $articleValidate;

    /**
     * @Inject()
     * @var ArticleServices
     */
    protected $articleServices;

    /**
     * @Inject()
     * @var ArticleCategoryServices
     */
    protected $articleCategoryServices;

    /**
     * 文章列表及分类数据
     * @return array
     */
    public function list()
    {

        $params = $this->request->all();
        $params['page']  = $params['page'] ?? 1;
        $params['per_page']  = $params['per_page'] ?? 20;

        // 数据验证
        $restValidate = $this->articleValidate->listValidate($params);
        if ($restValidate['status'] !== true) {
            return $this->error($restValidate['message']);
        }

        // 分类数据
        $categoryList =  $this->articleCategoryServices->listAll();

        // 列表数据
        $list  = $this->articleServices->getArticleList($params);

        // 走缓存时处理实时阅读量和收藏数
        $list['list'] = $list ? $this->articleServices->HomeDataDispose($list['list']) : [];

        // 数据返回
        $result = [
            'list' => $list['list'],
            'count' => $list['count'],
            'category_list' => $categoryList,
        ];
        return $this->success($result);
    }



    /**
     * 热门文章
     * @return array
     */
    public function hotArticleList()
    {
        $list =  $this->articleServices->getHotArticleList();
        return $this->success(['list' => $list]);
    }
}