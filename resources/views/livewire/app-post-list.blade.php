<div  class="pt-12 relative text-left w-full h-screen overflow-y-auto p-12">
    {{-- unread count --}}
    <script>window.numberOfPosts={{$posts->count()}}</script>
    <div class="max-w-7xl mx-auto cursor-pointer mb-6">
    <div id="ReadCount" class="bg-primary px-4 py-1 rounded-b-md text-white absolute top-0">
        <div class="max-w-7xl mx-auto">
            Unread: {{$posts->count()}} Index: {{$highlighted_post_index}}
        </div>
    </div>
</div>
    @foreach($posts as $key => $post)
        <div id="post-{{$key}}" data-postid="{{$post->id}}" class="@if($key == $highlighted_post_index) bg-yellow-50 @endif max-w-7xl mx-auto cursor-pointer p-2 border-b border-gray-200">
            <div wire:click="$emit('postSelected',{{$post->id}})" class="flex">
                <div class="mr-12 w-1/2">
                    <h2 class="text-2xl font-semibold text-gray-700 pt-6">{{$post->title}}</h2>
                    <h3 class="mt-2 font-semibold text-xl uppercase text-primary">{{$post->source->name}}</h3>
                    <h4 class="mt-4 text-gray-500 text-lg">{{$post->posted_at->diffForHumans()}}</h4>
                </div>
                <div class="w-1/2 font-light leading-relaxed text-gray-400 text-xl">
                    <p>{{$post->excerpt}}</p>
                </div>
            </div>
            <div class="w-1/2 mb-6">
                <button wire:click.prevent="$emit('markAsRead',{{$post->id}})" class="border border-gray-300 rounded-md px-4 py-2 mt-4 hover:bg-primary hover:text-white">Mark Read</button>
            </div>
        </div>
    @endforeach
</div>
<script>

class Posts {
  constructor(number, index) {
    this.number = number;
    this.index = index;
  }
  NextPost() {
    if(this.index < this.number - 1) {
        this.index++;
    }
  }
  PreviousPost(){
      if (this.index > 0) {
          this.index--;
      }
  }
  GetIndex(){
      return this.index;
  }
  GetNumber(){
      return this.number;
  }
  markPostAsRead(){
      this.number = this.number - 1;
  }
}

window.addEventListener('DOMContentLoaded', (event) => {
    
    IR_posts = new Posts(numberOfPosts,0);
    
    // When a post is marked as read. Reduce the count of the posts.
    Livewire.on('markAsRead', function(){
        IR_posts.markPostAsRead();       
        console.log(IR_posts.GetNumber() + ' posts still unread');
    });

    function updateHighlight() {
        Livewire.emit('postHighlighted', IR_posts.GetIndex());
        document.querySelector('#post-'+IR_posts.GetIndex()).scrollIntoViewIfNeeded();
        
        // if we're at the first, nudge the view to the top
        if (IR_posts.GetIndex() == 0 ) {
            document.querySelector('#ReadCount').scrollIntoView();
        }
    }
    
    // Keyboard shortcuts
    window.addEventListener('keydown', (e) => {
        
        if(e.key === 'Escape'){
            Livewire.emit('exitPost')
        }
        
        if (e.key == 'j' || e.key == 'J') {
            IR_posts.NextPost();
            updateHighlight();
        }
        if (e.key == 'k' || e.key == 'K') {
            IR_posts.PreviousPost();
            updateHighlight();
        }
        
    })
});
    
</script>