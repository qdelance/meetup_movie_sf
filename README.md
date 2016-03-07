meetup_movie_sf
==================

Sample project for Drupal meetup in Toulouse

Symfony project setup
---------------------

New command:

    symfony new project
    
No need to use composer to download all projects.
Much quicker.
parmeters.yml if already populated (defaults to "symfony" BD with root user)
vhost must still be defined:

    <VirtualHost *:80>
            ServerName meetup_sf_partners.localdomain
            ServerAlias meetup_sf_partners
    
            ServerAdmin webmaster@localhost
            DocumentRoot /var/www/meetup_sf_partners/web
    
            <Directory /var/www/meetup_sf_partners/web>
                    AllowOverride All
            </Directory>
    
            ErrorLog ${APACHE_LOG_DIR}/meetup_sf_partners-error.log
            CustomLog ${APACHE_LOG_DIR}/meetup_sf_partners-access.log combined
    
    </VirtualHost>

We still have to deal with ACL: https://symfony.com/doc/current/book/installation.html#book-installation-permissions

This should be a success at this point:

http://meetup_sf_partners.localdomain/app_dev.php (dev env, 127.0.0.1 access only)
http://meetup_sf_partners.localdomain/ (prod env)


Creating DB
-----------

    php bin/console doctrine:database:create
    
Creating entities
-----------------

No need to create a bundle (use default AppBundle).
    
    php bin/console doctrine:generate:entity
    php bin/console doctrine:schema:update --dump-sql
    php bin/console doctrine:schema:update --force
    
Before adding links between entities manually, add support for migrations:    
    
    composer require doctrine/doctrine-migrations-bundle "^1.0"
    
Register bundle ;)    
    
Add link between Partner <=> Level in source

    php bin/console doctrine:generate:entities AppBundle/Entity/Partner
    
In case you want to use migrations (not used currently, left as an example), apply in DB
    
    php bin/console doctrine:migrations:diff
    php bin/console doctrine:migrations:migrate
    
Creating content
----------------

Adding support for fixtures

    composer require --dev doctrine/doctrine-fixtures-bundle
    
Register bundle ;)    
    
Loading sample data

    php bin/console doctrine:fixtures:load

Viewing content
----------------

Use app_dev.php in case you want to use dev environment._

http://host/movies
http://host/movie/add
http://host/movie/:id/edit
http://host/movie/:id/view
http://host/movie/:id/delete

Importing CSV
--------------

You can use list of films exported thru IMDb.
Note that you MUST save the CSV with TAB ("\t") as field separator, as "," is already used to split "genres".

When you have the CSV file, you can import it with a console Command:

    php bin/console movie:import <path_to_csv>