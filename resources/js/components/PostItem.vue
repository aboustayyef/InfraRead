<template>
    <div
        class="p-2 mx-auto border-b border-gray-200 cursor-pointer max-w-7xl"
        :class="{ 'bg-yellow-50': highlighted }"
    >
        <div :id="'post-' + index" class="md:flex min-w-0">
            <!-- Title, author and date -->
            <div class="w-full md:mr-12 md:w-1/2 min-w-0">
                    <h2 v-on:click="$emit('displayPost', post)"
                        class="pt-2 md:pt-6 text-xl md:text-2xl font-semibold text-gray-700 cursor-pointer break-words [overflow-wrap:anywhere]">
                        {{ post.title }}
                    </h2>
                    <h3 v-on:click="$emit('switchSource', 'source', post.source.id, post.source.name)"
                    class="mt-2 text-lg md:text-xl font-semibold uppercase text-primary">
                        {{ post.source.name }}
                    </h3>
                    <h4 class="hidden md:block mt-2 md:mt-4 text-md md:text-lg text-gray-500">
                        {{ post.time_ago }}
                    </h4>
                </div>
                <!-- Body of Post -->
                <div v-on:click="$emit('displayPost', post)"
                    class="w-full mt-2 text-xl font-light leading-relaxed text-gray-400 cursor-pointer overflow-clip md:mt-4 md:w-1/2 min-w-0">
                    <p class="excerpt text-base md:text-xl break-words [overflow-wrap:anywhere]">{{ post.excerpt }}</p>
                </div>
        </div>
        <!-- Mark as Read Button -->
        <div class="mt-4 flex justify-between md:block w-full md:w-1/2 mb-2 md:mb-6">
                <button v-on:click="$emit('markRead', post)"
                    class="px-4 py-2 border border-gray-300 rounded-md active:bg-primary active:text-white md:hover:bg-primary md:hover:text-white">
                    Mark Read
                </button>
                <div class="md:hidden text-small text-gray-300">{{ post.time_ago }}</div>
            </div>

    </div>
</template>

<script>
export default {
    props: {
        post: Object,
        highlighter_on: Boolean,
        index: Number,
        highlighter_position: Number,
    },
    computed: {
        highlighted: function () {
            return (
                this.highlighter_on && this.index == this.highlighter_position
            );
        },
    },
};
</script>
