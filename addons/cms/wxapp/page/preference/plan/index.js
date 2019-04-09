const app = getApp()

Page({
    data: {
        plan: {},
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
        const plan_id = this.options.id
        const cartype = this.options.type

      app.request('/share/plan_details', { plan_id, cartype }, (data, ret) => {
            console.log(data)
            this.setData({
                plan: data && data.plan,
            })
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })
    },
})