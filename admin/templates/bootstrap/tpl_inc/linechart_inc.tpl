{*
    Params:
    linechart   - linechart object
    headline    - string
    id          - string
    width       - string
    height      - string
    ylabel      - string
    href        - bool
    legend      - bool
    ymin        - string
*}

{if $linechart->getActive()}
    <div id="{$id}" style="background: {$chartbg|default:'#fff'}; width: {$width}; height: {$height}; padding: {$chartpad|default:'0'};"></div>
    
    <script type="text/javascript">
        var chart;

        $(document).ready(function() {
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: '{$id}',
                    defaultSeriesType: 'area',
                    marginRight: 15,
                    marginBottom: 50,
                    spacingBottom: 25,
                    borderColor: '#CCC',
                    borderWidth: 0
                },
                title: {
                    style: {
                        color: '#435a6b'
                    },
                    text: '{$headline}',
                    align: 'left'
                },
                plotOptions: {
                    series: {
                        cursor: 'pointer',
                        marker: {
                            fillColor: '#FFFFFF',
                            lineWidth: 2,
                            lineColor: null
                        },
                        {if $href}
                        point: {
                            events: {
                                click: function() {
                                    location.href = this.options.url;
                                }
                            }
                        }
                        {/if}
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'top',
                    x: -10,
                    y: 100,
                    borderWidth: 0,
                    enabled: {if $legend}true{else}false{/if},
                },
                xAxis: {$linechart->getAxisJSON()},
                yAxis: {
                    title: {
                        style: {
                            color: '#5cbcf6'
                        },
                        text: '{$ylabel}'
                    },
                    labels: {
                        style: {
                            color: '#5cbcf6'
                        }
                    },
                    plotLines: [{
                        value: 0,
                        width: 2,
                        color: '#ddd'
                    }],
                    {if isset($ymin) && $ymin|@count_characters > 0}
                        min: {$ymin}
                    {/if}
                },
                series: {$linechart->getSeriesJSON()}
            });
        });
    </script>
{else}
    <div class="alert alert-info" role="alert">{__('statisticNoData')}</div>
{/if}