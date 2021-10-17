<template>
        <div v-if="showing" class="text-yellow-800 max-w-7xl bg-yellow-50 w-full mx-auto px-4 py-2 border border-yellow-200 flex justify-between items-center ">
            <div>
                <slot/>
            </div>
            <button @click="showing = false" class="p-1 transition-colors duration-200 transform rounded-md hover:bg-opacity-25 hover:bg-gray-600 focus:outline-none">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 18L18 6M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
</template>
<script>
export default {
    data() {
        return {
        showing: true
        }
    }
}
</script>
