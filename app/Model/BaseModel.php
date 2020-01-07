<?php

namespace App\Model;


use Hyperf\Database\Model\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;

    protected $field = ['*'];

    /**
     * 根据条件查询数据
     *
     * @param $queryDate
     * @param $page
     * @param $perPage
     * @param $sort
     * @param $order
     *
     * @return mixed
     */
    public static function getList($queryDate, $page, $perPage, $sort, $order, $field = [])
    {
        $field = $field ?: ['*'];
        return self::query()->where($queryDate)->forPage($page, $perPage)->select($field)->orderBy($sort,
            $order)->get()->toArray();
    }

    /**
     * 查询总数量
     *
     * @param $queryDate
     *
     * @return mixed
     */
    public static function getCount($queryDate)
    {
        return self::query()->where($queryDate)->count();
    }

    /**
     * 批量删除数据
     *
     * @param $ids int|array
     */
    public function dropIds($ids)
    {
        if ($ids == is_array($ids) && count($ids) > 0) {
            $return = self::query()->whereIn('id', $ids)->delete();
        } else {
            $return = self::query()->where(['id' => $ids])->delete();
        }
        return $return;
    }

}
