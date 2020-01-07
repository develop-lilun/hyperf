<?php

namespace App\Services;

use App\Model\ArticleTagModel;

class ArticleTagServices
{
    /**
     * 查询文章标签数据
     *
     * @param $params
     *
     * @return array
     */
    public function getList($params)
    {
        // 分页数据
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 20;
        $queryDate = [];

        // 排序数据
        $sort = $params['sort'] ?? 'id';
        $order = $params['order'] ?? 'desc';

        // 列表
        $list = ArticleTagModel::getList($queryDate, $page, $perPage, $sort, $order);
        // 总数
        $count = ArticleTagModel::getCount($queryDate);
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 新增或修改
     *
     * @param $params
     *
     * @return mixed
     */
    public function addEdit($params)
    {
        // 数据组装
        if (isset($params['name'])) {
            $data['name'] = $params['name'];
        }

        if (isset($params['id']) && $params['id'] !== 0) {
            // 修改
            $result = ArticleTagModel::query()->where(['id' => $params['id']])->update($data);
        } else {
            // 新增
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = ArticleTagModel::query()->insert($data);
        }

        return $result;
    }

    /**
     * 删除数据
     *
     * @param $ids
     */
    public function delete($ids)
    {
        return ArticleTagModel::dropIds($ids);
    }

    /**
     * 查询分类标签全部
     * @return mixed
     */
    public function listAll()
    {
        return ArticleTagModel::getListAll();
    }

    /**
     * 根据ids 查询文章标签 name
     * @param array $ids
     * @return array  [id => name]
     */
    public function getTagNameByIds($ids = [])
    {
        $list = ArticleTagModel::getArticleTagList($ids);
        return array_column($list, 'name', 'id');
    }
}
