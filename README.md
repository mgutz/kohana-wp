# Kohana for Wordpress (Kohana-WP)

Plugin for running the Kohana 3.0+ framework inside of Wordpress.

Work in progress.

## Description

Kohana-WP frees you from the constraints of WordPress to build unbelievably complex
websites demanded by clients. Kohana-WP is a plugin which allows developers
to leverage the Kohana 3 MVC framework for PHP 5 to build pages, forms, plugins, 
widgets and text macros.

Kohana-WP is made for developers who know HTML, Javascript and PHP. WordPress
is a serious platform with almost 12,000,000 websites and over 100,000,000 
plugin downloads. If you have not-invented-here syndrome, please do not bother.

The first phase is to concentrate on VC (views-controllers) which are working
but needs more examples. The second phase is to 
build a dynamic NoSQL-like ORM on top of WordPress Custom Post Types, Fields and
Taxonomy.

## Kohana-WP vs. Kohana-for-WordPress
 
This is a heavily modified version of the original plugin. My conventions,
directory structure and needs are different enough to warrant a different
plugin. New features:

1. Application namespaces. Many applications may be installed without fear of clashing.
MVC URL syntax is `application:controller/action/arg0/.../argn`

2. Intra-application helper links. Entire multi-step wizards can be rendered as a 
single page. No postback hell logic.

3. Convention over configuration. Directories are fixed and no longer configurable.
(Linux has symbolic links and Windows Server has junctions.)

4. Simplified admin. Hope to enhance this in the feature to manage Kohana framework versions 
and Kohana-WP applications.

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
