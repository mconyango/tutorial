/**
 * Created by antony on 2/11/16.
 */
//campaign form
MyApp.modules = MyApp.modules || {};
MyApp.modules.sales = {};
(function ($) {
    'use strict';

    var FORM = function (options) {
        var defaultOptions = {
            modalClass: 'campaigns',
            productIdField: 'product_id',
            bundlesField: 'bundles',
            isBundlesSelector: '#campaigns-is_bundled'
        };

        this.options = $.extend({}, defaultOptions, options || {});
    };

    var getContainerSelector = function (field) {
        // default selector names will always start with field
        return '.field-' + this.options.modalClass + '-' + field;
    };

    FORM.prototype.toggle = function () {
        var bundlesSelector = getContainerSelector.call(this, this.options.bundlesField)
            , productIdSelector = getContainerSelector.call(this, this.options.productIdField)
            , isBundledSelector = this.options.isBundlesSelector;


        var toggle = function (e) {
            if ($(e).is(':checked')) {
                $(bundlesSelector).show();
                $(productIdSelector).hide();
            } else {
                $(productIdSelector).show();
                $(bundlesSelector).hide();
            }
        };

        //click event
        $(isBundledSelector).on('click.sales', function (e) {
            toggle(this);
        });

        //on load
        toggle(isBundledSelector);
    };

    MyApp.modules.sales.campaignForm = function (options) {
        var obj = new FORM(options);
        obj.toggle();
    };
}(jQuery));

//company rates
(function ($) {
    'use strict';

    var FORM = function (options) {
        var defaultOptions = {
            modalClass: 'companyrates',
            campaignSelector: 'campaign_id',
            productSelector: 'product_id',
            typeField: 'type'
        };

        this.options = $.extend({}, defaultOptions, options || {});
    };

    var getContainerSelector = function (field) {
        // default selector names will always start with field
        return '.field-' + this.options.modalClass + '-' + field;
    };

    var getFieldSelector = function (field) {
        return '#' + this.options.modalClass + '-' + field;
    };

    FORM.prototype.toggle = function () {
        var campaignSelector = getContainerSelector.call(this, this.options.campaignSelector)
            , productSelector = getContainerSelector.call(this, this.options.productSelector)
            , rateTypeSelector = getFieldSelector.call(this, this.options.typeField);


        var toggle = function (e) {
            var val = parseInt($(e).val(), 10);
            if (val === 3 || val === 4) {
                $(campaignSelector).show();
                $(productSelector).hide();
            } else {
                $(campaignSelector).hide();
                $(productSelector).show();
            }
        };

        $(rateTypeSelector).on('change', function (e) {
            toggle(this);
        }).change();
    };

    MyApp.modules.sales.companyRatesForm = function (options) {
        var obj = new FORM(options);
        obj.toggle();
    };
}(jQuery));

// sales update
(function ($) {
    'use strict';

    var FORM = function (options) {
        var defaultOptions = {
            modalClass: 'salesleads',
            stageSelector: 'sale_stage_id',
            droppedReasonsSelector: 'reason_dropped_id',
            droppedCommentsSelector: 'dropped_comments'
        };

        this.options = $.extend({}, defaultOptions, options || {});
    };

    var getContainerSelector = function (field) {
        // default selector names will always start with field
        return '.field-' + this.options.modalClass + '-' + field;
    };

    var getFieldSelector = function (field) {
        return '#' + this.options.modalClass + '-' + field;
    };

    FORM.prototype.toggle = function () {
        var stageSelector = getFieldSelector.call(this, this.options.stageSelector)
            , droppedReasonsSelector = getFieldSelector.call(this, this.options.droppedReasonsSelector)
            , droppedReasonsContainer = getContainerSelector.call(this, this.options.droppedReasonsSelector)
            , droppedCommentsContainer = getContainerSelector.call(this, this.options.droppedCommentsSelector);


        var displayDroppedReasons = function (e) {
            var val = parseInt($(e).val(), 10);
            if (val === 5) {
                $(droppedReasonsContainer).show();
                $(droppedCommentsContainer).hide();
            } else {
                $(droppedReasonsContainer).hide();
            }
        };

        var displayDroppedComments = function (e) {
            var val = parseInt($(e).val(), 10);
            if (val === 3) {
                //$(droppedReasonsContainer).hide();
                $(droppedCommentsContainer).show();
            } else {
                $(droppedCommentsContainer).hide();
            }
        };

        $(stageSelector).on('change', function (e) {
            displayDroppedReasons(this);
        }).change();

        $(droppedReasonsSelector).on('change', function (e) {
            displayDroppedComments(this);
        }).change();
    };

    MyApp.modules.sales.salesUpdateForm = function (options) {
        var obj = new FORM(options);
        obj.toggle();
    };
}(jQuery));

//Sales Hierarchy form filters (region,branch,team,staff)
(function ($) {
    'use strict';

    var FILTER = function (options) {
        var defaultOptions = {
            filterOnLoad: true,
            regionIdSelector: '#region_id',
            branchIdSelector: '#branch_id',
            teamIdSelector: '#team_id',
            createdBySelector: '#created_by',

        };
        this.options = $.extend({}, defaultOptions, options || {})
    }
    /**
     *
     * @param selector
     * @param targetSelector
     * @param changeOnLoad
     */
    var filter = function (selector, targetSelector, changeOnLoad) {
        if (changeOnLoad === 'undefined') {
            changeOnLoad = false;
        }
        var ajaxPost = function (e) {
            var url = $(e).data('href')
                , value = $(e).val()
                , selected = $(targetSelector).data('selected');

            $.ajax({
                url: url,
                type: 'post',
                data: 'id=' + value,
                dataType: 'json',
                success: function (data) {
                    MyApp.utils.populateDropDownList(targetSelector, data,selected);
                    $(targetSelector).change();
                },
                error: function (xhr) {
                    if (MyApp.DEBUG_MODE) {
                        console.log(xhr);
                    }
                }
            })
        }

        //event
        $(selector).on('change', function (e) {
            ajaxPost(this);
        });
        if (changeOnLoad) {
            $(selector).change();
        }
    }

    FILTER.prototype.region = function () {
        var $this = this;
        filter.call($this, $this.options.regionIdSelector, $this.options.branchIdSelector, $this.options.filterOnLoad);
    }

    FILTER.prototype.branch = function () {
        var $this = this;
        filter.call($this, $this.options.branchIdSelector, $this.options.createdBySelector);
    }

    FILTER.prototype.team = function () {
        var $this = this;
        filter.call($this, $this.options.teamIdSelector, $this.options.createdBySelector, $this.options.filterOnLoad);
    }

    var PLUGIN = function (options) {
        var obj = new FILTER(options);
        obj.region();
        obj.branch();
        obj.team();
    }

    MyApp.modules.sales.filterSales = PLUGIN;
}(jQuery));