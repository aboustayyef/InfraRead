!function(e){var t={};function n(o){if(t[o])return t[o].exports;var i=t[o]={i:o,l:!1,exports:{}};return e[o].call(i.exports,i,i.exports,n),i.l=!0,i.exports}n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)n.d(o,i,function(t){return e[t]}.bind(null,i));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s=2)}({2:function(e,t,n){e.exports=n("X632")},X632:function(e,t){function n(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}var o=function(){function e(t,n){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e),this.number=t,this.index=n}var t,o,i;return t=e,(o=[{key:"NextPost",value:function(){this.index<this.number-1&&this.index++}},{key:"PreviousPost",value:function(){this.index>0&&this.index--}},{key:"GetIndex",value:function(){return this.index}},{key:"ResetIndex",value:function(){this.index=0}},{key:"GetNumber",value:function(){return this.number}},{key:"markPostAsRead",value:function(){this.number=this.number-1}}])&&n(t.prototype,o),i&&n(t,i),e}();window.addEventListener("DOMContentLoaded",(function(e){IR_posts=new o(numberOfPosts,0);var t=!1,n=null,i="list",r=0,s="all";function u(){0==t&&(IR_posts.ResetIndex(),t=!0),Livewire.emit("highlightPost",IR_posts.GetIndex()),(n=document.querySelector("#post-"+IR_posts.GetIndex())).scrollIntoView({behavior:"smooth",block:"center",inline:"nearest"})}Livewire.on("markPostAsRead",(function(){IR_posts.markPostAsRead()})),Livewire.on("viewPost",(function(e){i="post",r=e,console.log("switched viewing mode to Post  - Post: "+r)})),Livewire.on("exitPost",(function(){i="list",r=0,console.log("switched viewing mode to list")})),Livewire.on("updateSource",(function(e){s=e})),window.addEventListener("keydown",(function(e){"Escape"===e.key&&("list"==i?null==n?"all"!==s&&Livewire.emit("updateSource","all"):(IR_posts.ResetIndex(),n=null,Livewire.emit("disableHighlight")):Livewire.emit("exitPost")),"j"!=e.key&&"J"!=e.key||("list"==i?(n&&IR_posts.NextPost(),u()):document.querySelector("#post-view").scrollBy(0,200)),"k"!=e.key&&"K"!=e.key||("list"==i?(IR_posts.PreviousPost(),u()):document.querySelector("#post-view").scrollBy(0,-200)),"Enter"!=e.key&&"o"!=e.key&&"O"!=e.key||("list"==i?n?Livewire.emit("viewPost",n.dataset.postid):console.log("no posts have been highlighted yet"):window.open(document.querySelector("#post-view").dataset.url,"_blank")),"e"!=e.key&&"E"!=e.key||n&&Livewire.emit("markPostAsRead",n.dataset.postid),"s"!=e.key&&"S"!=e.key||"post"==i&&(console.log("saving for later"),Livewire.emit("savePostForReadLater",document.querySelector("#post-view").dataset.url)),console.log(e.key)}))}))}});