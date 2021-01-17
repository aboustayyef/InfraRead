<template>
            <div
                  id="post-view" 
                  class="w-full fixed bg-white top-0 right-0 h-screen overflow-y-auto transition duration-200 ease-out transform-gpu"
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
                <div v-html="post.content" class="content has-columns text-lg text-gray-500 leading-relaxed">
                </div>
            </div>
    
            <button @click="$emit('exit-post')" class="h-16 w-16 text-2xl font-bold absolute bottom-8 left-8 text-white bg-gray-800 rounded-full flex justify-center items-center">&times;</button>
            <button class="h-16 w-16 absolute bottom-8 left-28 border border-yellow-700 bg-yellow-50 text-gray-800 rounded-full flex justify-center items-center">
                save 
            </button>
        
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