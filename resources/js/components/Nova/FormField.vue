<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            <div v-for="(subfield,index) in subfields" :key="index"
                 class="enhanced-image-uploader-subfield mb-6">
                <p class="text-80 pt-2 mb-4 leading-tight" v-html="subfield.label"></p>

                <image-loader v-if="fileUrl(subfield)" :src="fileUrl (subfield)"></image-loader>

                <p v-if="fileUrl(subfield)" class="mt-3 flex items-center text-sm mb-6">
                    <delete-button @click="confirmRemoval(subfield.id)">
                        <span class="class ml-2 mt-1"> {{ 'Delete' }} </span>
                    </delete-button>
                </p>

                <form-image :index="index" :required="!fileUrl(subfield)"></form-image>
            </div>

            <portal to="modals">
                <confirm-upload-removal-modal
                    v-if="removeModalOpen"
                    @confirm="removeFile"
                    @close="closeRemoveModal"
                />
            </portal>

            <button v-if="field.limit > index" :class="`btn btn-default btn-primary ${index ? 'mt-4 mr-4': ''}`"
                    @click.stop.prevent="addSubfield">
                Add image
            </button>

            <button v-if="index" class="btn btn-default btn-danger mt-2"
                    @click.stop.prevent="subfieldConfirmRemoval">
                Delete last image
            </button>

            <p v-if="hasError" class="text-xs mt-2 text-danger">{{ firstError }}</p>
        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors, Errors } from 'laravel-nova';
import ImageLoader from '../ImageLoader';
import DeleteButton from '../DeleteButton';

export default {
    mixins: [HandlesValidationErrors, FormField],
    components: { ImageLoader, DeleteButton },
    props: [
        'resource',
        'resourceName',
        'resourceId',
        'relatedResourceName',
        'relatedResourceId',
        'viaRelationship',
        'field',
    ],
    data () {
        return {
            index: 0,
            repeaterIndex: 0,
            subfields: [],
            subfieldsConfig: {},
            uploadErrors: new Errors(),
            removeModalOpen: false,
            deleteSubfield: null,
            softDelete: true,
            fileChangedHandler: this.fileChanged,
        };
    },
    computed: {
        subfieldLabel () {
            const { width, height } = this.subfieldsConfig.fields[this.repeaterIndex].dimensions;
            const i = `<span style="color: #4099de">${this.getNumberWithOrdinal(this.index + 1)}</span>`;
            const w = `<span style="color: #4099de">${width}px</span>`;
            const h = `<span style="color: #4099de">${height}px</span>`;
            const r = `<span class="text-danger text-sm">*</span>`;

            return `${i} image dimensions: ${w} x ${h}. ${r}`;
        },
        hasError () {
            return this.uploadErrors.has(this.fieldAttribute);
        },
        firstError () {
            if (this.hasError) {
                return this.uploadErrors.first(this.fieldAttribute);
            }
        },
    },
    created () {
        this.subfieldsConfig = this.getSubfieldsConfig();

        const subfields = this.field.value;

        if (subfields) {
            for (let subfield of subfields) {
                this.addSubfield(subfield);
            }
        }
    },
    mounted () {
        this.$root.$on('enhanced-image-uploader-file-changed', this.fileChangedHandler);
    },
    beforeDestroy () {
        this.$root.$off('enhanced-image-uploader-file-changed', this.fileChangedHandler);
    },
    methods: {
        getSubfieldsConfig () {
            let model = null;
            const { layouts } = Nova.config.enhancedImageUploader;

            for (const resource of Nova.config.resources) {
                if (this.resourceName === resource.uriKey)
                    model = resource.singularLabel.toLowerCase();
            }

            return layouts.hasOwnProperty(model) ? layouts[model] : {};
        },
        addSubfield (params) {
            if (!this.subfieldsConfig.hasOwnProperty('fields') && !this.subfieldsConfig.fields.length)
                return;

            if (this.field.limit <= this.index)
                return;

            if (!this.subfieldsConfig.fields[this.repeaterIndex] && !this.subfieldsConfig.repeatable)
                return;

            if (!this.subfieldsConfig.fields[this.repeaterIndex] && this.subfieldsConfig.repeatable)
                this.repeaterIndex = 0;

            this.subfields.push(Object.assign({}, { label: this.subfieldLabel }, params));

            this.index++;
            this.repeaterIndex++;
        },
        removeSubfield () {
            const index = this.subfields.length - 1;

            this.subfields.splice(index, 1);

            this.index--;
            this.repeaterIndex--;
        },
        getNumberWithOrdinal (n) {
            let s = ['th', 'st', 'nd', 'rd'];
            let v = n % 100;

            return n + (s[(v - 20) % 10] || s[v] || s[0]);
        },
        fileChanged (params) {
            const { index, file } = params;

            for (let [i, subfield] of this.subfields.entries()) {
                if (index === i) {
                    subfield.file = file;
                }
            }
        },
        fill (formData) {
            for (let [i, subfield] of this.subfields.entries()) {
                if (subfield.file) {
                    formData.append(`enhanced_image_uploader_images[${i}][id]`, subfield.id || null);
                    formData.append(`enhanced_image_uploader_images[${i}][order]`, i);
                    formData.append(`enhanced_image_uploader_images[${i}][file]`, subfield.file);
                }
            }
        },
        fileUrl (subfield) {
            return subfield.optimized || subfield.original;
        },
        confirmRemoval (id) {
            this.removeModalOpen = true;
            this.deleteSubfield = id;
        },
        closeRemoveModal () {
            this.removeModalOpen = false;
            this.deleteSubfield = null;
            this.softDelete = true;
        },
        async removeFile () {
            this.uploadErrors = new Errors();

            const id = this.deleteSubfield;
            let uri = `/nova-vendor/enhanced-image-uploader/images/${id}`;

            if (this.softDelete) {
                uri += '/soft-delete';
            }

            try {
                await Nova.request().delete(uri);

                if (!this.softDelete) {
                    this.removeSubfield();
                } else {
                    this.deleteImage(id);
                }

                this.closeRemoveModal();

                this.$emit('file-deleted');

                Nova.success('The file was deleted!');
            } catch (error) {
                this.closeRemoveModal();

                if (parseInt(error.response.status) === 422) {
                    this.uploadErrors = new Errors(error.response.data.errors);
                }
            }
        },
        deleteImage (id) {
            for (let [i, subfield] of this.subfields.entries()) {
                if (id === subfield.id) {
                    this.subfields[i].name = null;
                    this.subfields[i].original = null;
                    this.subfields[i].optimized = null;
                    this.subfields[i].order = null;
                }
            }
        },
        subfieldConfirmRemoval () {
            const index = this.subfields.length - 1;
            const id = this.subfields[index].hasOwnProperty('id') ? this.subfields[index].id : null;

            if (id) {
                this.softDelete = false;

                this.confirmRemoval(id);
            } else {
                this.removeSubfield();
            }
        },
    },
};
</script>
