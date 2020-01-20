<template>
    <div class="columns">
        <!-- Left Pane -->
        <div class="column is-half">
            <h1 class="hfiller line1"></h1>
            <h1 class="hfiller line2"></h1>
            <h1 class="hfiller line3"></h1>
            <h1 class="hfiller line4"></h1>
        </div>

        <!-- Right Pane -->
        <div class="column is-half">
            <a href="#">
                <p class="has-text-grey">
                    <div class="pfiller line1"></div>
                    <div class="pfiller line2"></div>
                    <div class="pfiller line3"></div>
                    <div class="pfiller line4"></div>
                </p>
            </a>
        </div>
    </div>
</template>

<style scoped>
    .hfiller{
        display: block;
        height: 25px;
        margin-bottom: 5px;
        margin-top:0;

    }
    .hfiller.line1{width:80%;background:silver}
    .hfiller.line2{width:40%;background:silver}
    .hfiller.line3{margin-top:10px;width:20%;background:#eed4d4}
    .hfiller.line4{height:20px;margin-top:20px;width:30%;background:#e6e6e6}

    .pfiller{
        display: block;
        background: #e6e6e6; 
        height: 24px;
        margin-bottom: 2px;
    }
    .pfiller.line1{width:80%}
    .pfiller.line2{width:100%}
    .pfiller.line3{width:70%}
    .pfiller.line4{width:30%}
    
    p {
         cursor: pointer;
         line-height: 1.5;
    }
    .column{
        padding: 0.5rem 0.75rem;
        overflow-x:hidden;
    }
    .columns{
        margin-bottom: 0.75rem;
    }
    .highlighted{
        background-color: #f5f5dc6e;
    }
</style>