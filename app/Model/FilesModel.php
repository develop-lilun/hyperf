<?php

namespace App\Model;

/**
 * 文件表
 * Class File
 * @package App\Models
 */
class FilesModel extends Model
{
    protected $table = 'file';

    public $timestamps = false;

    /**
     * 创建图片
     *
     * @param array $data
     */
    public static function createData($data = [])
    {
        $insertData = [
            'ip' => isset($data['ip']) ? $data['ip'] : '',
            'original_name' => $data['original_name'] ?: '',
            'size' => $data['size'] ?: '',
            'type' => $data['type'] ?: '',
            'storage_position' => $data['storage_position'] ?: '',
            'domain' => $data['domain'] ?: '',
            'file_path' => $data['file_path'] ?: '',
            'storage_name' => $data['storage_name'] ?: '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        return self::query()->insertGetId($insertData);
    }

    /**
     * 根据id 查询domain、file_path
     *
     * @param $id
     *
     * @return mixed
     */
    public static function getById($id = 0)
    {
        return self::query()->select(['domain', 'file_path'])->find($id);
    }

    /**
     * 根据id 查询多个domain、file_path
     *
     * @param array $ids
     *
     * @return mixed
     */
    public static function getByIds($ids = [])
    {
        return self::query()->whereIn('id', $ids)->select(['id', 'domain', 'file_path'])->get();
    }

    public static function getByIdsData($ids = [])
    {
        return self::query()->whereIn('id', $ids)->get();
    }
}
