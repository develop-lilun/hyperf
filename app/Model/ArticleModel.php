<?php

namespace App\Model;

class ArticleModel extends BaseModel
{
    protected $table = 'article';

    protected $field = ['id', 'title', 'article_tag_ids', 'article_category_id', 'article_platform_id', 'author', 'thumb_pic_id', 'praise_num', 'comment_num',
        'read_num_true', 'read_num', 'collect_num', 'share_num', 'audit_status', 'is_disables', 'remark', 'created_at', 'updated_at', 'push_time'];

    const PUSH_PLATFORM_USER = 1;   // 发布平台-用户
    const PUSH_PLATFORM_SYSTEM = 2;     // 发布平台-系统后台
    const PUSH_PLATFORM_CAPTURE = 3;        // 发布平台-抓取


    /**
     * 根据条件查询信息
     *
     * @param        $queryDate
     * @param        $page
     * @param        $perPage
     * @param        $sort
     * @param        $order
     * @param array  $field
     * @param string $whereRaw
     *
     * @return mixed
     */
    public static function getList($queryDate, $page, $perPage, $sort, $order, $field = [], $whereRaw = '')
    {
        $field = $field ?: ['*'];
        return self::query()->where($queryDate)->whereRaw($whereRaw)->forPage($page, $perPage)->select($field)->orderBy($sort, $order)->get()->toArray();
    }


    /**
     * 查询总数量
     *
     * @param $queryDate
     *
     * @return mixed
     */
    public static function getCount($queryDate, $whereRaw = '')
    {
        return self::query()->where($queryDate)->whereRaw($whereRaw)->count();
    }

    /**
     * 根据用户id查询用户信息
     * @param $id
     * @param array $field
     * @return mixed
     */
    public static function getFirstById($id, $field = ['*'])
    {
        $info = self::query()->where(['id' => $id, 'is_disables' => 0])->whereIn('audit_status', [1, 3])->select($field)->first();
        return $info;
    }

    /**
     * 计算用户文章阅读量总和和收藏量总和
     * @param $userId
     * @return array
     */
    public static function getArticleReadAndCollectSum($userId)
    {
        $result = self::query()->where(['user_id' => $userId, 'is_disables' => 0])
            ->whereIn('audit_status', [1,3])
            ->selectRaw("sum(read_num) as read_num_count, sum(collect_num) as collect_num_count")
            ->first(['read_num_count', 'collect_num_count']);

        return [
            'read_num_count' => $result->read_num_count ?: 0,
            'collect_num_count' => $result->collect_num_count ?: 0,
        ];
    }


}
