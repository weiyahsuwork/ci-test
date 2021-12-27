<?php

/**
 * 部門資料管理Model
 * 
 * 提供通用函式 新增、讀取、更新、刪除、批次讀取、批次新增、批次更新、批次刪除 示範
 * 
 * @author Mars.Hung 2020-02-29
 */
class Dept_info_model extends CI_Model
{

    /**
     * 資料表名稱
     */
    protected $table = "dept_info";

    /**
     * 欄位資料
     */
    protected $tableColumns = [
        'd_id',
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

    public function __construct()
    {
        parent::__construct();

        // 載入資料連線
        $this->load->database();
    }

    /**
     * 取得資料 - 從主鍵
     * 
     * 本函式只能取出 rec_status==1 的資料
     * 
     * @param int $d_id 目標主鍵資料
     * @param string $col 輸出欄位
     * @return array
     */
    public function get($d_id, $col = '*')
    {
        return $this->db->select($col)->from($this->table)->where('d_id', $d_id)->where('rec_status', '1')->get()->result_array();
    }

    /**
     * 取得資料 - 從查詢條件
     * 
     * 本函式只能取出 rec_status==1 的資料
     * 
     * 格式：
     * $conditions = [
     *      '欄位名' => '欄位值string/int/array',
     * ];
     * 
     * @param array $conditions 查詢條件
     * @param string $col 輸出欄位
     * @return array
     */
    public function getBy($conditions = [], $col = '*')
    {
        // 查詢建構
        $query = $this->db->select($col)->from($this->table)->where('rec_status', '1');

        // 加入查詢條件
        foreach ($conditions as $key => $where) {
            if (is_array($where)) {
                // 加入陣列查詢條件
                $query->where_in($key, $where);
            } else {
                // 加入單一查詢條件
                $query->where($key, $where);
            }
        }

        // 執行查詢、取回資料並回傳
        return $query->get()->result_array();
    }

    /**
     * 新增資料
     *
     * @param array $data 部門資料
     * @return int
     */
    public function post($data)
    {
        // 過濾可用欄位資料
        $data = array_intersect_key($data, array_flip($this->tableColumns));

        // 移除主鍵欄位 - 新增時不帶入主鍵值，以便主鍵由sql自行增加
        unset($data['d_id']);

        // 寫入 date_create, user_create(未知，暫用0), rec_status
        $data['date_create'] = date('Y-m-d H:i:s');
        $data['user_create'] = 0;
        $data['rec_status'] = '1';

        // 移除 date_update, user_update, date_delete, user_delete
        unset($data['date_update']);
        unset($data['user_update']);
        unset($data['date_delete']);
        unset($data['user_delete']);

        // 寫入資料表
        $res = $this->db->insert($this->table, $data);

        // 寫入成功時回傳寫入主鍵鍵值，失敗時回傳 0
        return $res ? $this->db->insert_id() : 0;
    }

    /**
     * 更新資料 - 從主鍵
     *
     * @param array $data 部門資料
     * @return int
     */
    public function put($data)
    {
        // 過濾可用欄位資料
        $data = array_intersect_key($data, array_flip($this->tableColumns));

        $res = 0;

        // 檢查有無主鍵
        if (isset($data['d_id'])) {
            // 取出主鍵值並移除$data中主鍵欄位
            $d_id = $data['d_id'];
            unset($data['d_id']);

            // 寫入 date_update, user_update(未知，暫用0)
            $data['date_update'] = date('Y-m-d H:i:s');
            $data['user_update'] = 0;

            // 移除 date_create, user_create, date_delete, user_delete, rec_status
            unset($data['date_create']);
            unset($data['user_create']);
            unset($data['date_delete']);
            unset($data['user_delete']);
            unset($data['rec_status']);

            // 更新資料 - 成功時回傳主鍵鍵值，失敗時回傳 0
            $res = $this->db->where('d_id', $d_id)->update($this->table, $data) ? $d_id : 0;
        } else {
            // 報錯-沒有主鍵欄位
            throw new Exception('沒有主鍵欄位: d_id', 400);
        }

        return $res;
    }

    /**
     * 刪除資料 - 從主鍵
     * 
     * @param array|int $d_id 欲刪除的主鍵值
     * @param bool $forceDelete 是否強制刪除 false時為軟刪除
     * @return bool
     */
    public function delete($d_id, $forceDelete = false)
    {
        $d_id = (array) $d_id;

        // 刪除條件
        $this->db->where_in('d_id', $d_id);

        if ($forceDelete) {
            // 直接刪除 - CI SQL Builder有限定需有where才可以執行delete
            return $this->db->delete($this->table);
        } else {
            // 標記成刪除狀態 - 本練習中無法得知操作者id，暫不處理user_delete值
            $data['date_delete'] = date('Y-m-d H:i:s');
            $data['user_delete'] = 0;
            $data['rec_status'] = 0;

            return $this->db->update($this->table, $data);
        }
    }


    /**
     * 批次寫入資料 - 未完成，請補完
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
            unset($data['d_id']);

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
     * 批次更新資料 - 未完成，請補完
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
            if (isset($data['d_id'])){
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
            }else{
                // 報錯-沒有主鍵欄位
                throw new Exception('沒有主鍵欄位: d_id', 400);
            }

        }

        // 批次更新資料 - 成功時回傳更新列數，失敗時回傳 FALSE
        return $this->db->update_batch($this->table, $datas, 'd_id');
    }
}