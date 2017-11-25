<?php

namespace App\Fetchers;

interface Fetchable{
    public function fetch();
    public function get_list_of_post_links();
    public function get_new_posts();
}