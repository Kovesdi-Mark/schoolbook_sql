<?php

function getConn($dbName = DB_NAME): ?mysqli
{
    try {
        // Kapcsolódás az adatbázishoz
        $mysqli = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, $dbName);
 
        // Ellenőrizzük a csatlakozás sikerességét
        if (!$mysqli) {
            throw new Exception('Kapcsolódási hiba az adatbázishoz: ' . mysqli_connect_error());
        }
 
        return $mysqli;
 
    } catch (Exception $e) {
 
        // Hibanaplózás
        error_log($e->getMessage());
 
        // Hibás csatlakozás esetén `null`-t ad vissza
        return null;
    }
}

function execSql($sql): bool|int|array
{
    $mysqli = null;
 
    try {
        $mysqli = getConn();
 
        // Check if connection was established
        if (!$mysqli) {
            return false;
        }
 
        $result = $mysqli->query($sql);
 
        // Check if the query execution was successful
        if (!$result) {
            throw new Exception('Hiba lépett fel az SQL utasítás futtatása közben: ' . $mysqli->error);
        }
 
        // Handle INSERT queries
        if (str_starts_with(strtoupper(trim($sql)), 'INSERT')) {
            return $mysqli->insert_id > 0 ? $mysqli->insert_id : false;
        }
 
        // Handle SELECT queries
        if (str_starts_with(strtoupper(trim($sql)), 'SELECT')) {
            $numRows = $result->num_rows;
 
            if ($numRows === 0) {
                return false;
            }
 
            if ($numRows === 1) {
                return $result->fetch_assoc();
            }
 
            return $result->fetch_all(MYSQLI_ASSOC);
        }
 
        // For other types of queries (e.g., UPDATE, DELETE)
        return $mysqli->affected_rows > 0;
 
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    } finally {
        // Ensure the database connection is always closed
        $mysqli?->close();
    }
}

function dbExists($dbName = DB_NAME): bool
{
    try {
        $mysqli = getConn('mysql');
        if (!$mysqli) {
            return false;
        }
 
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName';";
        $result = $mysqli->query($query);
 
        if (!$result) {
            throw new Exception('Lekérdezési hiba: ' . $mysqli->error);
        }
        $exists = $result->num_rows > 0;
 
        return $exists;
 
    } catch (Exception $e) {
        error_log($e->getMessage());
 
        return false;
    } finally {
        // Ensure the database connection is always closed
        $mysqli?->close();
    }
 
}