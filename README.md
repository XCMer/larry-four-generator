# XCMer's Larry Four - The Laravel 4 Model & Migration Generator
***(with extended model generation options by Gadoma)***

**Master:**<br>
**(XCMer/larry-four-generator/tree/master)**<br>
Current Version : 1.0.1 (stable) [![Build Status](https://travis-ci.org/XCMer/larry-four-generator.png?branch=master)](https://travis-ci.org/XCMer/larry-four-generator)

**Extended Models:**<br>
**(XCMer/larry-four-generator/tree/Gadoma-extendedmodels)**<br>
Current Version : 1.1.0 (alpha) [![Build Status](https://travis-ci.org/XCMer/larry-four-generator.png?branch=Gadoma-extendedmodels)](https://travis-ci.org/XCMer/larry-four-generator)

**Extended Models Dev:**<br>
**(Gadoma/larry-four-generator/tree/Gadoma-extendedmodels)**<br>
Current Version : [![Build Status](https://travis-ci.org/Gadoma/larry-four-generator.png?branch=Gadoma-extendedmodels)](https://travis-ci.org/Gadoma/larry-four-generator)

Larry Four is a Laravel 4 package offering advanced model and migration generation functionality. Thanks to Larry you can quickly jot down the data scheme for your idea in an easy DSL and genereate migrations and models from it with just a single click. Larry can also analyse your existing data scheme and generate some magic for you too.  

Currently the Extended Models functionality enables to generate several code snippets integrating the created models with <br>
* Ardent [https://github.com/laravelbook/ardent](https://github.com/laravelbook/ardent)<br>
* Sluggable [https://github.com/cviebrock/eloquent-sluggable](https://github.com/cviebrock/eloquent-sluggable)

Apart from the above, you can easily generate callback stubs (beforeSave, afterUpdate, etc.) and configure the mass assign properties of the models <br>
* [http://four.laravel.com/docs/eloquent#mass-assignment](http://four.laravel.com/docs/eloquent#mass-assignment)<br>


## A bird's eye view

Here's how an input to larry would look:

    User users; hm Post; mo Image imageable; btm Role;
        id increments
        timestamps
        username string 50; default "hello world"; nullable;
        password string 64
        email string 250
        type enum admin, moderator, user

    Post; mm Image imageable;
        timestamps
        title string 250
        content text
        rating decimal 5 2

    Image
        filename string

    Role
        name string
        level integer

In the above case, Larry would do the following:

- Create migration files for all the tables required, along with all the columns. Larry automatically figures out which foreign key columns to add, depending on the relations you define. Pivot table in case of belongsToMany relation is also automatically created.
- Create model files for all the models defined. These models also have relational functions automatically defined in them.


## Installation

You can visit <a href="https://packagist.org/packages/raahul/larryfour">Packagist</a> to check the latest version of Larry Four. Currently, it is `1.0.1`.
If you want to get the Extended Models functionality you need to use the Gadoma-extendedmodels branch. 

Here are the steps:

- Put the following in your composer.json: `"raahul/larryfour": "1.*"` for the master or `"raahul/larryfour": "dev-Gadoma-extendedmodels"` for Extended Models branch. 
- Run `composer update`
- Add `'Raahul\LarryFour\LarryFourServiceProvider'` to the `providers` array of `app/config/app.php`

Alternatively if you want to get the latest dev version of Extended Models, you can manually install the package from <br> [https://github.com/Gadoma/larry-four-generator/tree/Gadoma-extendedmodels](https://github.com/Gadoma/larry-four-generator/tree/Gadoma-extendedmodels) .<br>

## Configuration

First you have to publish Larry Four settings:

    php artisan config:publish raahul/larryfour

The configuration is divided into two separate files and the detailed comments included make it pretty straight forward to tailor Larry to your needs.

First one is /app/config/packages/raahul/larryfour/slugs.php
    
    /*
      |--------------------------------------------------------------------------
      | Create slugs
      |--------------------------------------------------------------------------
      |
      | Defines if the models should be updated with rules for the
      | Sluggable (eloquent-sluggable) package
      |
     */

    'createSlugs' => false,
    
    /*
      |--------------------------------------------------------------------------
      | Select slugs
      |--------------------------------------------------------------------------
      |
      | Defines rules used to pick models for update 
      | Items (Array) - defines the model's field names
      | Function (Enum all|one) - defines the list selection criteria
      |
     */
    
    'selectSlugs' => array(
        'items' => array('slug'),
        'function' => 'one',
        
        
    ),
    
    /*
      |--------------------------------------------------------------------------
      | Configure slugs
      |--------------------------------------------------------------------------
      |
      | Defines the Sluggable default configuration in reference to the fields
      | of a model. The keys of the configureSlugs array can be friendly names 
      | of your configuration rule sets. The order of keys determines the order 
      | in which the rule sets will be matched, first good match is used.
      | 
      | The required and forbidden rules determine if a model matches the 
      | ruleset. If the model has all fields present in required array and none 
      | of the fields in the forbidden array it matches the ruleset and the 
      | associated rules are used with Sluggable.
      |
      | The attribute getter is required if build_from is not a column. In the 
      | example template it just echoes back the field name. The mapGetters 
      | below is also set for example (it is not needed here).
      | 
     */
    
    'configureSlugs' => array(
        'default' => array(
            'required' => array('title','slug'),
            'forbidden' => array(),
            'rules' => array(
                'build_from' => 'title',
                'save_to'    => 'slug',
                'method'     => null,
                'separator'  => '-',
                'unique'     => true,
                'on_update'  => false,
            ),
            'builder' => array(
              'createGetters' => true,
              'mapGetters' => array(
                  array('methodName'=>'Title','fieldName'=>'title'),
                  ),
            ),
        ),
        
    ),




The second file is /app/config/packages/raahul/larryfour/validation.php

    /*
      |--------------------------------------------------------------------------
      | Process models
      |--------------------------------------------------------------------------
      |
      | Defines if the models should be updated with additional namespaces,
      | parent class and validation rules array stub
      |
     */

    'processModels' => false,
    
    /*
      |--------------------------------------------------------------------------
      | Update models selection
      |--------------------------------------------------------------------------
      |
      | Defines rules used to pick models for update 
      | Items (Array) - defines the model names
      | Function (Enum all|only|except) - defines the list selection criteria
      |
     */
    
    'processSelection' => array(
        'items' => array(),
        'function' => 'except',
        
    ),
    
    
    /*
      |--------------------------------------------------------------------------
      | Namespaces
      |--------------------------------------------------------------------------
      |
      | Defines which namespaces are added into the model by default
      |
      |
     */

    'namespaces' => array(
    ),
    
    /*
      |--------------------------------------------------------------------------
      | Parent class
      |--------------------------------------------------------------------------
      |
      | Defines the parent class that the updated model extends
      |
      |
     */

    'parentClass' => '',
    
    /*
      |--------------------------------------------------------------------------
      | Validate models
      |--------------------------------------------------------------------------
      |
      | Defines if the model should be updated with validation rules array stub
      |
      |
     */

    'validateModels' => array(
        'createRules' => false,
        'includeEmpty' => true,
        'defaultRules' => array(),
        'mapRules' => array(
            'fieldTypes' => array(
                'string'=>array(), 
                'integer'=>array('integer'), 
                'bigInteger'=>array('integer'), 
                'smallInteger'=>array('integer'),
                'float'=>array('numeric'),
                'decimal'=>array('numeric'),
                'boolean'=>array(),
                'date'=>array(),
                'dateTime'=>array(),
                'time'=>array(),
                'timestamp'=>array(),
                'text'=>array(),
                'enum'=>array()
                ),
            'fieldNames' => array(
                
            ),
        ),
    ),
    
    /*
      |--------------------------------------------------------------------------
      | Fillable fields
      |--------------------------------------------------------------------------
      |
      | Defines if the model should be updated with fillable array stub with
      | default content. Fillable fields can be mass assigned.
      |
     */

    'fillModels' => array(
        'createFillable' => false,
        'defaultFillable' => array(),
        'allFillable' => false,
    ),
    
    /*
      |--------------------------------------------------------------------------
      | Guraded fields
      |--------------------------------------------------------------------------
      |
      | Defines if the model should be updated with guraded array stub with
      | default content. Guarded fields cannot be mass assigned.
      |
     */

    'guardModels' => array(
        'createGuarded' => false,
        'defaultGuarded' => array(),
        'allGuarded' => false,
    ),
    
    /*
      |--------------------------------------------------------------------------
      | Hidden fields
      |--------------------------------------------------------------------------
      |
      | Defines if the model should be updated with hidden array stub with
      | defualt content. Hidden fields are excluded from model's JSON form.
      |
     */

    'hideModels' => array(
        'createHidden' => false,
        'defaultHidden' => array(),
        'allHidden' => false,
    ),
    
    /*
      |--------------------------------------------------------------------------
      | Visable fields
      |--------------------------------------------------------------------------
      |
      | Defines if the model should be updated with visable array stub with
      | defualt content. Only visible fields are excluded in model's JSON form.
      |
     */

    'showModels' => array(
        'createVisable' => false,
        'defaultVisable' => array(),
        'allVisable' => false,
    ),
    
    
    /*
      |--------------------------------------------------------------------------
      | Ardent configuration
      |--------------------------------------------------------------------------
      |
      | Defines Ardent (laravelbook/ardent) specific configuration:
      | $autoHydrateEntityFromInput = true;
      | $autoPurgeRedundantAttributes = true;
      | $passwordAttributes  = array('password');
      | $autoHashPasswordAttributes = true;
      |
     */

    'ardentConfig' => array(
        'autoHydrate' => false,
        'autoPurge' => false,
        'hashFields' => array(),
        'autoHash' => false,
    ),
    
     /*
      |--------------------------------------------------------------------------
      | Callback configuration
      |--------------------------------------------------------------------------
      |
      | Defines whether to place event hook stubs in the model file
      |
      |
     */

    'exposeHooks' => array(
        'beforeSave' => false,
        'beforeUpdate' => false,
        'beforeDelete' => false,
        'beforeValidate' => false,
        'afterSave' => false,
        'afterUpdate' => false,
        'afterDelete' => false,
        'afterValidate' => false,
    ),

    
    /*
      |--------------------------------------------------------------------------
      | Defaults
      |--------------------------------------------------------------------------
      |
      | Defines default model parameters
      |
      |
     */

    'defaults' => array(
        'parentClass' => 'Eloquent'
    ),

## Usage

Once you've successfully installed Larry Four, its commands should be accessible via `artisan`. You can always type just `php artisan` to see all available commands, and those available under the `larry` namespace.

Larry Four supports **four commands.**

    php artisan larry:generate <input_file>

The above command takes `<input_file>` as the input, and generates models and migrations based on that. You have to provide a filename that exists at the root of your Laravel 4 installation, wherein artisan itself resides.

You cannot provide absolute paths for the input file yet. If you're providing a relative path instead of a filename, then it is relative to Laravel's root directory (or basepath).

There are two other commands:

    // Generate only migrations, not models
    php artisan larry:migrations <input_file>

    // Generate only models, not migrations
    php artisan larry:models <input_file>


### Generating migrations from existing tables

The fourth command is a bit different, since it allows you to generate migrations from existing tables. Use it as follows:

    php artisan larry:fromdb

By default, it will pick up all the tables in the database (except the Laravel migration table). Larry Four will always show you a list of tables that will be processed and ask you for a confirmation.

The tables that Larry processes can be altered by specifying the `only` and `except` options to the command.

    // This will process only the tables 'users' and 'posts'
    php artisan larry:fromdb --only=users,posts

    // This will process all tables except users and posts
    php artisan larry:fromdb --except=users,posts


Again, you'll get to confirm your selection before the migrations are generated.

Larry Four is intelligent, in that it can distinguish booleans from other tinyints, and increments from a normal unsigned integer.

Be aware that your tables need to have a integer primary key. If it is not found, the migration would automatically contain an `id` field.


## Syntax reference

Create a new text file at the root of your Laravel installation, and you can name it anything you want. The input file basically defines the following things:

- All the models that should be created
- The relationship between those models
- The fields inside each of those models
- Modifiers to the fields like default values, nullable/unsigned, and indices

All the models get an auto-incrementing primary key called `id`. You can override the name of the primary key. All foreign keys created are unsigned.

Larry provides ways to override foreign key and pivot table names as well. These are optional. By default, Larry follows the same convention as Laravel for naming tables, pivot tables, and foreign keys.

Finally, Larry ignores blank lines (even if they contain whitespace). So, you're free to beautify the looks of your input.

Now, let's begin with the syntax.

### Model definition

Model definition is how you tell Larry about a new model that has to be created. Since fields need a model to be added to, a model definition will be the first logical line of your input file.

When defining a new model, the line **should not** be indented by any amount of whitespace.

The most simple model definition would look like this:

    User

All models will automatically get an `id` field of type `increments`. Apart from just defining the model, you can also define relations between models on this line.

    User users; hm Post; mo Image imageable; btm Role;

In the above case, we specify the relation that the user has with the other models. The types of the relations supported are:

    hm: hasMany
    ho: hasOne
    btm: belongToMany
    btmc: belongsToMany with custom pivot table
    mm: morphMany
    mo: morphOne

Notice that you can't specify `belongsTo` and `morphTo` relations. They are added automatically to the concerned model when their inverses, `hasMany, hasOne, morphMany, morphOne`, are specified in a related model.

The `belongsToMany` with custom pivot table is covered in the section: "Creating orphan tables"

#### Semicolons

When defining related models, each definition is delimited by a semicolon. The final semicolon is optional.


#### Overriding table name

You can override the table name of the current model by simply adding it after the model name.

    User my_users; hm Post

The generated model and migration will take this into account.


#### Overriding foreign key and pivot table names

While specifying relations above, you can override the foreign key used. This can be done as:

    User; hm Post my_user_id;

In the above case, the foreign key `my_user_id` will be used instead of the conventional `user_id`. Larry takes care of the naming in the migrations, as well as overriding the default foreign key in the model's relation function.

In case of `belongsToMany`, you can override the pivot table name:

    // pivot table will be named "r_u" instead of the
    // conventional role_user
    User; btm Role r_u;

And also the foreign keys inside the pivot table:

    // foreign keys are named "u_id" & "r_id" instead of
    // the conventional "user_id" & "role_id"
    User; btm Role r_u u_id r_id;

For polymorphic relations (`morphOne` and `morphMany`), it is mandatory to specify a second paramater to the relation, indicating the name of the polymorphic function:

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
        softDeletes

Looking above, you'll get a good idea of how fields are defined. The syntax is as follows:

    <field_name> <field_type> <field_parameters>; <field_modifier>; <field_modifier> ...

- The `<field_name>` is simply the column name.
- The `<field_type>` is any of the field types supported by Laravel.
- `<field_parameters>` are additional parameters to a field function, like length of a string.
- `<field_modifier>` includes default, nullable, unsigned , and indices. Multiple field modifiers can be specified for a field.

**Below are certain points to note:**

The `increments` field is optional, and you should have a need to specify it only if you want your auto-incrementing field to be named differently from `id`.

The `timestamps` and `softDeletes` fields are special, for they have no field name. By default, timestamps and softDeletes are disabled in all the models, and migrations don't create columns for them. By adding either of them as a field, you enable them for that model, and the migration will contain the necessary columns.

Another field that has a different syntactical nuance is the `enum` field. The parameters to the enum fields are separated by commas. They may or may not be individually enclosed in quotes, like:

    type enum "admin", "moderator", "user"
    OR
    type enum admin, moderator, user

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
    timestamp (not to be confused with timestamp**s**)
    text
    binary
    enum
    softDeletes

And the following field modifiers are supported:

    default "value"
    nullable
    unsigned
    primary
    fulltext
    unique
    index


### Creating orphan tables

Now, Larry also allows you to create tables (or migrations to precise), without creating models and relations for that table. In short, it's a quick way to add in just a table.

This feature is primarily useful in creating custom pivot tables for the `btmc` relation type. Pivot tables are not associated with any models.

Normally, for `btm` relations, a pivot table is automatically created for you with the required fields. However, at times, you'd want to have additional fields in your pivot table. In such cases, you should use the `btmc` relation, and then specify a custom table with all the desired columns.

An example would be:

    Post; mm Image imageable; btmc Comment;
        timestamps
        title string 250
        content text
        rating decimal 5 2

    table comment_post
        comment_id integer; unsigned
        post_id integer; unsigned
        name string
        type string
        additional fields...

A Post now belongsToMany comments (unusual). Laravel's convention dictates that the pivot table should be called `comment_post`, and the fields inside should be `comment_id` and `post_id`.

You can override the pivot table name and field names the same way as you did in `btm`. Just make sure that your custom table reflects it.

Custom tables are defined using the term `table <table_name>`, as you might have noticed. This is how Larry differentiates whether you want to define a model, or an orphan table.

If you specify additional fields, they will be automatically added to `withPivot` function in the model's relation:

    return $this->belongsToMany('Comment')->withPivot('name', 'type');

If your pivot table contains timestamps, Larry will add them to the relational function too:

    return $this->belongsToMany('Comment')->withPivot('name', 'type')->withTimestamps();

**Note:** If you don't create a pivot table with the right name, or the pivot table doesn't contain the necessary columns for a `belongsToMany` relation, Larry will throw a helpful error. Remember that the foreign keys of a pivot table have to be unsigned integers.


## Error handling

Larry Four has an improved error handling mechanism. If there are syntax errors in your input file, you will be notified about it along with the line number. The error will tell you exactly what's wrong in plain English.

Currently, Larry can detect the following errors:

- Typo in relationship types (typing `hms` instead of `hm` will yield an error)
- Insufficient parameters to relationships or field definitions
- Non-existance of a model that was specified as related in another model
- Invalid field types

## Testing

The repo contains PHPUnit tests. This should keep the bugs out and also expedite feature releases.
