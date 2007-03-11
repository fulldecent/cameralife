<?php
  require 'main.inc';
  header('Content-type: text/xml');
  echo "<?xml version=\"1.0\"?>\n";
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
<ShortName><?= $cameralife->preferences['core']['sitename'] ?> Search</ShortName>
<Description><?= $cameralife->preferences['core']['sitename'] ?> Search</Description>
<Url type="text/html" method="get" template="<?= $cameralife->base_url ?>/search.php?q={searchTerms}"/>
</OpenSearchDescription>
