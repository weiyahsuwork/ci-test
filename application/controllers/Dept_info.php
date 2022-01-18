<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dept_info extends CI_Controller
{
    const LIMIT = 5;

    /**
     * 可用查詢欄位
     *
     * @var array
     */
    private $_columms = [
        'id',
        'd_code',
        'd_name',
        'd_level',
        'date_start',
        'date_end',
        'remark'
    ];

    public function __construct()
    {
        parent::__construct();

        // 載入部門資料庫
        $this->load->model('Dept_info_model');
    }

    public function index()
    {
        $this->load->view('dept');
    }

    /**
     * AJAX controller.
     */
    public function ajax($id = null)
    {
        // 參數處理
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        // 取得匯入資料
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        // 過濾可輸入資料
        $data = array_intersect_key($data, array_flip($this->_columms));

        // 行為分類
        switch ($method) {
            case 'POST':
                // 新增一筆資料
                $this->_create($data);
                break;
            case 'GET':
                if (empty($id)) {
                    // 讀取全部資料
                    $this->_list();
                } else {
                    // 讀取一筆資料
                    $this->_read($id);
                }
                break;
            case 'PATCH':
            case 'PUT':
                // 更新一筆資料
                $this->_update($data, $id);
                break;
            case 'DELETE':
                if (empty($id)) {
                    // 錯誤
                    http_response_code(404);
                    echo 'No Delete ID';
                    exit;
                } else {
                    // 刪除一筆資料
                    $this->_delete($id);
                }
                break;
        }
    }

    /**
     * 新增一筆
     *
     * @param array $data
     * @return array
     */
    protected function _create($data)
    {
        // 驗證資料
        if ($errorMsg = $this->Dept_info_model->validate($data)) {
            // 錯誤
            http_response_code(404);
            echo json_encode($errorMsg);
            exit;
        }

        // 使用Dept_info_model中的post函式新增資料
        if ($this->Dept_info_model->post($data)) {
            echo json_encode("Success");
        } else {
            http_response_code(404);
            echo 'Fail';
        }
    }

    /**
     * 讀取全部
     *
     * @return array
     */
    protected function _list()
    {
        // 搜尋條件 Like 子句
        $likes = [];

        // 排序條件
        $sort = [];

        // 取得頁數
        $page = $this->input->get('page');

        // 取得搜尋條件
        $search = $this->input->get('search');

        // 取得排序條件
        $sortBy = $this->input->get('sortBy');
        if ($sortBy) {
            $sort['column'] = $sortBy;
            $sort['type'] = $this->input->get('sortType');
        }

        // 表格查詢起始位置
        $offset = ($page - 1) * self::LIMIT;

        // 組合欄位與查詢條件
        if ($search !== '') {
            $likes = array_fill_keys($this->_columms, $search);

            // 移除主鍵欄位 - 不查詢主鍵資料
            unset($likes['id']);
        }

        $data = $this->Dept_info_model->getBy([], $this->_columms, self::LIMIT, $offset, $sort, $likes);

        // 取得總筆數
        $count = $this->Dept_info_model->count();

        // 輸出JSON
        echo json_encode([
            'data' => $data,
            'total_page' => ceil($count / self::LIMIT),
            'page' => $page,
        ]);
    }

    /**
     * 讀取一筆
     *
     * @param int $id 目標資料id
     * @return array
     */
    protected function _read($id)
    {
        $data = $this->Dept_info_model->get($id);

        // 輸出JSON
        echo json_encode($data);
    }

    /**
     * 更新一筆
     *
     * @param array $data 資料內容
     * @param int $id 目標資料id
     * @return array
     */
    protected function _update($data, $id)
    {
        // 驗證資料
        if ($errorMsg = $this->Dept_info_model->validate($data)) {
            // 錯誤
            http_response_code(404);
            echo json_encode($errorMsg);
            exit;
        }

        $data = $this->Dept_info_model->put($data);

        // 輸出JSON
        echo json_encode($data);
    }

    /**
     * 刪除一筆
     *
     * @param int $id 目標資料id
     * @return string
     */
    protected function _delete($id)
    {
        if ($this->Dept_info_model->delete($id)) {
            echo json_encode("Success");
        } else {
            http_response_code(404);
            echo 'Fail';
        }
    }

    /**
     * 匯入表格
     *
     * @return void
     */
    public function import()
    {
        // IO物件建構
        $io = new \marshung\io\IO();
        // 匯入處理 - 取得匯入資料
        $datas = $io->import($builder = 'Excel', $fileArgu = 'fileupload');
        // 取得匯入config名子
        $configName = $io->getConfig()->getOption('configName');
        // 取得有異常有下拉選單內容
        $mismatch = $io->getMismatch();
        $insertData = [];
        $updateData = [];
        $errorMsg = [];

        foreach ($datas as $data) {
            // 驗證資料
            // validate() $errorMsg[] = [...]

            if ($data['id'] === '') {
                $insertData[] = $data;
            } else {
                $updateData[] = $data;
            }
        }

        if (empty($errorMsg)) {
            if ($insertData) {
                // 批次新增
                $this->Dept_info_model->postBatch($insertData);
            }

            if ($updateData) {
                // 批次修改
                $this->Dept_info_model->putBatch($updateData);
            }

            echo json_encode("Success");
        } else {
            http_response_code(404);
            echo json_encode($errorMsg);
        }
    }

    /**
     * 匯出表格
     */
    public function export()
    {
        // 取得原始資料
        $data = $this->Dept_info_model->getBy([], $this->_columms);

        $defined = [];
        foreach ($this->_columms as $key => $columm) {
            $key = "t{$key}";
            $defined[$columm] = [
                'key' => $columm,
                'value' => $columm,
                'col' => '1',
                'row' => '1',
                'style' => array(),
                'class' => '',
                'default' => '',
                'list' => ''
            ];
        }
        // 標題1
        $title1 = array(
            'config' => array(
                'type' => 'title',
                'name' => 'title1',
                'style' => array(
                    'font-size' => '16'
                ),
                'class' => ''
            ),
            'defined' => $defined,
        );

        // 內容
        $content = array(
            'config' => array(
                'type' => 'content',
                'name' => 'content',
                'style' => array(),
                'class' => ''
            ),
            'defined' => $defined,
        );

        // IO物件建構
        $io = new \marshung\io\IO();

        // 手動建構相關物件
        $io->setConfig()
            ->setBuilder()
            ->setStyle();

        // 載入外部定義
        $conf = $io->getConfig()
            ->setTitle($title1)
            ->setContent($content);

        // 必要欄位設定 - 提供讀取資料時驗証用 - 有設定，且必要欄位有無資料者，跳出 - 因各版本excel對空列定義不同，可能編輯過列，就會產生沒有結尾的空列，導致在讀取excel時有讀不完狀況。
        $conf->setOption([
            'u_no'
        ], 'requiredField');

        // 匯出處理 - 建構匯出資料 - 手動處理
        $io->setData($data)->exportBuilder();

        echo json_encode("Success");
    }
}
