<template>
            <div class="realtive">
                <div    id="post-view"
                        class="fixed top-0 right-0 w-full h-screen overflow-y-auto transition duration-75 ease-out transform bg-white"
                        :class="{'translate-x-full' : !shown ,  'translate-x-0': shown }"
                >
                    <div v-if="shown" class="w-full max-w-4xl px-4 mx-auto mt-12 md:px-14 pb-72 overflow-hidden">
                        <div class="pb-4 mb-6 border-b border-gray-200">
                            <a :href="post.url">
                                <h1 class="text-xl md:text-2xl font-semibold text-gray-700 max-w-prose">
                                    {{post.title}}
                                </h1>
                            </a>
                            <h2 class="mt-2 text-lg md:text-xl font-semibold uppercase text-primary">{{post.source.name}}</h2>
                            <h3 class="mt-2 md:mt-6 text-gray-300">{{post.time_ago}}</h3>
                            <div class="mt-4">🔗&nbsp;<a class="text-primary ml-2 text-sm" :href="post.url">{{post.url}}</a></div>
                        </div>
                        <div id="summary" v-if="summary !== null" class="bg-yellow-50 p-4 my-4">
                            <h3 class="font-bold mb-2">Summary</h3>
                            <div v-if="summary === 'summarizing'" >
                                <LoadingIndicator />
                            </div>
                            <div v-else>
                                <p class="content text-gray-700" v-html="summary"></p>
                            </div>
                        </div>
                        <div id="post-content" v-html="post.content" class="text-xl font-light leading-loose text-gray-700 content break-words">
                        </div>
                    </div>


                </div>
                <div class="fixed flex bottom-4 left-4 space-x-4">
                    <button v-if="shown" @click="$emit('exit-post', post)" class="flex items-center justify-center w-16 h-16 bg-gray-800 rounded-full shadow-md group hover:bg-gray-600">
                        <svg class="h-10 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <SaveLaterButton :shown="shown" :url="post.url"></SaveLaterButton>
                    <SummarizeButton v-show="shown" :post="post.id" @summarized="handleSummary" />
                </div>
            </div>

</template>
<script>
import SaveLaterButton from "./SaveLaterButton.vue";
import SummarizeButton from "./SummarizeButton.vue";
import LoadingIndicator from "./partials/ui/LoadingIndicator.vue";

export default {
  props: ['post','summary'],
  components: {SaveLaterButton, SummarizeButton, LoadingIndicator},
  methods: {
      handleSummary: function(summary){
          this.$emit('summary-ready', summary);
      }
  },

  computed: {
      shown: function(){
          return Object.keys(this.post).length > 0;
      }
  }
};
</script>

<style scoped>
    figure{
        max-width: 100%;
    }
</style>
