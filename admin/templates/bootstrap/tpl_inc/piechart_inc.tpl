{*
    Params:
    piechart    - piechart object
    headline    - string
    id          - string
    width       - string
    height      - string
*}

{if $piechart->getActive()}
    <div id="{$id}" style="background: {$chartbg|default:'#fff'}; width: {$width}; height: {$height}; padding: {$chartpad|default:'0'};"></div>


    <script type="text/javascript">
    {literal}
    var chart;
    $(document).ready(function() {
        
        chart = new Highcharts.Chart({
            chart: {
    {/literal}
                renderTo: '{$id}',
    {literal}
                defaultSeriesType: 'line',
                backgroundColor: '#fff',
                borderColor: '#CCC',
                borderWidth: 0,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
    {/literal}
                text: '{$headline}'
    {literal}
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.percentage.toFixed(1) +' %'
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ this.percentage.toFixed(1) +' %';
                        }
                    }
                }
            },
    {/literal}
            series:
                {$piechart->getSeriesJSON()}
    {literal}
        });
    });
    {/literal}
    </script>
{else}
    <div class="alert alert-info" role="alert">{__('statisticNoData')}</div>
{/if}