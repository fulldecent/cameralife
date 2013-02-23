<?php
/**
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
*/

$cameralife->GetFeature('security');
$cameralife->GetFeature('photostore');

if (isset($_REQUEST['path']) && $_REQUEST['path'] != 'upload/'.$user['username'].'/')
{
  $cameralife->Security->Authorize('admin_file', 1);
  $path = $_REQUEST['path'];
}
else
{
  $cameralife->Security->Authorize('photo_upload', 1);
  $path = 'upload/'.$cameralife->Security->GetName().'/';
}
$folder = new Folder($path);
$icon = $folder->GetIcon();

$cameralife->Theme->Header('Upload photos');
?>
<BGSOUND src="<?= $cameralife->base_url ?>/filedone.wav">
<h1>Uploading Photos</h1>
<ul>
  <li>You can upload a photo in JPEG format or a ZIP of JPEG photos
  <li>The maximum file size is 20MB
<?php
  if ($cameralife->Security->Authorize('admin_file') && !isset($_REQUEST['path']))
    echo '    <li>To upload somewhere else, go to that folder and click "upload here"';
?>
</ul>

<form enctype="multipart/form-data" method=POST action="<?= $cameralife->base_url ?>/upload_controller.php" class="form-horizontal">
<input type="hidden" name="target" value="<?= $icon['href'] ?>">
  <div class="control-group">
    <label class="control-label" for="path">Path:</label>
    <div class="controls">
      <span class="input-xlarge uneditable-input"><i class="icon-folder-open"></i> <?= $path ?></span>
      <input name="path" type=hidden value="<?= $path ?>">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="userfile">Photo file:</label>
    <div class="controls">
      <input name="userfile" type="file">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="description">Photo description:</label>
    <div class="controls">
      <input name="description"  placeholder="Unnamed">
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
      <button type="submit" class="btn">Upload Photo</button>
    </div>
  </div>

</form>
<?php
  $cameralife->Theme->Footer();
?>
