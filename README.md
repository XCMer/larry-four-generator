# Larry Four - The Laravel Model & Migration Generator For Laravel 4

If you are not already familiar, I released a generator for Laravel called <a href="https://github.com/XCMer/larry-laravel-generator">Larry</a>. This version is intended to work with Laravel 4, while supporting new features like polymorphic relations.

This is still under works. I working on the parsing and generation of code. The Laravel specific
code to tie it up with the system will be written later.


## Changes from the old Larry

The old Larry for Laravel 3 relied heavily on defaults. There was no way to override table names
in a relationships, foreign keys, and it did not support the default values for fields.

The primary reason for all of this was the simplicity behind Larry's syntax. Supporting these
features with that syntax would have made things very messy.

If you've used the old Larry, the thing that'll stand out is the changes to the syntax. Though it
is in no way finalized, this is how it'll probably end up looking:

    User users; hm Post;
        id increments (optional)
        timestamps
        username string 50; default "hello world"; nullable;
        password string 64
        email string 250
        type enum admin, moderator, user

    Post; bt User; mm Image imageable;
        timestamps
        title string 250
        content text
        rating decimal 5 2

The primary motivation behind this to keep the syntax simple enough for default case use, but at the
same time giving the flexibility to customize more of the generated output.

Also, I'm following a test-based approach for this version of Larry, so you should see a PHPUnit
test suite for every code written. This was essential because parsing is tricky business, and the
most obvious way to make sure it's working right is to run it.