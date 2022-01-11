<?php

class Account_info_model extends My_model
{
    /**
     * 資料表名稱
     */
    protected $table = "account_info";

    /**
     * 欄位資料
     */
    protected $tableColumns = [
        'id',
        'accounrt',
        'name',
        'sex',
        'birthday',
        'email',
        'remark',
        'date_create',
        'user_create',
        'date_update',
        'user_update',
        'date_delete',
        'user_delete',
        'rec_status',
    ];

    /**
     * 可使用欄位
     */
    public function fields()
    {
        return [
            'id',
            'accounrt',
            'name',
            'sex',
            'birthday',
            'email',
            'remark',
        ];
    }

    /**
     * 資料驗證
     * 
     * @return array
     */
    public function validate($column)
    {
        // 錯誤訊息
        $errorMsg = [];

        // 驗證 帳號、姓名、性別、生日、信箱 不為空
        $validateColumn = ['accounrt', 'name', 'sex', 'birthday', 'email'];
        foreach ($validateColumn as $value) {
            if (empty($column[$value])) {
                $errorMsg[] = "{$value}為必填資料";
            }
        }

        // 驗證帳號
        // 限制字串大小為 5~15，英文數字至少各出現一次
        $pattern = "/^(?=.*[A-Za-z])(?=.*\d)[a-zA-Z\d]{5,15}$/i";
        if (!preg_match($pattern, $column['accounrt'])) {
            $errorMsg[] = '帳號不符合規則';
        }

        // 驗證生日
        if (!$this->checkDateFormat($column['birthday'])) {
            $errorMsg[] = '生日不符合規則';
        }

        // 驗證信箱
        if (!filter_var($column['email'], FILTER_VALIDATE_EMAIL)) {
            $errorMsg[] = '信箱不符合規則';
        }

        return $errorMsg;
    }

    /**
     * 檢查日期格式
     *
     * @param string $date
     * @return void
     */
    private function checkDateFormat($date)
    {
        $isDate = false;

        // 日期格式
        if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
            // 是否為真日期
            $isDate = checkdate($parts[2], $parts[3], $parts[1]);
        }

        return $isDate;
    }
}
