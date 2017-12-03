Project Goals
=============

1.  Release often

2.  Improve the storytelling experience

3.  Have fun

Feedback, hints and a couple patches are always coming, which is great. Code is highly modular, and is maybe even a model for people learning MVC, CMS, authentication, auditable systems.


How does this code work?
========================

This project follows the Model-View-Controller philosophy as well as PSR-1, -2, and -4 interoperability conventions. A typical page view is described below and separates concerns into Models, Views, and Controllers.

1.  A user loads <http://camera.phor.net/cameralife/photo/20739>

2.  ModRewrite in `.htaccess` translates to <http://camera.phor.net/cameralife/index.php?page=photo&id=20739>

3.  `index.php` loads the site configuration `config/config.php` and calls `Controller::handleRequest()`

4.  The `Controller` base class instantiates and delegates the request to `PhotoController` based on the URL

5.  `PhotoController` accesses the `Photo` model and other models to collect needed information

6.  `PhotoController` instantiates and calls `render()` on `PhotoView`, passing in the models

7.  `PhotoView` reads data from the models to write HTML to the browser

Complete API documentation is at <http://camera.phor.net/docs/>


Release Process
===============

When Changing the Database Schema
---------------------------------

1.  Increment version number in module-config.inc, main.inc, and setup/upgrade/upgrade.php

2.  Update db schema at setup/index2.php

3.  Update install.mysql

4.  Write an upgrader in setup/upgrade

Release process
---------------

-   Make a new flyover install video

-   edit composer.json

-   Update screenshots in README.md

-   Check https://packagist.org/packages/fulldecent/cameralife

-   Complete automated release below

    VERSION='2.7.0a6' # <-- EDIT THIS
    sed -i "/CAMERALIFE_VERSION/s/, '.*'/, '$VERSION'/" index.php
    git commit -am "Releasing version $VERSION"
    git tag -a "$VERSION" -m "Version $VERSION"
    git push --tags
    phpdoc --directory . --target ../docs/ --title 'Camera Life Developer Manual' --ignore 'vendor/'

 
