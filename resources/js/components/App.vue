<template>
    <div class="relative w-full h-screen text-left">
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

        <div
            class="relative w-full h-full p-2 pt-12 overflow-y-auto md:p-12"
        >
            <!-- API Client Test (Phase 5A) -->
            <api-client-test v-if="this.debug == true"></api-client-test>

            <!-- Loading Indicator -->
            <div v-cloak v-if="(posts_loaded == false)">
                <div>
                    <!-- First, the header -->
                    <div class="w-full max-w-7xl mx-auto">
                        <div class="mb-6 ml-2 w-8 h-8 md:w-12 md:h-12 bg-gray-200 animate-pulse"></div>
                    </div>
                    <!-- The the skeleton "posts" -->
                    <LoadingSkeleton />
                    <LoadingSkeleton />
                    <LoadingSkeleton />
                    <LoadingSkeleton />
                    <LoadingSkeleton />
                    <LoadingSkeleton />
                </div>
            </div>

            <!-- Header -->
            <div
                v-if="posts_loaded == true"
                class="flex items-center justify-between mx-auto mb-6 max-w-7xl"
            >
                <!-- Logo -->
                <a href="/"><IrLogo class="ml-2 h-8 md:h-12" /></a>

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
            <Message v-if="last_successful_crawl_data.status == 'warning' || last_successful_crawl_data.status == 'no_data' || last_successful_crawl_data.status == 'error'">
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
        </div>

        <!-- Single post details -->
        <post
            :post="displayed_post"
            :summary="displayed_summary"
            :is-loading="isLoadingPost"
            :read-later-service="readLaterService"
            v-on:exit-post="exit_post"
            @summary-ready="handleSummary"
        >
        </post>

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
import LoadingSkeleton from "./partials/ui/LoadingSkeleton.vue";
import UndoButton from "./partials/ui/UndoButton.vue";
import SettingsIcon from "./partials/ui/SettingsIcon.vue";

