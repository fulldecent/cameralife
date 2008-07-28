<?php
  # $cameralife set by ShowPage()

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

  $cameralife->Theme->Header('Upload photos','photo');
?>
<BGSOUND src="<?= $cameralife->base_url ?>/filedone.wav">
<div id="text">
<form enctype="multipart/form-data" method=POST action="<?= $cameralife->base_url ?>/upload_controller.php">
<input type="hidden" name="target" value="<?= $icon['href'] ?>">
<h1>Uploading Photos</h1>
  <ul>
    <li>You can upload a photo in JPEG format or a ZIP of JPEG photos
    <li>The maximum file size is 20MB
  </ul>

  <?php
    if ($cameralife->Security->Authorize('admin_file') && !isset($_REQUEST['path']))
    {
      echo '<h1>Notes</h1>';
      echo "<p>You can also upload to individual folders:</p>";
      echo "<ul>";
      echo "  <li>Go to the folder you would like to upload to";
      echo "  <li>Choose Upload photo here from the title menu link";
      echo "</ul>";
    }
  ?>

  <p>
  <input type="hidden" name="MAX_FILE_SIZE" value="20800000">
  <table>
    <tr><td>Path:<td><?= $path ?><input name="path" type=hidden value="<?= $path ?>">
    <tr><td>Photo file:<td><input name="userfile" type="file">
    <tr><td>Photo description:<td><input name="description" type="text" size=50>
  </table>
  <input type="submit" value="Upload Photo">
  </p>

</form>
<?php
  $cameralife->Theme->Footer();
?>
