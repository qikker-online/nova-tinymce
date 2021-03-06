<template>
    <default-field :field="field" :full-width-content="true">
        <template slot="field">
            <editor v-if="active"
                    :id="field.id || field.attribute"
                    v-model="value"
                    :class="errorClasses"
                    :placeholder="field.name"
                    :init="options"
            ></editor>

            <p v-if="hasError" class="my-2 text-danger">
                {{ firstError }}
            </p>
        </template>
    </default-field>
</template>

<script>
    import Vue from 'vue';
    import {FormField, HandlesValidationErrors} from 'laravel-nova'
    import Editor from '@tinymce/tinymce-vue'

    export default {
        components: {Editor},

        mixins: [FormField, HandlesValidationErrors],

        props: [
            'resourceName',
            'resourceId',
            'contentItemIndex',
            'fieldIndex'
        ],

        data() {
            return {
                active: true
            }
        },

        computed: {
            options() {
                let options = this.field.options

                options.images_upload_handler = this.imagesUploadHandler

                if (options.use_lfm) {
                    options['file_picker_callback'] = this.filePicker
                }

                return options
            }
        },
        watch: {
            // Rerender the component when the position changes, needed for iframes
            // see: https://github.com/tinymce/tinymce-vue/issues/10#issuecomment-540996933
            contentItemIndex: function (index) {
                this.active = false
                Vue.nextTick(() =>{
                    this.active = true
                })
            }
        },

        methods: {
            /*
             * Set the initial, internal value for the field.
             */
            setInitialValue() {
                this.value = this.field.value || ''
            },

            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                formData.append(this.field.attribute, this.value || '')
            },

            async imagesUploadHandler(blobInfo, success, failure) {
                let formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                formData.append('resourceId', this.resourceId)
                formData.append('resourceName', this.resourceName)

                let response;

                try {
                    response = await Nova.request().post(
                        this.options.images_upload_url,
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }
                    );
                } catch (e) {
                    failure('Error - ' + e)
                }

                if (!response.data.location) {
                    failure('Error - response invalid: ' + response.data);
                }

                success(response.data.location)
            },

            /**
             * Update the field's internal value.
             */
            handleChange(value) {
                this.value = value
            },

            filePicker: function (callback, value, meta) {
                let x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                let y = window.innerHeight || document.documentElement.clientHeight || document.getElementsByTagName('body')[0].clientHeight;

                let type = 'image' === meta.filetype ? 'Images' : 'Files';
                let url = this.options.path_absolute + this.options.lfm_url + '?editor=tinymce5&type=' + type;

                tinymce.activeEditor.windowManager.openUrl({
                    url: url,
                    title: 'File manager',
                    width: x * 0.8,
                    height: y * 0.8,
                    onMessage: (api, message) => {
                        callback(message.content);
                    }
                });
            }
        }
    }
</script>
