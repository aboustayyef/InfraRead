<template>
    <div class="relative w-full h-screen p-4 pt-12 overflow-y-auto text-left md:p-12">
        <!-- Loading Indicator -->
        <div v-cloak v-if="posts_loaded == false" class="max-w-7xl mx-auto flex">
            <svg class="w-8 h-8 text-primary animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <div class="ml-2">Loading...</div>
        </div>

        <!-- Header -->
        <div v-if="posts_loaded == true" class="flex items-center justify-between mx-auto mb-6 max-w-7xl">
            <!-- Logo -->
            <a href="/"><img class="h-8 md:h-12" src="/img/infraread144.png" /></a>

            <!-- Read Count -->
            <ReadCount :count="number_of_unread_posts" />

            <!-- Undo & Settings -->
            <div class="flex align-center">
                <!-- Undo Button if exists -->
                <svg v-if="undoable" @click="undo()" xmlns="http://www.w3.org/2000/svg" xml:space="preserve"
                    class="h-8 text-gray-300 cursor-pointer hover:text-primary mr-4" fill="currentColor"
                    viewBox="0 0 489.394 489.394">
                    <path
                        d="M375.789 92.867H166.864l17.507-42.795a22.21 22.21 0 0 0-6.691-25.744c-7.701-6.166-18.538-6.508-26.639-.879L9.574 121.71a22.297 22.297 0 0 0-9.563 18.995 22.278 22.278 0 0 0 10.71 18.359l147.925 89.823c8.417 5.108 19.18 4.093 26.481-2.499 7.312-6.591 9.427-17.312 5.219-26.202l-19.443-41.132h204.886c15.119 0 27.418 12.536 27.418 27.654V356.56c0 15.118-12.299 27.19-27.418 27.19h-226.74c-20.226 0-36.623 16.396-36.623 36.622v12.942c0 20.228 16.397 36.624 36.623 36.624h226.74c62.642 0 113.604-50.732 113.604-113.379v-149.85c.002-62.647-50.962-113.842-113.604-113.842z" />
                </svg>
                <!-- Settings Gear -->
                <a href="/admin" class="block">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 text-gray-300 cursor-pointer hover:text-primary"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Banner that will only be displayed if viewing one source -->
        <div v-if="which_source !== 'all'">
            <div
                class="container flex items-center justify-between p-2 py-4 mx-auto mb-4 rounded-md shadow-md bg-gray-50 max-w-7xl">
                <div class="text-sm font-semibold text-gray-600 uppercase">
                    Posts by {{ source_name }}
                </div>
                <button @click="reset_to_all()"
                    class="w-8 h-8 text-lg text-gray-400 bg-gray-100 rounded-full hover:bg-primary hover:text-white">
                    &times;
                </button>
            </div>
        </div>

        <Message v-if="last_successful_crawl_data.status == 'warning'">
            {{ last_successful_crawl_data.message }}
        </Message>

        <!-- Well Done! You've read everything -->
        <div v-if="number_of_unread_posts < 1" class="container mx-auto">
            <InboxZero />
        </div>

        <!-- List of Posts -->
        <div v-for="(post, index) in unread_posts" :key="post.id"
            class="p-2 mx-auto border-b border-gray-200 cursor-pointer max-w-7xl" :class="{
                'bg-yellow-50': highlighter_on && index == highlighter_position,
            }">
            <!-- Individual Post -->
            <div :id="'post-' + index" class="md:flex">
                <!-- Title, author and date -->
                <div class="w-full md:mr-12 md:w-1/2">
                    <h2 v-on:click="display_post(post)"
                        class="pt-6 text-2xl font-semibold text-gray-700 cursor-pointer">
                        {{ post.title }}
                    </h2>
                    <h3 v-on:click="
                        switch_source(
                            'source',
                            post.source.id,
                            post.source.name
                        )
                    " class="mt-2 text-xl font-semibold uppercase text-primary">
                        {{ post.source.name }}
                    </h3>
                    <h4 class="mt-4 text-lg text-gray-500">
                        {{ post.time_ago }}
                    </h4>
                </div>

                <!-- Body of Post -->
                <div v-on:click="display_post(post)"
                    class="w-full mt-6 text-xl font-light leading-relaxed text-gray-400 cursor-pointer overflow-clip md:mt-0 md:w-1/2">
                    <p>{{ post.excerpt }}</p>
                </div>
            </div>

            <!-- Mark as Read Button -->
            <div class="w-1/2 mb-6">
                <button v-on:click="mark_post_as_read(post)"
                    class="px-4 py-2 mt-4 border border-gray-300 rounded-md hover:bg-primary hover:text-white">
                    Mark Read
                </button>
            </div>
        </div>

        <post :post="displayed_post" v-on:exit-post="exit_post"> </post>

        <!-- Messages -->
        <div class="fixed inline-block px-8 py-2 transition duration-75 ease-out transform border border-gray-600 shadow-md translate-x-72 top-8 right-8"
            :class="{
                '-translate-x-72': show_message == true,
                'bg-yellow-100': message_kind == 'warning',
                'bg-blue-100': message_kind == 'info',
                'bg-green-100': message_kind == 'success',
            }">
            {{ message_content }}
        </div>
    </div>
</template>
<script>
// Import Keyboard Shortcuts
import { handle_keyboard_shortcut } from "../keyboard_shortcuts.js";
// Import Components
import Post from "./Post.vue";
import ReadCount from "./partials/ReadCount.vue";
import Message from "./partials/Message.vue";
import InboxZero from "./partials/InboxZero.vue";

