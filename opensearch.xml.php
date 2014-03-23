<?php
/**xml tool used - OpenSearchDescription
*@author Will Entriken <cameralife@phor.net>
*@copyright Copyright (c) 2001-2009 Will Entriken
*@access public
*/
/**
*/
  require 'main.inc';
  header('Content-type: text/xml');
  echo "<?xml version=\"1.0\"?>\n";
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
<ShortName><?= $cameralife->getPref('sitename') ?> Search</ShortName>
<Description><?= $cameralife->getPref('sitename') ?> Search</Description>
<Url type="text/html" method="get" template="<?= $cameralife->base_url ?>/search.php?q={searchTerms}"/>
</OpenSearchDescription>
