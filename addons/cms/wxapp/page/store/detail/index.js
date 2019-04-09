const app = getApp()
const isTel = (value) => /^1[3456789]\d{9}$/.test(value)
const defaultItems = [{
        type: 'radio',
        label: '默认排序',
        value: '1',
        children: [{
                label: '推荐排序',
                value: '11',
            },
            {
                label: '首付最低',
                value: '12',
            },
            {
                label: '月供最低',
                value: '13',
            },
            {
                label: '人气最高',
                value: '14',
            },
            {
                label: '车价最低',
                value: '15',
            },
        ],
    },
    {
        type: 'text',
        label: '品牌',
        value: '2',
    },
    {
        type: 'text',
        label: '首付',
        value: '3',
        children: [{
                label: '不限',
                value: '0-6',
                range: [0],
            },
            {
                label: '1万以内',
                value: '0-1',
                range: [0, 10000],
            },
            {
                label: '1-2万',
                value: '1-2',
                range: [10000, 20000],
            },
            {
                label: '2-3万',
                value: '2-3',
                range: [20000, 30000],
            },
            {
                label: '3-4万',
                value: '3-4',
                range: [30000, 40000],
            },
            {
                label: '4-5万',
                value: '4-5',
                range: [40000, 50000],
            },
            {
                label: '5万以上',
                value: '5-6',
                range: [50000],
            },
        ],
    },
    {
        type: 'text',
        label: '月供',
        value: '4',
        children: [{
                label: '不限',
                value: '0-6000',
                range: [0],
            },
            {
                label: '2000元以内',
                value: '0-2000',
                range: [0, 2000],
            },
            {
                label: '2000-3000元',
                value: '2000-3000',
                range: [2000, 3000],
            },
            {
                label: '3000-4000元',
                value: '3000-4000',
                range: [3000, 4000],
            },
            {
                label: '4000-5000元',
                value: '4000-5000',
                range: [4000, 5000],
            },
            {
                label: '5000元以上',
                value: '5000-6000',
                range: [5000],
            },
        ],
    },
]

const getRange = (value, array = [], num = 1) => {
    const reslut = array.filter(function(n) { return n.value === value })[0]
    if (reslut) {
        return reslut.range
    }
    return value && value !== '0-0' ? value.split('-').map((n) => Number(n) * num) : [0]
}

const checkValueInRange = (value = 0, min = 0, max = Infinity) => {
    return value >= min && value <= max
}

const defaultSearchValue = {
    sort: '',
    name: '',
    style: 'new',
    payment: [0, 0],
    monthly: [0, 0],
}

