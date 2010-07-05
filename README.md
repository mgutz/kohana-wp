# Kohana-WP

WordPress to execute Kohana MVC framework controllers and use the content within 
pages, widgets, posts ...

[Kohana-WP Home](http://kohana-wp.mgutz.com)

Licensed under the same license as WordPress [GPL2](http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)

## Description

Kohana-WP gives developers the tools needed to efficiently and correctly build 
web applications using the Kohana 3 HMVC framework. Kohana-WP is intended for developers 
experienced in HTML, Javascript and PHP.


## Why WordPress?

Many developers try to force WordPress or another CMS into what they do. I feel 
that's the wrong approach. CMS provide ACL, plugins, blogs, SEO, out of the
box. Why reinvent the whell. Many developers think in HTML, Javascript and PHP. 
For them, WordPress gets in the way. That used to be the
case, then came Kohana-for-WordPress and now Kohana-WP.

WordPress is a serious platform with nearly 12,000,000 websites and over 100,000,000 
plugin downloads as of July 2010. Unless your team is afflicted with Not Invented Here Syndome (NIHS), 
WordPress is a good foundation for most web applications. WordPress as a CMS? Don't
believe the FUD. Custom Post Types, Fields and Multi-Site features are now part of WordPress
3. WordPress will be THE CMS in 3-5 years! Believe that.

## RoadMap

Aug 2010 - Version 0.1 concentrate on views and controllers. Work in progress.
Oct 2010 - NoSQL-like ORM built on top of WordPress Custom Post Types, Fields.

## Kohana-WP vs. Kohana-for-WordPress
 
This plugin started our as a modified version of Kohana-for-WordPress. My conventions,
directory structure and needs differ enough to warrant a new plugin. New features:

1. Application namespaces. Many applications may be installed without fear of clashing.
MVC URL syntax is `application/controller/action/arg0/.../argn`

2. Intra-application helper links. Entire multi-step wizards can be rendered as a 
single page. No postback hell logic.

3. Convention over configuration. Directories are fixed and no longer configurable.
(Linux has symbolic links and Windows Server has junctions.)


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