export default {
    props: ["refreshinterval"], // Removed last_successful_crawl prop - now fetched via API
    components: {
        Post,
        PostItem,
        ReadCount,
        Message,
        InboxZero,
        IrLogo,
        LoadingIndicator,
        LoadingSkeleton,
        UndoButton,
        SettingsIcon,
        Notification,
    },
    data() {
        return {
            debug:false, // Temporarily enable for API testing
            posts_loaded: false,
            last_successful_crawl_data: {},
            posts: {},
            displayed_post: {},
            displayed_summary: null,
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
            isLoadingPost: false,
            readLaterService: 'none',
        };
    },
    created() {
        this.fetch_posts_from_server();
        this.fetch_crawl_status(); // Use API instead of prop
        this.fetchReadLaterService();
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

            // If it's a single digit (0-9), set a timeout to execute after 200ms
            if (window.keys_entered.match(/^\d$/)) {
                // Highlight the shortcut
                document.querySelectorAll(".externallink").forEach((item) => {
                    if (
                        item.textContent.trim() === window.keys_entered ||
                        item.textContent.trim().startsWith(window.keys_entered)
                    ) {
                        if (item.textContent.trim().length > 1) {
                            item.innerHTML = `<span class="bg-yellow-400 px-1">${
                                item.textContent.trim()[0]
                            }</span>${item.textContent.trim().slice(1)}`;
                        } else {
                            item.classList.replace(
                                "bg-yellow-200",
                                "bg-yellow-400"
                            );
                        }
                    }
                });
                window.shortcutTimer = setTimeout(() => {
                    handle_keyboard_shortcut(window.keys_entered, this);
                    window.keys_entered = ""; // Reset after execution
                    // Replace all instances of bg-yellow-400 with bg-yellow-200
                    document
                        .querySelectorAll(".bg-yellow-400")
                        .forEach((item) => {
                            item.classList.replace(
                                "bg-yellow-400",
                                "bg-yellow-200"
                            );
                        });
                }, 400);
            } else if (window.keys_entered.match(/^\d{2}$/)) {
                // If a second digit is entered, cancel the previous timeout and execute immediately
                clearTimeout(window.shortcutTimer);
                handle_keyboard_shortcut(window.keys_entered, this);
                window.keys_entered = ""; // Reset after execution
                document.querySelectorAll(".bg-yellow-400").forEach((item) => {
                    item.classList.replace("bg-yellow-400", "bg-yellow-200");
                });
            } else {
                // For non-numeric keys, process immediately
                handle_keyboard_shortcut(window.keys_entered, this);
                window.keys_entered = ""; // Reset after execution
            }
        });
    },
    mounted() {
        window.addEventListener("pageshow", this.handlePageShow);
    },
    beforeDestroy() {
        window.removeEventListener("pageshow", this.handlePageShow);
    },
    computed: {
        undoable: function () {
            return this.view == "list" && this.posts_marked_as_read.length > 0;
        },
        unread_posts: function () {
            if (Object.keys(this.posts).length > 0) {
                return this.posts.filter((post) => {
                    // Handle both boolean true and numeric 1 for read status
                    return post.read !== true && post.read !== 1;
                });
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
    watch: {
        readLaterService(newValue) {
            if (newValue === 'narrator' && this.displayed_post && this.displayed_post.id) {
                this.cacheNarratorMarkdown(this.displayed_post);
            }
        }
    },
    methods: {
        handlePageShow(event) {
            const navigationEntry = performance.getEntriesByType("navigation")[0];
            const isBackForwardNavigation = navigationEntry && navigationEntry.type === "back_forward";

            if (event.persisted || isBackForwardNavigation) {
                this.reloadFeedFromServer();
            }
        },
        reloadFeedFromServer() {
            this.posts_loaded = false;
            this.posts = [];
            this.displayed_post = {};
            this.displayed_summary = null;
            this.isLoadingPost = false;
            this.fetch_posts_from_server();
            this.fetch_crawl_status();
        },
        handleSummary(summary) {
            this.displayed_summary = summary;
        },
        async fetchReadLaterService() {
            try {
                const response = await axios.get('/api/v2_readlaterservice');
                this.readLaterService = response.data || 'none';
            } catch (error) {
                console.warn('⚠️ Failed to detect read-later service', error);
                this.readLaterService = 'none';
            }
        },
        fetch_posts_from_server: async function () {
            try {
                console.log('📡 Fetching posts using V1 API...', {
                    which_posts: this.which_posts,
                    which_source: this.which_source
                });

                // Build API filters based on current view
                const filters = { include: 'source,category' };

                // Handle source-specific filtering
                if (this.which_source !== 'all') {
                    filters.source = this.which_source;
                }

                // Always fetch unread posts (matching legacy behavior)
                filters.read = false;

                const response = await window.api.getPosts(filters);

                // Handle the new API response format (with pagination)
                if (response.data) {
                    this.posts = response.data;
                } else {
                    // Fallback for direct array response
                    this.posts = response;
                }

                this.posts_loaded = true;
                console.log('✅ Posts loaded via V1 API:', this.posts.length, 'posts');

            } catch (error) {
                console.error('❌ Failed to fetch posts via V1 API:', error);
                this.show_notification('warning', 'Failed to load posts: ' + error.getUserMessage(), 5000);
                this.posts_loaded = true; // Don't stay in loading state
            }
        },
        fetch_crawl_status: async function() {
            try {
                console.log('📊 Fetching crawl status via V1 API...');
                const response = await window.api.getCrawlStatus();

                // Transform API response to match legacy format
                this.last_successful_crawl_data = {
                    status: response.data.status, // 'ok', 'warning', or 'error'
                    message: response.data.message,
                    last_crawl_at: response.data.last_crawl_at,
                    minutes_ago: response.data.minutes_ago,
                    needs_attention: response.data.needs_attention
                };

                console.log('✅ Crawl status loaded:', this.last_successful_crawl_data);

            } catch (error) {
                console.error('❌ Failed to fetch crawl status:', error);

                // Fallback to show error status
                this.last_successful_crawl_data = {
                    status: 'error',
                    message: 'Unable to check crawl status: ' + error.getUserMessage(),
                    needs_attention: true
                };
            }
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
            console.log('🔄 Switching source view:', { which, details, name });

            if (which == "all") {
                this.reset_to_all();
            } else {
                // For V1 API, we use the source ID directly for filtering
                this.which_posts = which + "/" + details; // Keep for UI display logic
                this.which_source = details; // This will be used in API filtering
                this.source_name = name;
            }
            this.fetch_posts_from_server();
        },
        display_post: async function (p) {
            console.log('📖 Opening post, fetching full content on-demand:', p.id);
            this.external_links_shortcuts = false;
            this.displayed_summary = null;
            this.isLoadingPost = true;

            // Show the post immediately with available data
            this.displayed_post = {
                ...p,
                content: null // Will be loaded via API
            };

            try {
                // Fetch the full post with content using the V1 API show endpoint
                console.log('🔄 Fetching full content via API...');
                const response = await window.api.getPost(p.id, 'source,category');
                const fullPost = response.data || response;

                // Update with the full post data including content
                this.displayed_post = fullPost;
                this.isLoadingPost = false;
                console.log('✅ Full post content loaded:', fullPost.title);
                this.cacheNarratorMarkdown(fullPost);

            } catch (error) {
                console.error('❌ Failed to fetch full post content:', error);
                this.isLoadingPost = false;

                // Show error in content area
                this.displayed_post = {
                    ...p,
                    content: `<div class="text-center p-8 text-red-600">
                        <p>⚠️ Failed to load article content</p>
                        <p class="text-sm mt-2">${error.getUserMessage()}</p>
                        <a href="${p.url}" target="_blank" class="text-blue-600 underline mt-4 inline-block">Read on original site →</a>
                    </div>`
                };
            }
        },
        async cacheNarratorMarkdown(post) {
            if (this.readLaterService !== 'narrator' || !post || !post.id) {
                return;
            }

            try {
                await window.api.cachePostMarkdown(post.id);
            } catch (error) {
                console.warn('⚠️ Could not warm Narrator markdown cache', error.getUserMessage ? error.getUserMessage() : error);
            }
        },
        mark_post_as_read: async function (p) {
            // Update locally first (optimistic update)
            p.read = 1;
            this.posts_marked_as_read.push(p);

            try {
                // Update on the server using V1 API
                await window.api.markPostRead(p.id, true);

            } catch (error) {
                console.error('❌ Failed to mark post as read:', error);

                // Revert the optimistic update (same as old version)
                p.read = 0;
                this.posts_marked_as_read.pop();

                this.show_notification(
                    "warning",
                    "Cannot contact server: " + error.getUserMessage(),
                    3000
                );
            }
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
            // Find the actual post in the posts array (not the displayed_post object)
            const postInArray = this.posts.find(post => post.id === p.id);
            if (postInArray) {
                this.mark_post_as_read(postInArray);
            } else {
                // Fallback to the passed post object
                this.mark_post_as_read(p);
            }

            this.displayed_post = {};
            this.external_links = [];
            this.isLoadingPost = false;
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

        undo: async function() {
            if (this.undoable) {
                console.log('↩️ Undoing last read action via V1 API');

                // Get the last post marked as read
                let last_post_marked_as_read =
                    this.posts_marked_as_read[
                        this.posts_marked_as_read.length - 1
                    ];

                // Update locally first (optimistic update)
                last_post_marked_as_read.read = 0;

                try {
                    // Update on server using V1 API
                    await window.api.markPostRead(last_post_marked_as_read.id, false);

                    // If server update works, update list of posts marked as read
                    this.posts_marked_as_read.pop();
                    console.log('✅ Undo successful for post:', last_post_marked_as_read.id);

                } catch (error) {
                    console.error('❌ Failed to undo read status:', error);

                    // Revert the optimistic update
                    last_post_marked_as_read.read = 1;

                    this.show_notification(
                        "warning",
                        "Cannot contact server: " + error.getUserMessage(),
                        3000
                    );
                }
                return true;
            } else {
                return false;
            }
        },
    },
};
</script>

<style scoped></style>
