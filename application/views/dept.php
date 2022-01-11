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
    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css" />

    <script src="<?= JS_DIR; ?>bootstrap/deptAjax.js"></script>

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
                <h1>部門管理</h1>
            </div>
        </div>

        <!-- Controll Form -->
        <div class="row">
            <div class="col-sm-4 form-group">
                <input type="text" class="form-control" id="select">
            </div>
            <div class="col-sm-4"><button id="select_btn" class="btn btn-primary">搜尋</button></div>

            <div class="col-sm-4"><button id="creat_btn" class="btn btn-warning float-right" data-toggle="modal" data-target="#myModal">新增</button></div>
        </div>

        <table id="table" class="table table-striped table-dark table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th id="d_code">代碼</th>
                    <th id="d_name">名稱</th>
                    <th id="d_level">層級</th>
                    <th id="date_start">開始日</th>
                    <th id="date_end">結束日</th>
                    <th id="remark">備註</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        <!-- 筆數 -->
        <div class="row float-left" id="countNumber">
        </div>

        <!-- Pagination -->
        <div class="row float-right">
            <ul class="pagination">
            </ul>
        </div>
    </div>

    <!-- The Modal -->
    <div class="modal" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">部門資料</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form id="form">
                        <!-- label 綁 for，在點擊 label 內容時，等效於點擊 input -->
                        <div class="form-group" hidden>
                            <label for="id">id</label>
                            <input type="text" class="form-control" id="id" name="id" value="">
                        </div>

                        <div class="form-group">
                            <label for="d_code">d_code</label>
                            <input type="text" class="form-control" id="d_code" name="d_code">
                        </div>

                        <div class="form-group">
                            <label for="d_name">d_name</label>
                            <input type="text" class="form-control" id="d_name" name="d_name">
                        </div>

                        <div class="form-group">
                            <label for="d_level">d_level</label>
                            <select id="d_level" name="d_level" class="form-control">
                                <option value="部">部</option>
                                <option value="處">處</option>
                                <option value="科">科</option>
                                <option value="室">室</option>
                                <option value="組">組</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="date_start">date_start</label>
                            <input type="date" class="form-control" id="date_start" name="date_start">
                        </div>

                        <div class="form-group">
                            <label for="date_end">date_end</label>
                            <input type="date" class="form-control" id="date_end" name="date_end">
                        </div>

                        <div class="form-group">
                            <label for="remark">remark</label>
                            <input type="text" class="form-control" id="remark" name="remark">
                        </div>


                    </form>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button id="button" type="submit" class="btn btn-primary float-right">Submit</button>
                </div>

            </div>
        </div>
    </div>
</body>

</html>