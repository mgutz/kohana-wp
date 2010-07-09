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

2. Copy and paste `plugins/kohana-wp/htaccess.example` entries into `WORDPRESS_SITE/.htaccess`

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

    WORDPRESS_SITE/
        wp-content/
            kohana/
                framework/                #=> different Kohana versions (selectable in future admin)
                    current/
                        system/
                    kohana-3.0.6.2/
                modules/
                sites/                    
                    all/                  #=> available applications to all sites (preparing for MU use)
                        app1/
                            classes/
                            ...
                            modules/      #=> apps can override modules
                        app2/
                    default/
            plugins/
                kohana-wp/                #=> the plugin itself
                    classes/              #=> classes used to integrate with WordPress
                    modules/              #=> custom base controllers, helpers and dependent libraries for use by apps
                
## RoadMap

Aug 2010 - Version 0.1 concentrate on the default stack (ORM, default teplate engine, multiple applications). WIP.
