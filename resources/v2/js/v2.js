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
    let post_being_viewed = 0;

    // When a post is marked as read. Reduce the count of the posts.
    Livewire.on('markPostAsRead', function(){
        IR_posts.markPostAsRead();       
    });

    // When a post is viewed, change view_mode to 'post'
    Livewire.on('viewPost', function(p){
        view_mode = 'post';
        post_being_viewed = p;
        console.log('switched viewing mode to Post ' + ' - Post: ' + post_being_viewed);
    });
    
    // When a post is exited, change view_mode to 'list'
    Livewire.on('exitPost', function(){
        view_mode = 'list';
        post_being_viewed = 0;
        console.log('switched viewing mode to list');
    });

    // Function to update the position of the highlight
    function updateHighlightPosition() {
        if (keyboard_navigation == false) {
            IR_posts.ResetIndex();
            keyboard_navigation = true; 
        }
        Livewire.emit('highlightPost', IR_posts.GetIndex());
        highlighted_post = document.querySelector('#post-'+IR_posts.GetIndex());
        highlighted_post.scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
    }
    
    // Keyboard shortcuts
    window.addEventListener('keydown', (e) => {
        
        if(e.key === 'Escape'){
            if (view_mode == 'list') {
                IR_posts.ResetIndex();
                highlighted_post = null;
                Livewire.emit('disableHighlight');                 
            } else {
                Livewire.emit('exitPost');
            }
        }
        
        if (e.key == 'j' || e.key == 'J') {
            if (view_mode == 'list') {
                if (highlighted_post) {
                    IR_posts.NextPost();
                }
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

        if (e.key == 'Enter' || e.key == 'o' || e.key == 'O') {
            if (view_mode == 'list') {
                if (highlighted_post) {
                    Livewire.emit('viewPost', highlighted_post.dataset.postid)
                } else {
                    console.log('no posts have been highlighted yet');
                }
            } else {
                window.open(
                document.querySelector('#post-view').dataset.url,
                "_blank"
                );
            }
        }

        if (e.key == 'e' || e.key == 'E'){
            if (highlighted_post) {
                Livewire.emit('markPostAsRead', highlighted_post.dataset.postid)
            }
        }
        if (e.key == 's' || e.key == 'S'){
            if (view_mode == 'post') {
                console.log('saving for later');
                Livewire.emit('savePostForReadLater', document.querySelector('#post-view').dataset.url);
            }
        }

        console.log(e.key);
        
    })
});
    