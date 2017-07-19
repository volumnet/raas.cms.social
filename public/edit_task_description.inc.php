<?php
namespace RAAS\CMS;

$_RAASForm_Field = function (\RAAS\Field $Field) use (&$_RAASForm_Control, &$_RAASForm_Options, &$_RAASForm_Attrs) {
    $err = (bool)array_filter((array)$Field->Form->localError, function ($x) use ($Field) {
        return $x['value'] == $Field->name;
    });
    $dataHint = $Field->{'data-hint'};
    $attrs['type'] = false;
    $attrs['data-hint'] = false;
    ?>
    <div class="control-group<?php echo $err ? ' error' : ''?>">
      <label class="control-label" for="<?php echo htmlspecialchars($Field->name)?>">
        <?php echo htmlspecialchars($Field->caption ? $Field->caption . ':' : '')?>
      </label>
      <div class="controls">
        <textarea<?php echo $_RAASForm_Attrs($Field, $attrs)?>><?php echo htmlspecialchars($Field->Form->DATA[$Field->name])?></textarea>
      </div>
    </div>
    <pre><?php echo $dataHint?></pre>
    <?php
};
