<?php

namespace App\Services;



use App\Model\FilesModel;

class UploadService
{

    /**
     * 获取单图片地址
     * @param int $id
     * @return mixed|string
     */
    public function getFileIdToUrl(int $id = 0)
    {
        $fileData = FilesModel::getById($id);
        $url = $fileData ? $fileData->domain . $fileData->file_path : '';
        return $url;
    }

    /**
     * 获取多图片地址
     *
     * @param array $ids
     *
     * @return array|bool
     */
    public function getFileIdsToUrl(array $ids = [])
    {
        $result = [];
        if (!$ids) {
            return $result;
        }

        $fileData = FilesModel::getByIds($ids);
        foreach ($fileData as $v) {
            $result[$v->id] = $v->domain . $v->file_path;
        }
        return $result;
    }

}
