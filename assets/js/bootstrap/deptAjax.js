// IIFE 立即執行函式
(function(window, document, $, undefined) {
  // 使用嚴格模式
  'use strict';

  // DOM下載完後執行
  $(document).ready(function() {
    // init this page
    window.Page = window.Page || new function() {}();
    window.Page[name] = obj();
  });

  // Class Name
  var name = 'bootstrap';
  // Version
  var version = '1';
  // Default options
  var defaults = {};

  /**
      * *************** Object Build ***************
      */

  // Define a local copy of Object
  var obj = function(options) {
    return new obj.fn.init(options);
  };

  // Prototype arguments
  obj.fn = obj.prototype = {
    // Object Name
    _name: name,

    // Default options
    _defaults: defaults,

    // AJAX URL
    _ajaxUrls: {
      deptUrl: '/dept_info',
      deptApi: '/dept_info/ajax',
    },
  };

  /**
      * Javascript物件
      */
  obj.fn.init = function(options) {
    /**
      * *************** Object Argument Setting ***************
      */
    var self = this;
    // 預設參數
    var _options = options || {};

    /**
     * 全域使用變數
     */
    // 總頁數
    var totalPage = 0,
      // 目前頁數
      currentPage = 0,
      // 搜尋條件
      condition = {};

    /**
    * 建構子
    */
    var _construct = function() {
      _initialize();
    };

    /**
    * 解構子
    */
    var _destruct = function() {};

    /**
     * 初始化
     */
    var _initialize = function() {
      // 初始化表格筆數
      $('#countNumber').text('0筆');

      /**
       * 初始化表格搜尋條件
       */
      // 頁數
      condition.page = 1;
      // 搜尋字串
      condition.search = '';
      // 排序欄位
      condition.sortBy = '';
      // 排序方式
      condition.sortType = 'asc';

      // 顯示表格
      _listTable(condition);

      /**
       * 事件綁定
       */
      _evenBind();
    };

    /**
      * 事件綁定
      */
    var _evenBind = function() {
      // 表單送出
      $('#button').on('click', function() {
        // 取得 序列化表單
        let serializedData = $('#form').serialize();

        // 驗證表單資料
        if (_validate()) {
          // 取得 id
          let id = $('#id').attr('value');

          // 如果沒有 id 為新增
          if (id === '') {
            /**
           * 新增一筆
           */
            $.ajax({
              // 傳送方法
              method: 'POST',
              // 目標網址
              url: self._ajaxUrls.deptApi,
              // 傳送資料
              data: serializedData,
              // 回傳資料格式
              dataType: 'json',
            })
              .done(function(data) {
                // 關閉彈窗
                $('#myModal').modal('hide');
                // 重製表單
                $('#form').trigger('reset');
              })
              .fail(function(jqXHR) {});
          } else {
            // 如果有 id 為修改
            /**
           * 更新一筆
           */
            $.ajax({
              method: 'PUT',
              url: self._ajaxUrls.deptApi + '/' + id,
              data: serializedData,
              dataType: 'json',
            }).done(function(data) {
              // 關閉彈窗
              $('#myModal').modal('hide');
              // 重製表單
              $('#form').trigger('reset');
            });
          }
        }
      });

      // 搜尋按鈕
      $('#select_btn').on('click', function() {
        // 清除 table
        $('tbody').children().remove();
        // 清除分頁
        $('.pagination').children().remove();

        let searchValue = $('#select').val();
        condition.search = searchValue;

        _listTable(condition);
      });

      // 排序表格
      $('th').on('click', function() {
        // 清除 table
        $('tbody').children().remove();
        // 清除分頁
        $('.pagination').children().remove();

        condition.sortBy = this.id;
        condition.sortType = condition.sortType === 'asc' ? 'desc' : 'asc';
        _listTable(condition);
      });

      // 點擊垃圾桶刪除
      $('.fa-trash').on('click', function() {
        // 刪除資料陣列
        var deleteData = {};

        // 取得需要刪除的 id 陣列
        $(':checkbox:checked').each(function(i) {
          deleteData[i] = $(this).val();
        });

        if (confirm('確定要刪除嗎?') == true) {
          /**
             * 刪除整批
             */
          $.ajax({
            method: 'DELETE',
            url: self._ajaxUrls.deptUrl + '/deleteBatch',
            data: deleteData,
            dataType: 'json',
          })
            .done(function(data) {
              // 清除 table
              $('tbody').children().remove();
              // 清除分頁
              $('.pagination').children().remove();
              // 顯示 table
              _listTable(condition);
            })
            .fail(function(jqXHR) {});
        }
      });
    };

    // 顯示表格
    var _listTable = function(e) {
      /**
       * 讀取全部
       */
      $.ajax({
        method: 'GET',
        url: self._ajaxUrls.deptApi + '?' + $.param(e),
        dataType: 'json',
      }).done(function(data) {
        /**
         * 繪製畫面
         */
        // 建立表格
        _buildTable(data);

        // 建立分頁
        _buildPagination(data);

        /**
         * 綁定事件
         */
        // 綁定表格編輯事件
        _addTableEvent();

        // 綁定分頁事件
        _addPaginationEvent();
      });

      /**
       * 建立表格內容
       */
      var _buildTable = function(data) {
        // 建立表格繪製變數
        var tmp, tbody, tr, td;

        tmp = $('<div></div>');

        data.data.forEach(function(row) {
          // 建立標題
          tr = $('<tr name=' + row.id + '></tr>').appendTo(tmp);
          // 建立checkbox、鉛筆 icon
          td = $(
            '<td><input type="checkbox" value="' +
              row.id +
              '"> <i class="fas fa-pen color_green ml-3" data-toggle="modal" data-target="#myModal"></i></td>'
          ).appendTo(tr);
          $.each(row, function(key, value) {
            // 跳過主鍵不顯示
            if (key === 'id') {
              return;
            }

            // 建立表格欄位內容
            td = $('<td>' + value + '</td>').appendTo(tr);
          });
        });

        // 取得table元件
        tbody = $('.table tbody');
        // 將暫存容器內容移至table元件
        tmp.children().appendTo(tbody);

        // 修改筆數
        $('#countNumber').text(data.data.length + '筆');
      };

      /**
       * 綁定分頁
       */
      var _buildPagination = function(data) {
        // 建立分頁繪製變數
        var tmp, pagination, page, li;

        tmp = $('<div></div>');
        // 建立往前按鈕
        $(
          '<li class="page-item" id="previous"><a class="page-link" href="#">Previous</a></li>'
        ).appendTo(tmp);

        // 修改表格總頁數
        totalPage = data.total_page;

        // 建立分頁按鈕
        for (let i = 0; i < totalPage; i++) {
          page = i + 1;
          li = $(
            '<li class="page-item" value="' +
              page +
              '"><a class="page-link" href="#">' +
              page +
              '</a></li>'
          ).appendTo(tmp);

          // 如果為當前頁面
          if (page === parseInt(data.page)) {
            currentPage = page;
            li.addClass('active');
          }
        }

        // 建立往後按鈕
        $('<li class="page-item" id="next"><a class="page-link" href="#">Next</a></li>').appendTo(
          tmp
        );

        // 取得table元件
        pagination = $('.pagination');
        // 將暫存容器內容移至table元件
        tmp.children().appendTo(pagination);
      };
    };

    /**
    * 事件 - 表格編輯
    */
    var _addTableEvent = function(e) {
      // 點擊鉛筆編輯 取得資料帶入表單
      $('.fa-pen').on('click', function() {
        // 取得資料 id
        let id = $(this).parent().parent().attr('name');
        /**
         * 讀取一筆
         */
        $.ajax({
          method: 'GET',
          url: self._ajaxUrls.deptApi + '/' + id,
          dataType: 'json',
        }).done(function(data) {
          Object.entries(data['0']).forEach(([key, value]) => {
            $('#myModal #' + key).val(value);
          });
        });
      });

      // 點擊新增 清除表單內容
      $('#creat_btn').on('click', function() {
        $('#form').trigger('reset');
      });
    };

    // 點擊分頁事件
    var _addPaginationEvent = function(e) {
      // 點擊分頁按鈕 重新要 table 資料
      $('.page-item').on('click', function() {
        let directPage = parseInt(this.value);
        if (this.id === 'previous') {
          directPage = currentPage - 1 > 0 ? currentPage - 1 : 1;
        }
        if (this.id === 'next') {
          directPage = currentPage + 1 < totalPage ? currentPage + 1 : totalPage;
        }

        // 清除 table
        $('tbody').children().remove();
        // 清除分頁
        $('.pagination').children().remove();

        condition.page = directPage;
        _listTable(condition);
      });
    };

    // 驗證表單資料
    var _validate = function() {
      // 錯誤訊息
      let $errorMessage = [];
      if ($('#form #d_code').val().length === 0) {
        $errorMessage.push('d_code');
      }
      if ($('#form #d_name').val().length === 0) {
        $errorMessage.push('d_name');
      }
      if ($('#form #d_level').val().length === 0) {
        $errorMessage.push('d_level');
      }
      if ($('#form #date_start').val().length === 0) {
        $errorMessage.push('date_start');
      }

      if ($errorMessage.length > 0) {
        alert($errorMessage.join('、') + ' 為必填');
        return false;
      }
      return true;
    };

    _construct();
  };

  // Give the init function the Object prototype for later instantiation
  obj.fn.init.prototype = obj.prototype;

  // Alias prototype function
  $.extend(obj, obj.fn);
})(window, document, $);
