const app = getApp()
Page({
    data: {
        vehicle_configuration: {},
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
                vehicle_configuration: data && data.plan.models.vehicle_configuration,
            })
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })
    },
})