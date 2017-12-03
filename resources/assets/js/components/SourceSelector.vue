<template>
  <div class="modal is-active"> 
    <div class="modal-background"></div> 
    <div class="modal-card">
      <header class="modal-card-head">
        <a aria-label="close" @click="closeWindow" class="button">Cancel</a>
      </header> 
      <section class="modal-card-body">
        <div class="content">
          <div class="columns">
            <div class="column">
              <nav class="panel">
                <div class="panel-heading">
                  Show By Source
                </div>
                <div class="panel-block">
                  <p class="control">
                    <input v-model="source_filter" class="input is-small" type="text" placeholder="filter">
                  </p>
                </div>
                <a v-for="source in filtered_sources" @click="showSource(source)" class="panel-block" >
                  {{source.name}}
                </a>            
              </nav>
            </div>
            <div class="column">
              <nav class="panel">
                <div class="panel-heading">
                  Show By Category
                </div>
                <a v-for="category in categories" @click="showCategory(category)" class="panel-block" >
                  {{category.description}}
                </a>            
              </nav>
            </div>
          </div>
        </div>

      </section>
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