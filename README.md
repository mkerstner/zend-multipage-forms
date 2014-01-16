# zend-multipage-forms

This idea behind this project is to provide you with a simple set of base classes 
for using multi-page forms in the Zend Framework (ZF).

It features support for *complex multi-page forms*, built-in *breadcrumb navigation*
and a final *verification* step based on a CAPTCHA field.

Also includes functionality to directly jump to steps in your multi-page form. Furthermore,
you are able to specify custom view scripts for each step individually.

The original idea was published here: 
http://www.kerstner.at/2012/01/implementing-multi-page-forms-using-the-zend-framework/

## Requirements

This is a standard Zend Framework project. There is nothing fancy about the setup
so you should be good with the standard LAMP stack, although PHP 5.3+ is required.

## Setting Up Your VHOST

The following is a sample VHOST you might want to consider for your project.

> <VirtualHost *:80>
>   DocumentRoot "./zend-multipage-forms/public"
>   ServerName test.local
>
>   # This should be omitted in the production environment
>   SetEnv APPLICATION_ENV development
>
>   <Directory "./zend-multipage-forms/public">
>       Options Indexes MultiViews FollowSymLinks
>       AllowOverride All
>       Order allow,deny
>       Allow from all
>   </Directory>
>
> </VirtualHost>`

## Demo

Once setup you can start the demo setup by navigating to the register controller, 
e.g. http://test.local/register/
