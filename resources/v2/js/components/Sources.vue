<template>
<aside class="menu">
    <div v-for="category in categories">
        <p class="menu-label">
            {{category.description}}
        </p>
        <ul class="menu-list">
            <li v-for="source in sources">
                <a href="#" 
                    v-if="source.category_id == category.id">
                        {{source.description}} 
                        <span 
                            v-if="unreadBySource(source.id) > 0"
                            class="tag is-primary is-rounded is-pulled-right">{{unreadBySource(source.id)}}
                        </span>
                        
                </a>
            </li>
        </ul>
    </div>
</aside>
</template>

<script>
export default {
    props:['sources','categories','posts'],
    methods: {
        unreadBySource(s_id){
            return this.posts.filter(function(post){
                return (post.source_id == s_id && post.read == 0)
            }).length;
        }
    }

}
</script>

<style>

</style>