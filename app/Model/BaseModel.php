<?php

namespace App\Model;


use Hyperf\Database\Model\Events\Created;
use Hyperf\Database\Model\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;

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
    public static function getListContainer(array $queryDate, int $page, int $perPage, string $sort, string $order, array $field = ['*'])
    {
        return self::query()->where($queryDate)->forPage($page, $perPage)->select($field)->orderBy($sort,
            $order)->get()->toArray();
    }

    /**
     * 查询总数量
     * @param $queryDate
     * @return mixed
     */
    public static function getCount(array $queryDate)
    {
        return self::query()->where($queryDate)->count();
    }

    /**
     * 批量删除数据
     * @param array $ids
     * @return int|mixed
     */
    public static function dropIds(array $ids)
    {
        if ($ids == is_array($ids) && count($ids) > 0) {
            $return = self::query()->whereIn('id', $ids)->delete();
        } else {
            $return = self::query()->where(['id' => $ids])->delete();
        }
        return $return;
    }

    /**
     * 根据条件获取列表
     * @param $where
     * @param array $field
     * @return array
     */
    public static function getListByWhere(array $where, array $field = ['*'])
    {
        return self::query()->where($where)->get($field)->toArray();
    }

    /**
     * 根据条件In获取列表
     * @param $where
     * @param array $field
     * @return array
     */
    public static function getListByWhereIn(string $column, array $values, array $field = ['*'])
    {
        return self::query()->whereIn($column, $values)->get($field)->toArray();
    }


    /**
     * 根据条件获取第一条记录
     * @param $where
     * @param array $field
     * @return array
     */
    public static function getFirst(array $where, array $field = ['*'])
    {
        $result = self::query()->where($where)->first($field);
        return $result ? $result->toArray() : [];
    }

    /**
     * 根据ids 获取多条记录并以id为key
     * @param array $ids
     * @param array $field
     * @return array
     */
    public static function getListByIdsToKey(array $ids, array $field = ['*'])
    {
        if ($ids) {
            $result = self::query()->whereIn('id', $ids)->get($field)->toArray();
            return array_column($result, null, 'id');
        }
        return [];
    }

    /**
     * 根据ids 获取多条记录
     * @param array $ids
     * @param array $field
     * @return array
     */
    public static function getListByIds(array $ids, array $field = ['*'])
    {
        if ($ids) {
            return self::query()->whereIn('id', $ids)->get($field)->toArray();
        }
        return [];
    }

    /**
     * 根据id 获取单条记录
     * @param array $id
     * @param array $field
     * @return array
     */
    public static function getListById(array $id, array $field = ['*'])
    {
        if ($id) {
            $result = self::query()->whereIn('id', $id)->first($field);
            return $result ? $result->toArray() : [];
        }
        return [];
    }

    /**
     * 根据条件 获取单个字段的值
     * @param array $where
     * @param string $field
     * @return bool|\Carbon\CarbonInterface|float|int|mixed|string
     */
    public static function getValueByWhere(array $where, string $field)
    {
        return self::query()->where($where)->value($field);
    }

    /**
     * 根据id 获取单个字段的值
     * @param int $id
     * @param string $field
     * @return bool|\Carbon\CarbonInterface|float|int|mixed|string
     */
    public static function getValueById(int $id, string $field)
    {
        return self::query()->where(['id' => $id])->value($field);
    }
}
