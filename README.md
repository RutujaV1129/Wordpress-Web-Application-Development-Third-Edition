# Wordpress Web Application Development - Third Edition
This is the code repository for [Wordpress Web Application Development - Third Edition](https://www.packtpub.com/application-development/wordpress-web-application-development-third-edition?utm_source=github&utm_medium=repository&utm_campaign=9781787126800), published by [Packt](https://www.packtpub.com/?utm_source=github). It contains all the supporting project files necessary to work through the book from start to finish.
## About the Book
The rise of WordPress-powered websites is one of the standout trends in the modern web
development world. WordPress has taken over 25% of all the websites in the world. The
power of the plugin-based architecture and the flexibility of the built-in features offered by
WordPress has made developers use this framework for advanced web development. The
official plugin directory contains over forty thousand plugins, covering most of the areas
required in application development. Therefore, WordPress becomes one of the top
solutions for rapid application development with existing plugins. This book will act as a
comprehensive resource for building web applications with this amazing framework.
## Instructions and Navigation
All of the code is organized into folders. Each folder starts with a number followed by the application name. For example, Chapter02.



The code will look like the following:
```
add_action('pre_get_posts', 'search_restrictions');
 function search_restrictions($query) {
 if($query->is_search && $query->is_main_query() && !is_admin()){
 $search_blocked_ids = array('24','100');
 $search_allowed_types = array('wpwaf_topic','wpwaf_forum');
 $query->set('post__not_in', $search_blocked_ids );
 $query->set('post_type', $search_allowed_types );
 }
 return $query;
 }
```

Technically, you need a computer, browser, and an Internet connection with the following working environment:
The Apache web server
PHP Version 5.4 or higher
WordPress Version 4.7.2
MySQL Version 5.6 or higher
Once you have the preceding environment, you can download the Responsive theme from http://wordpress. org/themes/responsiveand activate it from the Themes section. Finally, you can activate the plugin developed for this book to get things started. Refer to Appendix A, Configurations, Tools, and Resources, for the application setup guide, required software, and plugins.

## Related Products
* [WordPress Web Application Development - Second Edition](https://www.packtpub.com/application-development/wordpress-web-application-development-second-edition?utm_source=github&utm_medium=repository&utm_campaign=9781782174394)

* [WordPress Web Application Development](https://www.packtpub.com/web-development/wordpress-web-application-development?utm_source=github&utm_medium=repository&utm_campaign=9781783280759)

* [WordPress Mobile Web Development: Beginner's Guide](https://www.packtpub.com/web-development/wordpress-mobile-web-development-beginners-guide?utm_source=github&utm_medium=repository&utm_campaign=9781849515726)

### Suggestions and Feedback
[Click here](https://docs.google.com/forms/d/e/1FAIpQLSe5qwunkGf6PUvzPirPDtuy1Du5Rlzew23UBp2S-P3wB-GcwQ/viewform) if you have any feedback or suggestions.
