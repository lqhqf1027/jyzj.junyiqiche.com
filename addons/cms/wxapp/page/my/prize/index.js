import timeago from '../../../utils/timeago'

const app = getApp()

Page({
    data: {
    	globalData: app.globalData,
        prizeList: [],
    },
	onShow() {
		this.setData({ globalData: app.globalData })
		this.getList()
	},
    onPullDownRefresh() {
        this.getList()
    },
    onRefresh() {
        this.getList()
    },
	getList() {
        app.request('/my/prizeList', {}, (data, ret) => {
            console.log(data)
            this.setData({
                prizeList: data && data.map((n) => {
                    return {
                        ...n,
                        timeago: timeago(n.awardtime * 1000)
                    }
                }),
            })
            wx.stopPullDownRefresh()
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })
    },
})