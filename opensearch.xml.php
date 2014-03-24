<?php
/**xml tool used - OpenSearchDescription
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */
/**
 */
require 'main.inc';
header('Content-type: text/xml');
echo "<?xml version=\"1.0\"?>\n";
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
    <ShortName><?= $cameralife->getPref('siteabbr') ?> Search</ShortName>
    <Description><?= $cameralife->getPref('sitename') ?> Search</Description>
    <Url type="text/html" template="<?= $cameralife->baseURL ?>/search.php?q={searchTerms}"/>
    <Query  role="example" searchTerms="cat" />
</OpenSearchDescription>
