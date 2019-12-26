<?php

declare(strict_types=1);

namespace App\Validates;


use Hyperf\Validation\Rule;

class IndexValidate extends BaseValidate
{
    public function foo($request)
    {
        $rules = [
            'page' => 'integer|min:0',
            'per_page' => 'integer|min:0',
            'keyword' => [
                'required',
                'string',
                'between:1,50',
            ],
            'type' => Rule::in([1,2,3,4,5,6,9])
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
            'keyword.required' => '-6101',
            'keyword.string' => '-6102',
            'keyword.between' => '-6103',
            'type.in' => '-6104',
        ];
        $validator = $this->validationFactory->make($request_data, $rules, $message);
        if ($validator->fails()) {
            $this->message = $validator->errors()->first();
            return false;
        }
        return true;
    }
}