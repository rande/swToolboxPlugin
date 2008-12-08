<ul class="sw-breadcrumb-list">
  <?php foreach( swBreadcrumb::get() as $key => $entry): ?>
    <?php $className = 'sw-breadcrumb-entry'; ?>
    <?php if($key == 0) $className .= ' sw-breadcrumb-first'; ?>
    <?php if($key == (count(swBreadcrumb::get()) - 1)) $className .= ' sw-breadcrumb-last'; ?>
      <li class="<?php echo $className ?>">
        <?php if($entry['url']): ?>
          <?php echo link_to($entry['breadcrumb'], $entry['url'], array('class' =>'sw-breadcrumb-link', 'title' => $entry['title'])); ?>
        <?php else: ?>
          <span class="sw-breakcrumb-text"><?php echo $entry['breadcrumb'] ?></span>
        <?php endif; ?>
      </li>
  <?php endforeach; ?>
</ul>
