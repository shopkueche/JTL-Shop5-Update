<script>
    $(function () {
        {$single = $single|default:false}
        var single = false,
            ranges = {
            '{__('datepickerToday')}': [moment(), moment()],
            '{__('datepickerYesterday')}': [
                moment().subtract(1, 'days'),
                moment().subtract(1, 'days')
            ],
            '{__('datepickerThisWeek')}': [
                moment().startOf('week').add(1, 'day'),
                moment().endOf('week').add(1, 'day')
            ],
            '{__('datepickerLastWeek')}': [
                moment().subtract(1, 'week').startOf('week').add(1, 'day'),
                moment().subtract(1, 'week').endOf('week').add(1, 'day')
            ],
            '{__('datepickerThisMonth')}': [
                moment().startOf('month'),
                moment().endOf('month')
            ],
            '{__('datepickerLastMonth')}': [
                moment().subtract(1, 'month').startOf('month'),
                moment().subtract(1, 'month').endOf('month')
            ],
            '{__('datepickerThisYear')}': [moment().startOf('year'), moment().endOf('year')],
            '{__('datepickerLastYear')}': [
                moment().subtract(1, 'year').startOf('year'),
                moment().subtract(1, 'year').endOf('year')
            ]
        };
        if ('{$single}') {
            ranges = false;
            single = true;
        }

        var $datepicker = $('{$datepickerID}');
        $datepicker.daterangepicker({
            locale: {
                format: '{$format}',
                separator: '{$separator}',
                applyLabel: '{__('apply')}',
                cancelLabel: '{__('cancel')}',
                customRangeLabel: '{__('datepickerCustom')}',
                daysOfWeek: ['{__('sundayShort')}', '{__('mondayShort')}',
                    '{__('tuesdayShort')}', '{__('wednesdayShort')}',
                    '{__('thursdayShort')}', '{__('fridayShort')}',
                    '{__('saturdayShort')}'
                ],
                monthNames: ['{__('january')}', '{__('february')}', '{__('march')}',
                    '{__('april')}', '{__('may')}', '{__('june')}', '{__('july')}',
                    '{__('august')}', '{__('september')}', '{__('october')}',
                    '{__('november')}', '{__('december')}'
                ],
                firstDay: 1
            },
            alwaysShowCalendars: true,
            autoUpdateInput: false,
            autoApply: false,
            parentEl: 'body',
            applyClass: 'btn btn-primary',
            cancelClass: 'btn btn-danger',
            singleDatePicker: single,
            ranges: ranges
        });
        $datepicker.on('apply.daterangepicker', function(ev, picker) {
            if ('{$single}') {
                $(this).val(picker.startDate.format('{$format}'));
            } else {
                $(this).val(picker.startDate.format('{$format}') + '{$separator}'
                    + picker.endDate.format('{$format}'));
            }
        });
        var curDateRange = '{$currentDate}'.split('{$separator}');
        if (curDateRange.length === 2) {
            $datepicker.val(curDateRange[0] + '{$separator}' + curDateRange[1]);
            $datepicker.data('daterangepicker').setStartDate(curDateRange[0]);
            $datepicker.data('daterangepicker').setEndDate(curDateRange[1]);
        } else if (curDateRange.length === 1 && curDateRange[0] !== '') {
            $datepicker.val(curDateRange[0]);
            $datepicker.data('daterangepicker').setStartDate(curDateRange[0]);
            $datepicker.data('daterangepicker').setEndDate(curDateRange[0]);
        }
    });
</script>
