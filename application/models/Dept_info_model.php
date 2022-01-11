<?php

/**
 * 部門資料管理Model
 * 
 * 提供通用函式 新增、讀取、更新、刪除、批次讀取、批次新增、批次更新、批次刪除 示範
 * 
 * @author Mars.Hung 2020-02-29
 */
class Dept_info_model extends My_model
{
    /**
     * 資料表名稱
     */
    protected $table = "dept_info";

    /**
     * 欄位資料
     */
    protected $tableColumns = [
        'id',
        'd_code',
        'd_name',
        'd_level',
        'date_start',
        'date_end',
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
     * 資料驗證
     * 
     * @return array
     */
    public function validate($column)
    {
        // 錯誤訊息
        $errorMsg = [];

        // 驗證 代碼、名稱、部門、起始日 不為空
        $validateColumn = ['d_code', 'd_name', 'd_level', 'date_start'];
        foreach ($validateColumn as $value) {
            if (empty($column[$value])) {
                $errorMsg[] = "{$value}為必填資料";
            }
        }

        return $errorMsg;
    }

    /**
     * 批次寫入資料
     *
     * 整批處理時，有一筆錯誤，整批都不可以處理
     * 
     * @param array $datas
     * @return void
     */
    public function postBatch($datas)
    {
        foreach ($datas as $key => $data) {
            // 過濾可用欄位資料
            $data = array_intersect_key($data, array_flip($this->tableColumns));

            // 移除主鍵欄位 - 新增時不帶入主鍵值，以便主鍵由sql自行增加
            unset($data['id']);

            // 寫入 date_create, user_create(未知，暫用0), rec_status
            $data['date_create'] = date('Y-m-d H:i:s');
            $data['user_create'] = 0;
            $data['rec_status'] = 1;

            // 移除 date_update, user_update, date_delete, user_delete
            unset($data['date_update']);
            unset($data['user_update']);
            unset($data['date_delete']);
            unset($data['user_delete']);

            // 將新資料取代舊資料
            $datas[$key] = $data;
        }

        // 批次寫入資料表 - 成功時回傳插入列數，失敗時回傳 FALSE
        return $this->db->insert_batch($this->table, $datas);
    }

    /**
     * 批次更新資料
     * 
     * 整批處理時，有一筆錯誤，整批都不可以處理
     * 
     * @param array $datas
     * @return void
     */
    public function putBatch($datas)
    {
        foreach ($datas as $key => $data) {
            // 過濾可用欄位資料
            $data = array_intersect_key($data, array_flip($this->tableColumns));

            // 檢查有無主鍵
            if (isset($data['id'])) {
                // 寫入 date_update, user_update(未知，暫用0)
                $data['date_update'] = date("Y-m-d H:i:s");
                $data['user_update'] = 0;

                // 移除 date_create, user_create, date_delete, user_delete, rec_status
                unset($data['date_create']);
                unset($data['user_create']);
                unset($data['date_delete']);
                unset($data['user_delete']);
                unset($data['rec_status']);

                // 將新資料取代舊資料
                $datas[$key] = $data;
            } else {
                // 報錯-沒有主鍵欄位
                throw new Exception('沒有主鍵欄位: id', 400);
            }
        }

        // 批次更新資料 - 成功時回傳更新列數，失敗時回傳 FALSE
        return $this->db->update_batch($this->table, $datas, 'id');
    }
}
