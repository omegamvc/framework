<div class="exception">
  <div class="exc-title">
    <?php foreach ($name as $i => $nameSection) : ?>
        <?php if ($i == count($name) - 1) : ?>
        <span class="exc-title-primary"><?php echo $tpl->escape($nameSection) ?></span>
        <?php else : ?>
            <?php echo $tpl->escape($nameSection) . ' \\' ?>
        <?php endif ?>
    <?php endforeach ?>
    <?php if ($code) : ?>
      <span title="Exception Code">(<?php echo $tpl->escape($code) ?>)</span>
    <?php endif ?>
  </div>

  <div class="exc-message">
    <?php if (!empty($message)) : ?>
      <span><?php echo $tpl->escape($message) ?></span>


        <?php if (count($previousMessages)) : ?>
        <div class="exc-title prev-exc-title">
          <span class="exc-title-secondary">Previous exceptions</span>
        </div>

        <ul>
            <?php foreach ($previousMessages as $i => $previousMessage) : ?>
            <li>
                <?php echo $tpl->escape($previousMessage) ?>
              <span class="prev-exc-code">(<?php echo $previousCodes[$i] ?>)</span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif ?>



    <?php else : ?>
      <span class="exc-message-empty-notice">No message</span>
    <?php endif ?>

    <span id="plain-exception"><?php echo $tpl->escape($plain_exception) ?></span>
    <button id="copy-button" class="rightButton clipboard" data-clipboard-text="
      <?php echo $tpl->escape($plain_exception) ?>" title="Copy exception details to clipboard">
      COPY
    </button>
    <button 
      id="hide-error" 
      class="rightButton" 
      title="Hide error message" 
      onclick="document.getElementsByClassName('Omega')[0].style.display = 'none';"
      >
      HIDE
    </button>
  </div>
</div>
