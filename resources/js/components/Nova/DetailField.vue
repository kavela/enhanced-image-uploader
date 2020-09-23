<template>
    <panel-item :field="field">
        <template slot="value">
            <div v-for="(subfield,index) in subfields" :key="index"
                 class="enhanced-image-uploader-subfield mb-4">
                <image-loader :src="fileUrl(subfield)"></image-loader>

                <p v-if="fileUrl(subfield)" class="flex items-center text-sm mt-3">
                    <a @keydown.enter.prevent="download(fileUrl(subfield))"
                       @click.prevent="download(fileUrl(subfield))" tabindex="0"
                       class="cursor-pointer dim btn btn-link text-primary inline-flex items-center">
                        <icon class=" mr-2" type="download" view-box="0 0 24 24" width="16" height="16" />

                        <span class="class mt-1">{{ 'Download' }}</span>
                    </a>
                </p>
            </div>
        </template>
    </panel-item>
</template>

<script>
import ImageLoader from '../ImageLoader';

export default {
    components: { ImageLoader },
    props: ['field'],
    computed: {
        subfields () {
            return this.field.value;
        },
    },
    methods: {
        fileUrl (subfield) {
            return subfield.optimized || subfield.original;
        },
        download (fileUrl) {
            let link = document.createElement('a');

            link.href = fileUrl;
            link.download = fileUrl.match(/[^\\/]*$/)[0];

            document.body.appendChild(link);

            link.click();

            document.body.removeChild(link);
        },
    },
};
</script>
