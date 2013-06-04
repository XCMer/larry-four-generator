[![Build Status](https://travis-ci.org/XCMer/larry-four-generator.png?branch=master)](https://travis-ci.org/XCMer/larry-four-generator)

# Larry Four - The Laravel 4 Model & Migration Generator

If you are not already familiar, I released a generator for Laravel called <a href="https://github.com/XCMer/larry-laravel-generator">Larry</a>. This version is intended to work with Laravel 4, while supporting new features like polymorphic relations.

Larry Four has been re-written from scratch for Laravel 4. We have a better syntax that allows more flexibility and room for adding in more features.

## A bird's eye view

Here's how an input to larry would look:

    User users; hm Post; mo Image imageable;
        id increments
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

    Image

In the above case, Larry would create model and migration files for all the three models defined. The foreign keys for migrations will be automatically added. The model files automatically get functions defining the relations to other models, if any.


## Installation

I'm still working on this information. I have to get this indexed in packagist. If you decide to clone this before that, remember to add the following entry in the `providers` array of your `app/config/app.php` file: `'Raahul\LarryFour\LarryFourServiceProvider'`

I'll update this section soon.

## Syntax reference

Create a new text file at the root of your Laravel installation, and you can name it anything you want. Now, let's cover the syntactical nuances of Larry.

### Model definition

When defining a new model, the line should not be indented by any amount of whitespace. Obviously, you'll have to define a model before you define fields for it.

The most simple model definition would look like this:

    User

All models will automatically get an `id` field of type `increments` in Laravel. Apart from just defining the model, you can also define relations between models on this line.

    User; hm Post; ho Profile; btm Role; mm Image imageable;

In the above case, we specify the relation that the user has with the other models. The types of the relations supported are:

    hm: has many
    ho: has one
    btm: belongs to many
    mm: morph many
    mo: morph one

Notice that you can't specify `belongs to` and `morph to` relations, but they are added in automatically whenever a `has many` or `has one` is specified in a related model.

You can override the table name of the current model by simply adding it after the model name.

    User users; hm Post

Notice how "segments" of information are separated by semicolon. It doesn't matter if you put a semicolon at the end or not, just that every segment should be separated by it.

### Relational overrides

While specifying relations above, you can override the foreign key used. This can be done as:

    User; hm Post my_user_id;

In the above case, the foreign key `my_user_id` will be used instead of the conventional `user_id`. Larry takes care of the naming in the migrations, as well as overriding the default foreign key in the model's relation function.

In case of belongs to many, you can override the pivot table name:

    User; btm Role r_u;

And also the foreign keys inside the pivot table:

    User; btm Role r_u u_id r_id;

For polymorphic relations, it is mandatory to specify a second paramater to the relation, indicating the name of the polymorphic function:

    User; mm Image imageable;

### Field definition

After you define a model, you need to define fields for it.

    User users; hm Post; mo Image imageable;
        id increments
        timestamps
        username string 50; default "hello world"; nullable;
        password string 64
        email string 250
        type enum admin, moderator, user

Looking above, you'll get a good idea of how fields are defined. The syntax is as follows:

    <field_name> <field_type> <field_parameters>; <field_modifier>; <field_modifier> ...

The `increments` field is optional, and you should have a need to specify it only if you want your auto-incrementing field to be named differently from `id`.

The `timestamps` field is special, for it takes no parameters. By default, timestamps are disabled in all the models, and migrations don't created columns for them. By adding `timestamps` as a field, you enable it for that model.

Another type that has a different syntactical nuance is the `enum` field. The parameters to the enum fields are separated by commas. They may or may not be individually enclosed in quotes, like:

    type enum "admin", "moderator", "user"

Other types work as expected, and have syntax similar to the `string` type you see above.

The following field types are supported:

    increments
    string
    integer
    bigInteger
    smallInteger
    float
    decimal
    boolean
    date
    dateTime
    time
    timestamp
    text
    binary
    enum

And the following field modifiers are supported:

    default "value"
    nullable
    unsigned
    primary
    fulltext
    unique
    index


## Testing

There are a battery of PHPUnit tests written for Larry Four. This should keep the bugs out and also expedite feature releases.
