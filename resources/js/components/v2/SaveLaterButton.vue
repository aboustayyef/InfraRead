<template>
    <button v-if="shown && readlaterservice != 'none'" class="group h-16 w-16 absolute bottom-8 left-28 border bg-yellow-50 shadow-md rounded-full flex justify-center items-center">
        <div v-if="readlaterservice=='pocket'">
            <svg class="h-8 opacity-50 group-hover:opacity-100"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M64 21.3c-35.1 0-64 28.9-64 64v149.3c0 141.2 114.8 256 256 256s256-114.8 256-256V85.3c0-35.1-28.9-64-64-64H64zM64 64h384c12.1 0 21.3 9.3 21.3 21.3v149.3C469.3 352.8 374.1 448 256 448S42.7 352.8 42.7 234.7V85.3c0-12 9.2-21.3 21.3-21.3zm78 94c-8.2 0-16.4 3.8-22.7 10-12.5 12.5-12.5 32.2 0 44.7L234.7 328c6 6 14.2 9.3 22.7 9.3s16.7-3.3 22.7-9.3l112.7-112c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-90 90-92.8-92.7c-6.3-6.2-14.5-10-22.7-10z"/></svg>
        </div> 
        <div v-if="readlaterservice=='instapaper'">
            <svg class="h-10 opacity-50 group-hover:opacity-100" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><circle cx="256" cy="256" r="256" fill="#7f8c8d"/><path d="M197.4 384.5v-14c3.7 0 39.1-1.9 39.1-25.1V168.5c0-24.2-35.4-26.1-39.1-26.1v-14h118.2v14c-4.7 0-39.1.9-39.1 24.2v178.8c0 24.2 34.4 25.1 39.1 25.1v14H197.4z" fill="#fff"/></svg>
        </div>
    </button>
</template>

<script>
export default {
    props: ['shown','url'],
    data(){
        return {
           readlaterservice:'none' 
        }
    },
    mounted() {
        axios.get('/simpleapi/readlaterservice').then((res) => {
            console.log(res);
        this.readlaterservice = res.data;
        })
    }
}
</script>

<style>

</style>