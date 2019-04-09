import { dateFormat } from '../../../utils/util'

const app = getApp()

Page({
    data: {
        globalData: app.globalData,
        list: [],
        key: 'notUsed',
        index: 0,
    },
    onLoad() {
        this.setData({ globalData: app.globalData })
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
        const cb = () => app.request('/my/coupons', {}, (data, ret) => {
            console.log(data)
            const { used, notUsed, overdues } = data.coupons
            this.setData({
                list: [{
                    name: '未使用',
                    type: 'notUsed',
                    detailed: notUsed.map((n) => {
                        return {
                            ...n,
                            date: `${dateFormat(n.createtime * 1000)} - ${dateFormat(n.validity_datetime * 1000)}`,
                        }
                    }),
                }, {
                    name: '已使用',
                    type: 'used',
                    detailed: used.map((n) => {
                        return {
                            ...n,
                            date: `${dateFormat(n.createtime * 1000)} - ${dateFormat(n.validity_datetime * 1000)}`,
                        }
                    }),
                }, {
                    name: '已过期',
                    type: 'overdues',
                    detailed: overdues.map((n) => {
                        return {
                            ...n,
                            date: `${dateFormat(n.createtime * 1000)} - ${dateFormat(n.validity_datetime * 1000)}`,
                        }
                    }),
                }],
            })
            wx.stopPullDownRefresh()
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })

        app.checkConfig(cb, this)
    },
    onChange(e) {
        const { key } = e.detail
        const index = this.data.list.map((n) => n.type).indexOf(key)

        this.setData({
            key,
            index,
        })
    },
    onSwiperChange(e) {
        const { current: index, source } = e.detail
        const { type: key } = this.data.list[index]

        if (!!source) {
            this.setData({
                key,
                index,
            })
        }
    },
})