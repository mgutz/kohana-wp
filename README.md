# Kohana-WP

WordPress 3+ plugin to execute a Kohana MVC framework route and inject
the output result into a WordPress page, post or widget.

[Kohana-WP Home](http://kohana-wp.mgutz.com) - Not yet live.

Licensed under [GPL2](http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) to meet the
requirement for a WordPress plugin. Frankly, if you need it to use it for commercial purposes just ask. 
I hope if you find benefit in using the plugin, you consider contributing back to the community.

## Description

Kohana-WP allows developers to use Kohana MVC framewor5k to efficiently and happily build 
web applications. Kohana-WP is for developers experienced in HTML, Javascript and PHP.

## Installation

### Install through Plugins in WordPress Admin

Plugin is not yet released. Please follow instructions in next section for now.

### Develop Using Edge Version

Good idea to start with a new database when I change the admin. Settings in DB could be in an invalid state.

1. Install plugin and examples

        cd WORDPRESS_SITE/wp-content/plugins
        git clone git://github.com/mgutz/kohana-wp.git
        cd ..
        git clone git://github.com/mgutz/kohana-wp-examples.git kohana

2. Copy and paste entries in `docs/htaccess.example` entries into `WORDPRESS_SITE/.htaccess`.

## Default Stack

One of the good things about Kohana MVC is choices. One of the bad things about Kohana MVC is choices.

Kohana-WP has a default stack:

* Template Engine - Mustache (code-behind class is optional)
* ORM - TBD, needs to be lightweight data mapper since WordPress has a schema already
* Generators - Generators will be web based for a basic application and CRUD generator for Custom Post Types
* Testing Framework - TBD

## Things to Ponder

1. The biggest hurdle, beside WordPress' non-object oriented framework is the concept of application spaces.
   Normally, there is
   one application using Kohana MVC. Kohana-WP allows multiple applications to coexist within
   WordPress and each applicatoin is dynamically bootstrapped as needed. You MUST use `app_url`,
   `controller_url` when creating links
   to an action or static asset. Kohana MVC applications are at the mercy of WordPress.
   URLs may change through SEO plugins, user customization etc. Pages may be moved.
   
2. WordPress path constants do not end with '/', Kohana path constants do.


## Directory Structure

Directory structure for applications follows the convention of Kohana MVC applications. 

    WORDPRESS_SITE/
        wp-content/
            kohana/
                sites/                      #=> non-member end-user tier (premium/ is another internal tier at my startup)
                    all/                    #=> selectable apps for this tier
                        app_name_1/
                            application/    
                                classes/        #=> controllers, models
                                ...
                            modules/        #=> app modules (all are loaded, suffix with .off to disable)
                                auth.off/
                                mustache.off/
                                db/
                            public/         #=> static assets
                            system/         #=> Kohana MVC framework (optional but recommended)
                            views/          #=> templates and code-behind classes
                        ...
                        app_name_2/
                    default/                #=> default apps for this tier
            plugins/
                kohana-wp/                  #=> the plugin
                    application/            #=> classes to integrate with WordPress
                    modules/                #=> custom controller, views and helpers to faciliate creating applications
                    system/                 #=> default Kohana MVC framework

## Constants

    WORDPRESS_SITE/                         #=> ABSPATH
        wp-content/                         #=> WP_CONTENT_DIR
            kohana/                         #=> KOHANA_ABSPATH
                sites/                      
                    all/                    
                        app_name_1/         #=> DOCROOT
                            application/    #=> APPPATH 
                                classes/
                            modules/        #=> MODPATH
                            public/         #=> PUBPATH 
                            system/         #=> SYSPATH
                        ...
                        app_name_2/
                    default/                
            plugins/
                kohana-wp/                  #=> KWP_DOCROOT
                    application/            #=> KWP_APPPATH
                        classes/
                    modules/                #=> KWP_MODPATH
                    public/                 #=> KWP_PUBPATH
                    system/                 #=> KWP_SYSPATH
                
## RoadMap

Aug 2010 - Version 0.1 concentrate on the default stack (ORM, default teplate engine, multiple applications). WIP.
