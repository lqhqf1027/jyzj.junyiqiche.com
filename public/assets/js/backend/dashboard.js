define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {

            //新车表
            // 基于准备好的dom，初始化echarts实例
            var newEchart = Echarts.init(document.getElementById('newechart'), 'walden');
            setInterval(function () {
                if ($("#newechart").width() != $("#newechart canvas").width() && $("#newechart canvas").width() < $("#newechart").width()) {
                    newEchart.resize();
                }
            }, 2000);
            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '车辆销售情况（总共历史成交数：' + Orderdata.count + '）',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ["以租代购（新车）","租车","以租代购（二手车）","全款（新车）","全款（二手车）"]
                },
                toolbox: {
                    show: false,
                    feature: {
                        magicType: {show: true, type: ['stack', 'tiled']},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: Orderdata.column
                },
                yAxis: {},
                grid: [{
                    left: 'left',
                    top: 'top',
                    right: '10',
                    bottom: 30
                }],
                series: [
                    {
                        name: "以租代购（新车）",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: 'rgb(132,215,251)'
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderdata.newsales
                    },
                    {
                        name: "租车",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: 'rgb(133,253,217)'
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderdata.rentalsales 
                    },
                    {
                        name: "以租代购（二手车）",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: 'rgb(146,153,179)'
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderdata.secondsales
                    },
                    {
                        name: "全款（新车）",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: 'rgb(188,195,253)'
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderdata.fullsales
                    },
                    {
                        name: "全款（二手车）",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: 'rgb(202,226,189)'
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderdata.fullsecondsales
                    }
                ]
            };
                                

            // 使用刚指定的配置项和数据显示图表。
            newEchart.setOption(option);
            
        }
    };

    return Controller;
});