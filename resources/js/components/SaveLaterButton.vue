<template>
    <button
        v-if="shown && readlaterservice != 'none'"
        @click="save"
        class="flex items-center justify-center w-16 h-16 border rounded-full shadow-md group bg-yellow-50"
        title="Save for later"
        aria-label="Save for later"
    >
        <svg
            v-if="!isAcknowledged"
            class="w-8 h-8 text-gray-700 opacity-70 transition group-hover:opacity-100"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.8"
                d="M17.25 21 12 17.25 6.75 21V5.25A2.25 2.25 0 0 1 9 3h6a2.25 2.25 0 0 1 2.25 2.25Z"
            />
        </svg>
        <svg
            v-else
            class="w-8 h-8 text-primary"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
        >
            <path d="M6.75 3A2.25 2.25 0 0 0 4.5 5.25V21l7.5-3.75L19.5 21V5.25A2.25 2.25 0 0 0 17.25 3h-10.5Z" />
        </svg>
    </button>
</template>

<script>
export default {
    props: {
        shown: Boolean,
        url: String,
        readLaterService: {
            type: String,
            default: 'none'
        },
        acknowledgedUrl: String,
    },
    data(){
        return {
           readlaterservice: this.readLaterService || 'none',
           status: 'save',
        }
    },
    watch: {
        readLaterService(newValue) {
            this.readlaterservice = newValue || 'none';
        },
        shown(newValue) {
            if (!newValue) {
                this.status = 'save';
            }
        },
        url() {
            this.status = 'save';
        }
    },
    computed: {
        isAcknowledged() {
            return this.status == 'queued' || this.acknowledgedUrl === this.url;
        }
    },
    mounted() {
        if (this.readlaterservice === 'none') {
            axios.get('/api/v2_readlaterservice').then((res) => {
                this.readlaterservice = res.data;
            }).catch(() => {
                this.readlaterservice = 'none';
            });
        }
    },
    methods: {
        save: function(){
            if (!this.url) {
                return;
            }

            this.status = 'queued';
            this.$emit('save-later', this.url);
        }
    },
}
</script>

<style>

</style>
