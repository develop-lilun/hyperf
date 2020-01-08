<?php


namespace App\Services;


use App\Model\ArticleCategoryModel;
use Hyperf\Cache\Annotation\Cacheable;

class ArticleCategoryServices
{
    /**
     * 查询文章分类数据
     *
     * @param $params
     *
     * @return array
     */
    public function getList($params = [])
    {
        // 分页数据
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 20;
        $queryDate = [];
        if (isset($params['pid'])) {
            $queryDate[] = ['pid', '=', $params['pid']];
        }
        // 排序数据
        $sort = $params['sort'] ?? 'created_at';
        $order = $params['order'] ?? 'desc';

        // 列表
        $list = ArticleCategoryModel::getCategoryList($queryDate, $page, $perPage, $sort, $order);
        // 总数
        $count = ArticleCategoryModel::getCategoryCount($queryDate);
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
        if (isset($params['tag'])) {
            $data['tag'] = $params['tag'];
        }
        if (isset($params['description'])) {
            $data['description'] = $params['description'];
        }
        if (isset($params['pid'])) {
            $data['pid'] = $params['pid'];
        }
        if (isset($params['is_show'])) {
            $data['is_show'] = $params['is_show'];
        }

        if (isset($params['id']) && $params['id'] !== 0) {
            // 修改
            $result = ArticleCategoryModel::query()->where(['id' => $params['id']])->update($data);
        } else {
            // 新增
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = ArticleCategoryModel::query()->insert($data);
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
        return ArticleCategoryModel::dropIds($ids);
    }

    /**
     * 获取顶级文章分类
     * @Cacheable(prefix="article_category_all", ttl=1000)
     * @return mixed
     */
    public function listAll()
    {
        return make(ArticleCategoryModel::class)->getTopList();
    }

    /**
     * 根据id 返回 信息
     *
     * @param array $ids
     *
     * @return  'id' => 'name'
     */
    public function getCategoryNameByIds($ids = [])
    {
        $list = ArticleCategoryModel::query()->whereIn('id', $ids)->select(['id', 'name'])->get()->toArray();
        $result = [];
        foreach ($list as $value) {
            $result[$value['id']] = $value['name'];
        }
        return $result;
    }
}
