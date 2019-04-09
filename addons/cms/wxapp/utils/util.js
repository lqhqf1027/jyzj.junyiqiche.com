const formatTime = date => {
    const year = date.getFullYear()
    const month = date.getMonth() + 1
    const day = date.getDate()
    const hour = date.getHours()
    const minute = date.getMinutes()
    const second = date.getSeconds()

    return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

const formatNumber = n => {
    n = n.toString()
    return n[1] ? n : '0' + n
}

const dateFormat = (val, fmt = 'yyyy-MM-dd') => {
    var date = new Date(val)
    var o = {   
        "M+" : date.getMonth()+1,                 //月份   
        "d+" : date.getDate(),                    //日   
        "h+" : date.getHours(),                   //小时   
        "m+" : date.getMinutes(),                 //分   
        "s+" : date.getSeconds(),                 //秒   
        "q+" : Math.floor((date.getMonth()+3)/3), //季度   
        "S"  : date.getMilliseconds()             //毫秒   
    };   
    if(/(y+)/.test(fmt)){
        fmt = fmt.replace(RegExp.$1, (date.getFullYear()+"").substr(4 - RegExp.$1.length));  
    } 
    for(var k in o) {
        if(new RegExp("("+ k +")").test(fmt)){
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
        }
    }
    return fmt;
}

const updateConfig = (nickname = '', avatar = '', qrcode = '', bgcolor = '') => {
    return {
        width: 750,
        height: 1017,
        backgroundColor: '#fff',
        debug: false,
        texts: [
            {
                x: 200,
                y: 80,
                text: nickname,
                fontSize: 36,
                color: '#fff',
            },
        ],
        images: [
            {
                width: 750,
                height: 1017,
                x: 0,
                y: 0,
                url: bgcolor,
            },
            {
                width: 100,
                height: 100,
                x: 60,
                y: 40,
                borderRadius: 20,
                url: avatar,
            },
            {
                width: 218,
                height: 254,
                x: 500,
                y: 740,
                url: qrcode,
            },
        ],
    }
}

module.exports = {
    dateFormat: dateFormat,
    updateConfig: updateConfig,
    formatTime: formatTime
}