<?php
  /*
  *  ==A theme must support these named images==
  *
  *  These go on the header of the page, should be the same size, about 64x64
  *  {main, photo, topic, album, admin, folder, search, login, stats}
  *  Also include a small-* of these for links to that page, about 30x30
  *
  *  And a special one: icon-folder, should be the size of a thumbnail
  */

  class ThemeModule extends CameraLifeModule
  {
    function ThemeModule() {}

    /**
    *  Image
    *
    *  Produces HTML necessary to show a named image from the theme's image assets.
    *  You may pass optional HTML attributes, which will be included in the IMG tag.
    *
    *  @param $name - string
    *  @param $extra - named array (optional)
    *
    *  @return none
    */
    function Image($name, $extra=array()) {}

    /**
    *  cssURL
    *
    *  Returns a URL to a style sheet to be applied, or NULL, for none
    *
    *  @param $name - string
    *
    *  @return a URL
    */
    function cssURL() {}

    /**
    *  TitleBar
    *
    *  This should probabily be on the top of each page.
    *
    *  @param $title - string - the title name of the page
    *  @param $image - string - URL of an image to use (optional), or HTML for the image
    *  @param $extra_info - string - a connotation about the title (optional)
    *  @param $menu - array - contains links to other pages, see below (optional)
    *
    *  $menu format:
    *    $menu contains arrays of the form:
    *      ('name'=>'Link name','href'=>'URL','img'=>'Image URL')
    *    The img is optional
    *
    *  @return none
    */
    function TitleBar($title, $image=FALSE, $extra_info=FALSE, $menu=FALSE) {}

    /**
    *  Grid
    *
    *  Produces a grid of thumbnails, for various purposes.
    *
    *  @param $query - DatabaseIterator - thumbnails to display
    *
    *  QUERY format:
    *    the query needs to return cetain columns
    *    tn            The image of the thumb
    *    tn_width      Width (pixels)
    *    tn_heught     Height (pixels)
    *    href          Where the link goes
    *    description   Captions text
    *    comment       (optional) Extra parenthetical text
    *
    *  @return none
    */
    function Grid($query) {}

    /**
    *  Section
    *
    *  A basic section denotation name
    *
    *  @param $name - string - the name of the section
    *  @param $href - string - url to link to (optional)
    *  @param $image - string - an image URL to go with it (optional)
    *
    *  @return none
    */
    function Section($name, $href=FALSE, $image=FALSE) {}

    /**
    *  MultiSection
    *
    *  Like a section, denote multiple pages of a page. Each is a link to the page
    *  that called it, but with a GET variable 'page' passed. Other GET variables can be
    *  passed in $http_get.
    *
    *  @param $sections - array - contains the pages to link to (see below)
    *  @param $http_get - array - contains http get parameters to pas (optional, see below)
    *
    *  $sections format:
    *    $sections contains arrays of the form:
    *      ('name'=>'Link name','page_name'=>'Page Name','image'=>'Image URL')
    *    page_name is the http_get variable that is passed for page
    *    The img is optional
    *
    *  $http_get format:
    *    $http_get contains elements of the form:
    *      $http_get_var_name => $http_get_var_value
    *
    *  @return none
    */
    function MultiSection($sections, $http_get = FALSE) {}

    /**
    *  PageSelector
    *
    *  Creates a graphical page selector. Each page is designated by its "start"
    *  element. For an example of this mechanism, search for something on google and
    *  look at the href's of the links "G" "o" "o" "o" "o" "l" "e" on the bottom. This
    *  creates a link to the page it was called from, with HTTP GET start => the first
    *  element of the selected page. Optionally, you can pass additional HTTP GET variables
    *
    *  If START is -1, we are viewing items randomly.
    *
    *  @param $start - int - the first element on the page
    *  @param $total - int - total number of elements
    *  @param $per_page - int - number of elements per page
    *  @param $param - string - HHTP GET string (optional)
    *
    *  Example HTTP GET string format:
    *    $param = "name=People&amp;val=var";
    *
    *  @return none
    */
    function PageSelector($start, $total, $per_page, $param = FALSE) {}
  }
?>
