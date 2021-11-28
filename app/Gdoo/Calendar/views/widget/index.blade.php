<div class="gdoo-list-grid">

    <div id="widget_calendar_datetime" class="m-t-xs"></div>

    @verbatim
    <div class="b-t" id="widget_calendar_event_list" style="width:100%;height:200px;">
        <div v-for="row in data.dayEvents">
            <li class="calendar-day-event">
                <div v-if="row.allday" class="day-tag">
                    <span title="全天">全天</span>
                </div>
                <div class="day-tag time-tag" v-else>
                    <span :title="row.start">{{row.start}}</span>
                    <span :title="row.end">{{row.end}}</span>
                </div>
                <div class="title-tag" v-if="row.allday" :title="row.title">
                    <div class="text_overflow" style="cursor:pointer;">{{row.title}}</div>
                    <i class="text_overflow">{{row._start}} - {{row._end}}&nbsp;&nbsp;</i>
                </div>
                <div class="title-tag title-time-tag" v-else :title="row.title">
                    <div class="text_overflow" style="cursor:pointer;">{{row.title}}</div>
                </div>
            </li>
        </div>
    </div>
    @endverbatim

</div>

<style>
#widget_calendar_event_list {
    overflow: hidden;
    overflow-y: auto;
    padding: 5px;
}
.calendar-day-event {
    list-style: none;
    position: relative;
    border-bottom: 1px solid #efefef;
    padding: 6px 0 0;
    height: 44px;
}
.calendar-day-event .day-tag {
    position: absolute;
    left: 0;
    top: 6px;
    width: 46px;
    border-right: 2px solid #53aaff;
    height: 30px;
    text-align: center;
    line-height: 30px;
}
.calendar-day-event .time-tag {
    line-height: 14px;
}

.calendar-day-event .day-tag span {
    display: block;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}
.calendar-day-event .title-tag {
    position: absolute;
    padding-left: 56px;
    margin: 0;
}
.calendar-day-event .title-time-tag {
    line-height: 30px;
    height: 30px;
}
.calendar-day-event .title-tag i {
    line-height: 14px;
    height: 14px;
    overflow: hidden;
    font-size: 12px;
    margin: 0;
    padding: 0;
    font-style: normal;
    color: #999;
}
</style>

<script>
$.fn.datetimepicker.Constructor.Default = $.extend({}, $.fn.datetimepicker.Constructor.Default, {
    dayViewHeaderFormat:'YYYY年MM月',
    icons: {
        today: 'fa fa-caret-square-o-right',
        clear: 'fa fa-trash-o',
        close: 'fa fa-close',
        time: "fa fa-clock-o",
        date: "fa fa-calendar-check-o",
        up: "fa fa-arrow-up",
        down: "fa fa-arrow-down",
        previous: 'fa fa-angle-left',
        next: 'fa fa-angle-right',
    },
    tooltips:{
        today: '今天',
        time: '时间',
        clear: '清除',
        close: '关闭',
        selectMonth: '选择月份',
        prevMonth: '上个月',
        nextMonth: '下个月',
        selectYear: '选择年份',
        prevYear: '上一年',
        nextYear: '下一年',
        selectDecade: '选择时期',
        prevDecade: '上个年代',
        nextDecade: '下个年代',
        prevCentury: '上个世纪',
        nextCentury: '下个世纪',
        incrementHour: '增加一小时',
        pickHour: '选择小时',
        decrementHour:'减少一小时',
        incrementMinute: '增加一分钟',
        pickMinute: '选择分',
        decrementMinute:'减少一分钟',
        incrementSecond: '增加一秒',
        pickSecond: '选择秒',
        decrementSecond:'减少一秒'
    }
});

(function ($) {
    var datas = [];
    var $calendar = $('#widget_calendar_datetime');
    $calendar.datetimepicker({
        format: 'YYYY-MM-DD',
        inline: true,
        sideBySide: true,
        onSelectDay: function(clsName, day) {
            if (datas[day]) {
                clsName += ' todo';
            }
            return clsName;
        }
    });
    var picker = $calendar.data('datetimepicker');

    function getStartEndDate(viewDate, callback) {
        datas = {};
        let start = viewDate.clone().startOf('M').startOf('w').startOf('d');
        let end = start.clone().add(41, 'd').format('L');
        start = start.format('L');
        $.getJSON(app.url('calendar/event/data', {start, end}), function(rows) {
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                datas[row.date] = datas[row.date] || [];
                datas[row.date].push(row);
            }
            picker._fillDate();
            if (typeof callback === 'function') {
                callback(datas);
            }
        });
    }

    // 初始化数据
    getStartEndDate(picker._viewDate, function() {
        let day = picker.date().format('L');
        vueData.dayEvents = datas[day] || [];
    });

    $calendar.on('update.datetimepicker', function(e) {
        getStartEndDate(e.viewDate);
    });

    $calendar.on('change.datetimepicker', function(e) {
        let day = e.date.format('L');
        vueData.dayEvents = datas[day] || [];
    });

    let vueData = Vue.reactive({dayEvents:[]});
    let vm = Vue.createApp({
        setup(props, ctx) {
            return {data: vueData};
        }
    }).mount("#widget_calendar_event_list");

    gdoo.widgets['calendar_widget_index'] = {
        remoteData: function() {
            picker._doAction({}, 'today');
            getStartEndDate(picker._viewDate);
        }
    };
})(jQuery);
</script>