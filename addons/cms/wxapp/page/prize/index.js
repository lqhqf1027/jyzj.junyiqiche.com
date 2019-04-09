import Wheel from '../components/wheel/wheel'
import getRand from '../../utils/getRand'

const app = getApp()

Page({
    data: {
        is_prize: 0,
        globalData: {},
    },
	onShow() {
		this.getList()
	},
    /**
     * 初始化转盘
     * @param    {Number} areaNumber [奖品总个数]
     * @param    {Number} awardNumer [中奖奖品索引]
     * @param    {Boolean} is_prize   [显示不同的按钮]
     * @param    {Boolean} hasMobile  [当用户未授权登录时，默认 hasMobile = true，用于触发 onStart 及唤起之后的授权登录提示]
     * @return   {[type]} [description]
     */
    initWheel(areaNumber = 6, awardNumer = 1, is_prize = true, hasMobile = false) {
        let mobile = !app.getGlobalData().session_key ? true : hasMobile

        // 禁用状态，默认 hasMobile = true，避免触发手机号授权
        if (!is_prize) {
            mobile = true
        }

        this.wheel = new Wheel(this, {
            hasMobile: mobile,
            disabled: !is_prize,
            prizeUrl: '/page/components/wheel/images/wheel.png',
            btnUrl: `/page/components/wheel/images/${is_prize ? 'btn_yellow' : 'btn_grey'}.png`,
            areaNumber,
            speed: 16,
            awardNumer,
            mode: 2,
            callback: () => {
                const { prize, prizeList } = this.data
                const areaNumber = prizeList.length

                this.initWheel(areaNumber, prize && prize.flag, false, true)

                wx.showModal({
                    title: '提示',
                    content: `抽奖成功 ，礼品为：${prize.prize_name}，已放入"我的->我的奖品"`,
                    showCancel: false,
                })
            },
            getPhoneNumber: (e) => {
                console.log('getPhoneNumber', e)
                if (e.detail.errMsg === 'getPhoneNumber:ok') {
                    const params = {
                        iv: e.detail.iv,
                        encryptedData: e.detail.encryptedData,
                        sessionKey: app.getGlobalData().session_key
                    }

                    // 登录态检查
                    wx.checkSession({
                        success: () => {                    
                            this.prizeResult(params)
                        },
                        fail: () => {
                            console.log('session_key 已过期')
                            app.showLoginModal(function(){}, function(){}, true)
                        },
                    })
                }
            },
            onStart: (e) => {
                this.prizeResult()
            },
        })
    },
    /**
     * 获取奖品列表
     */
	getList() {
        const city = wx.getStorageSync('city')

        app.request('/index/prizeShow?noAuth=1', { city_id: city.id }, (data, ret) => {
            console.log(data)
            this.setData({
                globalData: app.globalData,
                is_prize: data.is_prize,
                zhuanpan_bk_img: data.zhuanpan_bk_img,
                prizeList: data.prizeList,
            }, () => {
                this.initWheel(data.prizeList.length, 1, data.is_prize === 0, !!data.mobile)
            })
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
        })
    },
    /**
     * 领奖
     */
    prizeResult(params = {}) {
        console.log('prizeResult--解密参数', params)

        // 未授权登录
        if (!app.getGlobalData().session_key) {
            app.showLoginModal(function(){}, function(){}, true)
            return
        }

        const { prizeList, is_prize } = this.data
        const areaNumber = prizeList.length
        const prize = new getRand(prizeList, 'win_prize_number')

        // test
        // this.initWheel(areaNumber, prize.flag, true, true)
        // this.wheel.start(true)

        // return

        // 奖品不存在
        if (!prize || is_prize !== 0 || this.disabled) return

        // 奖品概率全部为 0
        const hasPrize = prizeList && prizeList.filter((n) => parseFloat(n['win_prize_number']) > 0).length
        if (!hasPrize) {
            wx.showModal({
                title: '提示',
                content: '哦嚯，奖品被抽光了！',
                showCancel: false,
            })

            return
        }

        // 避免连续点击重复抽奖
        this.disabled = true

        app.request('/index/prizeResult', { ...params, prize_id: prize.id }, (data, ret) => {
            console.log(data)
            this.setData({ is_prize: 1, prize }, () => {
                this.initWheel(areaNumber, prize.flag, true, true)
                this.wheel.start(true)
            })
            // app.success(ret.msg)
        }, (data, ret) => {
            console.log(data)
            app.error(ret.msg)
            this.disabled = false
        })
    },
    onImageLoad(e) {
        console.log(e)
        const { type } = e.currentTarget.dataset
        const { width, height } = e.detail
        const imageStyle = `width: 100%; height: ${height}rpx`

        this.setData({
            imageStyle,
        })
    },
})