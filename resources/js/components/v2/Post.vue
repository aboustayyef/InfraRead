<template>
            <div class="realtive">
                <div    id="post-view" 
                        class="fixed top-0 right-0 w-full h-screen overflow-y-auto transition duration-75 ease-out transform bg-white"
                        :class="{'translate-x-full' : !shown ,  'translate-x-0': shown }" 
                >
                    <div v-if="shown" class="w-full mx-auto mt-12 pb-36 max-w-7xl">
                        <div class="pb-4 mb-6 border-b border-gray-200">
                            <a :href="post.url">
                                <h1 class="text-3xl font-semibold text-gray-700 max-w-prose">
                                    {{post.title}}
                                </h1>
                            </a>
                            <h2 class="mt-2 text-xl font-semibold uppercase text-primary">{{post.source.name}}</h2>
                            <h3 class="mt-6 text-gray-300">{{post.time_ago}}</h3>
                        </div>
                        <div v-html="post.content" class="text-xl font-light leading-relaxed text-gray-700 content has-columns">
                        </div>
                    </div>
            
            
                </div>
                    <button v-if="shown" @click="$emit('exit-post')" class="absolute flex items-center justify-center w-16 h-16 bg-gray-800 rounded-full shadow-md group bottom-8 left-8 hover:bg-gray-600">
                        <svg class="h-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <save-later-button :shown="shown" :url="post.url"></save-later-button>
            </div>

</template>
<script>
export default {
  props: ['post'],
  computed: {
      shown: function(){
          return Object.keys(this.post).length > 0;
      }
  }
};
</script>

<style scoped>
</style>