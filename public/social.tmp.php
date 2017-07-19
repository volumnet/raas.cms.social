<?php
namespace RAAS\CMS\Social;

$VIEW = Module::i()->view;
?>
<h2><?php echo $VIEW->_('PROFILES')?></h2>
<?php
$Table = $profilesTable;
include $VIEW->tmp('/table.tmp.php');
?>
<div>
  <?php echo $VIEW->_('ADD_PROFILE')?>:
  <?php foreach (array('facebook', 'vk', 'twitter') as $key) { ?>
      <?php if ($loginUrls[$key]) { ?>
          <a class="cms-social-login-button" href="<?php echo $loginUrls[$key]?>" title="<?php echo $VIEW->_(strtoupper($key)); ?>">
            <img src="<?php echo $VIEW->publicURL?>/<?php echo $key?>.png" alt="<?php echo $VIEW->_(strtoupper($key)); ?>">
          </a>
      <?php } ?>
  <?php } ?>
</div>

<h2 style="margin-top: 50px;"><?php echo $VIEW->_('GROUPS')?></h2>
<?php
$Table = $groupsTable;
include $VIEW->tmp('/table.tmp.php');
?>
<form action="" method="POST">
  <input type="text" name="add_group" id="add_group" required="required" style="margin-bottom: 0;" />
  <button type="submit" class="btn btn-primary"><?php echo $VIEW->_('ADD_GROUP')?></button>
</form>

<h2 style="margin-top: 50px;"><?php echo $VIEW->_('TASKS')?></h2>
<form action="" method="GET">
  <input type="hidden" name="p" value="cms" />
  <input type="hidden" name="m" value="social" />
  <input type="hidden" name="sub" value="dev" />
  <input type="hidden" name="action" value="edit_task" />
  <?php echo $VIEW->_('ADD_TASK_FOR')?>
  <select name="pid" style="margin-bottom: 0;">
    <?php foreach ($materialTypes as $materialType) { ?>
        <option value="<?php echo (int)$materialType->id?>"><?php echo htmlspecialchars($materialType->name)?></option>
    <?php } ?>
  </select>
  <button type="submit" class="btn btn-primary"><?php echo $VIEW->_('ADD')?></button>
</form>
<?php
$Table = $tasksTable;
include $VIEW->tmp('/table.tmp.php');
?>
