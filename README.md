# Kohana-WP

WordPress 3+ plugin to execute Kohana MVC framework routes and insert/replace
content within a WordPress page, post, widget ...

[Kohana-WP Home](http://kohana-wp.mgutz.com)

Licensed under the same license as WordPress [GPL2](http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)


## Description

Kohana-WP gives developers the tools needed to efficiently and correctly build 
web applications using the Kohana 3 HMVC framework. Kohana-WP is intended for developers 
experienced in HTML, Javascript and PHP.

## Installation

### Install through Plugins in WordPress Admin

This is a not yet released plugin. Please follow instructions in next section for now.

### Develop Using Edge Version

1. Install plugin and examples

    cd WORDPRESS_SITE/wp-content/plugins
    git clone git://github.com/mgutz/kohana-wp.git
    cd ..
    git clone git://github.com/mgutz/kohana-wp-examples.git

2. Copy `plugins/kohana-wp/htaccess.example` entries into `WORDPRESS\_SITE/.htaccess`

## Directory Structure

    WPROOT
        wp-content/
            kohana/
                framework/                #=> different Kohana versions (selecteable in future admin)
                    kohana-3.0.6.2/
                        system/
                        modules/          #=> not sure if this belongs here, version agnostic?
                    kohana-3.1.0-dev/
                sites/                    
                    all/                  #=> available applications to all sites (preparing for MU use)
                        app1/
                        app2/
                    default/
            plugins/
                kohana-wp                 #=> the new plugin

## RoadMap

Aug 2010 - Version 0.1 concentrate on supporting multiple applications and admin changes. Work in progress.
