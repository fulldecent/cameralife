<?php
/**
 *Theme name - Light
 *@link http://fdcl.sourceforge.net
 *@version 2.6.3b5
 *@author Will Entriken<cameralife@phor.net>
 *@access public
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@todo make this HTML valid
 */

/** Implements a ThemeModule
*/
  class LightTheme extends ThemeModule
  {
    function LightTheme() 
    {
      $this->name = 'Light';

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
      echo ">\n";
    }

    function Header($title, $image=FALSE, $extrahead='', $onload='', $extratitle='')
    {
      global $cameralife;

      $myfolder = new Folder ('upload/'.$cameralife->Security->GetName());
      $myfoldericon = $myfolder->GetIcon();

      $body = $onload ? "<body onLoad=\"$onload\">" : '<body>';
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<!--
  Welcome to <?= $cameralife->GetPref('sitename') ?>!
  This site is maintained by: <?= $cameralife->GetPref('owner_email') ?>

  This site is powered by Camera Life version <?= $cameralife->version ?> by Will Entriken "Full Decent",
  available at: http://fdcl.sourceforge.net
-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?= $title ?></title>
<link href="<?= $cameralife->base_url."/modules/theme/".(basename(dirname(__FILE__)))."/style.css" ?>" rel="stylesheet" type="text/css" />
<?= $extrahead  ?>
</head>
<?= $body ?>
  <!-- header -->
  <div id="header">
    <div id="menu">
       <div id="menu_list">
        <a href="<?= $cameralife->base_url ?>/index.php"><?= $cameralife->GetPref('siteabbr') ?></a>
        <img src="<?= $cameralife->base_url."/modules/theme/".(basename(dirname(__FILE__))) ?>/images/splitter.gif" class="splitter" alt="" />
        <a href="<?= $cameralife->base_url ?>/stats.php">Stats</a>
        <img src="<?= $cameralife->base_url."/modules/theme/".(basename(dirname(__FILE__))) ?>/images/splitter.gif" class="splitter" alt="" />
<?php

  if ($cameralife->Security->GetName())
  {

    echo '        <a href="'.$cameralife->base_url.'/logout.php">Logout '.$cameralife->Security->GetName().'</a>';
    echo '        <img src="'.$cameralife->base_url."/modules/theme/".(basename(dirname(__FILE__))).'/images/splitter.gif" class="splitter" alt="" />';
    echo '        <a href="'.$myfoldericon['href'].'">My Uploads</a>';
    echo '        <img src="'.$cameralife->base_url."/modules/theme/".(basename(dirname(__FILE__))).'/images/splitter.gif" class="splitter" alt="" />';
    echo '        <a href="'.$cameralife->base_url.'/admin/index.php">Administer</a>';
#    echo '        <img src="images/splitter.gif" class="splitter" alt="" />';
#    echo '        <a href="admin/appearance.php">Customize this page</a>';
  }
  else
  { 
    echo '        <a href="'.$cameralife->base_url.'/login.php">Login</a>';
    echo '        <img src="'.$cameralife->base_url."/modules/theme/".(basename(dirname(__FILE__))).'/images/splitter.gif" class="splitter" alt="" />';
    echo '        <a href="http://fdcl.sourceforge.net/">Powered by Camera Life</a>';
  }

echo '
        </div>
     </div>
  </div>
  <div id="logo">';

echo '    <div id="slogan">';
      if (isset($image)) $this->Image($image);
echo '    <strong id="title">'.$title.'</strong>';
      if (isset($extratitle))
        echo "<br />".$extratitle;
echo '      </div>
      <div id="logo_text">
        <a href="#">Search</a>
        <br/><br/>
        Type here to search by photo description, keywords, albums and more
        <form action="'.$cameralife->base_url.'/search.php" method="get" style="margin: 1em 0">
          <input name="q" value="'.(isset($_GET['q'])?$_GET['q']:'').'" size="20" type="text">
          <input value="search" type="submit">
        </form>
      </div>
    </div>
  <!--end header -->
  <!-- main -->
  <div id="main">
';
    }

    function Footer()
    {
?>
          </div>
    </div>
    <!-- end main -->
    <!-- footer -->
    <div id="footer">
    <div id="left_footer"></div>
    <div id="right_footer">

<!-- Please do not change or delete this link. Read the license! Thanks. :-) -->
Design by <a href="http://www.realitysoftware.ca" title="Website Design"><strong>Reality Software</strong></a>

    </div>
</div>
    <!-- end footer -->
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
    function MultiSection($sections, $base_url, $conjunction='and')
    {
      global $_GET;
      $sectionhtml = array();
      $finalhtml = '';

      foreach($sections as $section)
      {
        $html = "<a";
        if ($_GET['page'] == $section['page_name'])
          $html .= ' class="current"';
        $html .= " href=\"".AddParam($base_url, 'page', $section['page_name'])."\">";
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
