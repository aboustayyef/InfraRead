<template>
<aside class="menu">
    <p class="menu-label">
       <span class="icon is-small"><i class="fa fa-cog"></i></span> Automatic 
    </p>
    <ul class="menu-list">
        <li>
            <a href="#">All Unread
                <span 
                    v-if="allUnread() > 0 "
                    class="tag is-primary is-rounded is-pulled-right is-small">{{allUnread()}}
                </span>
            </a>
        </li> 
        <li>
            <a href="#">Today 
            <span 
                v-if="unreadToday() > 0 "
                class="tag is-primary is-rounded is-pulled-right is-small">{{unreadToday()}}
            </span>
            </a>
        </li> 

    </ul>
    <hr>
    <div v-for="category in categories">
        <p class="menu-label">
            <span class="icon is-small"><i class="fa fa-folder"></i></span>
            {{category.description}}
        </p>
        <ul class="menu-list">
            <li v-for="source in sources">
                <a href="#" 
                    v-if="source.category_id == category.id">
                        <span class="source-name">{{source.name}}</span> 
                        <span 
                            v-if="unreadBySource(source.id) > 0 "
                            class="tag is-primary is-rounded is-pulled-right is-small">{{unreadBySource(source.id)}}
                        </span>
                        
                </a>
            </li>
        </ul>
        <hr>
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
        },
        allUnread(){
            return this.posts.filter(function(post){
                return (post.read == 0)
            }).length;
        },
        unreadToday(){
            let todayseconds = (new Date).setHours(0,0,0,0);
            return this.posts.filter(function(post){
                return (post.seconds > todayseconds && post.read == 0)
            }).length;
        }
   }

}
</script>

<style>
    .source-name {
    display: inline-block;
    width: 80%;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    }
</style>