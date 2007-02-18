<?php
// this code need updating

/**
 *  theme.php - responsible for writing HTML to the client
 *  Will Entriken 'Full Decent' 11/06/2003
 *
 *  If modifying/forking this file, please continue to use theme.css
 *  when possible and you must accept the same paramaters (and
 *  ommissions) as these functions.
 */

/**
 *  html_title_bar
 *
 *  This should probabily be on the top of each page. I'm not calling it a
 *  header because I generally consider them as not needing parameters. :-)
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
  class GnomishTheme extends ThemeModule
  {
function html_title_bar($title, $image=FALSE, $extra_info=FALSE, $menu=FALSE)
{
  global $theme;

  echo "<table width=\"100%\" align=center>\n";
  echo "  <tr>\n";
  if ($image)
    echo "    <td width=50><img src=\"$image\">\n";

  echo "    <td><font class=\"title\">$title</font>\n";
  if ($extra_info)
    echo "    <br><font class=\"title-caption\">$extra_info</font>\n";

  if ($menu)
  {
    echo "    <td align=left width=\"10%\" nowrap=\"nowrap\">\n";

    echo '<table cellpadding=0 cellspacing=0>';
    echo '<tr><td class="title-topleft" valign=top><img src="themes/'.$theme.'/images/title-topleft">';
    echo '<td class="title-top" valign=top align=center><img src="themes/'.$theme.'/images/title-title">';
    echo '<td class="title-right" valign=top><img src="themes/'.$theme.'/images/title-topright">';

    foreach ($menu as $link)
    {
      echo '<tr><td class="title-left"><img src="themes/'.$theme.'/images/title-left">';
      echo "<td class=\"title-menu\" nowrap=nowrap><font class=\"menu-item\"><a class=\"menu-item\" href=\"".$link["href"]."\">";
      if ($link["img"])
        echo "<img align=center src=\"".$link["img"]."\"> ";
      echo $link["name"]."</a></font><br>\n";
      echo '<td class="title-right"><img src="themes/'.$theme.'/images/title-right">';
    }

    echo '<tr><td class="title-bottomleft" valign=top><img src="themes/'.$theme.'/images/title-bottomleft">';
    echo '<td class="title-bottom" valign=top align=center><img src="themes/'.$theme.'/images/title-bottom">';
    echo '<td class="title-bottomright" valign=top><img src="themes/'.$theme.'/images/title-bottomright">';
    echo '</table>';
  }
  echo "</table>";
}

/**
 *  html_grid
 *
 *  Runs a SQL query and represents the results a table with thumbnails,
 *  the query is specially formatted as specified below.
 *
 *  @param $SQL - string - a query to run, specially formated (see below)
 *
 *  SQL format:
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
function html_grid($SQL)
{
  global $photo_link;

  $result = mysql_query($SQL, $photo_link)
    or die("Error getting thumbnails: ".mysql_error());
  //echo "  <!-- This table based on SQL: $SQL -->\n";
  echo "  <table width='100%' class='grid' align=center cellspacing=3 cellpadding=3>\n";

  $i=0;

  while ($thumb = mysql_fetch_array($result))
  {
    if ($i%4==0) echo "    <tr>\n";
    $i++;

    echo "      <td class='thumbnail' align='center' width='25%'>".
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
 *  html_section
 *
 *  A basic section denotation name
 *
 *  @param $name - string - the name of the section
 *  @param $href - string - url to link to (optional)
 *  @param $image - string - an image URL to go with it (optional)
 *
 *  @return none
 */
function html_section($name, $href=FALSE, $image=FALSE)
{
  global $theme;
  global $_GET;

  echo '<table><tr valign=middle><td>';
  if ($href)  echo "<a href=\"$href\">";
  if ($image) echo "<img alt=\"".basename($image)."\" align=\"middle\" src=\"$image\"> ";
  echo "<font class=\"section\">$name</font>";
  if ($href)  echo '</a>';
  echo '</table>';
}

/**
 *  html_multi_section
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
function html_multi_section($sections, $http_get = FALSE)
{
  global $theme;

  global $_GET;
  echo "<table cellpadding=1 class=\"multi-section\" width=\"100%\">\n";
  echo "<tr valign=middle>\n";
  echo "<td class=\"multi-section-tab\" width=5><img src=\"themes/".$theme."/images/multisection-bg\">";

  foreach($sections as $section)
  {
    if ($_GET['page'] == $section['page_name'])
      echo "<td width=175 class=\"section-selected\">";
    else
      echo "<td width=175 class=\"section-unselected\">";
    echo "<a href=\"".$_SERVER['PHP_SELF']."&#63;page=".$section['page_name'];
    if ($http_get)
      foreach($http_get as $var => $val)
        echo "&amp;$var=$val";
    echo "\">";
    if ($section['image'])
      echo "<img alt=\"".basename($section['image'])."\" align=\"middle\"
                 src=\"".$section['image']."\"> ";
    echo "<font class=\"multi-section\">".$section['name']."</font>";
    echo "</a>";
  }

  echo "<td width=\"*\">&nbsp;</table>";
}

/**
 *  html_page_selector
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
function html_page_selector($start, $total, $per_page, $param = FALSE)
{
  global $theme;

  if ($total > $per_page) // Refuse to only show one page
  {
    $first=max($start-2*$per_page,0);
    $last=min($first+4*$per_page,intval(($total-1)/$per_page)*$per_page);
    $first=max($last-4*$per_page,0);
    if ($last==0) $last=1;

    echo "Go to page: ";

    for ($i=$first; $i<=$last; $i+=$per_page)
    {
      if ($i==$start)
      {
       echo "<img src=\"themes/$theme/images/small-onpage\" alt=\"page\" align=middle>";
       echo "<u><b>".($i/$per_page+1)."</b></u> ";
      }
      else
      {
        echo "<a href=\"".$_SERVER["PHP_SELF"]."&#63;$param&amp;start=$i\">";
        echo "<img src=\"themes/$theme/images/small-page\" alt=\"page\" align=middle>";
  echo ($i/$per_page+1)."</a> ";
      }
    }
  }
}
}
?>
