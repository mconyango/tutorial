/**
 * Created by mconyango on 7/17/15.
 */
//document.ready bootstraping
(function ($) {
    'use strict';
    //shorthand for $( document ).ready....
    $(function () {
        var init = {
            theme_setup: function () {
                pageSetUp();
                //settings
                $('#sadmin-setting').click(function () {
                    $('#ribbon .sadmin-options').toggleClass('activate');
                });
            },
            updateGridView: function () {
                var updateGrid = function (e) {
                    var url = $(e).data('href')
                        , confirm_msg = $(e).data('confirm-message')
                        , dataType = $(e).data('dataType')
                        , pjax_id = $(e).data('grid');

                    if (MyApp.utils.empty(dataType)) {
                        dataType = 'html';
                    }

                    var ajax = function () {
                        $.ajax({
                            type: 'POST',
                            url: url,
                            dataType: dataType,
                            success: function (data) {
                                MyApp.grid.updateGrid(pjax_id);
                            }
                            ,
                            beforeSend: function () {
                                MyApp.utils.startBlockUI('Please wait...');
                            }
                            ,
                            complete: function () {
                                MyApp.utils.stopBlockUI();
                            }
                            ,
                            error: function (XHR) {
                                var message = XHR.responseText;
                                MyApp.utils.showAlertMessage(message, 'error');
                                return false;
                            }
                        });
                    }

                    if (MyApp.utils.empty(confirm_msg)) {
                        ajax();
                    } else {
                        BootstrapDialog.confirm(confirm_msg, function (result) {
                            if (result) {
                                ajax();
                            }
                        });
                    }
                };
                $('body').on('click', 'a.grid-update', function (e) {
                    e.preventDefault();
                    updateGrid(this);
                });
            }
            ,
            activateTabs: function () {
                var path = window.location.pathname;
                path = path.replace(/\/$/, "");
                path = decodeURIComponent(path);
                var checkLink = function (e) {
                    var href = $(e).attr('href');
                    if (href.substring(0, path.length) === path) {
                        return true;
                    } else {
                        return false;
                    }
                }
                //activate tabs
                $('ul.my-nav li>a').each(function () {
                    if (checkLink(this)) {
                        $(this).parent().addClass('active');
                    }
                });
                //activate list-group links
                $('div.my-list-group>a').each(function () {
                    if (checkLink(this)) {
                        $(this).addClass('active');
                    }
                });
            }
            ,
            enableLinkableRow: function () {
                var selector = 'table tr.linkable > td:not(.skip-export ,.grid-actions)';
                $(document.body).on('click.tr.linkable', selector, function () {
                    var url = $(this).parent('tr').data('href');
                    if (!MyApp.utils.empty(url)) {
                        MyApp.utils.reload(url);
                    }
                });
            }
            ,
            showDatePicker: function () {
                $(document.body).on('focusin.datepicker', "input[type='text'].show-datepicker,.show-datepicker input[type='text']", function () {
                    $(this).datepicker({
                        dateFormat: 'yy-mm-dd',
                        prevText: '<i class="fa fa-chevron-left"></i>',
                        nextText: '<i class="fa fa-chevron-right"></i>',
                    });
                });
            }
            ,
            showTimePicker: function () {
                $(document.body).on('focusin.timepicker', "input[type='text'].show-timepicker", function () {
                    $(this).timepicker();
                });
            }
            ,
            initPlugins: function () {
                //modal form
                MyApp.plugin.modal({});
                //notifications
                MyApp.plugin.notif({});
                //timeago
                //$("time.timeago").timeago();
                //select2
                $(".select2").select2();
                //show tooltip
                $('#content').tooltip({
                    selector: '.show-tooltip',
                });
                //show popover
                $('#content').popover({
                    selector: '.show-popover',
                });
            }
        };
        var key;
        //inherit the properties in parent
        for (key in init) {
            MyApp.utils.executeMethodByName(key, init);
        }


        // scroll up
        $.scrollUp();
    });

})(jQuery);
