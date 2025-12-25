<template>
    <button v-if="shown && readlaterservice != 'none'" class="flex items-center justify-center w-16 h-16 border rounded-full shadow-md group bg-yellow-50">
        <!-- button for Pocket -->
        <div @click="save" v-if="status == 'save' && readlaterservice=='pocket'">
            <svg class="h-8 opacity-50 group-hover:opacity-100"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M64 21.3c-35.1 0-64 28.9-64 64v149.3c0 141.2 114.8 256 256 256s256-114.8 256-256V85.3c0-35.1-28.9-64-64-64H64zM64 64h384c12.1 0 21.3 9.3 21.3 21.3v149.3C469.3 352.8 374.1 448 256 448S42.7 352.8 42.7 234.7V85.3c0-12 9.2-21.3 21.3-21.3zm78 94c-8.2 0-16.4 3.8-22.7 10-12.5 12.5-12.5 32.2 0 44.7L234.7 328c6 6 14.2 9.3 22.7 9.3s16.7-3.3 22.7-9.3l112.7-112c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-90 90-92.8-92.7c-6.3-6.2-14.5-10-22.7-10z"/></svg>
        </div>
        <!-- button for Instapaper -->
        <div @click="save" v-if="status == 'save' && readlaterservice=='instapaper'">
            <svg class="h-10 opacity-50 group-hover:opacity-100" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><circle cx="256" cy="256" r="256" fill="#7f8c8d"/><path d="M197.4 384.5v-14c3.7 0 39.1-1.9 39.1-25.1V168.5c0-24.2-35.4-26.1-39.1-26.1v-14h118.2v14c-4.7 0-39.1.9-39.1 24.2v178.8c0 24.2 34.4 25.1 39.1 25.1v14H197.4z" fill="#fff"/></svg>
        </div>
        <!-- button for Narrator -->
        <div @click="save" v-if="status == 'save' && readlaterservice=='narrator'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 opacity-50 group-hover:opacity-100 lucide lucide-message-square-text" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/>
                <path d="M7 11h10"/>
                <path d="M7 15h6"/>
                <path d="M7 7h8"/>
            </svg>
        </div>
        <!-- button for Omnivore -->
        <div @click="save" v-if="status == 'save' && readlaterservice=='omnivore'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"> <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" /> </svg>
        </div>
        <!-- saving -->
        <div v-if="status == 'saving'">
            <svg class="w-10 h-10 text-gray-700 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <!-- saved -->
        <div v-if="status == 'saved'">
            <svg class="w-12 h-12 text-green-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <!-- error -->
        <div v-if="status == 'error'">
           <svg class="w-10 h-10 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
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
        }
    },
    data(){
        return {
           readlaterservice: this.readLaterService || 'none',
           status: 'save'
        }
    },
    watch: {
        readLaterService(newValue) {
            this.readlaterservice = newValue || 'none';
        }
    },
    mounted() {
        window.addEventListener('keydown', (e) => {
          this.handle_keyboard_shortcut(e.key);
        });
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
            this.status = 'saving';
            axios.get('/app/readlater/?url=' + this.url).then((res) => {
                if (res.data.status == 'ok') {
                    this.status = 'saved';
                    setTimeout(() => {
                        this.status = 'save';
                    }, 1000)
                }
            }).catch((res)=> {
                this.status = 'error';
                setTimeout(() => {
                    this.status = 'save';
                }, 1000)
            })
        },
        handle_keyboard_shortcut(k){
            if (this.url) {
                if (k == 's' || k == 'Save') {
                    this.save();
                }
            }
        }
    },
}
</script>

<style>

</style>
