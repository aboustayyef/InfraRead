<template>
    <button
        class="flex items-center justify-center w-16 h-16 border rounded-full shadow-md group bg-yellow-50"
    >
        <div @click="summarize" v-if="status == 'summarize'">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="opacity-50 group-hover:opacity-100 w-6 h-6"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25-.75L17.25 9m0 0L21 12.75M17.25 9v12"
                />
            </svg>
        </div>
        <!-- summarizing -->
        <div v-if="status == 'summarizing'">
            <svg
                class="w-10 h-10 text-gray-700 animate-spin"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                ></circle>
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
            </svg>
        </div>
        <!-- saved -->
        <div v-if="status == 'summarized'">
            <svg
                class="w-12 h-12 text-green-700"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
        </div>
        <!-- error -->
        <div v-if="status == 'error'">
            <svg
                class="w-10 h-10 text-primary"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                />
            </svg>
        </div>
    </button>
</template>

<script>
export default {
    props: ["post"],
    data() {
        return {
            status: "summarize",
            summary: null,
        };
    },
    mounted() {
        window.addEventListener("keydown", (e) => {
            this.handle_keyboard_shortcut(e.key);
        });
    },
    methods: {
        handle_keyboard_shortcut(k) {
            if (k == "m") {
                this.summarize();
            }
        },
        summarize() {
            this.status = "summarizing";
            this.$emit("summarized", "Summarizing post...");
            axios
                .get("/summary/" + this.post)
                .then((res) => {
                    if (res.data.summary) {
                        this.status = "summarize";
                        this.summary = res.data.summary;
                        this.$emit("summarized", this.summary);
                        setTimeout(() => {
                            this.status = "summarize";
                        }, 1000);
                    }
                })
                .catch((res) => {
                    this.status = "error";
                    setTimeout(() => {
                        this.status = "summarize";
                    }, 1000);
                });
        },
    },
};
</script>

<style></style>
