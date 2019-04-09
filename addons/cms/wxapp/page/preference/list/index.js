import { $wuxActionSheet } from '../../../assets/libs/wux/index'
import Poster from '../../../assets/libs/wxa-plugin-canvas/poster/poster'
import { updateConfig } from '../../../utils/util'

var app = getApp()

Page({
    data: {
        tags: [{
            label: '1万以内',
            payment: [0, 10],
        }, {
            label: '1-2万',
            payment: [10, 20],
        }, {
            label: '2-3万',
            payment: [20, 30],
        }, {
            label: '4万以上',
            payment: [40],
        }],
        swiperIndex: 'index',
        globalData: {},
        shares: {},
        brandPlan: {},
        brand_id: '',
        city: app.globalData.city,
        deviceHeight: '100%',
        posterConfig: {},
    },
    showActionSheet() {
        $wuxActionSheet().showSheet({
            theme: 'wx',
            buttons: [{
                    text: '立即分享',
                    icon: '/assets/images/wx.jpg',
                    openType: 'share',
                },
                {
                    text: '生成海报，保存分享',
                    icon: '/assets/images/share.jpg',
                },
            ],
            buttonClicked: (index, item) => {
                if (index === 1) {
                    this.onPosterCreate()
                }

                return true
            },
            cancelText: '取消',
            cancel() {},
        })
    },
    onPosterCreate() {
        const { index_share_bk_qrcode, share_moments_bk_img } = app.globalData.shares || {}
        const { userInfo } = app.getGlobalData() || {}
        const { nickname, avatar } = userInfo || {}
        const qrcode = app.cdnurl(index_share_bk_qrcode)
        const bgcolor = app.cdnurl(share_moments_bk_img)

        if (!index_share_bk_qrcode || !share_moments_bk_img || !avatar) {
            app.showLoginModal(function() {}, function() {}, true)
            return
        }

        this.setData({
            isPoster: true,
            posterConfig: updateConfig(nickname, avatar, qrcode, bgcolor)
        }, () => Poster.create())
    },
    onPosterSuccess(e) {
        console.log('onPosterSuccess', e)
        this.setData({
            posterVisible: true,
            posterUrl: e.detail,
        })
        // const { detail } = e
        // wx.previewImage({
        //     current: detail,
        //     urls: [detail]
        // })
    },
    onPosterFail(e) {
        console.log('onPosterFail', e)
    },
    onPosterClose() {
        this.setData({ posterVisible: false })
    },
    onPosterSaveImage() {
        wx.saveImageToPhotosAlbum({
            filePath: this.data.posterUrl,
            success: () => {
                app.success('保存成功')
                this.onPosterClose()
            },
        })
    },
    onShareAppMessage() {
        const { index_share_title, index_share_img } = app.globalData.shares || {}

        return {
            title: index_share_title,
            path: '/page/preference/list/index',
            imageUrl: app.cdnurl(index_share_img),
        }
    },
    channel: 0,
    page: 1,
    onPageScroll(e) {
        this.setData({
            fixed: e && e.scrollTop,
        })
    },
    onLoad() {
        this.getSystemInfo()
        wx.setStorageSync('city', app.globalData.city)
    },
    onShow() {
        this.setGlobalData()
    },
    onPullDownRefresh() {
        this.setGlobalData(true)
    },
    setGlobalData(isForce) {
        const that = this
        const city = wx.getStorageSync('city')
        const noChanged = city && city.id === this.data.city.id && !isForce

        console.log('noChanged', noChanged)

        // 判断是否同一城市下，取其缓存
        if (noChanged) {
            if (this.data.brandList && this.data.brandList.length) {
                return
            }
        }

        this.setData({
            city,
        })

        const cb = () => app.request('/common/init?noAuth=1', { city_id: city.id }, function(data, ret) {
            app.globalData.config = data.config;
            app.globalData.bannerList = data.bannerList;
            app.globalData.shares = data.shares;

            wx.setStorageSync('config', data.config)

            that.setData({
                globalData: app.globalData,
                appointment: data.appointment.map((n) => {
                    const mobile = n.mobile ? n.mobile.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2') : ''
                    const content = `${mobile} 成功下单 ${n.models_name}`

                    return {...n, content }
                }),
                brandList: data.brandList,
                carType: data.carType,
                shares: data.shares,
            })

            wx.stopPullDownRefresh()

            //如果需要一进入小程序就要求授权登录,可在这里发起调用
            // app.check(function(ret) {
            //     callback()
            // });
        }, function(data, ret) {
            app.error(ret.msg);
        });

        app.checkConfig(cb, this)
    },
    bindchange(e) {
        console.log(e)
    },
    onClose() {
        this.setData({
            visible: false,
        })
    },
    onBrandPlan(e) {
        const { id } = e.currentTarget.dataset
        const { brandPlan } = this.data

        console.log(id)

        var that = this
        var city = wx.getStorageSync('city')

        // 判断是否同一城市下，取其缓存
        if (city && city.id === this.data.city.id) {
            if (brandPlan[id]) {
                this.setData({
                    visible: true,
                    brand_id: id,
                })
                return
            }
        }

        that.setData({
            city,
        })

        app.request('/index/brandPlan?noAuth=1', { city_id: city.id, brand_id: id }, function(data, ret) {
            console.log(data)
            that.setData({
                visible: true,
                brand_id: id,
                [`brandPlan.${id}`]: (data || []).map((n) => ({ ...n, payment: parseFloat(n.payment / 10000).toFixed(2) })),
            })
        }, function(data, ret) {
            console.log(data)
            app.error(ret.msg)
        })
    },
    onSpecial(e) {
        const { id, title } = e.currentTarget.dataset

        wx.navigateTo({
            url: `/page/preference/special/index?id=${id}&title=${title}`,
        })
    },
    onOpenDetail(e) {
        const { id, type } = e.currentTarget.dataset

        wx.navigateTo({
            url: `/page/preference/detail/index?id=${id}&type=${type}`,
        })
    },
    onTag(e) {
        const { payment } = e.currentTarget.dataset

        console.log('onTag', payment)

        wx.setStorage({
            key: 'searchVal',
            data: {
                payment,
            },
            success: () => {
                this.toMore()
            },
        })
    },
    toMore() {
        wx.switchTab({
            url: '/page/index/index',
        })
    },
    onSearch() {
        wx.navigateTo({
            url: '/page/search/index',
        })
    },
    onSelect() {
        const { userInfo } = app.getGlobalData()

        // 判断是否已授权，否则提示
        if (!userInfo || !userInfo.id) {
            app.showLoginModal(function() {}, function() {}, true)
            return
        }

        wx.navigateTo({
            url: '/page/city/index',
        })
    },
    makePhoneCall() {
        wx.makePhoneCall({
            phoneNumber: app.globalData.phoneNumber
        })
    },
    getSystemInfo() {
        wx.getSystemInfo({
            success: (res) => {
                this.setData({
                    windowWidth: res.windowWidth,
                    deviceHeight: res.windowHeight - 20 + 'px',
                })
            }
        })
    },
    onImage(e) {
        const { title, url } = e.currentTarget.dataset.value

        console.log(title, url)

        if (!!url) {
            wx.navigateTo({
                url,
            })
        } else if (title === '疯狂汽车节') {
            wx.navigateTo({
                url: '/page/preference/image/index',
            })
        } else if (title === '千元购车') {
            wx.setStorage({
                key: 'searchVal',
                data: {
                    payment: [1, 10], // 首付1000~10000以内
                    style: 'new',
                },
                success() {
                    wx.switchTab({
                        url: '/page/index/index',
                    })
                },
            })
        }
    },
    getList() {
        var that = this
        var city = wx.getStorageSync('city')

        this.setData({
            city,
        })

        app.request('/index/index', { city_id: city.id }, function(data, ret) {
            console.log(data)
            that.setData({
                appointment: data.appointment.map((n) => {
                    const mobile = n.mobile ? n.mobile.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2') : ''
                    const content = `${mobile} 成功下单 ${n.models_name}`

                    return {...n, content }
                }),
                brandList: data.brandList,
                carType: data.carType,
                shares: data.shares,
            })
        }, function(data, ret) {
            console.log(data)
            app.error(ret.msg)
        })
    },
})