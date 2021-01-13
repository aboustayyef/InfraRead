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

    // When a post is marked as read. Reduce the count of the posts.
    Livewire.on('markAsRead', function(){
        IR_posts.markPostAsRead();       
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
            IR_posts.NextPost();
            updateHighlightPosition();
        }
        if (e.key == 'k' || e.key == 'K') {
            IR_posts.PreviousPost();
            updateHighlightPosition();
        }

        if (e.key == 'Enter') {
            if (highlighted_post) {
                console.log('there is a highlighted post');
                Livewire.emit('postSelected', highlighted_post.dataset.postid)
            } else {
                console.log('no posts have been highlighted yet');
            }
        }

        console.log(e.key);
        
    })
});
    