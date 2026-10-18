<?php
/** @var PDO $pdo */
/** @var string $table */
/** @var string $pk */
/** @var array $fields */
/** @var array $rows */
/** @var array|null $editRow */
/** @var string $action */
/** @var string $title */
/** @var array $listColumns */
/** @var bool $allowAdd */
/** @var bool $allowDelete */
/** @var string|null $error */

$showForm = $action === 'add' || $action === 'edit';
$selfFile = basename($_SERVER['PHP_SELF']);

$pwField = null;
foreach ($fields as $fk => $ff) {
    if (($ff['type'] ?? '') === 'password') { $pwField = [$fk, $ff]; break; }
}
?>
<div class="admin-content">
  <div class="admin-content-head">
    <h1><?= e($title) ?></h1>
    <?php if ($allowAdd && !$showForm): ?>
      <a href="?action=add" class="btn btn-primary">+ Add New</a>
    <?php elseif ($showForm): ?>
      <a href="<?= e($selfFile) ?>" class="btn btn-ghost">&larr; Back to list</a>
    <?php endif; ?>
  </div>

  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

  <?php if ($showForm): ?>
    <div class="glass-card" style="padding:30px 34px;max-width:640px;">
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="do" value="<?= $action === 'edit' ? 'update' : 'create' ?>">
        <?php if ($action === 'edit'): ?><input type="hidden" name="id" value="<?= (int) $editRow[$pk] ?>"><?php endif; ?>

        <?php foreach ($fields as $key => $f):
            $type = $f['type'] ?? 'text';
            if ($type === 'password') continue;
            $val = $editRow[$key] ?? ($f['default'] ?? '');
        ?>
          <div class="field">
            <label><?= e($f['label']) ?></label>
            <?php if ($type === 'select' && isset($f['options'])): ?>
              <select name="<?= e($key) ?>" <?= ($f['required'] ?? false) ? 'required' : '' ?>>
                <option value="">Select&hellip;</option>
                <?php foreach ($f['options'] as $opt): ?>
                  <option value="<?= (int) $opt['id'] ?>" <?= (string) $val === (string) $opt['id'] ? 'selected' : '' ?>><?= e($opt['name']) ?></option>
                <?php endforeach; ?>
              </select>
            <?php elseif ($type === 'select' && isset($f['enum'])): ?>
              <select name="<?= e($key) ?>">
                <?php foreach ($f['enum'] as $ev => $el): ?>
                  <option value="<?= e((string) $ev) ?>" <?= (string) $val === (string) $ev ? 'selected' : '' ?>><?= e($el) ?></option>
                <?php endforeach; ?>
              </select>
            <?php elseif ($type === 'checkbox'): ?>
              <input type="checkbox" name="<?= e($key) ?>" value="1" <?= $val ? 'checked' : '' ?>>
            <?php elseif ($type === 'textarea'): ?>
              <textarea name="<?= e($key) ?>" rows="3" maxlength="500"><?= e((string) $val) ?></textarea>
            <?php elseif ($type === 'date'): ?>
              <input type="date" name="<?= e($key) ?>" value="<?= e((string) $val) ?>" <?= ($f['required'] ?? false) ? 'required' : '' ?>>
            <?php elseif ($type === 'time'): ?>
              <input type="time" name="<?= e($key) ?>" value="<?= e((string) $val) ?>" <?= ($f['required'] ?? false) ? 'required' : '' ?>>
            <?php elseif ($type === 'number'): ?>
              <input type="number" step="<?= e($f['step'] ?? '1') ?>" name="<?= e($key) ?>" value="<?= e((string) $val) ?>" <?= ($f['required'] ?? false) ? 'required' : '' ?>>
            <?php else: ?>
              <input type="text" name="<?= e($key) ?>" value="<?= e((string) $val) ?>" <?= ($f['required'] ?? false) ? 'required' : '' ?>>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <?php if ($pwField): [$pwKey, $pwF] = $pwField; ?>
          <div class="field">
            <label><?= e($pwF['label']) ?><?= $action === 'edit' ? ' (leave blank to keep current password)' : '' ?></label>
            <input type="password" name="<?= e($pwKey) ?>" minlength="8" <?= $action === 'add' ? 'required' : '' ?>>
          </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary btn-block">Save</button>
      </form>
    </div>
  <?php else: ?>
    <div class="glass-card" style="padding:0;overflow:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <?php foreach ($listColumns as $col): ?>
              <th><?= e($fields[$col]['label'] ?? ucfirst(str_replace('_', ' ', $col))) ?></th>
            <?php endforeach; ?>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="<?= count($listColumns) + 1 ?>" style="text-align:center;color:var(--text-soft);padding:26px;">No records yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($rows as $row): ?>
            <tr>
              <?php foreach ($listColumns as $col):
                  $f = $fields[$col] ?? null;
                  $v = $row[$col] ?? '';
                  if ($f && ($f['type'] ?? '') === 'select' && isset($f['options'])) {
                      $match = array_filter($f['options'], fn($o) => (string) $o['id'] === (string) $v);
                      $v = $match ? reset($match)['name'] : $v;
                  } elseif ($f && ($f['type'] ?? '') === 'select' && isset($f['enum'])) {
                      $v = $f['enum'][$v] ?? $v;
                  } elseif ($f && ($f['type'] ?? '') === 'checkbox') {
                      $v = $v ? 'Yes' : 'No';
                  }
              ?>
                <td><?= e((string) $v) ?></td>
              <?php endforeach; ?>
              <td class="admin-actions">
                <a href="?action=edit&id=<?= (int) $row[$pk] ?>">Edit</a>
                <?php if ($allowDelete): ?>
                  <form method="post" onsubmit="return confirm('Delete this record? This cannot be undone.');" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="do" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $row[$pk] ?>">
                    <button type="submit" class="link-btn">Delete</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
