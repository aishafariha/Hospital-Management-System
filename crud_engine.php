<?php
declare(strict_types=1);

/**
 * Drives list / add / edit / delete for one database table from a small
 * config array. Every admin/*.php page just describes its fields and calls
 * this — that's what keeps 11 modules from turning into 11 copy-pasted
 * CRUD scripts.
 *
 * Config keys:
 *   table         (string)   required
 *   title         (string)   required
 *   primary_key   (string)   default 'id'
 *   fields        (array)    key => [label, type, required, options|enum, step, nullable]
 *   list_columns  (array)    which field keys show as table columns (default: all fields)
 *   where         (array)    ['sql' => 'role = ?', 'params' => ['patient']]
 *   fixed         (array)    key => value forced on every insert (e.g. role => 'patient')
 *   allow_add     (bool)     default true
 *   allow_delete  (bool)     default true
 */
function render_crud(PDO $pdo, array $config): void {
    $table       = $config['table'];
    $pk          = $config['primary_key'] ?? 'id';
    $fields      = $config['fields'];
    $where       = $config['where'] ?? null;
    $fixed       = $config['fixed'] ?? [];
    $allowAdd    = $config['allow_add'] ?? true;
    $allowDelete = $config['allow_delete'] ?? true;
    $listColumns = $config['list_columns'] ?? array_keys($fields);
    $title       = $config['title'];

    $action = $_GET['action'] ?? 'list';
    $id     = isset($_GET['id']) ? (int) $_GET['id'] : null;
    $error  = null;

    // ---- Delete ----
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
        if (!csrf_verify($_POST['csrf_token'] ?? '')) {
            $error = 'Session expired. Please refresh and try again.';
        } elseif (!$allowDelete) {
            $error = 'Deleting is not allowed here.';
        } else {
            $delId = (int) $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
            $stmt->execute([$delId]);
            header('Location: ' . basename($_SERVER['PHP_SELF']));
            exit;
        }
    }

    // ---- Create / Update ----
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['do'] ?? '', ['create', 'update'], true)) {
        if (!csrf_verify($_POST['csrf_token'] ?? '')) {
            $error = 'Session expired. Please refresh and try again.';
        } else {
            $cols = [];
            $vals = [];

            foreach ($fields as $key => $f) {
                $type = $f['type'] ?? 'text';

                if ($type === 'password') {
                    $pw = (string) ($_POST[$key] ?? '');
                    if ($pw !== '') {
                        $cols[] = 'password_hash';
                        $vals[] = password_hash($pw, PASSWORD_BCRYPT);
                    }
                    continue;
                }

                if ($type === 'checkbox') {
                    $cols[] = $key;
                    $vals[] = isset($_POST[$key]) ? 1 : 0;
                    continue;
                }

                $v = trim(strip_tags((string) ($_POST[$key] ?? '')));

                if (($f['nullable'] ?? false) && $v === '') {
                    $cols[] = $key;
                    $vals[] = null;
                    continue;
                }

                $cols[] = $key;
                $vals[] = $v;
            }

            foreach ($fixed as $k => $v) {
                $cols[] = $k;
                $vals[] = $v;
            }

            if ($_POST['do'] === 'create') {
                $colSql = implode(',', array_map(fn($c) => "`$c`", $cols));
                $ph     = implode(',', array_fill(0, count($cols), '?'));
                try {
                    $stmt = $pdo->prepare("INSERT INTO `$table` ($colSql) VALUES ($ph)");
                    $stmt->execute($vals);
                    header('Location: ' . basename($_SERVER['PHP_SELF']));
                    exit;
                } catch (Exception $e) {
                    $error = 'Could not save this record. Double check the fields and try again.';
                }
            } else {
                $editId = (int) $_POST['id'];
                $setSql = implode(',', array_map(fn($c) => "`$c` = ?", $cols));
                $vals[] = $editId;
                try {
                    $stmt = $pdo->prepare("UPDATE `$table` SET $setSql WHERE `$pk` = ?");
                    $stmt->execute($vals);
                    header('Location: ' . basename($_SERVER['PHP_SELF']));
                    exit;
                } catch (Exception $e) {
                    $error = 'Could not save this record. Double check the fields and try again.';
                }
            }
        }
    }

    // ---- Fetch list ----
    $sql = "SELECT * FROM `$table`";
    $params = [];
    if ($where) {
        $sql .= ' WHERE ' . $where['sql'];
        $params = $where['params'] ?? [];
    }
    $sql .= " ORDER BY `$pk` DESC LIMIT 200";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // ---- Fetch single record for edit ----
    $editRow = null;
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
        $stmt->execute([$id]);
        $editRow = $stmt->fetch();
        if (!$editRow) {
            $action = 'list';
        }
    }

    include __DIR__ . '/crud_view.php';
}
