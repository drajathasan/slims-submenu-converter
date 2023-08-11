<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-09-17 12:01:45
 * @modify date 2023-08-11 18:39:35
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\DB;
use SLiMS\Url;

defined('INDEX_AUTH') or die('Direct access is not allowed!');

// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require LIB . 'ip_based_access.inc.php';

function httpQuery($query = [])
{
    return http_build_query(array_unique(array_merge($_GET, $query)));
}

if (isset($_POST['saveData']))
{
  // apakah URL nya valid?
  if (!Url::isValid($_POST['url'])) 
      die(toastr($_POST['url'] . ' is not valid URL!')->error());

  // Memparsing URL dan ambil Path nya
  $lastSwb = Url::parse($_POST['url'])->getPath();
  $currentSwb = Url::getPath();
  
  if ($lastSwb === $currentSwb) exit(toastr('Tidak perlu mengkonversi semua aman terkendali 😆')->info());
  
  $state = DB::getInstance()
              ->query('select 
                module_id,group_id,menus 
                from group_access 
                where menus is not null');
  $result = [];
  $process = 0;
  
  while ($data = $state->fetchObject()) {
    $module = DB::getInstance()->prepare('select * from mst_module where module_id = ?');
    $module->execute([$data->module_id]);

    if ($module->rowCount() < 1) continue;

    $moduleDetail = $module->fetchObject();

    if (!file_exists($path = MDLBS . $moduleDetail->module_path . DS . 'submenu.php')) continue;

    // Memasukan submenu
    include $path;

    // Process iterasi
    foreach ($menu as $item) {
      if ($item[0] === 'Header') continue;
      $url = $lastSwb . str_replace(SWB, '', $item[1]);
      if (in_array(md5($url), json_decode($data->menus, true))) {
        $result[] = md5($item[1]);
      }
    }

    if (count($result) < 1) continue;

    // Mengupdate daftar menu
    $update = DB::getInstance()->prepare('update group_access set menus = ? where group_id = ? and module_id = ?');
    $updateProcess = $update->execute([
      json_encode($result),
      $data->group_id,
      $data->module_id
    ]);

    if ($updateProcess) $process++;
  }

  if ($process) toastr('Berhasil memprocess ' . $process . ' submenu.')->success();
  else toastr('Tidak ada process yang dilakukan.')->info();
  exit;
}
?>
<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2>Pengkonversi Submenu</h2>
    </div>
  </div>
</div>
<?php
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'] . '?' . httpQuery(), 'post');
$form->submit_button_attr = 'name="saveData" value="'.__('Save Settings').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';
$form->addTextField('text', 'url', 'URL SLiMS Sebelumnya', '', 'style="width: 100%;" class="form-control"');

// print out the object
echo $form->printOut();
