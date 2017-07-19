<?php
namespace RAAS\CMS\Social;

include $VIEW->tmp('/form.inc.php');
?>
<ol>
  <li>
    <?php echo sprintf(\CMS\Social\ADD_VK_PROFILE_STEP1_VISIT_AUTH_PAGE, str_replace('response_type=code', 'response_type=token', Vk::getLoginUrl('https://oauth.vk.com/blank.html')));?>
  </li>
  <li>
    <p><?php echo \CMS\Social\ADD_VK_PROFILE_STEP2_CONFIRM_ACCESS?></p>
    <p><img src="<?php echo ViewSub_Main::i()->publicURL?>/vk_confirm_access.jpg" alt=""><br />&nbsp;</p>
  </li>
  <li>
    <p><?php echo \CMS\Social\ADD_VK_PROFILE_STEP3_COPY_URL?></p>
    <p><img src="<?php echo ViewSub_Main::i()->publicURL?>/vk_copy_url.jpg" alt=""><br />&nbsp;</p>
  </li>
  <li><?php echo \CMS\Social\ADD_VK_PROFILE_STEP4_INSERT_URL?></li>
</ol>
<form<?php echo $_RAASForm_Attrs($Form)?>>
  <?php
  if (array_filter((array)$Form->children, function($x) { return $x instanceof \RAAS\FormTab; })) {
      $_RAASForm_Form_Tabbed($Form->children);
  } else {
      $_RAASForm_Form_Plain($Form->children);
  }
  ?>
  <div class="form-horizontal">
    <div class="control-group">
      <div class="controls">
        <input type="submit" class="btn btn-primary" value="<?php echo $Form->submitCaption ? htmlspecialchars($Form->submitCaption) : SAVE?>" />
      </div>
    </div>
  </div>
</form>
