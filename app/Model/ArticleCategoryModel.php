<?php

namespace App\Model;

use Hyperf\Cache\Annotation\Cacheable;

class ArticleCategoryModel extends BaseModel
{
    protected $table = 'article_category';

    protected $field = ['*'];

    /**
     * 查询分类数据
     *
     * @param $page
     * @param $perPage
     * @param $sort
     * @param $order
     *
     * @return mixed
     */
    public function getCategoryList($queryDate, $page, $perPage, $sort, $order)
    {
        return self::query()->where($queryDate)->forPage($page, $perPage)->orderBy($sort, $order)->get()->toArray();
    }

    /**
     * 查询总数量
     *
     * @param $queryDate
     *
     * @return mixed
     */
    public function getCategoryCount($queryDate)
    {
        return self::query()->where($queryDate)->count();
    }

    /**
     * 获取顶级文章分类
     *
     * @return array
     */
    public function getTopList()
    {
        return self::query()->where(['pid' => 0, 'is_show' => 1])->select(['id', 'name'])->get()->toArray();
    }
}
