<?php

namespace App\Model;

class ArticleModel extends BaseModel
{
    protected $table = 'article';

    protected $field = [
        'id',
        'title',
        'article_tag_ids',
        'article_category_id',
        'article_platform_id',
        'author',
        'thumb_pic_id',
        'praise_num',
        'comment_num',
        'read_num_true',
        'read_num',
        'collect_num',
        'share_num',
        'audit_status',
        'is_disables',
        'remark',
        'created_at',
        'updated_at',
        'push_time'
    ];

    // 发布平台
    const PUSH_PLATFORM_USER = 1;       // 发布平台-用户
    const PUSH_PLATFORM_SYSTEM = 2;     // 发布平台-系统后台
    const PUSH_PLATFORM_CAPTURE = 3;    // 发布平台-抓取

    // 审核状态
    const AUDIT_STATUS_PENDING = 0;     // 待审核
    const AUDIT_STATUS_SUCCESS = 1;     // 已审核
    const AUDIT_STATUS_ERROR = 2;       // 已驳回
    const AUDIT_STATUS_NEEDLESS = 3;    // 无需审核

    // 是否禁用状态
    const IS_DISABLES_OFF = 0;          // 否
    const IS_DISABLES_ON = 1;           // 是


    /**
     * 根据条件查询信息
     *
     * @param        $queryDate
     * @param        $page
     * @param        $perPage
     * @param        $sort
     * @param        $order
     * @param array $field
     * @param string $whereRaw
     *
     * @return mixed
     */
    public static function getList($queryDate, $page, $perPage, $sort, $order, $field = [], $whereRaw = '')
    {
        $field = $field ?: ['*'];
        return self::query()->where($queryDate)->whereRaw($whereRaw)->forPage($page,
            $perPage)->select($field)->orderBy($sort, $order)->get()->toArray();
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
        $info = self::query()->where(['id' => $id, 'is_disables' => self::IS_DISABLES_OFF])
            ->whereIn('audit_status',[self::AUDIT_STATUS_SUCCESS, self::AUDIT_STATUS_NEEDLESS])
            ->select($field)
            ->first();
        return $info;
    }

    /**
     * 计算用户文章阅读量总和和收藏量总和
     * @param $userId
     * @return array
     */
    public static function getArticleReadAndCollectSum($userId)
    {
        $result = self::query()->where(['user_id' => $userId, 'is_disables' => self::IS_DISABLES_OFF])
            ->whereIn('audit_status', [self::AUDIT_STATUS_SUCCESS, self::AUDIT_STATUS_NEEDLESS])
            ->selectRaw("sum(read_num) as read_num_count, sum(collect_num) as collect_num_count")
            ->first(['read_num_count', 'collect_num_count']);

        return [
            'read_num_count' => $result->read_num_count ?: 0,
            'collect_num_count' => $result->collect_num_count ?: 0,
        ];
    }

    /**
     * 查询已存在的文章详情信息
     * @param int $id
     * @param array $field
     * @return array
     */
    public static function getFirst(int $id, array $field = ['*'])
    {
        $time = date('Y-m-d H:i:s');
        $result = self::query()->where(['id' => $id, 'is_disables' => 0])
            ->whereIn('audit_status', [self::AUDIT_STATUS_SUCCESS, self::AUDIT_STATUS_NEEDLESS])
            ->whereRaw("(push_time is NULL or push_time <= '$time')")
            ->first($field);
        return $result ? $result->toArray() : [];
    }
}
