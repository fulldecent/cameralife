<?php
/**
 * Theme name - Bootstrap
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
      $this->about = 'Sends all pages a marked-up Twitter Bootstrap content and style';

      $this->preferences[] = array('name'=>'main_thumbnails', 'desc'=>'Show random, most popular, latest photos...', 'type'=>array('1'=>'Show N thumbnails','0'=>'No'), 'default'=>'1');
      $this->preferences[] = array('name'=>'main_thumbnails_n', 'desc'=>'... N=', 'type'=>'number', 'default'=>'4');
      $this->preferences[] = array('name'=>'main_topics', 'desc'=>'Photo album topics', 'type'=>array('0'=>"Don't show",'1'=>'Show all topics','2'=>"Show all topics, and N albums each"), 'default'=>'2');
      $this->preferences[] = array('name'=>'main_topics_n', 'desc'=>'... N=', 'type'=>'number', 'default'=>'3');
      $this->preferences[] = array('name'=>'main_folders', 'desc'=>'Folders on main page', 'type'=>array('0'=>"Don't show",'1'=>'Show N random folders, M photos each'), 'default'=>'1');
      $this->preferences[] = array('name'=>'main_folders_n', 'desc'=>'... N=', 'type'=>'number', 'default'=>'5');
      $this->preferences[] = array('name'=>'main_folders_m', 'desc'=>'... M=', 'type'=>'number', 'default'=>'5');
      $this->preferences[] = array('name'=>'analytics', 'desc'=>'Tracking code for Google Analytics (optional)', 'type'=>'string', 'default'=>'');
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

      $myfolder = new Folder ('upload/'.$cameralife->Security->GetName().'/');
      $myfoldericon = $myfolder->GetIcon();

      $body = $onload ? "<body onLoad=\"$onload\">" : '<body>';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?= htmlentities($title, NULL, "UTF-8") ?></title> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="generator" content="Camera Life version <?= $cameralife->version ?>">
    <meta name="author" content="<?= $cameralife->GetPref('owner_email') ?>">
    <?= $extrahead ?>

    <!-- Le styles -->
    <link href="<?= $cameralife->base_url ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?= $cameralife->base_url."/modules/theme/".(basename(dirname(__FILE__))) ?>/css-extra/custom.css" rel="stylesheet">
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <?= $body ?>
  
    <nav class="navbar navbar-default navbar-static-top" role="navigation">
      <div class="container">
      <a class="navbar-brand" href="<?= $cameralife->base_url ?>"><?= $cameralife->GetPref('sitename') ?></a>
      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse">
        <form class="navbar-form navbar-left" action="<?= $cameralife->base_url ?>/search.php" method="get">
          <input type="text" class="form-control" placeholder="Search" name="q" />
        </form>
        <ul class="nav navbar-nav navbar-right">
        
<?php
if ($cameralife->Security->GetName()) {
 $gravitarHTML = htmlentities("http://www.gravatar.com/avatar/".md5($cameralife->Security->GetName())."?s=16&d=identicon");

?>  
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><img src="<?= $gravitarHTML ?>" height=16 width=16> <?= $cameralife->Security->GetName() ?> <b class="caret"></b></a>  
            <ul class="dropdown-menu">
              <li><a href="<?= $myfoldericon['href'] ?>">My Uploads</a></li>
              <li><a href="<?= $cameralife->base_url.'/admin/index.php' ?>">Administer</a></li>
              <li><a href="<?= $cameralife->base_url.'/admin/index.php' ?>">Customize this page</a></li>
              <li class="divider"></li>
              <li><a href="<?= $cameralife->base_url.'/logout.php' ?>">Sign Out</a></li>
            </ul>
          </li>
<?php
} else {
?>
          <a class="btn btn-default navbar-btn narbar-right" href="<?= $cameralife->Security->LoginURL() ?>">
            <i class="icon-user"></i>
            <img alt="facebook" src="http://facebook.com/favicon.ico" height=16 width=16>
            <img alt="google" src="http://google.com/favicon.ico" height=16 width=16>
            <img alt="yahoo" src="http://yahoo.com/favicon.ico" height=16 width=16>
            Login / Free account
          </a>
<?php
}
?>
        </ul>
<?php
if (isset($_GET['path'])) {
?>
        <a class="btn btn-default navbar-btn navbar-right" href="<?= $cameralife->base_url ?>/upload.php?path=<?= htmlentities($_GET['path'])?>">
<?php
} else {
?>
        <a class="btn btn-default navbar-btn navbar-right" href="<?= $cameralife->base_url ?>/upload.php">
<?php
}
?>
          <i class="icon-plus"></i>
            Upload
        </a>
        <a class="btn btn-default navbar-btn navbar-right" href="#">
          <i class="icon-star"></i>
            Get email updates
        </a>
      </div><!-- /.navbar-collapse -->
      </div>
    </nav>  
    
    <div class="container">
<?php
      if ($this->menu) {
?>
      <div class="row-fluid">
        <div class="span9">
<?php
      }
    }

    function Footer()
    {
      global $cameralife;
      if ($this->menu) {
?>
        </div><!--/span-->
        <div class="span3">
          <div class="well sidebar-nav">
            <?= $this->menu ?>
          </div>
        </div>
      </div><!--/row-->
<?php
      }
?>
      <hr>

      <footer>
        <p>
          <a href="mailto:<?= $cameralife->GetPref('owner_email') ?>"><i class="icon-envelope"></i> Contact site owner</a>
          &nbsp;
          <a href="<?= $cameralife->base_url ?>/stats.php"><i class="icon-signal"></i> Site stats</a>
          &nbsp;
          <a href="http://fulldecent.github.com/cameralife"><i class="icon-globe"></i> Built with Camera Life</a>
        </p>
      </footer>

    </div><!--/.fluid-container-->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
    <script src="<?= $cameralife->base_url ?>/bootstrap/js/bootstrap.min.js"></script>
<?php
      if ($cameralife->Theme->GetPref('analytics')) {
?>
<!--TRACKING CODE-->
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?= $cameralife->Theme->GetPref('analytics') ?>']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<!--END TRACKING CODE-->
<?php
      }
?>
  </body>
</html>
<?php
    }

    // Query is an array of Icons or classes that have Icons
    function Grid($query) 
    {
      echo '<div>'.PHP_EOL;
      foreach ($query as $thumb) {
        if (is_array($thumb))
          $icon = $thumb;
        else
          $icon = $thumb->GetIcon();

        echo '<div class="l1" style="margin-bottom:1em">'.PHP_EOL;
        echo '<a class="l2" href="'.$icon["href"].'">';
        if (isset($icon['width']) && isset($icon['height'])) 
            $imageattrs = array('alt'=>'thumb', 'width'=>$icon['width'], 'height'=>$icon['height'], 'class'=>'l3');
        else
            $imageattrs = array('alt'=>'thumb', 'class'=>'l3');
        $this->Image($icon["image"], $imageattrs);
        if (isset($icon['width']) && isset($icon['height'])) 
            echo '<div class="l4" style="width:'.$icon['width'].'px">';
        else
            echo '<div class="l4">';
        if ($icon['name'] != 'unnamed')
            echo $icon['name'];
        echo '</div>';
        echo '</a>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
      }
      echo '</div>'.PHP_EOL;
    }

/*
    function Section($name, $href=FALSE, $image=FALSE)
    {
      echo "  <div class=\"section\">\n";
      if ($href)  echo "    <a href=\"$href\">\n";
      if ($image) $this->Image($image, array('align'=>'middle'));
      echo "    $name\n";
      if ($href)  echo "    </a>\n";
      echo "  </div>\n";
    }
*/

    /// Taken an Icon to make base url
    function MultiSection($sections, $base_url, $conjunction='and', $query=NULL)
    {
      global $_GET;
      $sectionhtml = array();
      $finalhtml = '';

      echo '<ul class="nav nav-tabs">'.PHP_EOL;
      foreach($sections as $section) {
        if ($_GET['page'] == $section['page_name'])
          echo "<li class=\"active\">";
        else
          echo "<li>";
      
        $url = $base_url;
        $url = AddParam($url, 'page', $section['page_name']);
        if (isset($query))
          foreach($query as $p => $v)
            $url = AddParam($url, $p, $v);
        echo "<a href=\"$url\">";
        echo $section['name'];
        echo "</a>";
        echo "</li>";
      }
      
      echo "</ul>";
    }

    function PageSelector($start, $total, $per_page, $url)
    {
      global $theme;
    
      if ($total > $per_page) // Refuse to only show one page
      {
        echo '<ul class="pagination">';

        if ($start==-1) {
          echo "<span class='current'>Randomly showing <b>$per_page</b> of <b>$total</b></span> ";
          echo "<a class='nextprev' href=\"$url\">More &#187;</a>";
        } else {
          $first=max($start-2*$per_page,0);
          $last=min($first+4*$per_page,intval(($total-1)/$per_page)*$per_page);
          $first=max($last-4*$per_page,0);
          if ($last==0) $last=1;
      
          if ($first != $start)
            echo "<li><a href=\"".AddParam($url,'start',$start-$per_page)."\">&laquo;</a></li>";
          else
            echo "<li><a class=\"disabled\" href=\"#\">&laquo;</a></li>";
  
          for ($i=$first; $i<=$last; $i+=$per_page) {
            if ($i==$start) 
                echo "<li class=\"active\"><a href=\"".AddParam($url,'start',$i)."\">".($i/$per_page+1)."</a></li>";
            else
                echo "<li><a href=\"".AddParam($url,'start',$i)."\">".($i/$per_page+1)."</a></li>";
          }
  
          if ($last != $start)
            echo "<li><a href=\"".AddParam($url,'start',$start+$per_page)."\">&raquo;</a></li>";
          else 
            echo "<li><a class=\"disabled\" href=\"#\">&laquo;</a></li>";
        }
        echo "</ul>\n";
      }
    }
  }
?>
