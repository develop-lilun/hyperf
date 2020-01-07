<?php

namespace App\Model;

class ArticleTagModel extends BaseModel
{
    protected $table = 'article_tag';

    /**
     * 根据ids获取文章标签列表
     * @param $ids
     * @return array
     */
    public static function getArticleTagList(array $ids)
    {
        return self::query()->whereIn('id', $ids)->select(['id', 'name'])->get()->toArray();
    }

    /**
     * 查询分类标签全部
     * @return mixed
     */
    public static function getListAll()
    {
        return self::query()->select(['id', 'name'])->get()->toArray();
    }

    /**
     * 修改
     * @param array $where
     * @param array $update
     * @return int
     */
    public function set(array $where, array $update)
    {
        return ArticleTagModel::query()->where($where)->update($update);
    }
}
