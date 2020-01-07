<?php


namespace App\Services;


use App\Model\UsersModel;

class UserServices
{
    /**
     * 用户信息
     * array $ids [1,2]
     * @return array
     */
    public function getUserList($ids, $field = [])
    {
        if(!$field){
            $field = ['user_id', 'nick', 'avatar'];
        }
        $list = UsersModel::query()->whereIn('user_id', $ids)->select($field)->get()->toArray();
        $result = [];
        foreach ($list as $key => $value){

            $result[$value['user_id']] = $value;
            if (isset($value['avatar'])){
                $result[$value['user_id']]['face_url'] = $value['avatar'];
            }
            if(isset($value['nick'])){
                $result[$value['user_id']]['user_name'] = $value['nick'];
            }
        }
        return $result;
    }
}