import { dateFormat } from '../../../utils/util'

const app = getApp()

Page({
    data: {
    	messageDetails: {},
    },
    onLoad(options) {
        console.log(options)
        this.options = options
        this.getDetail()
    },
    onRefresh() {
        this.getDetail()
    },
	getDetail() {
		const message_id = this.options.id
        const isRead = this.options.read

        app.request('/my/messageDetails', { message_id, isRead }, (data, ret) => {
            console.log(data)
            this.setData({
            	messageDetails: Object.assign({}, data.messageDetails, {
            		date: dateFormat(data.messageDetails.createtime * 1000),
            	}),
            })
            wx.stopPullDownRefresh()
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })
    },
})