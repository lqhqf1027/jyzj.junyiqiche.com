const app = getApp()

Page({
    data: {
        cdn_url: '',
        store_layout: '',
        list: [],
        store: [],
        activeIndex: 0,
    },
    onLoad() {
        // this.getList()
    },
    getList() {
        app.request('/store/store_show?noAuth=1', {}, (data, ret) => {
            console.log(data)

            let list = []

            Object.keys(data.list).forEach((n) => {
                list = [...list, ...data.list[n]]
            })

            this.setData({
                cdn_url: data.cdn_url,
                store_layout: data.store_layout,
                list,
                store: list,
            })

            wx.stopPullDownRefresh()
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })
    },
    changeTab(e) {
        const { index } = e.currentTarget.dataset

        this.setData({
            activeIndex: index,
        })
    },
    onChange(e) {
        console.log(e)
        const { value } = e.detail
        const list = this.data.store.filter((n) => n.cities_name.indexOf(value.trim()) !== -1)

        this.setData({
            list,
        })
    },
    onOpenDetail(e) {
        const { id } = e.currentTarget.dataset

        wx.navigateTo({
            url: `/page/store/detail/index?id=${id}`,
        })
    },
    onShow: function () {
      this.getList();
    },
    onPullDownRefresh() {
      this.getList();
    },
    previewImage(e) {
        const { url } = e.currentTarget.dataset

        wx.previewImage({
            current: url,
            urls: [url],
        })
    },
})