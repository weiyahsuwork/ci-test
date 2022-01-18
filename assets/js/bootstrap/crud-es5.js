// IIFE 立即執行函式
// CRUD = function($, BootstrapDialog)
(function($, CRUD) {
  $(document).ready(function() {
    // 在 window 物件下，註冊頁面封裝實體物件
    window.mainPage = new CRUD($, window.BootstrapDialog);
  });
})(window.$, function($, BootstrapDialog) {
  // 宣告全域 this 指向
  var app = this;

  // 宣告 DOM 節點物件
  var nodes = {};

  // 註冊全域 DOM 節點物件
  app.nodes = nodes;

  // 註冊表格欲渲染 DOM 節點 tbody
  nodes.$tbody = $('#table > tbody');

  // 註冊分頁欲渲染 DOM 節點 ul
  nodes.$ul = $('#pagination > ul');

  // 頁面主題
  app.pageTitle = '部門管理2';

  // API 選項配置
  app.api = {
    userInfo: '/dept_info/ajax',
  };

  // 搜尋條件
  app.condition = {};

  /**
     * 全域使用變數
     */
  // 總頁數
  var totalPage = 0,
    // 目前頁數
    currentPage = 0;

  /**
     * HTML 樣板產生器
     * 
     * @param {object} el - Element Templates
     * @param {array} data - Template Render Data
     * @param {function} created - Callback Method
     * @return {HTMLElement}
     */
  var tmpl = function tmpl(el, data, created) {
    var $el = $(el);

    // tagName取得元素標籤名
    var templateHTML = /script|template/i.test($el.prop('tagName'))
      ? // 如果標籤是<script> 或者 <template> 則取得被選元素的内容（innerHTML）
        $el.html()
      : // outerHTML 序列化HTML
        $el.prop('outerHTML');

    var $compiledEl = $(templateHTML);
    // // 尋找 Html 標籤
    // var $compiledEl = $(
    //   // 找出群組 p1 = (.*?)所找出的匹配任意字符到下一個符合條件的字符，結果為匹配全部
    //   // 最終沒有可以匹配的 故取得 div#edit-form-dialog
    //   (templateHTML || '').replace(/{{ *(.*?) *}}/g, (match, p1) => {
    //     try {
    //       return (
    //         // concat 合併陣列 data & p1.split('.')
    //         // p1 由.分隔內容
    //         // reduce 每項元素（由左至右）傳入回呼函式
    //         [data || {}].concat(p1.split('.')).reduce((a, b) => {
    //           return a[b];
    //         }) || ''
    //       );
    //     } catch (e) {
    //       return '';
    //     }
    //   })
    // );

    // if (typeof created === 'function') {
    //   created($compiledEl, data);
    // }

    return $compiledEl;
  };

  /**
     * 建構子
     */
  var constructor = function() {
    // 執行初始化
    app.initialize();
  };

  /**
     * 初始化
     */
  app.initialize = function() {
    /**
       * 初始化表格搜尋條件
       */
    // 頁數
    app.condition.page = 1;
    // 搜尋字串
    app.condition.search = '';
    // 排序欄位
    app.condition.sortBy = '';
    // 排序方式
    app.condition.sortType = 'asc';

    // 產生表格物件
    app.refetchTable();
  };

  /**
     * 產生表格物件
     */
  app.refetchTable = function() {
    // 初始清空 tbody DOM 節點
    nodes.$tbody.empty();

    // 初始清空 ul DOM 節點
    nodes.$ul.empty();

    // 取得使用者資料
    axios
      .get(app.api.userInfo + '?' + $.param(app.condition))
      .then(function(response) {
        // 確認 API 回應成功
        if (response.status && response.status === 200) {
          var data = response.data || [];
          var row;

          // 確認響應資料存在才顯示表格物件，反之顯示資料不存在
          if (data.data && data.data.length) {
            $.each(data.data, function(key, item) {
              row = $('<tr></tr>');

              row.append(
                $(
                  '<td  data-id="' +
                    item.id +
                    '"><a class="btn btn-success edit-btn">Edit</a> | <a class="btn btn-danger delete-btn">Delete</a></td>'
                )
              );
              row.append($('<td></td>').html(item.d_code));
              row.append($('<td></td>').html(item.d_name));
              row.append($('<td></td>').html(item.d_level));
              row.append($('<td></td>').html(item.date_start));
              row.append($('<td></td>').html(item.date_end));
              row.append($('<td></td>').html(item.remark));

              nodes.$tbody.append(row);
            });

            // 等待表格物件渲染完成，再綁定事件
            app.bindEvents();
          } else {
            row = $('<tr></tr>');
            row.append($(`<td colspan="7"></td>`).html('no data'));
            nodes.$tbody.append(row);
          }

          // 建立分頁
          app.buildPagination(data);
          // 點擊分頁事件
          app.addPaginationEvent();
        }
      })
      .catch(function(error) {
        console.log(error);
      });
  };

  /**
     * 綁定事件
     * 
     * - 編輯
     * - 刪除
     */
  app.bindEvents = function() {
    // 編輯事件
    $('.edit-btn').on('click', function(event) {
      // 防止冒泡事件
      event.preventDefault();

      // 取得序號
      var _id = $(this).parent().data('id');

      // 取得單一使用者資料
      axios
        .get(app.api.userInfo + '/' + _id)
        .then(function(response) {
          if (response.status && response.status === 200) {
            var data = response.data[0] || [];

            // 開啟編輯彈窗
            app.showEditDialog({
              mode: 'edit',
              title: 'Edit',
              formData: {
                id: data.id,
                d_code: data.d_code,
                d_name: data.d_name,
                d_level: data.d_level,
                date_start: data.date_start,
                date_end: data.date_end,
                remark: data.remark,
              },
              onSave: function(dialog, formData) {
                // 驗證資料
                let errorMessage = this.validate(dialog);
                // 如果有錯誤訊息
                if (errorMessage.length > 0) {
                  alert(errorMessage);
                  return false;
                } else {
                  // 修改使用者資料
                  axios
                    .put(app.api.userInfo + '/' + _id, {
                      id: dialog.initSelector.$id.val(),
                      d_code: dialog.initSelector.$d_code.val(),
                      d_name: dialog.initSelector.$d_name.val(),
                      d_level: dialog.initSelector.$d_level.val(),
                      date_start: dialog.initSelector.$date_start.val(),
                      date_end: dialog.initSelector.$date_end.val(),
                      remark: dialog.initSelector.$remark.val(),
                    })
                    .then(function(response) {
                      if (response.status && response.status === 200) {
                        app.refetchTable();
                      }
                    })
                    .catch(function(error) {
                      console.log(error);
                    });
                  return true;
                }
              },
            });
          }
        })
        .catch(function(error) {
          console.log(error);
        });
    });

    // 刪除事件
    $('.delete-btn').on('click', function(event) {
      // 防止冒泡事件
      event.preventDefault();

      if (confirm('確定要刪除嗎?') == true) {
        // 取得序號
        var _id = $(this).parent().data('id');

        // 刪除使用者資料
        axios
          .delete(app.api.userInfo + '/' + _id)
          .then(function(response) {
            if (response.status && response.status === 200) {
              app.refetchTable();
            }
          })
          .catch(function(error) {
            console.log(error);
          });
      }
    });
  };

  /**
     * 顯示彈窗
     * 
     * @param {object} options - 選項參數物件
     */
  app.showEditDialog = function(options) {
    var DEFAULTS = {
      // 操作模式: add|edit
      mode: 'add',
      // 標題
      title: '',
      // 表單資料
      formData: {
        id: '',
        d_code: '',
        d_name: '',
        d_level: '',
        date_start: '',
        date_end: '',
        remark: '',
      },
      // 儲存按鈕的callback
      onSave: null,
      // 驗證方式
      validate: function(dialog) {
        let errorMessage = [];
        // 需要驗證的資料
        let data = {
          d_code: dialog.initSelector.$d_code.val(),
          d_name: dialog.initSelector.$d_name.val(),
          d_level: dialog.initSelector.$d_level.val(),
          date_start: dialog.initSelector.$date_start.val(),
        };

        $.each(data, function(key, value) {
          // 如果去除空白後長度為0
          if (value.trim().length === 0) {
            errorMessage.push(key + '為必須');
          }
        });

        return errorMessage;
      },
    };

    // 繼承外部變更的選項參數物件
    var currentOpt = $.extend(true, {}, DEFAULTS, $.isPlainObject(options) && options);

    // 呼叫顯示彈窗套件
    BootstrapDialog.show({
      title: currentOpt.title,
      buttons: [
        {
          // 關閉
          id: 'close',
          label: 'Close',
          cssClass: 'btn-light',
          action: function(dialog) {
            dialog.close();
          },
        },
        {
          // 儲存
          id: 'save',
          label: 'Save',
          cssClass: 'btn-primary',
          action: function(dialog) {
            if ($.isFunction(currentOpt.onSave)) {
              // 表單資料
              const formData = {
                id: dialog.initSelector.$id.val(),
                d_code: dialog.initSelector.$d_code.val(),
                d_name: dialog.initSelector.$d_name.val(),
                d_level: dialog.initSelector.$d_level.val(),
                date_start: dialog.initSelector.$date_start.val(),
                date_end: dialog.initSelector.$date_end.val(),
                remark: dialog.initSelector.$remark.val(),
              };

              // 呼叫儲存
              if (currentOpt.onSave(dialog, formData)) {
                dialog.close();
              }
            } else {
              dialog.close();
            }
          },
        },
      ],
      // 彈窗顯示前的 Callback
      onshow: function(dialog) {
        // 取得彈窗主內容區塊
        var modalBody = dialog.getModalBody();
        // 表單樣板
        var bodyTemp =
          '<script id="tmpl-edit-form-dialog" type="text/template">' +
          '<div id="edit-form-dialog">' +
          '<form>' +
          '<div class="form-group">' +
          '<label for="d_code">代碼:</label>' +
          '<input type="text" class="form-control" id="d_code" name="d_code">' +
          '</div>' +
          '<div class="form-group">' +
          '<label for="d_name">名稱:</label>' +
          '<input type="text" class="form-control" id="d_name" name="d_name">' +
          '</div>' +
          '<div class="form-group">' +
          '<label for="d_level">層級:</label>' +
          '<select id="d_level" name="d_level" class="form-control">' +
          '<option value="部">部</option>' +
          '<option value="處">處</option>' +
          '<option value="科">科</option>' +
          '<option value="室">室</option>' +
          '<option value="組">組</option>' +
          '</select>' +
          '</div>' +
          '<div class="form-group">' +
          '<label for="date_start">開始日:</label>' +
          '<input type="date" class="form-control" id="date_start" name="date_start">' +
          '</div>' +
          '<div class="form-group">' +
          '<label for="date_end">結束日:</label>' +
          '<input type="date" class="form-control" id="date_end" name="date_end" value="">' +
          '</div>' +
          '<div class="form-group">' +
          '<label for="remark">備註:</label>' +
          '<input type="text" class="form-control" id="remark" name="remark" value="">' +
          '</div>' +
          '<div class="form-group">' +
          '<input type="hidden" id="id" name="id" value="">' +
          '</div>' +
          '</form>' +
          '</div>' +
          '</script>';

        // 產生預渲染表單樣板物件
        dialog.templateForm = tmpl(bodyTemp);

        // 尋找表單欄位 DOM
        dialog.initSelector = {
          $id: dialog.templateForm.find('#id'),
          $d_code: dialog.templateForm.find('#d_code'),
          $d_name: dialog.templateForm.find('#d_name'),
          $d_level: dialog.templateForm.find('#d_level'),
          $date_start: dialog.templateForm.find('#date_start'),
          $date_end: dialog.templateForm.find('#date_end'),
          $remark: dialog.templateForm.find('#remark'),
        };

        // 替換表單資料
        switch (currentOpt.mode) {
          // 新增
          case 'add':
            dialog.initSelector.$id.val('');
            dialog.initSelector.$d_code.val('');
            dialog.initSelector.$d_name.val('');
            dialog.initSelector.$d_level.val('部');
            dialog.initSelector.$date_start.val('');
            dialog.initSelector.$date_end.val('');
            dialog.initSelector.$remark.val('');
            break;
          // 修改
          case 'edit':
            dialog.initSelector.$id.val(currentOpt.formData.id);
            dialog.initSelector.$d_code.val(currentOpt.formData.d_code);
            dialog.initSelector.$d_name.val(currentOpt.formData.d_name);
            dialog.initSelector.$d_level.val(currentOpt.formData.d_level);
            dialog.initSelector.$date_start.val(currentOpt.formData.date_start);
            dialog.initSelector.$date_end.val(currentOpt.formData.date_end);
            dialog.initSelector.$remark.val(currentOpt.formData.remark);
            break;
        }

        // 產生實體表單樣板物件
        modalBody.append(dialog.templateForm);
      },
    });
  };

  // 建立分頁
  app.buildPagination = function(data) {
    // 建立分頁繪製變數
    var tmp, page, li;

    // 暫存空間
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
        // 狀態為存活
        currentPage = page;
        li.addClass('active');
      }
    }

    // 建立往後按鈕
    $('<li class="page-item" id="next"><a class="page-link" href="#">Next</a></li>').appendTo(tmp);

    nodes.$ul.append(tmp.children());
  };

  // 點擊分頁事件
  app.addPaginationEvent = function() {
    // 點擊分頁按鈕 重新要 table 資料
    $('.page-item').on('click', function() {
      // 點選的分頁頁數
      let directPage = parseInt(this.value);
      // 如果為往前則取得當前分頁頁數 -1
      if (this.id === 'previous') {
        directPage = currentPage - 1 > 0 ? currentPage - 1 : 1;
      }
      // 如果為往後則取得當前分頁頁數 +1
      if (this.id === 'next') {
        directPage = currentPage + 1 < totalPage ? currentPage + 1 : totalPage;
      }

      // 導向分頁頁數
      app.condition.page = directPage;
      app.refetchTable();
    });
  };

  // 呼叫建構子
  constructor();
});
