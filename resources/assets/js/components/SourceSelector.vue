<template>
      <div class="section">
        <div class="container">
         <div class="level">
          <a aria-label="close" @click="closeWindow" class="button">Cancel</a>
         </div> 
         <div class="columns">
            <div class="column">
              <h2 class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">Articles</h2>
              <ul>
                <li class="is-primary" @click="showAllPosts()" >Show All Articles</li>
              </ul>
              
              <h2 class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">Categories</h2>
              <ul>
                <li class="is-primary"  v-for="category in categories" @click="showCategory(category)">{{category.description}}</li>
              </ul>
            </div>

            <div class="column">
              <h2 class="has-text-grey-dark is-title is-size-4 has-text-weight-bold">Sources</h2>
              <p class="control">
                <input v-model="source_filter" class="input is-small" type="text" placeholder="filter">
              </p>
              <ul>
                <li class="is-primary"  v-for="source in filtered_sources" @click="showSource(source)">{{source.name}}</li>
              </ul>
            </div>

          </div> 
        </div>
      </div>
</template>
<script>
    export default {
        props: ['sources','categories'],
        data()
        {
          return {
            source_filter: ''
          }
        },
        methods:
        {
            showAllPosts()
            {
              this.$emit('showAllPosts');
            },
            showSource(source)
            {
              this.$emit('showBySource', source);
            },
            showCategory(category)
            {
              this.$emit('showByCategory', category);
            },
            closeWindow()
            {
                this.$emit('closeWindow'); 
            }
        },
        computed:
        {
          filtered_sources()
          {
            return this.sources.filter( (source) => {
              return source.name.toLowerCase().indexOf(this.source_filter.toLowerCase()) >= 0;
            });
          }
        }
    }
</script>
<style scoped>
  ul{
    margin:1em 0 2em 0;
  }
  li{
    text-transform: uppercase;
    border-top: 1px solid whitesmoke;
    padding: 3px 0;
    cursor: pointer;
  }
</style>