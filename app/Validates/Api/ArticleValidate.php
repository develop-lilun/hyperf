<?php

declare(strict_types=1);

namespace App\Validates\Api;


use App\Validates\BaseValidate;

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
     * 统一验证处理
     *
     * @param $request_data
     * @param $rules
     *
     * @return bool
     */
    protected function validate($request_data, $rules)
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
        $validator = $this->validationFactory->make($request_data, $rules, $message);
        if ($validator->fails()) {
            $this->message = $validator->errors()->first();
            return false;
        }
        return true;
    }
}