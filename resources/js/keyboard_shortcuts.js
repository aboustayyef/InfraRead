import { upperCase } from "lodash";

let settings = {
    down: "j",
    up: "k",
    escape: "Escape",
    showLinks: "f",
    jump: " ",
    showPost: "Enter",
    open: "o",
    undo: "u",
    markAsRead: "e",
    go_to_bottom: "ShiftG",
    go_to_top: "gg",
    search_on_twitter: "t",
};

export function handle_keyboard_shortcut(key, app) {
    if (key == "") {
        return;
    }
    console.log(key);
    // external links shortcuts
    if (key.match(/\d/)) {
        if (eval(key) < app.external_links.length) {
            window.open(app.external_links[key], "_blank");
        }
    }
    switch (key) {
        case settings.showLinks:
            if (app.view == "post") {
                if (!app.external_links_shortcuts) {
                    console.log("turn on");
                    app.turn_on_external_links_shortcuts();
                } else {
                    app.turn_off_external_links_shortcuts();
                }
            }

            break;
        case settings.escape:
            if (app.view == "post") {
                app.exit_post();
            }
            if (app.view == "list" && app.highlighter_on) {
                app.highlighter_on = false;
                app.highlighter_position = 0;
            }
            break;
        case settings.jump:
            if (app.view == "post") {
                document
                    .querySelector("#post-view")
                    .scrollBy({ top: 400, behavior: "smooth" });
            }
            break;
        case settings.down:
            if (app.view == "post") {
                document.querySelector("#post-view").scrollBy(0, 200);
            } else {
                if (app.highlighter_on == false) {
                    app.highlighter_on = true;
                    app.show_highlighted_post();
                } else {
                    if (
                        app.highlighter_position <
                        app.number_of_unread_posts - 1
                    ) {
                        app.highlighter_position++;
                        app.show_highlighted_post();
                    }
                }
            }
            break;
        case settings.up:
            if (app.view == "post") {
                document.querySelector("#post-view").scrollBy(0, -200);
            } else {
                if (app.highlighter_on == false) {
                    app.highlighter_on = true;
                    app.show_highlighted_post();
                } else {
                    if (app.highlighter_position > 0) {
                        app.highlighter_position--;
                        app.show_highlighted_post();
                    }
                }
            }
            break;
        case settings.showPost:
            if (app.view == "list" && app.highlighter_on == true) {
                app.display_post(app.highlighted_post);
            }
            break;
        case settings.open:
            if (app.view == "list" && app.highlighter_on == true) {
                app.display_post(app.highlighted_post);
                return;
            }
            if (app.view == "post") {
                window.open(app.displayed_post.url, "_blank");
            }
            break;
        case settings.markAsRead:
            if (app.view == "list" && app.highlighter_on == true) {
                app.mark_post_as_read(app.highlighted_post);
                // Check if it was the last post, then highlight to previous post
                if (app.highlighter_position == app.number_of_unread_posts) {
                    app.highlighter_position = app.number_of_unread_posts - 1;
                    app.show_highlighted_post();
                }
                return;
            }
            if (app.view == "post") {
                app.exit_post();
            }
            break;
        case settings.undo:
            app.undo();
        case settings.go_to_bottom:
            if (app.view == "post") {
                document.querySelector("#post-view").scrollBy(0, 20000);
            } else {
                app.highlighter_position = app.number_of_unread_posts - 1;
                app.show_highlighted_post();
            }
            break;
        case settings.go_to_top:
            if (app.view == "post") {
                document.querySelector("#post-view").scrollBy(0, -20000);
            } else {
                app.highlighter_position = 0;
                app.show_highlighted_post();
            }
            break;
        case settings.search_on_twitter:
            if (app.view == "list" && app.highlighter_on == true) {
                window.open('https://twitter.com/search?q=' + encodeURI(app.highlighted_post.url), "_blank");
                return;
            }
            if (app.view == "post") {
                window.open('https://twitter.com/search?q=' + encodeURI(app.displayed_post.url), "_blank");
            }
            break;
        default:
            break;
    }
}
