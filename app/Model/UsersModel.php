<?php

namespace App\Model;


/**
 * 用户表
 * Class Users
 * @package App\Models
 */
class UsersModel extends BaseModel
{
    protected $table = 'users';

    /**
     * 积分等级规则
     */
    const LEVEL_INTEGRAL = [
        0 => ['integral' => 0],
        1 => ['integral' => 1000],
        2 => ['integral' => 2500],
        3 => ['integral' => 10000],
        4 => ['integral' => 20000],
        5 => ['integral' => 40000],
        6 => ['integral' => 90000],
        7 => ['integral' => 150000]
    ];

    /**
     *  用户手机号和密码
     * @param $mobile
     *
     * @return mixed
     */
    public function getUserInfo($mobile)
    {
        return self::query()->select(['mobile', 'password'])->where(['mobile' => $mobile])->get()->toArray();
    }


    /**
     * 用户手机号和密码
     * @return mixed
     */
    public function getUserInfoEvent($mobile)
    {
        return self::query()->select(['mobile', 'password', 'id'])->where(['mobile' => $mobile])->first();
    }


    /**
     *  排行榜
     *
     *
     * @param array $field
     * @param string $group_by
     * @param string $sort_field
     * @param string $sort_rule
     * @param int $offset
     * @param int $limit
     * @param array $whereAry
     *
     * @return mixed
     */
    public function top(array $field, $group_by, $sort_field, $sort_rule, $offset = 0, $limit = 6, $whereAry = [])
    {
        $data = self::query()
            ->select($field)
            ->groupBy($group_by)
            ->orderBy($sort_field, $sort_rule)
            ->where($whereAry)
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();

        return $data;
    }


    /**
     *  用户详情
     *
     * @return mixed
     */
    public function userDetails($id)
    {
        $field = [
            'id',
            'mobile',
            'nick',
            'sex',
            'sign',
            'wechat_img',
            'wechat',
            'email',
            'type',
            'status',
            'integral',
            'exponent'
        ];
        $whereAry = ['id' => $id];
        $data = self::query()->where($whereAry)->select($field)->get()->toArray();

        return $data;
    }


    /**
     * 获取用户粉丝量
     * @param $userId
     * @return int
     */
    public function getUserFansCount($userId)
    {
        $result = self::query()->where('user_id', $userId)->value('fans_num');

        return $result ?: 0;
    }

    /**
     * 查询用户信息
     * @param array $arrUids
     * @param array $field
     * @return mixed
     */
    public function getUserInfoByUid($arrUids = [], $field = ['user_id', 'mobile', 'nick', 'avatar'])
    {
        $this->userData = self::query()->select($field)->wherein('user_id', $arrUids)->get()->toArray();
        return $this->userData;
    }

    /*************** *************/

    /** 用户信息
     * @param array $field 查询的字段
     * @param array $whereAry 查询条件
     *
     * @return mixed
     */
    public function getUserList($field, $whereAry = [])
    {
        return UsersModel::query()->select($field)->where($whereAry)->get()->toArray();
    }
}
