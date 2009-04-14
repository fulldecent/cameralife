<?php
#TODO: more alt = basename(img)


/**The module carries the iphone theme
*
*@todo more alt= basename(img)
*@version 2.6.2
  *@author Will Entriken <cameralife@phor.net>
  *@copyright © 2001-2009 Will Entriken
  *@access public
  *@link http://fdcl.sourceforge.net
*/
/**Carries the functions that develop the iphone theme
*/

  class iPhoneTheme extends ThemeModule
  {
    function iPhoneTheme() 
    {
      $this->name = 'iphone';

      $this->preferences[] = array('name'=>'main_thumbnails', 'desc'=>'Show random, most popular, latest photos...', 'type'=>array('1'=>'Show N thumbnails','0'=>'No'), 'default'=>'1');
      $this->preferences[] = array('name'=>'main_thumbnails_n', 'desc'=>'... N=', 'type'=>'number', 'default'=>'2');
      $this->preferences[] = array('name'=>'main_topics', 'desc'=>'Photo album topics', 'type'=>array('0'=>"Don't show",'1'=>'Show all topics','2'=>"Show all topics, and N albums each"), 'default'=>'2');
      $this->preferences[] = array('name'=>'main_topics_n', 'desc'=>'... N=', 'type'=>'number', 'default'=>'3');
      $this->preferences[] = array('name'=>'main_folders', 'desc'=>'Folders on main page', 'type'=>array('0'=>"Don't show",'1'=>'Show N random folders'), 'default'=>'1');
      $this->preferences[] = array('name'=>'main_folders_n', 'desc'=>'... N=', 'type'=>'number', 'default'=>'5');
    }

    function ShowPage($name, $object=NULL)
    {
      global $cameralife, $_SERVER, $_GET, $_POST;
      $objectname = strtolower($name);
      $$objectname = $object;
      require dirname(__FILE__) . '/' . strtolower($name) . '.inc';
    }

    function cssURL()
    {
      global $cameralife;
      return $cameralife->base_url."/modules/theme/".$this->name."/theme.css";
    }

    function ImageURL($name) 
    {
//todo error checking
      global $cameralife;

      return $cameralife->base_url."/modules/iconset/".$cameralife->GetPref('iconset')."/$name";
    }

    function Image($name, $extra=array()) 
    {
      if (eregi('src="([^"]*)"', $name)) # HTML IMG
      {
        $image['src'] = $regs[1];
        $image['alt'] = basename($image['src']);
      }
      else if (eregi('\.|/', $name)) # Image URL
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

    function TitleBar($title, $image=FALSE, $extra_info='', $menu=FALSE)
    {
      echo "<div id='TitleBar' align=center>\n";

      if ($image) $this->Image($image);

      if (is_array($menu))
      {
        $lastsection = '';
        echo '<ul>';
        foreach ($menu as $link)
        {
          if ($lastsection != $link['section'])
          {
            echo '</ul>';
            $this->Section($link['section']);
            echo '<ul>';
          }
          echo "<li>";
          if ($link['href']) 
          {
            echo "<a href=\"".$link["href"]."\"";
            if (strstr($link['href'], 'action='))
              echo ' rel="nofollow"';
            if ($link['onclick'])
              echo ' onclick="'.$link['onclick'].'"';
            if ($link['rel'])
              echo ' rel="'.$link['rel'].'"';
            if ($link['rev'])
              echo ' rev="'.$link['rev'].'"';
            echo '>';
          }
          if ($link['image']) $this->Image($link['image'],array('align'=>'middle','height'=>20));

          if ($link['class']=='referer')
            echo '<b>'.$link["name"].'</b>';
          else
            echo $link["name"];

          if ($link['href']) echo "</a>\n";

          $lastsection = $link['section'];
        }
        echo "</ul>\n";
      }
      echo "</div>\n";

      echo "<h1 id=\"title\">$title\n";
      if ($extra_info)
        echo "    <font id=\"titlebar-caption\">$extra_info</font>\n";
      echo "</h1>";
    

    }

    // query is an array of Icons, or and array of classes that have Icons
    /**Query is an array of Icons, or and array of classes that have Icons
    */
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

        if ($i%2==0) echo "    <tr>\n";
          $i++;

        echo "      <td align='center' width='25%'>".
             "<a href=\"".$icon["href"]."\">\n";
        $imageattrs = array('alt'=>'thumb', 'width'=>$icon['width'], 'height'=>$icon['height']);
        $this->Image($icon["image"], $imageattrs);
        echo "</a><br>".$icon['name'];
        if ($icon['context'])
          echo " <i><font size='-2'>(".$icon['context'].")</font></i>";
        echo "\n";
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
        echo " href=\"".urlencode($_SERVER['PHP_SELF'])."&#63;page=".$section['page_name'];
        if ($http_get)
          foreach($http_get as $var => $val)
            echo "&amp;$var=$val";
        echo "\">";
        #if ($section['image']) $this->Image($section['image'], array('align'=>'middle'));
        echo $section['name'];
        echo "</a>\n";
      }
      echo "</ul>\n";
    }

    function PageSelector($start, $total, $per_page, $param = FALSE)
    {
      global $theme;
    
      if ($total > $per_page) // Refuse to only show one page
      {
        echo '<div class="pageselector">';

        if ($start==-1)
        {
          echo "<span class='current'>Randomly showing <b>$per_page</b> of <b>$total</b></span> ";
          echo "<a class='nextprev' href=\"".urlencode($_SERVER["PHP_SELF"])."&#63;$param\">More &#187;</a>";
        }
        else
        {
          $first=max($start-2*$per_page,0);
          $last=min($first+4*$per_page,intval(($total-1)/$per_page)*$per_page);
          $first=max($last-4*$per_page,0);
          if ($last==0) $last=1;
      
          if ($first != $start)
            echo "<a class='nextprev' href=\"".urlencode($_SERVER["PHP_SELF"])."&#63;$param&amp;start=".($start-$per_page)."\">";
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
              echo "<a href=\"".urlencode($_SERVER["PHP_SELF"])."&#63;$param&amp;start=$i\">";
              echo ($i/$per_page+1)."</a> ";
            }
          }
  
          if ($last != $start)
            echo "<a class='nextprev' href=\"".urlencode($_SERVER["PHP_SELF"])."&#63;$param&amp;start=".($start+$per_page)."\">";
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
