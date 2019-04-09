const app = getApp()

Page({
    data: {
        new: [],
        used: [],
        logistics: [],
        searchModels: [],
        inputVal: '',
    },
    onLoad() {
        this.setData({
            searchModels: this.getSearchModels(),
        })
    },
    setSearchModels(data) {
        return wx.setStorageSync('searchModels', data || [])
    },
    getSearchModels() {
        return wx.getStorageSync('searchModels') || []
    },
    updateSearchModels(value) {
        const index = !value ? 5 : 4
        let searchModels = this.getSearchModels().reverse().slice(0, index).reverse()

        if (value) {
            searchModels = searchModels.filter((n) => n !== value)
            searchModels = [...searchModels, value]
        }

        this.setSearchModels(searchModels)

        return searchModels
    },
    onClear() {
        this.setSearchModels()
        this.setData({
            searchModels: [],
        })
    },
    onCancel() {
        wx.navigateBack()
    },
    onChange(e) {
        console.log('onChange', e)
        const { value } = e.detail
        this.setData({ inputVal: value })
        this.getList(value)
    },
    onConfirm(e) {
        console.log('onConfirm', e)
        const { value } = e.detail

        this.setData({
            searchModels: this.updateSearchModels(value),
        })
        this.switchTab(value)
    },
    onSelect(e) {
        const { name } = e.currentTarget.dataset

        this.switchTab(name)
    },
    getList(queryModels) {
        const city = wx.getStorageSync('city')

        if (this.timeout) {
            clearTimeout(this.timeout)
            this.timeout = null
        }

        if (!queryModels || !city) {
            this.setData({
                new: [],
                used: []
            })
            return
        }

        this.timeout = setTimeout(() => {
            app.request('/share/searchModels?noAuth=1', { city_id: city.id, queryModels }, (data, ret) => {
                console.log(data)
                this.setData({
                    new: data && data.searchModel.new,
                    used: data && data.searchModel.used,
                    logistics: data && data.searchModel.logistics,
                })
            }, (data, ret) => {
                console.log(data)
                app.error(ret.msg)
            })
        }, 250)
    },
    onClick(e) {
        console.log(e)
        const { value } = e.currentTarget.dataset
        const { name, style, type } = value

        this.setData({
            searchModels: this.updateSearchModels(name),
        })
        this.switchTab(name, type || style)
    },
    switchTab(name = '', style = 'new') {
        wx.setStorage({
            key: 'searchVal',
            data: {
                name,
                style,
            },
            success() {
                wx.switchTab({
                    url: '/page/index/index',
                })
            },
        })
    },
})