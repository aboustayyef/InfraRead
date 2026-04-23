<template>
  <div class="realtive">
    <div
      id="post-view"
      class="fixed inset-0 w-full h-[100dvh] overflow-y-auto transition duration-75 ease-out transform bg-white"
      :class="{ 'translate-x-full': !shown, 'translate-x-0': shown }"
    >
      <div
        v-if="shown"
        class="w-full max-w-4xl px-4 mx-auto mt-12 md:px-14 pb-72 overflow-hidden"
      >
        <div class="pb-4 mb-6 border-b border-gray-200">
          <a :href="post.url">
            <h1
              class="text-xl md:text-3xl font-semibold text-gray-900 max-w-prose"
            >
              {{ post.title }}
            </h1>
          </a>
          <h2
            class="mt-2 text-lg md:text-xl font-semibold uppercase text-primary"
          >
            {{ post.source.name }}
          </h2>
          <h3 class="mt-2 md:mt-6 text-gray-300">{{ post.time_ago }}</h3>
          <div class="mt-4">
            🔗&nbsp;<a class="text-primary ml-2 text-sm" :href="post.url"
              >{{ post.url }}
            </a>
            &nbsp;<a
              class="text-primary ml-2 text-sm"
              :href="`https://archive.is/latest/${post.url}`"
              >[📖]</a
            >
          </div>
        </div>
        <!-- Summary -->
        <div id="summary" v-if="summary !== null" class="bg-yellow-50 p-4 my-4">
          <h3 class="font-bold mb-2">Summary</h3>
          <div v-if="summary === 'summarizing'">
            <SummarySkeleton />
          </div>
          <div v-else>
            <p class="content text-gray-900" v-html="summary"></p>
          </div>
        </div>
        <div
          id="post-content"
          v-if="!isLoading"
          ref="postContent"
          v-html="post.content"
          class="text-xl font-light leading-loose text-gray-600 content break-words"
        ></div>
        <PostContentSkeleton v-else />
      </div>
    </div>
    <div class="fixed flex bottom-4 left-4 space-x-4">
      <button
        v-if="shown"
        @click="$emit('exit-post', post)"
        class="flex items-center justify-center w-16 h-16 bg-gray-800 rounded-full shadow-md group hover:bg-gray-600"
      >
        <svg
          class="h-10 text-white"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
      <SaveLaterButton
        :shown="shown"
        :url="post.url"
        :read-later-service="readLaterService"
        :acknowledged-url="acknowledgedReadLaterUrl"
        @save-later="$emit('save-later', $event)"
      ></SaveLaterButton>
      <SummarizeButton
        v-show="shown"
        :post="post.id"
        @summarized="handleSummary"
      />
    </div>
  </div>
</template>
<script>
import SaveLaterButton from "./SaveLaterButton.vue";
import SummarizeButton from "./SummarizeButton.vue";
import PostContentSkeleton from "./partials/ui/PostContentSkeleton.vue";
import SummarySkeleton from "./partials/ui/SummarySkeleton.vue";

export default {
  props: ["post", "summary", "isLoading", "readLaterService", "acknowledgedReadLaterUrl"],
  components: { SaveLaterButton, SummarizeButton, PostContentSkeleton, SummarySkeleton },
  data() {
    return {
      quoteExplanations: {},
    };
  },
  watch: {
    "post.content": function () {
      this.prepareQuoteExplainers();
    },
    "post.id": function () {
      this.prepareQuoteExplainers();
    },
    isLoading: function (newValue) {
      if (!newValue) {
        this.prepareQuoteExplainers();
      }
    },
    shown: function (newValue) {
      if (newValue) {
        this.prepareQuoteExplainers();
      }
    },
  },
  updated() {
    this.prepareQuoteExplainers();
  },
  methods: {
    handleSummary: function (summary) {
      this.$emit("summary-ready", summary);
    },
    prepareQuoteExplainers: function () {
      this.$nextTick(() => {
        const container = this.$refs.postContent;

        if (!container || this.isLoading) {
          return;
        }

        const blockquotes = Array.from(container.querySelectorAll("*"))
          .filter((element) => element.tagName && element.tagName.toLowerCase() === "blockquote");

        blockquotes.forEach((blockquote) => {
          if (blockquote.dataset.explainReady === "true") {
            return;
          }

          const quoteText = this.normalizedText(blockquote.textContent || "");

          if (this.wordCount(quoteText) <= 75) {
            return;
          }

          blockquote.dataset.explainReady = "true";
          blockquote.classList.add("relative", "pr-14");

          const button = document.createElement("button");
          button.type = "button";
          button.className = "absolute top-2 right-2 z-10 flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 bg-white text-sm font-semibold text-primary shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-30";
          button.title = "Explain this in simple language";
          button.setAttribute("aria-label", "Explain this in simple language");
          button.textContent = "?";

          const panel = document.createElement("div");
          panel.className = "mt-4 hidden rounded border border-red-100 bg-red-50 p-3 text-base leading-relaxed text-gray-800";

          button.addEventListener("click", () => {
            this.explainQuote(quoteText, button, panel);
          });

          blockquote.appendChild(button);
          blockquote.appendChild(panel);
        });
      });
    },
    explainQuote: async function (quoteText, button, panel) {
      if (!this.post || !this.post.id) {
        return;
      }

      const quoteHash = this.hashText(quoteText);
      panel.classList.remove("hidden");

      if (this.quoteExplanations[quoteHash]) {
        panel.innerHTML = this.quoteExplanations[quoteHash];
        return;
      }

      button.disabled = true;
      button.classList.add("opacity-60");
      panel.innerHTML = '<div class="animate-pulse text-gray-500">Explaining...</div>';

      try {
        const response = await window.api.explainQuote(this.post.id, quoteText);
        const explanation = response.data && response.data.explanation
          ? response.data.explanation
          : null;

        if (!explanation) {
          throw new Error("No explanation returned.");
        }

        this.$set(this.quoteExplanations, quoteHash, explanation);
        panel.innerHTML = explanation;
      } catch (error) {
        console.error("Failed to explain quote:", error);
        panel.innerHTML = '<p>Could not explain this quote right now.</p>';
      } finally {
        button.disabled = false;
        button.classList.remove("opacity-60");
      }
    },
    normalizedText: function (text) {
      return text.replace(/\s+/g, " ").trim();
    },
    wordCount: function (text) {
      if (!text) {
        return 0;
      }

      return text.split(/\s+/).filter(Boolean).length;
    },
    hashText: function (text) {
      let hash = 5381;

      for (let i = 0; i < text.length; i++) {
        hash = ((hash << 5) + hash) + text.charCodeAt(i);
        hash = hash & hash;
      }

      return Math.abs(hash).toString(36);
    },
  },

  computed: {
    shown: function () {
      return Object.keys(this.post).length > 0;
    },
  },
};
</script>

<style scoped>
figure {
  max-width: 100%;
}
</style>
