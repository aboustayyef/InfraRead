<template>
    <div
        class="relative w-full h-screen p-2 md:p-4 pt-12 overflow-y-auto text-left md:p-12"
    >
        <!-- Debug Bar -->
        <div
            v-if="this.debug == true"
            class="fixed bg-green-100 top-0 left-0 right-0 p-2 space-x-2"
        >
            <span>Highlighter on: {{ this.highlighter_on }}</span>
            <span>Highlighter position: {{ this.highlighter_position }}</span>
            <span
                >posts marked as read:
                {{ this.posts_marked_as_read.length }}</span
            >
            <span v-if="posts_loaded"
                >unread posts length: {{ this.unread_posts.length }}</span
            >
        </div>

        <!-- Loading Indicator -->
        <div
            v-cloak
            v-if="posts_loaded == false"
            class="max-w-7xl mx-auto flex"
        >
            <LoadingIndicator />
        </div>

        <!-- Header -->
        <div
            v-if="posts_loaded == true"
            class="flex items-center justify-between mx-auto mb-6 max-w-7xl"
        >
            <!-- Logo -->
            <a href="/"><IrLogo class="h-8 md:h-12" /></a>

            <!-- Read Count -->
            <ReadCount :count="number_of_unread_posts" />

            <!-- Undo & Settings -->
            <div class="flex align-center">
                <!-- Undo Button if exists -->
                <div v-if="undoable" @click="undo()">
                    <UndoButton />
                </div>
                <!-- Settings -->
                <a href="/admin" class="block"><SettingsIcon /> </a>
            </div>
        </div>

        <!-- Banner that will only be displayed if viewing one source -->
        <div v-if="which_source !== 'all'">
            <div
                class="container flex items-center justify-between p-2 py-4 mx-auto mb-4 rounded-md shadow-md bg-gray-50 max-w-7xl"
            >
                <div class="text-sm font-semibold text-gray-600 uppercase">
                    Posts by {{ source_name }}
                </div>
                <button
                    @click="reset_to_all()"
                    class="w-8 h-8 text-lg text-gray-400 bg-gray-100 rounded-full hover:bg-primary hover:text-white"
                >
                    &times;
                </button>
            </div>
        </div>

        <!-- Crawl Warning Message if last crawl was long ago -->
        <Message v-if="last_successful_crawl_data.status == 'warning'">
            {{ last_successful_crawl_data.message }}
        </Message>

        <!-- Well Done! You've read everything message -->
        <div
            v-if="number_of_unread_posts < 1"
            class="container max-w-md mx-auto"
        >
            <InboxZero />
        </div>

        <!-- List of Posts -->
        <ul>
            <li v-for="(post, index) in unread_posts" :key="post.id">
                <PostItem
                    :post="post"
                    :highlighter_on="highlighter_on"
                    :index="index"
                    :highlighter_position="highlighter_position"
                    v-on:displayPost="display_post"
                    v-on:switchSource="switch_source"
                    v-on:markRead="mark_post_as_read"
                />
            </li>
        </ul>

        <!-- Single post details -->
        <post :post="displayed_post" v-on:exit-post="exit_post"> </post>

        <!-- Notifications (hidden if none) -->
        <Notification :notification="notification" />
    </div>
</template>
<script>
// Import Keyboard Shortcuts
import { handle_keyboard_shortcut } from "../keyboard_shortcuts.js";

// Import Components
import Post from "./Post.vue";
import PostItem from "./PostItem.vue";
import ReadCount from "./partials/ReadCount.vue";
import Message from "./partials/Message.vue";
import InboxZero from "./partials/InboxZero.vue";
import Notification from "./partials/Notification.vue";

// UI Elements
import IrLogo from "./partials/ui/IrLogo.vue";
import LoadingIndicator from "./partials/ui/LoadingIndicator.vue";
import UndoButton from "./partials/ui/UndoButton.vue";
import SettingsIcon from "./partials/ui/SettingsIcon.vue";

export default {
    props: ["refreshinterval", "last_successful_crawl"],
    components: {
        Post,
        PostItem,
        ReadCount,
        Message,
        InboxZero,
        IrLogo,
        LoadingIndicator,
        UndoButton,
        SettingsIcon,
        Notification,
    },
    data() {
        return {
            debug: false,
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
            notification: {
                displayed: false,
                kind: "warning",
                message: "This is the message",
            },
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
        window.shortcutTimer = null; // Timer for handling multi-digit shortcuts

        window.addEventListener("keydown", (e) => {
            window.keys_entered += e.key;

            // Handle special multi-key sequences like "gg" and "ShiftG"
            if (
                window.keys_entered === "g" ||
                window.keys_entered === "Shift"
            ) {
                return; // Wait for the next key
            }

            if (window.keys_entered.match(/^\d$/)) {
                // If it's a single digit (0-9), set a timeout to execute after 200ms
                window.shortcutTimer = setTimeout(() => {
                    handle_keyboard_shortcut(window.keys_entered, this);
                    window.keys_entered = ""; // Reset after execution
                }, 400);
            } else if (window.keys_entered.match(/^\d{2}$/)) {
                // If a second digit is entered, cancel the previous timeout and execute immediately
                clearTimeout(window.shortcutTimer);
                handle_keyboard_shortcut(window.keys_entered, this);
                window.keys_entered = ""; // Reset after execution
            } else {
                // For non-numeric keys, process immediately
                handle_keyboard_shortcut(window.keys_entered, this);
                window.keys_entered = ""; // Reset after execution
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
        },
        mark_post_as_read: function (p) {
            // update locally
            p.read = 1;
            this.posts_marked_as_read.push(p);
            // update on the server
            axios
                .patch("/api/posts/" + p.id, { read: 1 })
                // If server update works, don't report anything
                .then((res) => {})
                // If there's a problem, undo mark as read
                .catch((res) => {
                    p.read = 0;
                    this.posts_marked_as_read.pop();
                    this.show_notification(
                        "warning",
                        "Cannot contact server",
                        2000
                    );
                });
        },
        show_notification(kind, content, time) {
            console.log("showing notification");
            this.notification.kind = kind;
            this.notification.message = content;
            this.notification.displayed = true;
            setTimeout(() => {
                this.notification.displayed = false;
            }, time);
        },
        exit_post: function (p) {
            this.mark_post_as_read(p);
            this.displayed_post = {};
            this.external_links = [];
        },
        show_highlighted_post() {
            console.log("Showing post " + this.highlighter_position);
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
                var html = `<span class="externallink ${shortcut_style}">${shortcut}</span>`;
                link.insertAdjacentHTML("beforeend", html);
                this.external_links.push(link.getAttribute("href"));
                shortcut++;
            });
            this.external_links_shortcuts = true;
        },

        turn_off_external_links_shortcuts: function () {
            document.querySelector("#post-content").innerHTML =
                this.displayed_post.content;
            this.external_links_shortcuts = false;
            this.external_links = [];
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
                        this.show_notification(
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

<style scoped></style>
