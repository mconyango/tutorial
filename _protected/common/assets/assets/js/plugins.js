/**
 * @author Fred <mconyango@gmail.com>
 * DATE: 7/20/15
 * TIME: 10:47 PM
 */
//defines all custom plugins (or wrappers for external plugins)
MyApp.plugin = {};
//MODAL Handles the modal form submit
(function ($) {
    'use strict';
    var MODAL = function (options) {
        var defaultOptions = {
            modal_id: 'my_bs_modal',
            notif_id: 'my-modal-notif',
            form_id: 'my-modal-form',
            success_class: 'alert-success',
            error_class: 'alert-danger',
            modalTriggerSelector: '.show_modal_form',
            onShown: function (e, modal) {
                var grid_id = $(e).data('grid');
                if (!MyApp.utils.empty(grid_id)) {
                    $('#' + modal.options.form_id).attr('data-grid', grid_id);
                }
            },
            onHidden: function (e, modal) {
                var refresh = $(e).data('refresh');
                if (!MyApp.utils.empty(refresh)) {
                    MyApp.utils.reload();
                } else {
                    $(this).removeData('bs.modal');
                }
            },
            onShow: function (e, modal) {
                var loading = '<div class="row"><div class="col-md-12"><div class="content-loading"><i class="fa fa-spinner fa-5x fa-spin text-warning"></i></div></div></div>';
                $('#' + modal.options.modal_id).find('.modal-content').html(loading);
            },
            onLoaded: function (e, modal) {
                $(".select2").select2({});
            }
        };
        this.options = $.extend({}, defaultOptions, options || {});
    };

    /**
     * show the modal
     */
    MODAL.prototype.show = function () {
        var $this = this;
        var modal_id = $this.options.modal_id;
        var clickHandler = function (e) {
            var modal_size = $(e).data('modal-size');
            if (!MyApp.utils.empty(modal_size)) {
                $('#' + modal_id).find('.modal-dialog').addClass(modal_size);
            }
            var url = $(e).attr('href');
            var modal = $('#' + modal_id);
            modal
                .on('shown.bs.modal', function () {
                    $this.options.onShown.call(this, e, $this);
                })
                .on('hidden.bs.modal', function () {
                    $this.options.onHidden.call(this, e, $this);
                })
                .on('show.bs.modal', function () {
                    $this.options.onShow.call(this, e, $this);
                })
                .on('loaded.bs.modal', function () {
                    $this.options.onLoaded.call(this, e, $this);
                })
                .modal({
                    remote: url,
                    backdrop: 'static',
                    refresh: true,
                });
        };

        $(document.body).off('click.myapp.modal').on('click.myapp.modal', $this.options.modalTriggerSelector, function (e) {
            e.preventDefault();
            clickHandler(this);
        });
    };

    /**
     * submit the modal form
     */
    MODAL.prototype.submitForm = function () {
        var $this = this;

        var submit_form = function (e, grid_id) {
            var form = $('#' + $this.options.form_id)
                , data = form.serialize()
                , action = form.attr('action')
                , method = form.attr('method') || 'POST'
                , originalButtonHtml = $(e).html();
            $.ajax({
                type: method,
                url: action,
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        var message = '<i class=\"fa fa-check\"></i> ';
                        message += response.message;
                        MyApp.utils.showAlertMessage(message, 'success', '#' + $this.options.notif_id);
                        if (response.forceRedirect) {
                            MyApp.utils.reload(response.redirectUrl, 1000);
                        }
                        else if (MyApp.utils.empty(grid_id)) {
                            if (!MyApp.utils.empty(response.redirectUrl)) {
                                MyApp.utils.reload(response.redirectUrl, 1000);
                            }
                        } else {
                            setTimeout(function () {
                                $('#' + $this.options.modal_id).modal('hide');
                                MyApp.grid.updateGrid(grid_id);
                            }, 1000);
                        }
                    }
                    else {
                        MyApp.utils.display_model_errors(response.message, '#' + $this.options.notif_id, form.data('model'));
                    }
                },
                beforeSend: function () {
                    $(e).attr('disabled', 'disabled').html('Please wait....');
                },
                complete: function () {
                    $(e).html(originalButtonHtml).removeAttr('disabled');
                },
                error: function (XHR) {
                    console.log(XHR.responseText);
                    MyApp.utils.showAlertMessage(XHR.responseText, 'error', '#' + $this.options.notif_id);
                }
            });
        };

        $('#' + $this.options.modal_id).off("click.myapp.modal").on("click.myapp.modal", '#' + $this.options.form_id + ' button[type="submit"]', function (e) {
            e.preventDefault();
            var grid_id = $('#' + $this.options.form_id).data('grid');
            submit_form(this, grid_id);
        });
    };

    var PLUGIN = function (options) {
        var obj = new MODAL(options);
        obj.show();
        obj.submitForm();
    };

    MyApp.plugin.modal = PLUGIN;
}(jQuery));
//NOTIFICATIONS
(function ($) {
    'use strict';
    var NOTIF = function (options) {
        var defaultDefault = {
            checkDelay: 30000, //30 secs
            selector: '#notif-container',
            check_notif_url: null,
            mark_as_seen_url: null,
            mark_as_read_url: null,
            mark_all_as_read_id: 'notif-mark-all-as-read',
            notif_item_selector: '.lv-body a.lv-item',
            refresh_notif_selector: '#notif-refresh'
        };
        this.options = $.extend({}, defaultDefault, options || {});

        var $this = this;
        if (MyApp.utils.empty($this.options.check_notif_url)) {
            $this.options.check_notif_url = $($this.options.selector).data('check-notif-url');
        }
        if (MyApp.utils.empty($this.options.mark_as_seen_url)) {
            $this.options.mark_as_seen_url = $($this.options.selector).data('mark-as-seen-url');
        }
        if (MyApp.utils.empty($this.options.mark_as_read_url)) {
            $this.options.mark_as_read_url = $($this.options.selector).data('mark-as-read-url');
        }
    };

    var loadUrl = function (url, container, show_loading) {
        var $this = this;
        var show_bubble = function (unseen) {
            var bubble = $($this.options.selector + ' .tm-notification > i');
            if (unseen > 0) {
                bubble.text(unseen).addClass("bg-color-red bounceIn animated").show();
            }
            else {
                bubble.hide();
            }
        };

        var update_total_notif = function (total) {
            $('span.total-notif').text('(' + total + ')');
        };

        if (typeof show_loading === 'undefined')
            show_loading = true;

        $.ajax({
            type: "GET",
            url: url,
            dataType: 'json',
            cache: true, // (warning: this will cause a timestamp and will call the request twice)
            beforeSend: function () {
                // cog placed
                if (show_loading) {
                    container.html('<h1 class="text-center" style="margin-top:50px;"><i class="fa fa-spinner fa-spin text-warning"></i> <span class="text-muted">Loading...</span></h1>');
                }
            },
            success: function (data) {
                if (show_loading) {
                    container.css({
                        opacity: '0.0'
                    }).html(data.html).delay(50).animate({
                        opacity: '1.0'
                    }, 300);
                } else {
                    container.html(data.html);
                }
                show_bubble(data.unseen);
                update_total_notif(data.total);

                setTimeout(function () {
                    loadUrl.call($this, url, container, false);
                }, $this.options.checkDelay);
            },
            error: function (xhr, ajaxOptions, thrownError) {
            },
            async: false
        });
    };

    NOTIF.prototype.show = function () {
        var $this = this
            , selector = $this.options.selector + ' > a';

        var mark_as_seen = function (e) {
            var $elem = $(e);

            $elem.find('i').hide();

            var url = $(e).data('mark-as-seen-url');
            $.ajax({
                type: "GET",
                url: url,
                dataType: 'json'
            });
        };

        $(selector).on('click.myapp.notif', function (e) {
            e.preventDefault();
            mark_as_seen(this);
        });
    };

    NOTIF.prototype.get = function () {
        var $this = this
            , container = $($this.options.selector + ' .lv-body')
            , url = $($this.options.selector).data('check-notif-url');
        loadUrl.call($this, url, container);
    };

    NOTIF.prototype.markAsRead = function () {
        var $this = this
            , selector = $this.options.notif_item_selector + '.unread';
        var mark_as_read = function (e) {
            var notif_item = $(e)
                , url = notif_item.data('mark-as-read-url')
                , target_url = $(e).attr('href');

            $.ajax({
                type: "GET",
                url: url,
                dataType: 'json',
                complete: function () {
                    notif_item.removeClass('unread');
                    MyApp.utils.reload(target_url);
                }
            });
        };

        $('#header').on('click.myapp.notif', selector, function (e) {
            e.preventDefault();
            mark_as_read(this);
        });
    };

    NOTIF.prototype.markAllAsRead = function () {
        var $this = this;

        var mark_all_as_read = function (e) {
            var url = $(e).data('href');
            $.ajax({
                type: "GET",
                url: url,
                dataType: 'json',
                success: function (response) {
                    $($this.options.notif_item_selector).removeClass('unread');
                }
            });
        };
        $('#' + $this.options.mark_all_as_read_id).on('click', function (e) {
            e.preventDefault();
            mark_all_as_read(this);
        });
    };

    NOTIF.prototype.refresh = function () {
        var $this = this
            , selector = $this.options.refresh_notif_selector;

        $(selector).on('click.myapp.notif', function () {
            //var btn = $(this);
            //btn.button('loading');
            $this.get();
            //btn.button('reset');
        });
    };
    var PLUGIN = function (options) {
        var obj = new NOTIF(options);
        obj.show();
        obj.get();
        obj.markAsRead();
        obj.markAllAsRead();
        obj.refresh();
    };
    MyApp.plugin.notif = PLUGIN;
}(jQuery));
//TYPEAHEAD
(function ($) {

    'use strict';
    var TypeAhead = function (options) {
        var defaultOptions = {
            selector: 'input.show-typeahead',
        };
        this.options = $.extend({}, defaultOptions, options || {});
    };
    TypeAhead.prototype.init = function () {
        var $this = this
            , e = $($this.options.selector)
            , data = e.data();

        console.log(e);

        if (MyApp.utils.empty(data.href))
            return false;

        var url = MyApp.utils.addParameterToURL(data.href, 'q', '%QUERY');

        var engine = new Bloodhound({
            datumTokenizer: function (d) {
                return Bloodhound.tokenizers.whitespace(d.value);
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: url,
            },
        });
        engine.initialize();

        e.typeahead(null, {
            source: engine.ttAdapter(),
            minLength: 1,
            displayKey: 'value',
            templates: {
                suggestion: Handlebars.compile('<p><strong>{{value}}</strong></p>')
            }
        });
    };

    var PLUGIN = function (options) {
        var obj = new TypeAhead(options);
        obj.init();
    };

    MyApp.plugin.typeahead = PLUGIN;
}(jQuery));
//EXCEL
(function ($) {
    'use strict';

    var EXCEL = {
        /**
         *
         * @param sheetSelector
         * @param response
         */
        setSheets: function (sheetSelector, response) {
            if (!MyApp.utils.empty(response)) {
                MyApp.utils.populateDropDownList(sheetSelector, response.sheets);
            }

            //trigger change event
            $(sheetSelector).trigger('change');
        }
        ,
        /**
         *
         * @returns {boolean}
         */
        setPreview: function () {
            var $this = this
                , form = $('#' + $this.options.form)
                , url = $this.options.previewUrl;

            if (MyApp.utils.empty(url)) {
                return false;
            }

            var set_preview = function (show_progress) {
                if (typeof show_progress === 'undefined')
                    show_progress = false;
                var form = $('#' + $this.options.form)//refresh the form
                    , data = form.serialize()
                    , placeholder_columns = '#placeholder_columns';
                if (MyApp.utils.empty(url)) {
                    return false;
                }
                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (response) {
                        if (response.success) {
                            $(placeholder_columns).html(response.html).removeClass('hidden');
                        } else {
                            $(placeholder_columns).html("").addClass('hidden');
                        }
                    },
                    beforeSend: function () {
                        if (show_progress) {
                            MyApp.utils.startBlockUI('Setting Preview. Please wait...');
                        }
                    },
                    complete: function () {
                        if (show_progress) {
                            MyApp.utils.stopBlockUI();
                        }
                    },
                    error: function (xhr) {
                    }
                });
            };

            //events
            var on_blur_selector = $this.options.excel.startRowSelector + ',' + $this.options.excel.endRowSelector + ',' + $this.options.excel.startColumnSelector + ',' + $this.options.excel.endColumnSelector;
            form.on('change.myapp.excel', 'select.placeholder', function () {
                set_preview();
            });
            form.on('change.myapp.excel', $this.options.excel.sheetSelector, function () {
                set_preview(true);
            });
            form.on('blur.myapp.excel', on_blur_selector, function () {
                set_preview();
            });
        }
        ,
        /**
         * submit the import form
         */
        submit: function () {
            var $this = this;
            var _submit = function (e) {
                var form = $('#' + $this.options.form)
                    , url = form.attr('action')
                    , data = form.serialize();

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            MyApp.utils.reload(response.redirectUrl);
                        } else {
                            MyApp.utils.display_model_errors(response.message, '', true);
                        }
                    },
                    beforeSend: function () {
                        MyApp.utils.startBlockUI('Please wait...');
                    },
                    complete: function () {
                        MyApp.utils.stopBlockUI();
                    },
                    error: function (XHR) {
                        if (MyApp.DEBUG_MODE) {
                            var message = XHR.responseText;
                            MyApp.utils.showAlertMessage(message, 'error');
                        }
                    }
                });
            };
            $('#' + $this.options.form).find('button[type="submit"]').on('click.myapp.excel', function (e) {
                e.preventDefault();
                _submit(this);
            });
        }
    };
    MyApp.plugin.excel = EXCEL;

    //IMPORT EXCEL
    var IMPORT = function (options) {
        var defaultOptions = {
            form: undefined,
            excel: {
                sheetSelector: undefined,
                startRowSelector: undefined,
                endRowSelector: undefined,
                startColumnSelector: undefined,
                endColumnSelector: undefined,
            },
            previewUrl: undefined,
        };

        this.options = $.extend(true, {}, defaultOptions, options || {});
    };

    //set preview
    IMPORT.prototype.setPreview = function () {
        var $this = this;
        MyApp.plugin.excel.setPreview.call($this);
    };
    //submit
    IMPORT.prototype.submit = function () {
        var $this = this;
        MyApp.plugin.excel.submit.call($this);
    };

    var PLUGIN = function (options) {
        var obj = new IMPORT(options);
        obj.setPreview();
        obj.submit();
    };

    MyApp.plugin.importExcel = PLUGIN;
}(jQuery));