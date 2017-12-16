<img src="public/img/touch/infraread144.png" width="72" height="72">

# InfraRead

InfraRead (pronounced _infrared_ ) is an elegant self-hosted RSS feed reader and sync service. It is optimized for simplicity, reading and comfort. It is inspired by [Unread](https://itunes.apple.com/us/app/unread-rss-reader/id1252376153?mt=8) and built with [Laravel](https://laravel.com) and [VueJs](https://vuejs.org/).

![](public/img/screenshot.png?raw=true)

## Features:

<img src="public/img/toggle-details.png?raw=true" width="365" height="42">

*  Straightforward to Install and setup
*  Distraction free. Attention to details and optimized for reading
*  Import your own OPML or add feeds manually
*  Progressive Web App installable on Android devices 
(Lighthouse score 100)

## Requirements

*  [Same Requirements as Laravel 5.5](https://laravel.com/docs/5.5/installation#server-requirements)
*  Ability to create Cron Jobs

## Installation

*  Download or clone the repository to your server 
*  run ```composer install```
*  run ```php artisan key:generate```
*  make a copy of ```.env.example``` and save as ```.env```
*  enter your admin details in ```.env```
*  enter your database details in ```.env```
*  run ```php artisan migrate``` to migrate the database
*  run ```php artisan db:seed```, this will populate your admin details in the database
*  If you want to modify the js and css assets, also run: ```npm install```, and then ```npm run dev```

## License

M.I.T. do anything you want with the code as long as you provide attribution back and you and don’t hold me liable

## Contact or Questions

Find me on [Twitter](https://twitter.com/beirutspring)

## Built on top of

Laravel, VueJs, Bulma, Bootstrap, [Will Vincent Feeds](https://github.com/willvincent/feeds)