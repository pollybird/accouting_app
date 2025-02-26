<?php
session_start();
include 'db_connection.php';

function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        return true;
    } elseif (isset($_COOKIE['user_id'])) {
        $user_id = $_COOKIE['user_id'];
        $query = $GLOBALS['db']->prepare('SELECT * FROM users WHERE id = :user_id');
        $query->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $query->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        if ($user) {
            $_SESSION['user_id'] = $user_id;
            return true;
        }
    }
    return false;
}

function getLedgers() {
    global $db;
    $user_id = $_SESSION['user_id'];
    $query = $db->prepare('SELECT * FROM ledgers WHERE user_id = :user_id ORDER BY id DESC');
    $query->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $result = $query->execute();
    $ledgers = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $ledgers[] = $row;
    }
    return $ledgers;
}

function getTransactions($ledger_id, $offset = 0, $limit = 10, $search = '') {
    global $db;
    $baseSql = 'SELECT * FROM transactions WHERE ledger_id = :ledger_id';
    $params = [':ledger_id' => $ledger_id];

    if (!empty($search)) {
        $baseSql .= ' AND description LIKE :search';
        $params[':search'] = "%$search%";
    }

    $baseSql .= ' ORDER BY date DESC LIMIT :limit OFFSET :offset';
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;

    $query = $db->prepare($baseSql);
    foreach ($params as $paramName => $paramValue) {
        $type = is_int($paramValue)? SQLITE3_INTEGER : (is_float($paramValue)? SQLITE3_FLOAT : SQLITE3_TEXT);
        $query->bindValue($paramName, $paramValue, $type);
    }

    $result = $query->execute();
    $transactions = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $transactions[] = $row;
    }
    return $transactions;
}


function getTransactionsWithCustomQuery($sql, $params) {
    global $db;
    $query = $db->prepare($sql);
    foreach ($params as $paramName => $paramValue) {
        $type = is_int($paramValue)? SQLITE3_INTEGER : (is_float($paramValue)? SQLITE3_FLOAT : SQLITE3_TEXT);
        $query->bindValue($paramName, $paramValue, $type);
    }
    $result = $query->execute();
    $transactions = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $transactions[] = $row;
    }
    return $transactions;
}