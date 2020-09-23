<template>
    <div v-if="name" class="position-relative mb-4">
        <div class="inline form-file mr-4">
            <input type="file" :name="name" :id="name" :ref="ref" class="form-file-input select-none"
                   @change="fileChange" :required="required">

            <label :for="name" class="form-file-btn btn btn-default btn-primary select-none">
                <span>{{ btnLabel }}</span>
            </label>
        </div>

        <div class="inline text-90 text-sm select-none">
            {{ label }}
        </div>
    </div>
</template>

<script>
export default {
    props: ['index', 'required'],
    data: () => ({
        file: null,
        fileName: '',
    }),
    computed: {
        name () {
            return `enhanced_image_uploader_images[${this.index}]`;
        },
        ref () {
            return `image-${this.index}`;
        },
        label () {
            return this.fileName || 'no file selected';
        },
        btnLabel () {
            return `Choose ${this.getNumberWithOrdinal(this.index + 1)} File`;
        },
    },
    methods: {
        getNumberWithOrdinal (n) {
            let s = ['th', 'st', 'nd', 'rd'];
            let v = n % 100;

            return n + (s[(v - 20) % 10] || s[v] || s[0]);
        },
        fileChange (e) {
            let path = e.target.value;

            this.fileName = path.match(/[^\\/]*$/)[0];
            this.file = this.$refs[this.ref].files[0];

            this.$root.$emit('enhanced-image-uploader-file-changed', {
                index: this.index,
                file: this.file,
            });
        },
    },
};
</script>