Page({
    data: {
        tabs: [{
            type: 'new',
            car_type_name: '新车',
        },{
            type: 'used',
            car_type_name: '二手车',
        },{
            type: 'logistics',
            car_type_name: '新能源车',
        }],
        items: defaultItems,
        list: [],
        logisticsList: [],
        newcarList: [],
        usedcarList: [],
        allList: [],
        brandList: {},
        carBrandList: [],
        searchVal: {...defaultSearchValue },
        globalData: app.globalData,
        info: {},
        logic: [],
        plans: [],
        backtop: false,
        scrollTop: 0,
        wishVisible: false,
        wishCodeText: '获取验证码',
        form: {
            fill_models: '',
            expectant_city: '',
            mobile: '',
            code: '',
        },
    },
    makePhoneCall() {
        const { phone } = this.data.info || {}

        if (!phone) return

        wx.makePhoneCall({
            phoneNumber: phone,
        })
    },
    onPageScroll(e) {
        this.setData({
            backtop : e.scrollTop > 100,
        })
    },
    backtop() {
        this.setData({ backtop: false })
        wx.pageScrollTo({
            scrollTop: 0,
            duration: 300,
        })
    },
    goHome() {
        wx.switchTab({
            url: '/page/preference/list/index',
        })
    },
    onPageScroll(e) {
        if (!this.data.sticky) return

        const { top, height } = this.data.sticky
        const isFixed = e.scrollTop >= top

        this.setData({
            scrollTop: e.scrollTop,
            isFixed,
        })
    },
    updateDataChange(isForce) {
        console.log('updateDataChange', this.data.sticky && !isForce)
        if (this.data.sticky && !isForce) return

        const className = '.i-sticky-item';
        const query = wx.createSelectorQuery().in(this);
        query.select( className ).boundingClientRect((res)=>{
                if( res ){
                    this.setData({
                        sticky: {
                            top: res.top,
                            height: res.height,
                        },
                    })
                }
        }).exec()
    },
    onShareAppMessage() {},
    onLoad(options) {
        console.log(options)
        this.options = options        
    },
    onShow() {
        const { style } = wx.getStorageSync('searchVal') || {}
        this.getList(style)
        this.setData({ globalData: app.globalData })
    },
    onReady() {
        // this.updateDataChange()
    },
    onPullDownRefresh() {
        this.getList(this.data.searchVal.style, true)
    },
    onRefresh() {
        this.onPullDownRefresh()
    },
    /**
     * 门店详情接口
     * @param {String} 车辆类型
     * @param {Boolean} 是否强制更新
     */
    getList(cartype = this.data.searchVal.style, isForce, callback) {
        const store_id = this.options.id
        const noChanged = !isForce

        if (noChanged) {
            if (this.data.brandList[cartype]) {
                this.setCars({...this.data.searchVal, style: cartype}, callback)
                return
            }
        } else {
            this.setData({
                brandList: {},
            })
        }
        
        const cb = () => app.request('/store/store_details', { store_id, cartype }, (data, ret) => {
            console.log(data)

            let carSelectList = [data.plans]
            let logisticsList = (!noChanged || cartype === 'logistics') ? [] : this.data.logisticsList
            let newcarList = (!noChanged || cartype === 'new') ? [] : this.data.newcarList
            let usedcarList = (!noChanged || cartype === 'used') ? [] : this.data.usedcarList
            let brandList = this.data.brandList || {}

            if (carSelectList.length > 0) {
                carSelectList.forEach((n) => {
                    console.log(n)
                    brandList[n.type] = {}
                    if (n.type === 'new') {
                        n.carList.forEach((m) => {
                            if (brandList[n.type][m.brand_initials] = brandList[n.type][m.brand_initials] || []) {
                                if (!brandList[n.type][m.brand_initials].map((n) => n.id).includes(m.id)) {
                                    brandList[n.type][m.brand_initials].push({
                                        id: m.id,
                                        name: m.name,
                                    })
                                }
                            }
                            newcarList = [...newcarList, ...m.planList.map((v) => ({...v, brand_id: m.id }))]
                        })
                    } else if (n.type === 'used') {
                        n.carList.forEach((m) => {
                            if (brandList[n.type][m.brand_initials] = brandList[n.type][m.brand_initials] || []) {
                                if (!brandList[n.type][m.brand_initials].map((n) => n.id).includes(m.id)) {
                                    brandList[n.type][m.brand_initials].push({
                                        id: m.id,
                                        name: m.name,
                                    })
                                }
                            }
                            usedcarList = [...usedcarList, ...m.planList.map((v) => ({...v, brand_id: m.id }))]
                        })
                    } else if (n.type === 'logistics') {
                        n.carList.forEach((m) => {
                            if (brandList[n.type][m.brand_initials] = brandList[n.type][m.brand_initials] || []) {
                                if (!brandList[n.type][m.brand_initials].map((n) => n.id).includes(m.id)) {
                                    brandList[n.type][m.brand_initials].push({
                                        id: m.id,
                                        name: m.name,
                                    })
                                }
                            }
                            logisticsList = [...logisticsList, ...m.planList.map((v) => ({...v, brand_id: m.id }))]
                        })
                    }
                })
            }

            this.setData({
                info: data && data.info,
                logic: data && data.logic || [],
                brandList,
                logisticsList,
                newcarList,
                usedcarList,
                allList: [...logisticsList, ...newcarList, ...usedcarList],
            }, () => {
                this.updateDataChange()
                this.setCars({ ...this.data.searchVal, style: cartype }, callback)
            })

            wx.stopPullDownRefresh()
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })

        app.checkConfig(cb, this)
    },
    onBrand(e) {
        const { brand } = e.currentTarget.dataset

        this.setData({ 'searchVal.brand': brand }, this.onSelectChange)
    },
    onOpenDetail(e) {
        const { id, type } = e.currentTarget.dataset

        wx.navigateTo({
            url: `/page/preference/detail/index?id=${id}&type=${type}`,
        })
    },
    getBrandList() {
        let carBrandList = []
        const { brandList, searchVal } = this.data
        const data = brandList[searchVal.style]
        const words = Object.keys(data)

        words.forEach((item, index) => {
            carBrandList[index] = {
                key: item,
                list: data[item].map((n, i) => ({...n, key: n.id })),
            }
        })

        this.setData({ carBrandList })
    },
    setFilter(style, cb) {
        let items = [...defaultItems]
        const searchVal = {...defaultSearchValue, style }
        
        if (style === 'used') {
            items = items.filter((n) => n.value !== '4').map((n) => {
                if (n.value === '1') {
                    return Object.assign({}, n, {
                        children: n.children.filter((m) => m.value !== '13'),
                    })
                }
                
                return n
            })
        }

        this.setData({
            items,
            searchVal,
            backdrop: false,
            list: [],
        }, () => this.getList(style, false, cb))
    },
    onChange(e) {
        console.log(e)
        this.setFilter(e.detail.key, () => {
            const { sticky } = this.data
            if (sticky) {
                this.setData({ scrollTop: sticky.top })
                wx.pageScrollTo({ scrollTop: sticky.top, duration: 0 })
            }
        })
    },
    setCars(searchVal = this.data.searchVal, cb) {
        const { sort, brand, style, name, payment, monthly } = searchVal

        console.log('searchVal', searchVal)

        let list = []

        // 按类型过滤
        if (style === 'new') {
            list = [...this.data.newcarList]
        } else if (style === 'used') {
            list = [...this.data.usedcarList]
        } else if (style === 'logistics') {
            list = [...this.data.logisticsList]
        } else {
            list = [...this.data.allList]
        }

        // 按名称过滤
        if (name) {
            list = list.filter((n) => n.models && n.models.name.indexOf(name) !== -1)
        }

        // 按品牌过滤
        if (brand) {
            list = list.filter((n) => n.brand_id === brand.id)
        }

        // 按首付过滤
        if (payment) {
            const value = payment.map((n) => Number(n) / 10).join('-')
            const range = getRange(value, defaultItems[2]['children'], 10000)
            console.log('payment', range)
            list = list.filter((n) => checkValueInRange(n.payment, range[0], range[1]))
        }

        // 按月供过滤
        if (monthly) {
            const value = monthly.map((n) => Number(n) * 100).join('-')
            const range = getRange(value, defaultItems[3]['children'])
            console.log('monthly', range)
            list = list.filter((n) => checkValueInRange(n.monthly, range[0], range[1]))
        }

        // 排序
        if (sort === '12') {
            list = list.sort((a, b) => a.payment - b.payment)
        } else if (sort === '13') {
            list = list.sort((a, b) => a.monthly - b.monthly)
        } else if (sort === '14') {
            list = list.sort((a, b) => b.popularity - a.popularity)
        } else if (sort === '15') {
            list = list.sort((a, b) => a.models.price - b.models.price)
        }

        console.log('list', list)

        this.setData({
            searchVal,
            list,
        }, cb)
    },
    onTag(e) {
        const { meta } = e.currentTarget.dataset
        const { searchVal, items } = this.data

        console.log('onTag', meta)

        if (meta === 'name') {
            searchVal.name = ''
        } else if (meta === 'brand') {
            searchVal.brand = ''
        } else if (meta === 'payment' || meta === 'monthly') {
            searchVal[meta] = [0, 0]
            const index = meta === 'payment' ? 2 : 3
            const children = items[index].children.map((n) => Object.assign({}, n, {
                checked: false,
            }))

            this.setData({
                [`items[${index}].children`]: children,
            })
        }

        console.log('searchVal', searchVal)

        this.setCars(searchVal)
    },
    onReset() {
        console.log('onReset', defaultSearchValue)
        this.setCars({...defaultSearchValue })
    },
    onCancel() {
        const { index } = this.data
        const items = this.data.items.map((n, i) => {
            return Object.assign({}, n, {
                checked: index !== i ? n.checked : false,
                visible: index !== i ? n.checked : false,
            })
        })

        this.setData({
            items,
            backdrop: false,
        })
    },
    onClick(e) {
        const { index, checked } = e.currentTarget.dataset
        const items = this.data.items.map((n, i) => {
            return Object.assign({}, n, {
                checked: index === i ? !checked : false,
                visible: index === i ? !checked : false,
            })
        })

        if (index === 1) {
            this.getBrandList()
        }

        this.setData({
            index,
            items,
            backdrop: !checked,
        })
    },
    radioChange(e) {
        const { value } = e.detail
        const { index, item } = e.currentTarget.dataset
        const children = item.children.map((n) => Object.assign({}, n, {
            checked: n.value === value,
        }))
        const params = {
            'searchVal.sort': value,
            [`items[${index}].children`]: children,
        }

        this.setData(params, this.onSelectChange)
    },
    onRadioChange(e) {
        const { value } = e.detail
        const { index, item } = e.currentTarget.dataset
        const children = item.children.map((n) => Object.assign({}, n, {
            checked: n.value === value,
        }))
        const params = {
            [`items[${index}].children`]: children,
        }

        if (index === 2) {
            params['searchVal.payment'] = value.split('-').map((n) => n * 10)
        } else if (index === 3) {
            params['searchVal.monthly'] = value.split('-').map((n) => n / 100)
        }

        console.log(params)

        this.setData(params, this.onSelectChange)
    },
    onPaymentChange(e) {
        const index = 2
        const item = this.data.items[index]
        const children = item.children.map((n) => Object.assign({}, n, {
            checked: false,
        }))
        console.log(e)
        this.setData({
            [`items[${index}].children`]: children,
            'searchVal.payment': e.detail.value,
        }, this.setCars)
    },
    onMonthlyChange(e) {
        const index = 3
        const item = this.data.items[index]
        const children = item.children.map((n) => Object.assign({}, n, {
            checked: false,
        }))
        console.log(e)
        this.setData({
            [`items[${index}].children`]: children,
            'searchVal.monthly': e.detail.value,
        }, this.setCars)
    },
    onSelectChange() {
        const items = this.data.items.map((n) => ({...n, checked: false, visible: false }))

        setTimeout(() => {
            this.setData({
                items,
                backdrop: false,
            }, this.setCars)
        }, 300)
    },
    receiveCoupons(e) {
        const { id } = e.currentTarget.dataset

        app.request('/store/receiveCoupons', { coupon_id: id }, (data, ret) => {
            console.log(data)
            this.onRefresh()
            app.success(ret.msg)
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })
    },
    /**
     * 发送验证码
     * @param {number} mobile 手机号
     */
    sendSMS(mobile) {
        app.request('/share/sendMessage', { mobile }, (data, ret) => {
            console.log(data)
            app.success(ret.msg)
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })
    },
    /**
     * 获取验证码
     */
    getWishCode() {
        if (this.disabled || this.timeout)  return

        // 验证手机号码
        if (!this.data.form.mobile) {
            wx.showToast({ title: '请输入联系电话', icon: 'none' })
            return
        } else if (!isTel(this.data.form.mobile)) {
            wx.showToast({ title: '请输入正确的联系电话', icon: 'none' })
            return
        }

        // 倒计时
        this.renderWishCodeText()

        // 发送验证码
        this.sendSMS(this.data.form.mobile)
    },
    /**
     * 60s倒计时
     * @param {number} [num=60] 倒计时时间
     */
    renderWishCodeText(num = 60) {
        this.disabled = num !== 0

        if (num <= 0) {
            clearTimeout(this.timeout)
            this.timeout = null
            this.setData({ wishCodeText: '重新发送' })
            return
        }

        this.setData({ wishCodeText: `${num--} 秒` })

        this.timeout = setTimeout(() => {
            this.renderWishCodeText(num)
        }, 1000)
    },
    onWish() {
        const { userInfo } = app.getGlobalData()

        // 判断是否已授权，否则提示
        if (!userInfo || !userInfo.id) {
            app.showLoginModal(function(){}, function(){}, true)
            return
        }

        this.setData({ wishVisible: true })
    },
    onWishClose() {
        this.setData({ wishVisible: false })
    },
    onInputChange(e) {
        const { model } = e.currentTarget.dataset
        const { value } = e.detail
        const isMobile = model === 'form.mobile' ? isTel(value) : isTel(this.data.form.mobile)

        this.setData({ [model]: value, isMobile })
    },
    onSubmit() {
        const { fill_models, expectant_city, mobile, code } = this.data.form

        if (!expectant_city) {
            wx.showToast({ title: '请输入意向购车城市', icon: 'none' })
            return
        } else if (!fill_models) {
            wx.showToast({ title: '请输入意向车型', icon: 'none' })
            return
        } else if (!mobile) {
            wx.showToast({ title: '请输入联系电话', icon: 'none' })
            return
        } else if (!isTel(mobile)) {
            wx.showToast({ title: '请输入正确的联系电话', icon: 'none' })
            return
        } else if (!code) {
            wx.showToast({ title: '请输入验证码', icon: 'none' })
            return
        }

        app.request('/share/wishList', { fill_models, expectant_city, mobile, code }, (data, ret) => {
            console.log(data)
            this.onWishClose()
            app.success(ret.msg)
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })
    },
})