Component({
    externalClasses: ['wux-class', 'wux-header-class', 'wux-body-class'],
    options: {
        multipleSlots: true,
    },
    properties: {
        bordered: {
            type: Boolean,
            value: true,
        },
        full: {
            type: Boolean,
            value: false,
        },
        title: {
            type: String,
            value: '',
        },
        thumb: {
            type: String,
            value: '',
        },
        thumbStyle: {
            type: String,
            value: '',
        },
        extra: {
            type: String,
            value: '',
        },
    },
})