<?php
/**
 * Theme name - Light
 * @link http://fdcl.sourceforge.net
 * @version 
 * @author Will Entriken<cameralife@phor.net>
 * @access public
 * @copyright Copyright (c) 2001-2009 Will Entriken
 * @todo make this HTML valid
 */

  class BootstrapTheme extends ThemeModule
  {
    var $menu;
  
    function BootstrapTheme() 
    {
      $this->name = 'Bootstrap';

      $this->preferences[] = array('name'=>'main_thumbnails', 'desc'=>'Show random, most popular, latest photos...', 'type'=>array('1'=>'Show N thumbnails','0'=>'No'), 'default'=>'1');
      $this->preferences[] = array('name'=>'main_thumbnails_n', 'desc'=>'... N=', 'type'=>'number', 'default'=>'4');
      $this->preferences[] = array('name'=>'main_topics', 'desc'=>'Photo album topics', 'type'=>array('0'=>"Don't show",'1'=>'Show all topics','2'=>"Show all topics, and N albums each"), 'default'=>'2');
      $this->preferences[] = array('name'=>'main_topics_n', 'desc'=>'... N=', 'type'=>'number', 'default'=>'3');
      $this->preferences[] = array('name'=>'main_folders', 'desc'=>'Folders on main page', 'type'=>array('0'=>"Don't show",'1'=>'Show N random folders, M photos each'), 'default'=>'1');
      $this->preferences[] = array('name'=>'main_folders_n', 'desc'=>'... N=', 'type'=>'number', 'default'=>'5');
      $this->preferences[] = array('name'=>'main_folders_m', 'desc'=>'... M=', 'type'=>'number', 'default'=>'5');
    }

    function ShowPage($name, $object=NULL)
    {
      global $cameralife, $_SERVER, $_GET, $_POST;
      $objectname = strtolower($name);
      $$objectname = $object;
      require dirname(__FILE__) . '/' . strtolower($name) . '.inc';
    }

    function ImageURL($name) 
    {
//todo error checking
      global $cameralife;

      return $cameralife->base_url."/modules/iconset/".$cameralife->GetPref('iconset')."/$name";
    }

    function Image($name, $extra=array()) 
    {
      if (preg_match('/src="([^"]*)"/i', $name)) # HTML IMG
      {
        $image['src'] = $regs[1];
        $image['alt'] = basename($image['src']);
      }
      else if (preg_match('#\.|/#', $name)) # Image URL
      {
        $image['src'] = $name;
        $image['alt'] = basename($image['src']);
      }
      else # Named asset, like 'small-album'
      {
        $image['src'] = $this->ImageURL($name);
        $image['alt'] = $name;
      }

      $image = array_merge($image, $extra);

      echo "<img";
      foreach ($image as $attr => $val)
        if ($val)  
          echo " $attr=\"$val\"";
      echo " />\n";
    }

    /**
     * @access public
     * @param string $title In <title> tag
     * @param string $menu Block of HTML to show on side of page in a well 
     * @param string $extrahead (default: '')
     * @param string $onload (default: '')
     * @return void
     */
    function Header($title, $menu='', $extrahead='', $onload='')
    {
      global $cameralife;
      $this->menu = $menu;

      $myfolder = new Folder ('upload/'.$cameralife->Security->GetName());
      $myfoldericon = $myfolder->GetIcon();

      $body = $onload ? "<body onLoad=\"$onload\">" : '<body>';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="generator" content="Camera Life version <?= $cameralife->version ?>">
    <meta name="author" content="<?= $cameralife->GetPref('owner_email') ?>">

    <!-- Le styles -->
    <link href="<?= $cameralife->base_url."/modules/theme/".(basename(dirname(__FILE__))) ?>/css-extra/custom.css" rel="stylesheet">
    <link href="<?= $cameralife->base_url."/modules/theme/".(basename(dirname(__FILE__))) ?>/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    <link href="<?= $cameralife->base_url."/modules/theme/".(basename(dirname(__FILE__))) ?>/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>

  <?= $body ?>
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="<?= $cameralife->base_url ?>"><?= $cameralife->GetPref('sitename') ?></a>
          <div class="btn-group pull-right">
            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
              <i class="icon-user"></i>
              <img src="http://facebook.com/favicon.ico" height=16 width=16>
              <img src="http://google.com/favicon.ico" height=16 width=16>
              <img src="http://yahoo.com/favicon.ico" height=16 width=16> Login
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="#">Profile</a></li>
              <li class="divider"></li>
              <li><a href="#">Sign Out</a></li>
            </ul>
          </div>
          <a class="btn pull-right" href="#">
            <i class="icon-plus"></i>
              Upload Photos
          </a>
          <a class="btn pull-right" href="#">
            <i class="icon-star"></i>
              Get email updates
          </a>
          <div class="nav-collapse">
            <ul class="nav">
              <li><form class="navbar-search" action="<?= $cameralife->base_url ?>/search.php" method="get">
                  <input type="text" class="search-query span3" placeholder="Search" name="q" />
                  </form>
              </li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    <div class="container-fluid">
<?php
      if ($menu) {
?>
      <div class="row-fluid">
        <div class="span3">
          <div class="well sidebar-nav">
            <?= $menu ?>
          </div>
        </div>
        <div class="span9">
<?php
      }
    }

    function Footer()
    {
      global $cameralife;
      if ($menu) {
?>
        </div><!--/span-->
      </div><!--/row-->
<?php
      }
?>
      <hr>

      <footer>
        <p>
          <a href="<?= $cameralife->base_url ?>"><i class="icon-globe"></i> <?= $cameralife->GetPref('sitename') ?></a>
          <a href="mailto:<?= $cameralife->GetPref('owner_email') ?>"><i class="icon-envelope"></i> Contact site owner</a>
          <a href="<?= $cameralife->base_url ?>/stats.php"><i class="icon-signal"></i> Site stats</a>
        </p>
      </footer>

    </div><!--/.fluid-container-->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="../assets/js/jquery.js"></script>
    <script src="../assets/js/bootstrap-transition.js"></script>
    <script src="../assets/js/bootstrap-alert.js"></script>
    <script src="../assets/js/bootstrap-modal.js"></script>
    <script src="../assets/js/bootstrap-dropdown.js"></script>
    <script src="../assets/js/bootstrap-scrollspy.js"></script>
    <script src="../assets/js/bootstrap-tab.js"></script>
    <script src="../assets/js/bootstrap-tooltip.js"></script>
    <script src="../assets/js/bootstrap-popover.js"></script>
    <script src="../assets/js/bootstrap-button.js"></script>
    <script src="../assets/js/bootstrap-collapse.js"></script>
    <script src="../assets/js/bootstrap-carousel.js"></script>
    <script src="../assets/js/bootstrap-typeahead.js"></script>

  </body>
</html>
<?php
    }

    // query is an array of Icons, or and array of classes that have Icons
    function Grid($query) 
    {
      echo "  <table width='100%' class='grid' align=center cellspacing=3 cellpadding=3>\n";
      $i=0;
    
      foreach ($query as $thumb)
      {
        if (is_array($thumb))
          $icon = $thumb;
        else
          $icon = $thumb->GetIcon();

        if ($i%4==0) echo "    <tr>\n";
          $i++;

        echo "      <td align='center' width='25%' class='border'>".
             "<a href=\"".$icon["href"]."\">\n";
        $imageattrs = array('alt'=>'thumb', 'width'=>$icon['width'], 'height'=>$icon['height']);
        $this->Image($icon["image"], $imageattrs);
        echo "</a><br>".$icon['name'];
        if ($icon['context'])
          echo " <i><font size='-2'>(".$icon['context'].")</font></i>";
        echo "</td>\n";
      }
      echo "  </table>\n";
    }

    function Section($name, $href=FALSE, $image=FALSE)
    {
      echo "  <div class=\"section\">\n";
      if ($href)  echo "    <a href=\"$href\">\n";
      if ($image) $this->Image($image, array('align'=>'middle'));
      echo "    $name\n";
      if ($href)  echo "    </a>\n";
      echo "  </div>\n";
    }

    /// Taken an Icon to make base url
    function MultiSection($sections, $base_url, $conjunction='and', $query=NULL)
    {
      global $_GET;
      $sectionhtml = array();
      $finalhtml = '';

      foreach($sections as $section)
      {
        $html = "<a";
        if ($_GET['page'] == $section['page_name'])
          $html .= ' class="current"';
        $url = $base_url;
        $url = AddParam($url, 'page', $section['page_name']);
        if (isset($query))
          foreach($query as $p => $v)
            $url = AddParam($url, $p, $v);
        $html .= " href=\"$url\">";
        #if ($section['image']) $this->Image($section['image'], array('align'=>'middle'));
        $html .= $section['name'];
        $html .= "</a>";
        $sectionhtml[] = $html;
      }

      $last = array_pop($sectionhtml);
      if ($conjunction == 'and' || $conjunction == 'or')
        $finalhtml = implode(', ', $sectionhtml);
      else
        $finalhtml = implode(" $conjunction ", $sectionhtml);
      echo "$finalhtml $conjunction $last";
    }

    function PageSelector($start, $total, $per_page, $url)
    {
      global $theme;
    
      if ($total > $per_page) // Refuse to only show one page
      {
        echo '<div class="pageselector">';

        if ($start==-1)
        {
          echo "<span class='current'>Randomly showing <b>$per_page</b> of <b>$total</b></span> ";
          echo "<a class='nextprev' href=\"$url\">More &#187;</a>";
        }
        else
        {
          $first=max($start-2*$per_page,0);
          $last=min($first+4*$per_page,intval(($total-1)/$per_page)*$per_page);
          $first=max($last-4*$per_page,0);
          if ($last==0) $last=1;
      
          if ($first != $start)
            echo "<a class='nextprev' href=\"".AddParam($url,'start',$start-$per_page)."\">";
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
              echo "<a href=\"".AddParam($url,'start',"$i")."\">";
              echo ($i/$per_page+1)."</a> ";
            }
          }
  
          if ($last != $start)
            echo "<a class='nextprev' href=\"".AddParam($url,'start',$start+$per_page)."\">";
          else
            echo "<span class='nextprev'>";
          echo 'Next &#187;';
          if ($last != $start)
            echo "</a>";
          else
            echo "</span>\n";
        }
        echo "</div>\n";
      }
    }
  }
?>
