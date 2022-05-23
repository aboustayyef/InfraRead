<template>
        <div id="ReadCount" class="text-gray-500 uppercase">
            <span v-if="count > 0"> unread: {{count}} </span>
            <span v-else>All Posts Read!</span>
        </div>
</template>
<script>
export default {
    props: ['count']
}
</script>
