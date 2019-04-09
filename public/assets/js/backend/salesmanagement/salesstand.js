define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    /**
     * 销售看台
     * @type {{index: index}}
     */
    var Controller = {
        index: function () {

            //销售一部
            // 基于准备好的dom，初始化echarts实例
            var oneEchart = Echarts.init(document.getElementById('oneechart'), 'walden');

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '销售一部销售情况（单位：月）',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ["以租代购（新车）","租车","以租代购（二手车）","全款车"]
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
                    data: Orderonedata.column
                },
                yAxis: {},
                grid: [{
                    left: 'left',
                    top: 'top',
                    right: '10',
                    bottom: 30
                }],
                series: [{
                    name: "以租代购（新车）",
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        normal: {}
                    },
                    lineStyle: {
                        normal: {
                            width: 1.5
                        }
                    },
                    data: Orderonedata.newonesales
                },
                    {
                        name: "租车",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {}
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderonedata.rentalonesales
                    },
                    {
                        name: "以租代购（二手车）",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: 'pink'
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderonedata.secondonesales
                    },
                    {
                        name: "全款车",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: 'red'
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderonedata.fullonesales
                    }
                ]
            };

            // 使用刚指定的配置项和数据显示图表。
            oneEchart.setOption(option);


            //销售二部
            // 基于准备好的dom，初始化echarts实例
            var secondEchart = Echarts.init(document.getElementById('secondechart'), 'walden');

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '销售二部销售情况（单位：月）',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ["以租代购（新车）","租车","以租代购（二手车）","全款车"]
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
                    data: Orderseconddata.column
                },
                yAxis: {},
                grid: [{
                    left: 'left',
                    top: 'top',
                    right: '10',
                    bottom: 30
                }],
                series: [{
                    name: "以租代购（新车）",
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        normal: {}
                    },
                    lineStyle: {
                        normal: {
                            width: 1.5
                        }
                    },
                    data: Orderseconddata.newsecondsales
                },
                    {
                        name: "租车",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {}
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderseconddata.rentalsecondsales
                    },
                    {
                        name: "以租代购（二手车）",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: 'pink'
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderseconddata.secondsecondsales
                    },
                    {
                        name: "全款车",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: 'red'
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderseconddata.fullsecondsales
                    }
                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            secondEchart.setOption(option);


            //销售三部
            // 基于准备好的dom，初始化echarts实例
            var threeEchart = Echarts.init(document.getElementById('threeechart'), 'walden');

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '销售三部销售情况（单位：月）',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ["以租代购（新车）","租车","以租代购（二手车）","全款车"]
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
                    data: Orderthreedata.column
                },
                yAxis: {},
                grid: [{
                    left: 'left',
                    top: 'top',
                    right: '10',
                    bottom: 30
                }],
                series: [{
                    name: "以租代购（新车）",
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        normal: {}
                    },
                    lineStyle: {
                        normal: {
                            width: 1.5
                        }
                    },
                    data: Orderthreedata.newthreesales
                },
                    {
                        name: "租车",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {}
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderthreedata.rentalthreesales
                    },
                    {
                        name: "以租代购（二手车）",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: 'pink'
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderthreedata.secondthreesales
                    },
                    {
                        name: "全款车",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: 'red'
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderthreedata.fullthreesales
                    }
                ]
            };

            // 使用刚指定的配置项和数据显示图表。
            threeEchart.setOption(option);
            

        }
    };

    return Controller;
});