<?php

declare(strict_types=1);

namespace App\Validates\Api;


use App\Model\ArticleModel;
use App\Validates\BaseValidate;
use Hyperf\Validation\Rule;

class ArticleValidate extends BaseValidate
{
    public function listValidate($request)
    {
        $rules = [
            'page' => 'integer|min:0',
            'per_page' => 'integer|min:0',
            'article_category_id' => 'integer',
        ];
        $rest_validate = $this->validate($request, $rules);
        if ($rest_validate === true) {
            return $this->baseSucceed();
        } else {
            return $this->baseFailed($this->message);
        }
    }


    /**
     * 添加提交验证
     *
     * @param $requestData
     *
     * @return array
     */
    public function addValidate($request)
    {
        $rules = [
            'title' => 'required|string|between:1,200|unique:article',
            'article_platform_id' => 'required|integer',
            'article_tag_ids' => 'required|array|between:1,3',
            'thumb_pic_id' => 'required|integer',
            'content' => 'required|string|between:0,50000',
            'integral_num' => 'integer|between:0,200',
            'no_name' => Rule::in([0, 1]),
        ];
        $rest_validate = $this->validate($request, $rules);
        if ($rest_validate === true) {
            return $this->baseSucceed();
        } else {
            return $this->baseFailed($this->message);
        }
    }

    /**
     * 文章详情
     *
     * @param $requestData
     *
     * @return array
     */
    public function infoValidate($requestData)
    {
        $rules = [
            'id' => [
                'required',
                'integer',
                'min:1'
            ],
            'invitation' => 'string|between:1,28'
        ];
        $rest_validate = $this->validate($requestData, $rules);
        if ($rest_validate === true) {
            // 是否需要邀请码查看
            $articleInfo = ArticleModel::getFirst($requestData['id'], ['invitation', 'user_id']);

            // 文章不存在
            if (!$articleInfo) {
                return $this->baseFailed('-1017');
            }

            // 本人不需要验证邀请码
            if ((isset($requestData['user_info']['user_id']) && $articleInfo['user_id'] == $requestData['user_info']['user_id'])) {
                return $this->baseSucceed();
            }

            // 需要邀请才能查看
            if ($articleInfo['invitation'] && !isset($requestData['invitation'])) {
                return $this->baseFailed('-1042');
            }

            // 验证邀请码
            if ($articleInfo['invitation'] && isset($requestData['invitation']) && $requestData['invitation'] !== $articleInfo['invitation']) {
                return $this->baseFailed('-1045');
            }

            return $this->baseSucceed();
        } else {
            return $this->baseFailed($this->message);
        }
    }


    /**
     * 统一验证处理
     *
     * @param $request_data
     * @param $rules
     *
     * @return bool
     */
    protected function validate($requestData, $rules)
    {
        $message = [
            'page.integer' => '-1001',
            'page.min' => '-1002',
            'per_page.integer' => '-1003',
            'per_page.min' => '-1004',
            'article_category_id.integer' => '-1005',
            'id.required' => '-2007',
            'id.integer' => '-2008',
            'id.min' => '-2009',
            'title.required' => '-2010',
            'title.string' => '-2011',
            'title.between' => '-2012',
            'article_platform_id.required' => '-1006',
            'article_platform_id.integer' => '-1007',
            'article_tag_ids.required' => '-1008',
            'article_tag_ids.array' => '-1009',
            'article_tag_ids.between' => '-1010',
            'thumb_pic_id.integer' => '-1011',
            'thumb_pic_id.required' => '-1012',
            'content.required' => '-2019',
            'content.string' => '-2020',
            'content.between' => '-2021',
            'integral_num.integer' => '-1013',
            'integral_num.between' => '-1014',
            'no_name.in' => '-1015',
            'title.unique' => '-1016',
            'id.exists' => '-1017',
            'invitation.string' => '-1043',
            'invitation.between' => '-1044',
        ];
        $validator = $this->validationFactory->make($requestData, $rules, $message);
        if ($validator->fails()) {
            $this->message = $validator->errors()->first();
            return false;
        }
        return true;
    }
}