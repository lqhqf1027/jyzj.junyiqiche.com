(function ($) {
    'use strict';

    $.extend($.fn.bootstrapTable.defaults, {
        fixedColumns: false,
        fixedNumber: 1
    });
    var BootstrapTable = $.fn.bootstrapTable.Constructor,
            _initHeader = BootstrapTable.prototype.initHeader,
            _initBody = BootstrapTable.prototype.initBody,
            _resetView = BootstrapTable.prototype.resetView;

    BootstrapTable.prototype.initFixedColumns = function () {
        this.$fixedBody = $([
            '<div class="fixed-table-column" style="position: absolute; background-color: #fff;z-index:9999">',
            '<table id="fixed-table">',
            '<thead></thead>',
            '<tbody></tbody>',
            '</table>',
            '</div>'].join(''));

        this.timeoutHeaderColumns_ = 0;
        this.timeoutBodyColumns_ = 0;
        this.$fixedBody.find('table').attr('class', this.$el.attr('class'));
        this.$fixedHeaderColumns = this.$fixedBody.find('thead');
        this.$fixedBodyColumns = this.$fixedBody.find('tbody');
        this.$tableBody.before(this.$fixedBody);
    };

    BootstrapTable.prototype.initHeader = function () {
        _initHeader.apply(this, Array.prototype.slice.apply(arguments));

        if (!this.options.fixedColumns) {
            return;
        }

        this.initFixedColumns();

        var $tr = this.$header.find('tr:eq(0)').clone(),
                $ths = $tr.clone().find('th');

        $tr.html('');
        for (var i = 0; i < this.options.fixedNumber; i++) {
            $tr.append($ths.eq(i).clone());
        }
        this.$fixedHeaderColumns.html('').append($tr);

    };

    BootstrapTable.prototype.initBody = function () {
        _initBody.apply(this, Array.prototype.slice.apply(arguments));

        if (!this.options.fixedColumns) {
            return;
        }

        var that = this;

        this.$fixedBodyColumns.html('');
        this.$body.find('> tr[data-index]').each(function () {
            var $tr = $(this).clone(),
                    $tds = $tr.clone().find('td');
            $tr.html('');
            for (var i = 0; i < that.options.fixedNumber; i++) {
                $tr.append($tds.eq(i).clone());
            }
            that.$fixedBodyColumns.append($tr);
        });
    };

    BootstrapTable.prototype.resetView = function () {
        _resetView.apply(this, Array.prototype.slice.apply(arguments));

        if (!this.options.fixedColumns) {
            return;
        }
        clearTimeout(this.timeoutHeaderColumns_);
        this.timeoutHeaderColumns_ = setTimeout($.proxy(this.fitHeaderColumns, this), this.$el.is(':hidden') ? 100 : 0);

        clearTimeout(this.timeoutBodyColumns_);
        this.timeoutBodyColumns_ = setTimeout($.proxy(this.fitBodyColumns, this), this.$el.is(':hidden') ? 100 : 0);
    };

    BootstrapTable.prototype.fitHeaderColumns = function () {
        var that = this,
                visibleFields = this.getVisibleFields(),
                headerWidth = 0;

        this.$body.find('tr:first-child:not(.no-records-found) > *').each(function (i) {
            var $this = $(this),
                    index = i;

            if (i >= that.options.fixedNumber) {
                return false;
            }

            if (that.options.detailView && !that.options.cardView) {
                index = i - 1;
            }

            that.$fixedBody.find('thead th[data-field="' + visibleFields[index] + '"]')
                    .find('.fht-cell').width($this.innerWidth());
            headerWidth += $this.outerWidth();
        });
        this.$fixedBody.width(headerWidth).show();
    };

    BootstrapTable.prototype.fitBodyColumns = function () {
        var that = this,
                top = -(parseInt(this.$el.css('margin-top')) - 2),
                height = this.$tableBody.height() - 2;

        if (!this.$body.find('> tr[data-index]').length) {
            this.$fixedBody.hide();
            return;
        }

        this.$body.find('> tr').each(function (i) {
            that.$fixedBody.find('tbody tr:eq(' + i + ')').height($(this).height());
        });

        //// events
        this.$tableBody.on('scroll', function () {
            that.$fixedBody.find('table').css('top', -$(this).scrollTop());
        });
        this.$body.find('> tr[data-index]').off('hover').hover(function () {
            var index = $(this).data('index');
            that.$fixedBody.find('tr[data-index="' + index + '"]').addClass('hover');
        }, function () {
            var index = $(this).data('index');
            that.$fixedBody.find('tr[data-index="' + index + '"]').removeClass('hover');
        });
        this.$fixedBody.find('tr[data-index]').off('hover').hover(function () {
            var index = $(this).data('index');
            that.$body.find('tr[data-index="' + index + '"]').addClass('hover');
        }, function () {
            var index = $(this).data('index');
            that.$body.find('> tr[data-index="' + index + '"]').removeClass('hover');
        });
    };

    BootstrapTable.prototype.fixedEvents = function () {
	var options = $('table.table').bootstrapTable('getOptions');
	if(options.fixedColumns)
    {
        $('table.table').bootstrapTable('refreshOptions',{showToggle:false});
    }
	$('table.table').bootstrapTable('refreshOptions',{showToggle:false});
        var that = this;
        $(window).resize(function () {
            $('table.table').bootstrapTable('resetView');
        });

        //全选/反选
        that.$fixedBody.on('click', 'input[name="btSelectAll"]', function () {
            if ($(this).is(':checked')) {
                $('input[name="btSelectItem"]').prop('checked', true);
            } else {

                $('input[name="btSelectItem"]').prop('checked', false);
            }

        });
        //逐个选择
        that.$fixedBody.on('click', 'input[name="btSelectItem"]', function () {
            var inputs = $(this).parents('.fixed-table-body-columns').find('input[name="btSelectItem"]');
            var num = 0;
            inputs.each(function () {
                if ($(this).is(':checked')) {
                    num++;
                }
            });
            if (num === inputs.length) {
                $('input[name="btSelectAll"]').prop('checked', true);
            } else {
                $('input[name="btSelectAll"]').prop('checked', false);

            }
            var index = $(this).parents('tr').index();
            that.$body.find('input[name="btSelectItem"]').eq(index).click();
        });

        that.$body.on('change', 'input[name="btSelectItem"]', function () {
           var inputs = $(this).parents('.fixed-table-body-columns').find('input[name="btSelectItem"]');
            var num = 0;
            inputs.each(function () {
                if ($(this).is(':checked')) {
                    num++;
                }
            });
            if (num === inputs.length) {
                $('input[name="btSelectAll"]').prop('checked', true);
            } else {
                $('input[name="btSelectAll"]').prop('checked', false);

            }
            var index = $(this).parents('tr').index();
            that.$fixedBody.find('input[name="btSelectItem"]').eq(index).prop('checked', $(this).is(':checked')?true:false);
        });
    }
    $.fn.bootstrapTable.methods.push('fixedEvents', 'resetView');
})(jQuery);

