zend-multipage-forms
====================

zend-multipage-forms

The project's homepage is accessible here: http://www.kerstner.at/2012/01/implementing-multi-page-forms-using-the-zend-framework/


=====================
Setting Up Your VHOST
=====================

The following is a sample VHOST you might want to consider for your project.

<VirtualHost *:80>
   DocumentRoot "./zend-multipage-forms/public"
   ServerName test.local

   # This should be omitted in the production environment
   SetEnv APPLICATION_ENV development

   <Directory "./zend-multipage-forms/public">
       Options Indexes MultiViews FollowSymLinks
       AllowOverride All
       Order allow,deny
       Allow from all
   </Directory>

</VirtualHost>
