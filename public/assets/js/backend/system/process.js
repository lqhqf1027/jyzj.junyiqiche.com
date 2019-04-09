define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {

            //新车表
            // 基于准备好的dom，初始化echarts实例
            var newEchart = Echarts.init(document.getElementById('newechart'), 'walden');

            // 指定图表的配置项和数据
            var option = {
                title : {
                    text: '以租代购（新车）签单流程',
                    subtext: ''
                },
                tooltip : {
                    trigger: 'item',
                    formatter: "{b}: {c}: {d}"
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : false,
            
                series : [
                    {
                        name:'树图',
                        type:'tree',
                        // orient: 'horizontal',  // vertical horizontal
                        // rootLocation: {x: 100, y: '60%'}, // 根节点位置  {x: 'center',y: 10}
                        // nodePadding: 20,
                        // symbol: '',
                        // symbolSize: 40,
                        // itemStyle: {
                        //     normal: {
                        //         label: {
                        //             show: true,
                        //             position: 'inside',
                        //             textStyle: {
                        //                 color: '#cc9999',
                        //                 fontSize: 15,
                        //                 fontWeight:  'bolder'
                        //             }
                        //         },
                                // lineStyle: {
                                //     color: '#000',
                                //     width: 1,
                                //     type: 'broken' // 'curve'|'broken'|'solid'|'dotted'|'dashed'
                                // }
                            // },
                            // emphasis: {
                            //     label: {
                            //         show: true
                            //     }
                            // }
                        // },
                        data: [
                            {
                                name: '新车预定',
                                value: '销售选择方案，填写资料',
                                symbolSize: [90, 70],
                                symbol: '',
                                itemStyle: {
                                    normal: {
                                        label: {
                                            show: false
                                        }
                                    }
                                },
                                children: [
                                    {
                                        name: '发送内勤',
                                        value: '内勤录入定金金额',
                                        symbol: '', 
                                        symbolSize: [90, 70],
                                        itemStyle: {
                                            normal: {
                                                label: {
                                                    show: false
                                                }
                                            }
                                        },
                                        children: [
                                            {
                                                name: '车管处理',
                                                symbol: '',
                                                symbolSize: [90, 70],
                                                value: '内勤录入定金，提交给车管',
                                                itemStyle: {
                                                    normal: {
                                                        label: {
                                                            show: false
                                                        }
                                                    }
                                                },
                                                children: [
                                                    {
                                                        name: '正在匹配金融',
                                                        value: '车管确认，提交财务，进行金融匹配',
                                                        symbol: '',
                                                        symbolSize: [90, 70],
                                                        itemStyle: {
                                                            normal: {
                                                                label: {
                                                                    show: false
                                                                }
                                                            }
                                                        },
                                                        children: [
                                                            {
                                                                name: '正在匹配金融',
                                                                value: '车管确认，提交财务，进行金融匹配',
                                                                symbol: '',
                                                                symbolSize: [90, 70],
                                                                itemStyle: {
                                                                    normal: {
                                                                        label: {
                                                                            show: false
                                                                        }
                                                                    }
                                                                },
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
            };
                                
                            
            // 使用刚指定的配置项和数据显示图表。
            newEchart.setOption(option);

        }
    };

    return Controller;
});