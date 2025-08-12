# Objectives

Refactor this RSS reader from a monolith to an API based structure with a backend that reads, processes, organizes and stores RSS feeds and a frontend that consumes and updates it.

# Current Structure

This is a laravel project that runs a Cron job every n minutes to check on a bunch of RSS feeds (which are in the database), checks for new items, and then stores them in the database after processing them using various plugins. 
The Laravel project also includes a webapp that is used to read the feeds, mark them as read, save for later, etc. it relies heavily on vue.js for responsiveness and for use of keyboard shortcuts. It also uses custom endpoints to communicate with the database (as opposed to standard CRUD APIs). The App includes the ability to subscribe to new RSS feeds, mark posts as read/unread, saver for later, summarize using AI, etc.

# Desired outcome

The desired outcome for this major refactor is to have an app that runs on the server and presents API access to any frontend app. And to have a separate vue app that consumes that API. I want the vue app to have the exact functionality of the existing app, including the keyboard shortcuts. What will change is how it communicates with the server, using APIs instead of through direct calls to laravel. 
Also That should open up the possibility for me to create apps on different platforms to consume the same API

# Plan of action

For the time being, I want you to look at my entire codebase, understand how it works, and give me a plan of action for how you're planning to accomplish the objectives

