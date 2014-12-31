Project Goals
=============

1.  Release often

2.  Integrate with Content Management Systems

3.  Integrate with other great services

Feedback, hints and a couple patches are always coming, which is great. Code is highly modular, and patches are always coming from people integrating into their own site.

How does this code work?
========================

This project follows the Model-View-Controller philosophy as well as PSR-1, -2, and -4 interoperability conventions. A typical page view is described below and separates concerns into Models, Views, and Controllers.

1.  A user loads <http://camera.phor.net/cameralife/photo/20739>

2.  ModRewrite in `.htaccess` translates to <http://camera.phor.net/cameralife/index.php?page=photo&id=20739>

3.  `index.php` loads the site configuration `config.php` and calls `Controller::handleRequest()`

4.  The `Controller` base class instantiates and delegates the request to `PhotoController` based on the URL

5.  `PhotoController` accessess the `Photo` model and other models to collect needed information

6.  `PhotoController` instantiates and calls `render()` on `PhotoView`, passing in the models

7.  `PhotoView` reads data from the models to write HTML to the browser

Complete API documentation is at <http://camera.phor.net/docs/>

Release Process
===============

Regression testing (move all this to Continuous Integration)
------------------------------------------------------------

-   Validate OpenSearch: <http://feedvalidator.org/check.cgi?url=http%3A%2F%2Fcamera.phor.net%2Fcameralife%2Fopensearch.xml.php>

-   Validate OpenGraph: https://developers.facebook.com/tools/debug/og/object?q=camera.phor.net

-   Validate RSS: http://validator.w3.org/feed/check.cgi?url=camera.phor.net/cameralife/rss.php%3Fq%3Dwill

-   Try to upload a non-image

-   `git grep -i Deprecated` # create issues for these

-   `git grep -i TODO` # create issues for these and remove from code?

-   static code analysis in PHPStorm

-   http://html5.validator.nu/?doc=http%3A%2F%2Fcamera.phor.net%2Fcameralife%2F

-   http://html5.validator.nu/?doc=http%3A%2F%2Fcamera.phor.net%2Fcameralife%2Fphotos%2F15608

-   http://html5.validator.nu/?doc=http%3A%2F%2Fcamera.phor.net%2Fcameralife%2Ftopics%2FPeople

-   http://html5.validator.nu/?doc=http%3A%2F%2Fcamera.phor.net%2Fcameralife%2Falbums%2F168

-   http://html5.validator.nu/?doc=http%3A%2F%2Fcamera.phor.net%2Fcameralife%2Ffolders%2F2012%2F2012-06%2520Pool%2520party%2F

-   `git grep GET` and remove these

-   `git grep PHP_SELF` and remove these

-   `git grep Database -- sources/Controllers`

-   `git grep Database -- sources/Views`

-   Validate OpenID login

-   XSSme SQLinjectme wapiti zaproxy

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

 
