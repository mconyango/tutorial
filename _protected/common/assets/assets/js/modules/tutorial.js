/**
 * Created by mconyango on 6/9/16.
 */
MyApp.tutorial = MyApp.tutorial || {};

(function ($) {
    'use strict';

    //constructor for our object
    var FORM = function (options) {
        var defaultOptions = {
            categorySelector: '#category_id',
            subcategorySelector: '#subcategory_id',
            url: undefined
        }

        //object property
        this.options = $.extend({}, defaultOptions, options || {});
    }

    //object method
    FORM.prototype.getSubCategory = function () {

        var $this = this;

        var ajax = function (e) {
            var url = $this.options.url
                , value = $(e).val();

            $.ajax({
                url: url,
                type: 'get',
                data: 'category_id=' + value,
                dataType: 'json',
                success: function (data) {
                    MyApp.utils.populateDropDownList($this.options.subcategorySelector, data);
                },
                beforeSend:function(){
                    MyApp.utils.startBlockUI('Loading sub-categories. Please wait...');
                },
                complete:function(){
                    MyApp.utils.stopBlockUI();
                },
                error: function (xhr) {
                    if (MyApp.DEBUG_MODE) {
                        console.log(xhr);
                    }
                }
            })
        }

       // ajax($this.options.categorySelector);

        //events : onload and onchange

        $($this.options.categorySelector).on('change', function (event) {
            //call the ajax function
            ajax(this);
        });
    }

    MyApp.tutorial.dependentLists = function (options) {
        var obj = new FORM(options);
        obj.getSubCategory();
    }
}(jQuery));