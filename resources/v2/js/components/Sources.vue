<template>
  <ul>
    <li v-for="category in categories">
    ----------------------------<br>
    {{category.description}}
    <br>----------------------------<br>
    <ul>
        <li v-for="source in sources">
            <div v-if="source.category_id == category.id">{{source.description}} {{unreadBySource(source.id)}}</div>    
        </li>
    </ul>
    </li>
  </ul>
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