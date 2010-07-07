# Kohana-WP

WordPress 3+ plugin to execute a Kohana MVC framework route and insert/replace
the resulting output into a WordPress page, post or widget.

[Kohana-WP Home](http://kohana-wp.mgutz.com) - Not yet live.

Licensed under [GPL2](http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) to meet the
requirement for a WordPress plugin. Frankly, if you need it to use it for commercial purposes just ask. 
I hope if you find benefit in using the plugin, you consider contributing back to the community.

## Description

Kohana-WP gives developers the tools needed to efficiently and correctly build 
web applications using the Kohana 3 HMVC framework. Kohana-WP is intended for developers 
experienced in HTML, Javascript and PHP.

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

## Plugin Load Sequence

### Plugin LifeCycle

if plugin is activated
    WordPress loads `kohana-wp.php` on any page
    define kwp constants
    if page is in admin area
        register plugin hooks
        load `classes/kwp/admin/hooker.php`
        `$hooker->register_hooks()`
    else
        load `classes/kwp/non_admin/hooker.php`
        `$hooker->register_hooks()`

### Kohana Execution
    TBD

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
                    modules/              #=> utility classes, Controller_KWP, Helper_KWP ...
                
## RoadMap

Aug 2010 - Version 0.1 concentrate on supporting multiple applications and admin changes. Work in progress.
