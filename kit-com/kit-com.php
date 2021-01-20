<?php
   /*
   Plugin Name: Kit
   Plugin URI: https://github.com/kitinc/kit-wordpress-plugin
   Description: Share your favorite products in an interactive, shoppable embed module directly on your blog.
   Version: 1.1.1
   Author: Kit.co
   Author URI: https://kit.co
   License: GPL2
   */
wp_oembed_add_provider(
    'https://kit.com/*/*', 'https://kit.co/oembed', false);

wp_oembed_add_provider(
    'https://kit.co/*/*', 'https://kit.co/oembed', false);
?>
