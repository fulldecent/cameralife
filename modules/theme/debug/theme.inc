<?php
#TODO: more alt = basename(img)
  class DebugTheme extends ThemeModule
  {
    function DebugTheme() 
    {
//TODO better way to reference here?!
      $this->cssURL = "$base_url/modules/theme/debug/theme.css";
    }

    /**
    *  AssetURL
    *
    *  Returns a URL to the asset described by NAME.
    *
    *  @param $name - string
    *
    *  @return a URL
    */
    function AssetURL($name) 
    {
      return "$base_url/modules/theme/debug/images/image.jpg";
    }

    /**
    *  TitleBar
    *
    *  This should probabily be on the top of each page.
    *
    *  @param $title - string - the title name of the page
    *  @param $image - string - URL of an image to use (optional)
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
    function TitleBar($title, $image=FALSE, $extra_info=FALSE, $menu=FALSE) 
    {
      echo "<table width=\"100%\" align=center>\n";
      echo "  <tr>\n";
      if ($image)
        echo "    <td width=96><img src=\"$image\" alt=\"Logo\">\n";
    
      echo "    <td><font class=\"titlebar\">$title</font>\n";
      if ($extra_info)
        echo "    <font class=\"titlebar-caption\">$extra_info</font>\n";
    
      if ($menu)
      {
        echo "    <td align=left width=\"5%\" nowrap=\"nowrap\">\n";
        echo '<ul class="titlebar-menu">';
        foreach ($menu as $link)
        {
          if ($link["img"])
            echo "<li>";
          else
            echo "<li>";
            echo "<a href=\"".$link["href"]."\"> ".$link["name"]."</a>\n";
        }
        echo '</ul>';
      }
      echo "</table>";
    }

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
#TODO: consider not passing a database object here
    function Grid($query) 
    {
      echo "  <table width='100%' class='grid' align=center cellspacing=3 cellpadding=3>\n";
      $i=0;
    
      while ($thumb = $query->FetchAssoc())
      {
        if ($i%4==0) echo "    <tr>\n";
        $i++;
    
        echo "      <td align='center' width='25%'>".
            "<a href=\"".$thumb["href"]."\">".
            "<img alt=\"thumb\" src=\"".$thumb["tn"]."\" ".
            "width=".$thumb['tn_width']." height=".$thumb['tn_height'].">".
            "</a><br>".$thumb['description'];
        if ($thumb["comment"])
          echo " <i><font size='-2'>(".$thumb['comment'].")</font></i>";
        echo "\n";
      }
      echo "  </table>\n";
    }

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
    function Section($name, $href=FALSE, $image=FALSE)
    {
      echo "  <div class=\"section\">\n";
      if ($href)  echo "    <a href=\"$href\">\n";
      if ($image) echo "    <img alt=\"".basename($image)."\" align=\"middle\" src=\"$image\">\n";
      echo "    $name\n";
      if ($href)  echo "    </a>\n";
      echo "  </div>\n";
    }

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
    function MultiSection($sections, $http_get = FALSE)
    {
      global $_GET;
      
      echo "<ul id=\"multisection\">\n";
    
      foreach($sections as $section)
      {
        echo '<li>';
        echo "<a";
        if ($_GET['page'] == $section['page_name'])    
          echo ' id="current"';
        echo " href=\"".$_SERVER['PHP_SELF']."&#63;page=".$section['page_name'];
        if ($http_get)
          foreach($http_get as $var => $val)
            echo "&amp;$var=$val";
        echo "\">";
        #if ($section['image'])
        #  echo "<img alt=\"".basename($section['image'])."\" align=\"middle\" src=\"".$section['image']."\"> ";
        echo $section['name'];
        echo "</a>\n";
      }
      echo "</ul>\n";
    }

    /**
    *  PageSelector
    *
    *  Creates a graphical page selector. Each page is designated by its "start"
    *  element. For an example of this mechanism, search for something on google and
    *  look at the href's of the links "G" "o" "o" "o" "o" "l" "e" on the bottom. This
    *  creates a link to the page it was called from, with HTTP GET start => the first
    *  element of the selected page. Optionally, you can pass additional HTTP GET variables
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
    function PageSelector($start, $total, $per_page, $param = FALSE)
    {
      global $theme;
    
      if ($total > $per_page) // Refuse to only show one page
      {
        $first=max($start-2*$per_page,0);
        $last=min($first+4*$per_page,intval(($total-1)/$per_page)*$per_page);
        $first=max($last-4*$per_page,0);
        if ($last==0) $last=1;
    
        echo '<div class="pageselector">';
        if ($first != $start)
          echo "<a class='nextprev' href=\"".$_SERVER["PHP_SELF"]."&#63;$param&amp;start=".($start-$per_page)."\">";
        else
          echo "<span class='nextprev'>";
        echo '&#171; Previous';
        if ($first != $start)
          echo "</a>";
        else
          echo "</span>";

        for ($i=$first; $i<=$last; $i+=$per_page)
        {
          if ($i==$start)
          {
          echo "<span class='current'>".($i/$per_page+1)."</span> ";
          }
          else
          {
            echo "<a href=\"".$_SERVER["PHP_SELF"]."&#63;$param&amp;start=$i\">";
            echo ($i/$per_page+1)."</a> ";
          }

          echo '</dib>';
        }

        if ($last != $start)
          echo "<a class='nextprev' href=\"".$_SERVER["PHP_SELF"]."&#63;$param&amp;start=".($start+$per_page)."\">";
        else
          echo "<span class='nextprev'>";
        echo 'Next &#187;';
        if ($last != $start)
          echo "</a>";
        else
          echo "</span>";
      }
    }
  }
?>
