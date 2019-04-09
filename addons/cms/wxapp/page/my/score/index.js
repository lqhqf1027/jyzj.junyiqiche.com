import timeago from '../../../utils/timeago'

const app = getApp()

Page({
    data: {
        currentScore: 0,
        integral: [],
        key: 'fabulous',
        index: 0,
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
        app.request('/my/myScore', {}, (data, ret) => {
            console.log(data)
            this.setData({
                integral: data && data.integral.map((n) => {
                    return {
                        ...n,
                        detailed: n.detailed.map((m) => ({ ...m, timeago: timeago((m.fabuloustime || m.sign_time) * 1000) }))
                    }
                }),
                currentScore: data && data.currentScore,
            })
            wx.stopPullDownRefresh()
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })
    },
    onChange(e) {
        const { key } = e.detail
        const index = this.data.integral.map((n) => n.type).indexOf(key)

        this.setData({
            key,
            index,
        })
    },
    onSwiperChange(e) {
        const { current: index, source } = e.detail
        const { type: key } = this.data.integral[index]

        if (!!source) {
            this.setData({
                key,
                index,
            })
        }
    },
})