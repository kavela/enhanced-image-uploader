Nova.booting(Vue => {
    Vue.component('index-enhanced-image-uploader', require('./components/Nova/IndexField'));
    Vue.component('detail-enhanced-image-uploader', require('./components/Nova/DetailField'));
    Vue.component('form-enhanced-image-uploader', require('./components/Nova/FormField'));
    Vue.component('form-image', require('./components/SubFields/FormImage'));
});
