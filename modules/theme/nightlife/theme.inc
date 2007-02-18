<?php
#TODO: more alt = basename(img)
  class NightlifeTheme extends ThemeModule
  {
    function NightlifeTheme() 
    {
      $this->name = 'nightlife';
    }

    function cssURL()
    {
      global $base_url;
      return "$base_url/modules/theme/".$this->name."/theme.css";
    }

    function ImageURL($name) 
    {
      global $cameralife;

//TODO: a cameralife method to get a base url
//todo error checking
      global $base_url;

      return "$base_url/modules/iconset/".$cameralife->preferences['core']['iconset']."/$name";
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
        echo " $attr=\"$val\"";
      echo ">\n";
    }

    function TitleBar($title, $image=FALSE, $extra_info='', $menu=FALSE) 
    {
      echo "<table width=\"100%\" align=center>\n";
      echo "  <tr><td width='100'>\n";

      if ($image) $this->Image($image);
    
      echo "    <td><font class=\"titlebar\">$title</font>\n";
      if ($extra_info)
        echo "    <font class=\"titlebar-caption\">$extra_info</font>\n";
    
      if (is_array($menu))
      {
        echo "    <td align=left width=\"5%\" nowrap=\"nowrap\">\n";
        echo '<ul class="titlebar-menu">';
        foreach ($menu as $link)
        {
          echo "<li>";
          echo "<a href=\"".$link["href"]."\"> ".$link["name"]."</a>\n";
        }
        echo "</ul>\n";
      }
      echo "</table>\n";
    }

#TODO: consider not passing a database object here, an array of arrays
    function Grid($query) 
    {
      echo "  <table width='100%' class='grid' align=center cellspacing=3 cellpadding=3>\n";
      $i=0;
    
      while ($thumb = $query->FetchAssoc())
      {
        if ($i%4==0) echo "    <tr>\n";
        $i++;
    
        echo "      <td align='center' width='25%'>".
             "<a href=\"".$thumb["href"]."\">\n";
        $imageattrs = array('alt'=>'thumb', 'width'=>$thumb['tn_width'], 'height'=>$thumb['tn_height']);
        $this->Image($thumb["tn"], $imageattrs);
        echo "</a><br>".$thumb['description'];
        if ($thumb["comment"])
          echo " <i><font size='-2'>(".$thumb['comment'].")</font></i>";
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
        echo " href=\"".$_SERVER['PHP_SELF']."&#63;page=".$section['page_name'];
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
          echo "<a class='nextprev' href=\"".$_SERVER["PHP_SELF"]."&#63;$param\">More &#187;</a>";
        }
        else
        {
          $first=max($start-2*$per_page,0);
          $last=min($first+4*$per_page,intval(($total-1)/$per_page)*$per_page);
          $first=max($last-4*$per_page,0);
          if ($last==0) $last=1;
      
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
          }
  
          if ($last != $start)
            echo "<a class='nextprev' href=\"".$_SERVER["PHP_SELF"]."&#63;$param&amp;start=".($start+$per_page)."\">";
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
