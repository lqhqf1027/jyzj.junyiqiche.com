Page({
	data: {
		planImageStyle: {},
	},
	onPlanImageLoad(e) {
        console.log(e)
        const { type } = e.currentTarget.dataset
        const { width, height } = e.detail
        const planImageStyle = `width: 100%; height: ${height}rpx`

        this.setData({
            [`planImageStyle.${type}`]: planImageStyle,
        })
    },
})