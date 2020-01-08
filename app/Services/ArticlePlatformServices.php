<?php

namespace App\Services;

use App\Model\ArticlePlatformModel;

class ArticlePlatformServices
{
    /**
     * 查询文章平台数据
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
        $sort = $params['sort'] ?? 'created_at';
        $order = $params['order'] ?? 'desc';

        // 列表
        $list = ArticlePlatformModel::getListContainer($queryDate, $page, $perPage, $sort, $order);
        // 总数
        $count = ArticlePlatformModel::getCount($queryDate);
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
            $result = ArticlePlatformModel::query()->where(['id' => $params['id']])->update($data);
        } else {
            // 新增
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = ArticlePlatformModel::query()->insert($data);
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
        return ArticlePlatformModel::dropIds($ids);
    }

    /**
     * 查询平台列表全部
     * @return mixed
     */
    public function listAll()
    {
        return ArticlePlatformModel::query()->select(['id', 'name'])->get()->toArray();
    }

    /**
     * 根据id 返回 信息
     *
     * @param array $ids
     *
     * @return  'id' => 'name'
     */
    public function getPlatformNameByIds($ids = [])
    {
        $list = ArticlePlatformModel::query()->whereIn('id', $ids)->select(['id', 'name'])->get()->toArray();
        $result = [];
        foreach ($list as $value) {
            $result[$value['id']] = $value['name'];
        }
        return $result;
    }
}
