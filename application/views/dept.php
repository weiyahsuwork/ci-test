<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Bootstrap 4 Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/vendor/bower-asset/bootstrap/dist/css/bootstrap.min.css">
    <script src="/vendor/npm-asset/jquery/dist/jquery.min.js"></script>
    <script src="/vendor/npm-asset/popper.js"></script>
    <script src="/vendor/bower-asset/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="vendor/npm-asset/axios/dist/axios.min.js"></script>
    <script src="vendor/bower-asset/bootstrap4-dialog-gedmarc/dist/js/bootstrap-dialog.js"></script>

    <script src="<?= JS_DIR; ?>bootstrap/crud-es5.js"></script>

    <style>
        .float-right {
            float: right;
        }
    </style>

</head>

<body>
    <div class="container">
        <!-- Title -->
        <div class="row">
            <div class="col-sm-12">
                <h1 id="page-title">部門管理</h1>
            </div>
        </div>

        <!-- Controll Form -->
        <div class="row">
            <div class="col-sm-4 form-group">
                <input type="text" class="form-control" id="select">
            </div>
            <div class="col-sm-4"><button id="select_btn" class="btn btn-primary">搜尋</button></div>

            <div class="col-sm-4"><button id="add-btn" class="btn btn-warning float-right" data-toggle="modal" data-target="#myModal">新增</button></div>
        </div>

        <table id="table" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th width="25%"></th>
                    <th id="d_code" width="10%">代碼</th>
                    <th id="d_name" width="15%">名稱</th>
                    <th id="d_level" width="10%">層級</th>
                    <th id="date_start" width="15%">開始日</th>
                    <th id="date_end" width="15%">結束日</th>
                    <th id="remark" width="10%">備註</th>
                </tr>
            </thead>
            <!-- Data List -->
            <tbody>
            </tbody>
        </table>

        <!-- 筆數 -->
        <div class="row float-left" id="countNumber">
        </div>

        <!-- Pagination -->
        <div id='pagination' class="row float-right">
            <ul class="pagination">
            </ul>
        </div>
    </div>

</body>

</html>

<script>
    $(document).ready(function() {
        // 產生頁面主題
        $('body').find('#page-title').text(window.mainPage.pageTitle);

        // 新增使用者資料
        $('#add-btn').on('click', function(e) {
            e.preventDefault();

            // 呼叫頁面封裝內的開啟彈窗方法
            window.mainPage.showEditDialog({
                onSave: function(dialog, formData) {
                    // 驗證資料
                    let errorMessage = this.validate(dialog);

                    if (errorMessage.length > 0) {
                        alert(errorMessage);
                        return false;
                    } else {
                        axios.post(window.mainPage.api.userInfo, {
                            d_code: dialog.initSelector.$d_code.val(),
                            d_name: dialog.initSelector.$d_name.val(),
                            d_level: dialog.initSelector.$d_level.val(),
                            date_start: dialog.initSelector.$date_start.val(),
                            date_end: dialog.initSelector.$date_end.val(),
                            remark: dialog.initSelector.$remark.val(),
                        }).then(function(response) {
                            if (response.status && response.status === 200) {
                                window.mainPage.refetchTable();
                            }
                        }).catch(function(error) {
                            console.log(error);
                        });
                    }
                },
            });
        });

        // 綁定搜尋條件事件
        $('#select_btn').on('click', function() {
            let searchValue = $('#select').val();
            window.mainPage.condition.search = searchValue;

            window.mainPage.refetchTable();
        });

        // 綁定排序表格事件
        $('th:not(:first-child)').on('click', function() {
            // 如果為第二次點擊，則為相反排序
            if (window.mainPage.condition.sortBy === this.id) {
                window.mainPage.condition.sortType = window.mainPage.condition.sortType === 'asc' ? 'desc' : 'asc';
            } else {
                window.mainPage.condition.sortType = 'asc';
            }
            // 排序欄位
            window.mainPage.condition.sortBy = this.id;

            window.mainPage.refetchTable();
        });
    });
</script>