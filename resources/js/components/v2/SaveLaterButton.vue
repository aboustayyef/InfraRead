<template>
    <button v-if="shown && readlaterservice != 'none'" class="absolute flex items-center justify-center w-16 h-16 border rounded-full shadow-md group bottom-8 left-28 bg-yellow-50">
        <div @click="save" v-if="status == 'save' && readlaterservice=='pocket'">
            <svg class="h-8 opacity-50 group-hover:opacity-100"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M64 21.3c-35.1 0-64 28.9-64 64v149.3c0 141.2 114.8 256 256 256s256-114.8 256-256V85.3c0-35.1-28.9-64-64-64H64zM64 64h384c12.1 0 21.3 9.3 21.3 21.3v149.3C469.3 352.8 374.1 448 256 448S42.7 352.8 42.7 234.7V85.3c0-12 9.2-21.3 21.3-21.3zm78 94c-8.2 0-16.4 3.8-22.7 10-12.5 12.5-12.5 32.2 0 44.7L234.7 328c6 6 14.2 9.3 22.7 9.3s16.7-3.3 22.7-9.3l112.7-112c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-90 90-92.8-92.7c-6.3-6.2-14.5-10-22.7-10z"/></svg>
        </div> 
        <div @click="save" v-if="status == 'save' && readlaterservice=='instapaper'">
            <svg class="h-10 opacity-50 group-hover:opacity-100" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><circle cx="256" cy="256" r="256" fill="#7f8c8d"/><path d="M197.4 384.5v-14c3.7 0 39.1-1.9 39.1-25.1V168.5c0-24.2-35.4-26.1-39.1-26.1v-14h118.2v14c-4.7 0-39.1.9-39.1 24.2v178.8c0 24.2 34.4 25.1 39.1 25.1v14H197.4z" fill="#fff"/></svg>
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
            <svg class="w-12 h-12 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </button>
</template>

<script>
export default {
    props: ['shown','url'],
    data(){
        return {
           readlaterservice:'none',
           status: 'save' 
        }
    },
    mounted() {
        window.addEventListener('keydown', (e) => {
          this.handle_keyboard_shortcut(e.key);
        });
        axios.get('/simpleapi/readlaterservice').then((res) => {
            console.log(res);
        this.readlaterservice = res.data;
        })
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