export default {
    props: ["refreshinterval", "last_successful_crawl"],
    components: { Post, ReadCount, Message, InboxZero },
    data() {
        return {
            posts_loaded: false,
            last_successful_crawl_data: {},
            posts: {},
            displayed_post: {},
            posts_marked_as_read: [],
            which_posts: "all",
            which_source: "all",
            source_name: "",
            highlighter_on: false,
            highlighter_position: 0,
            message_kind: "warning",
            message_content: "this is the message",
            show_message: false,
            external_links_shortcuts: false,
            external_links: [],
        };
    },
    created() {
        this.fetch_posts_from_server();
        this.last_successful_crawl_data = JSON.parse(
            this.last_successful_crawl
        );
        window.keys_entered = "";
        window.addEventListener("keydown", (e) => {
            window.keys_entered += e.key;
            if (window.keys_entered !== "") {
                handle_keyboard_shortcut(window.keys_entered, this);
                this.reset_keys_entered();
            }
        });
    },
    computed: {
        undoable: function () {
            return this.view == "list" && this.posts_marked_as_read.length > 0;
        },
        unread_posts: function () {
            if (Object.keys(this.posts).length > 0) {
                return this.posts.filter((post) => !post.read == 1);
            }
        },
        number_of_unread_posts: function () {
            if (Object.keys(this.posts).length > 0) {
                return Object.keys(this.unread_posts).length;
            }
            if (this.posts_loaded) {
                return 0;
            }
        },
        //   view mode: post or list
        view: function () {
            if (Object.keys(this.displayed_post).length > 0) {
                return "post";
            }
            return "list";
        },
        highlighted_post: function () {
            return this.unread_posts[this.highlighter_position];
        },
    },
    methods: {
        fetch_posts_from_server: function () {
            axios.get("/api/" + this.which_posts).then((res) => {
                this.posts = res.data;
                this.posts_loaded = true;
            });
        },
        reset_keys_entered: function () {
            // This function clears the keys entered if they're not relevant
            let k = window.keys_entered;
            if (k == "g" || k == "Shift") {
                // dont clear and wait for the next keypress
                return;
            } else {
                window.keys_entered = "";
            }

        },
        reset_to_all: function () {
            this.which_posts = "all";
            this.which_source = "all";
            this.source_name = "";
            this.fetch_posts_from_server();
        },
        switch_source: function (which, details = null, name = null) {
            if (which == "all") {
                this.reset_to_all();
            } else {
                this.which_posts = which + "/" + details;
                this.which_source = details;
                this.source_name = name;
            }
            this.fetch_posts_from_server();
        },
        display_post: function (p) {
            this.external_links_shortcuts = false;
            this.displayed_post = p;
            // Timeout the animation then set as read
            this.mark_post_as_read(p);
        },
        mark_post_as_read: function (p) {
            // update locally
            p.read = 1;
            this.posts_marked_as_read.push(p);
            // update on the server
            axios
                .patch("/api/posts/" + p.id, { read: 1 })
                // If server update works, don't report anything
                .then((res) => { })
                // If there's a problem, undo mark as read
                .catch((res) => {
                    p.read = 0;
                    this.posts_marked_as_read.pop();
                    this.display_message(
                        "warning",
                        "Cannot contact server",
                        2000
                    );
                });
        },
        display_message(kind, content, time) {
            this.message_kind = kind;
            this.message_content = content;
            this.show_message = true;
            setTimeout(() => {
                this.show_message = false;
            }, time);
        },
        exit_post: function () {
            this.displayed_post = {};
            this.external_links = [];
        },
        show_highlighted_post() {
            document
                .querySelector("#post-" + this.highlighter_position)
                .scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                    inline: "nearest",
                });
        },

        turn_on_external_links_shortcuts: function () {
            let shortcut_style =
                "mr-1 text-gray-700 px-2 bg-yellow-200 text-grey-800";
            var shortcut = 0;
            document.querySelectorAll("#post-content a").forEach((link, i) => {
                if (this.isInViewport(link)) {
                    var html = `<span class="externallink ${shortcut_style}">${shortcut}</span>`;
                    link.insertAdjacentHTML("beforeend", html);
                    this.external_links.push(link.getAttribute("href"));
                    shortcut++;
                }
            });
            this.external_links_shortcuts = true;
        },

        turn_off_external_links_shortcuts: function () {
            document.querySelector("#post-content").innerHTML =
                this.displayed_post.content;
            this.external_links_shortcuts = false;
            this.external_links = [];
        },

        isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <=
                (window.innerHeight ||
                    document.documentElement.clientHeight) &&
                rect.right <=
                (window.innerWidth || document.documentElement.clientWidth)
            );
        },

        undo() {
            if (this.undoable) {
                // mark last post in list as unread
                let last_post_marked_as_read =
                    this.posts_marked_as_read[
                    this.posts_marked_as_read.length - 1
                    ];
                last_post_marked_as_read.read = 0;

                // update on server
                axios
                    .patch("/api/posts/" + last_post_marked_as_read.id, {
                        read: 0,
                    })
                    // If server update works, update list of posts marked as read
                    .then((res) => {
                        this.posts_marked_as_read.pop();
                    })
                    // If there's a problem, undo mark as read
                    .catch((res) => {
                        last_post_marked_as_read.read = 1;
                        this.display_message(
                            "warning",
                            "Cannot contact server",
                            2000
                        );
                    });
                return true;
            } else {
                return false;
            }
        },
    },
};
</script>

<style scoped>
</style>
