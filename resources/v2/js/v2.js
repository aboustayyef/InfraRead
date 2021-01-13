// Posts Class
// This class is used to handle the position of the highlighting in keyboard shortcuts
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
  ResetIndex(){
      this.index = 0;
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
    let keyboard_navigation = false;
    let highlighted_post = null;
    let view_mode = 'list';

    // When a post is marked as read. Reduce the count of the posts.
    Livewire.on('markAsRead', function(){
        IR_posts.markPostAsRead();       
    });

    // When a post is viewed, change view_mode to 'post'
    Livewire.on('postSelected', function(){
        view_mode = 'post';
        console.log('switched viewing mode to Post');
    });
    
    // When a post is exited, change view_mode to 'list'
    Livewire.on('exitPost', function(){
        view_mode = 'list';
        console.log('switched viewing mode to list');
    });

    // Function to update the position of the highlight
    function updateHighlightPosition() {
        if (keyboard_navigation == false) {
            IR_posts.ResetIndex();
            keyboard_navigation = true; 
        }
        Livewire.emit('postHighlighted', IR_posts.GetIndex());
        highlighted_post = document.querySelector('#post-'+IR_posts.GetIndex());
        highlighted_post.scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
    }
    
    // Keyboard shortcuts
    window.addEventListener('keydown', (e) => {
        
        if(e.key === 'Escape'){
            Livewire.emit('exitPost')
        }
        
        if (e.key == 'j' || e.key == 'J') {
            if (view_mode == 'list') {
                IR_posts.NextPost();
                updateHighlightPosition();
            } else {
                document.querySelector('#post-view').scrollBy(0, 200);
            }
        }
        if (e.key == 'k' || e.key == 'K') {
            if (view_mode == 'list') {
                IR_posts.PreviousPost();
                updateHighlightPosition();
            } else {
                document.querySelector('#post-view').scrollBy(0, -200);
            }

        }

        if (e.key == 'Enter') {
            if (highlighted_post) {
                Livewire.emit('postSelected', highlighted_post.dataset.postid)
            } else {
                console.log('no posts have been highlighted yet');
            }
        }

        if (e.key == 'e' || e.key == 'E'){
            if (highlighted_post) {
                Livewire.emit('markAsRead', highlighted_post.dataset.postid)
            }
        }

        console.log(e.key);
        
    })
});
    