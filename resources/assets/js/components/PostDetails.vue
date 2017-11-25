<template>
    <div id="offCanvas" :class="{active: visible}">
        <a class="button" @click="toggleWindowState">Close</a>
        <h1 class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">{{ activepost.title }}</h1>
        <h2 class="has-text-primary is-subtitle is-size-5 is-uppercase has-text-weight-semibold">{{ activepost.source.name }}</h2>
        <h3 class="is-size-6 has-text-grey-light" >{{activepost.time_ago}}</h3>
        <div class="content" v-html="activepost.content"></div>
    </div>
</template>
<script>
    export default {
        props: ['activepost','visible'],
        methods:
        {
            toggleWindowState()
            {
                this.$emit('toggle'); 
            }
        }
    }
</script>