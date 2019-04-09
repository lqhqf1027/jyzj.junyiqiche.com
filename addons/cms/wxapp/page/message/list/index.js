import timeago from '../../../utils/timeago'

const app = getApp()

Page({
    data: {
    	messageList: [],
    },
	onShow() {
		this.getList()
	},
    onPullDownRefresh() {
        this.getList()
    },
    onRefresh() {
        this.getList()
    },
	getList() {
        app.request('/my/messageList', {}, (data, ret) => {
            console.log(data)
            this.setData({
            	messageList: data && data.messageList.map((n) => ({ ...n, timeago: timeago(n.createtime * 1000) })),
            })
            wx.stopPullDownRefresh()
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })
    },
})