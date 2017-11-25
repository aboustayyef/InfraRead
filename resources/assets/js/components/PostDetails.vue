<template>
    <div id="offCanvas" :class="{active: visible}">
        <h1 class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">{{ activepost.title }}</h1>
        <h2 class="has-text-primary is-subtitle is-size-5 is-uppercase has-text-weight-semibold">{{ activepost.source.name }}</h2>
        <h3 class="is-size-6 has-text-grey-light" >{{activepost.time_ago}}</h3>
        <span v-html="activepost.content"></span>
    </div>
</template>
<script>
    export default {
        props: ['activepost'],
        data() {
            return {
                visible: false,
            };
        },
        
        methods: {
            toggleVisible() {
                this.visible = ! this.visible;
            }
        }
    }
</script>