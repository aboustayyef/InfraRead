<template>
            <div class="realtive">
                <div    id="post-view" 
                        class="w-full fixed bg-white top-0 right-0 h-screen overflow-y-auto transition duration-75 ease-out transform"
                        :class="{'translate-x-full' : !shown ,  'translate-x-0': shown }" 
                >
                    <div v-if="shown" class="mt-12 p-12 w-full max-w-7xl mx-auto">
                        <div class="mb-6 pb-4 border-b border-gray-200">
                            <a :href="post.url">
                                <h1 class="text-gray-700 text-3xl max-w-prose font-semibold">
                                    {{post.title}}
                                </h1>
                            </a>
                            <h2 class="uppercase text-primary font-semibold text-xl mt-2">{{post.source.name}}</h2>
                            <h3 class="text-gray-300 mt-6">{{post.time_ago}}</h3>
                        </div>
                        <div v-html="post.content" class="content has-columns text-xl font-light text-gray-700 leading-relaxed">
                        </div>
                    </div>
            
            
                </div>
                    <button v-if="shown" @click="$emit('exit-post')" class="group h-16 w-16 absolute bottom-8 left-8 shadow-md bg-gray-800 hover:bg-gray-600 rounded-full flex justify-center items-center">
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