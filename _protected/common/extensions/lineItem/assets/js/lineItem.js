/**
 * LineItem Script
 * @author Fred <mconyango@gmail.com>
 * Timestamp: 2nd Jun 2014 at 1803hr
 */

(function ($) {
    'use strict';

    var LINEITEM = function (options) {
        var defaultOptions = {
            selectors: {
                container: undefined,
                itemsTable: undefined,
                addNewItemOnSave: undefined,
                headerIdField: undefined,
                addItem: undefined,
                cancel: undefined,
                submit: undefined,
                notification: undefined,
                itemIdField: undefined,
                itemHeaderIdField: undefined,
                saveItem: '.save-line-item',
                deleteItem: '.delete-line-item',
                item: 'tr.line-item'
            },
            saveItemUrl: undefined,
            deleteItemUrl: undefined,
            beforeSave: function (tr, settings) {
            },
            afterSave: function (tr, response, settings) {
            },
            beforeDelete: function (tr, settings) {
            },
            afterDelete: function (tr, response, settings) {
            },
            beforeFinish: function (e, settings) {
            },
            afterFinish: function (response, settings) {
                $(settings.selectors.container).html(response.html);
                MyApp.utils.showAlertMessage(response.message, 'success', settings.selectors.notification);
            }
        };

        this.options = $.extend(true, {}, defaultOptions, options || {});
    };

    //add a new row
    LINEITEM.prototype.add = function () {
        var $this = this
            , container = $($this.options.selectors.container)
            , selector = $this.options.selectors.addItem;

        var add = function (e) {
            var url = $(e).data('href')
                , index = container.find($this.options.selectors.item).length + 1;
            $.ajax({
                type: 'POST',
                url: url,
                data: 'index=' + index,
                success: function (html) {
                    container.find($this.options.selectors.itemsTable).find('tbody').append(html);
                }
            });
        };
        //onclick
        container.on('click.myapp.lineitem', selector, function (e) {
            e.preventDefault();
            add(this);
        });
    };
    //save row
    LINEITEM.prototype.save = function () {
        var $this = this;

        var save = function (e) {
            var tr = $(e).closest('tr');
            $this.options.beforeSave.call($this, tr);

            var input_error_class = 'my-form-error';
            var show_error = function (input_class) {
                tr.find('.' + input_class).addClass(input_error_class);
                tr.addClass('bg-danger');
            };

            var hide_error = function () {
                tr.find('.' + input_error_class).removeClass(input_error_class);
                tr.removeClass('bg-danger');
            };

            var mark_as_saved = function () {
                var saved_css_class = 'text-success'
                    , unsaved_css_class = 'text-warning';
                tr.find($this.options.selectors.saveItem).removeClass(unsaved_css_class).addClass(saved_css_class);
            };


            //set the parent_id of the row
            var head_id_field = $($this.options.selectors.headerIdField);
            if (MyApp.utils.empty(tr.find($this.options.selectors.itemHeaderIdField).val())) {
                $($this.options.selectors.itemHeaderIdField).val(head_id_field.val());
            }

            var url = $this.options.saveItemUrl
                , data = tr.find('input,select,textarea').serialize();

            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $this.options.afterSave.call($this, tr, response);

                        if (!MyApp.utils.empty(response.data.head_id) && MyApp.utils.empty(head_id_field.val())) {
                            head_id_field.val(response.data.head_id);
                        }
                        hide_error();
                        var add_row_on_save = $($this.options.selectors.addNewItemOnSave).is(':checked');
                        if (add_row_on_save && MyApp.utils.empty(tr.find($this.options.selectors.itemIdField).val())) {
                            $($this.options.selectors.addItem).trigger('click');
                        }
                        tr.find($this.options.selectors.itemIdField).val(response.data.id);
                        mark_as_saved();
                    }
                    else {
                        hide_error();
                        //show error
                        var jsonData = $.parseJSON(response.message);
                        $.each(jsonData, function (i) {
                            show_error(i);
                        });
                        MyApp.utils.display_model_errors(response.message, false, true);
                    }
                },
                error: function (XHR) {
                    if (MyApp.DEBUG_MODE) {
                        console.log(XHR.responseText);
                        MyApp.utils.showAlertMessage(XHR.responseText,'error');
                    }
                }
            });
        };
        //on click
        $($this.options.selectors.container).on('click.myapp.lineitem', $this.options.selectors.saveItem, function (e) {
            e.preventDefault();
            save(this);
        });
    };
    //remove item
    LINEITEM.prototype.remove = function () {
        var $this = this;

        var _remove = function (e) {
            var tr = $(e).closest('tr');
            $this.options.beforeDelete.call($this, tr);

            var url = $this.options.deleteItemUrl
                , id = tr.find($this.options.selectors.itemIdField).val();

            if (MyApp.utils.empty(id)) {
                tr.remove();
                return false;
            }

            $.ajax({
                type: 'POST',
                url: url,
                data: 'id=' + id,
                dataType: 'json',
                success: function (response) {
                    $this.options.afterDelete.call($this, tr, response);
                    tr.remove();
                }
            });
        };

        //on click
        $($this.options.selectors.container).on('click.myapp.lineitem', $this.options.selectors.deleteItem, function (e) {
            e.preventDefault();
            var $this = this;
            var confirm_msg = $($this).data('delete-confirm');
            if (MyApp.utils.empty(confirm_msg)) {
                _remove($this);
            } else {
                BootstrapDialog.confirm(confirm_msg, function (result) {
                    if (result) {
                        _remove($this);
                    }
                })
            }
        });
    };
    //cancel the form:@todo remove this functionality
    LINEITEM.prototype.cancel = function () {
        var $this = this;

        var _cancel = function (e) {
            var url = $(e).data('href')
                , id = $($this.options.selectors.headerIdField).val();

            $.ajax({
                type: 'POST',
                url: url,
                data: 'id=' + id,
                success: function (html) {
                    $($this.options.selectors.container).html(html);
                }
            });
        };
        //on click
        $($this.options.selectors.container).on('click.myapp.lineitem', $this.options.selectors.cancel, function (e) {
            e.preventDefault();
            _cancel(this);
        });
    };
    //finish @todo remove this functionality
    LINEITEM.prototype.finish = function () {
        var $this = this;

        var submit = function (e) {
            $this.options.beforeFinish.call($this, e);
            var url = $(e).data('href')
                , data = $($this.options.selectors.container).find('form').serialize();

            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $this.options.afterFinish.call($this, response);
                    } else {
                        MyApp.utils.display_model_errors(response.message, false, true);
                    }
                },
                beforeSend: function () {
                    MyApp.utils.startBlockUI();
                },
                complete: function () {
                    MyApp.utils.stopBlockUI();
                },
                error: function (XHR) {
                    if (MyApp.DEBUG_MODE) {
                        console.log(XHR.responseText);
                    }
                }
            });
        };
        //on click
        $($this.options.selectors.container).on('click.myapp.lineitem', $this.options.selectors.submit, function (e) {
            e.preventDefault();
            submit(this);
        });
    };

    MyApp.plugin.lineItem = function (options) {
        var obj = new LINEITEM(options);
        obj.add();
        obj.save();
        obj.remove();
        obj.cancel();
        obj.finish();
    };
}(jQuery));