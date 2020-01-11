<template>
    <div>
        <!-- Selection UI-->
        <div class="section" v-show="visible">
            <div class="container">
                <h3 class="is-3">Filters Come here</h3>
            </div>
        </div>
        <!-- Breadcrumbs Indicating Selection status -->
            <div class="container">
                <nav class="breadcrumb" aria-label="breadcrumbs">
                    <ul>
                        <li><a href="#" @click.prevent="resetFilters">Home</a></li>
                        <li>{{filter.name}}</li>
                    </ul>
                </nav>
            </div>
    </div>
</template>

<script>
     export default {
        data() {
            return {
                filter: {
                    api_path: 'api/postsByReadStatus',
                    argument: 'unread',
                },
                visible: true 
            };
        },
        computed: {
            filterPath(){
                return this.filter.apiPath + '/' + this.filter.argument;
            }
        },
        methods: {
            filterByReadStatus(status)
            {
                this.filter = 
                {
                    api_path: 'api/postsByReadStatus',
                    argument: status
                }                
            },   
            filterByCategory(category)
            {
                this.filter = 
                {
                    api_path: 'api/postsByCategory',
                    argument: category
                }                
            },
            filterBySource(source)
            {
                this.filter = 
                {
                    api_path: 'api/postsBySource',
                    argument: source
                }                
            },            

            resetFilters()
            {
                this.filterByReadStatus('unread');
            }
        }
    }   
</script>