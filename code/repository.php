<?php
require "../vendor/autoload.php";
use \Firebase\JWT\JWT;
include_once 'database.php';

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class MyDB extends SQLite3 {
    function __construct() {
        $this->registryDir = getenv("REGISTRY_DIR");
        $needInit = file_exists($this->registryDir.'sqlite.db') == false;
        $this->open($this->registryDir.'sqlite.db');
        if ($needInit) $this->InitDB();
    }
    function InitDB() {
        $sql =<<<EOF
            CREATE TABLE packages
            (name TEXT NOT NULL,
             namespace TEXT NOT NULL,
             version TEXT NOT NULL
            );
        EOF;
        /*
             description TEXT,
             updated DATETIME NOT NULL,
             tags TEXT,             
             license TEXT NOT NULL,
             dependencies TEXT
             */
        $ret = $this->exec($sql);
        if(!$ret){
            die($this->lastErrorMsg());
        }
        $this->UpdateDB();
    }

    function UpdateDB() {
        $dir = @dir($this->registryDir);
        while(false !== ($entry = $dir->read())){
            if($entry[0] == ".") continue;
            if(is_dir("{$this->registryDir}{$entry}")){
                $namespaces = @dir("{$this->registryDir}{$entry}/");
                while(false !== ($pkgEntry = $namespaces->read())){
                    if($pkgEntry[0] == ".") continue;
                    if(is_dir("{$this->registryDir}{$entry}/{$pkgEntry}/")){
                        $pkgDir = @dir("{$this->registryDir}{$entry}/{$pkgEntry}/");
                        while(false !== ($fileVersion = $pkgDir->read())){
                            if($fileVersion[0] == ".") continue;
                            if(is_file("{$this->registryDir}{$entry}/{$pkgEntry}/{$fileVersion}")){
                                $version = rtrim($fileVersion,".kpkg");
                                $sql =<<<EOF
                                INSERT INTO packages (name, namespace, version)
                                VALUES ("{$pkgEntry}", "{$entry}", "{$version}");
                              EOF;
                              $ret = $this->exec($sql);
                            }
                        }
                        $pkgDir->close();
                    }
                }
                $namespaces->close();
            }
        }
        $dir->close();
    }

    function GetPage($page) {
        $offset = 10 * $page;
        $sql =<<<EOF
          SELECT * FROM packages LIMIT 10 OFFSET {$offset};
        EOF;
        $ret = $this->query($sql);
        if(!$ret){
            die($this->lastErrorMsg());
        } else {
            $data = array();
            while($row = $ret->fetchArray(SQLITE3_ASSOC)){
                $data[] = (object)[
                    "namespace" => $row['namespace'],
                    "package" => $row['name'],
                    "version" => $row['version']
                ];
            }
            return $data;
        }
    }

    function SearchPackage($value, $page) {
        $offset = 10 * $page;
        $sql =<<<EOF
          SELECT * FROM packages WHERE name LIKE '%{$value}%' LIMIT 10 OFFSET {$offset};
        EOF;
        $ret = $this->query($sql);
        if(!$ret){
            die($this->lastErrorMsg());
        } else {
            $data = array();
            while($row = $ret->fetchArray(SQLITE3_ASSOC)){
                $data[] = (object)[
                    "namespace" => $row['namespace'],
                    "package" => $row['name'],
                    "version" => $row['version']
                ];
            }
            return $data;
        }
    }
}
$db = new MyDB();
if(!$db) {
    echo $db->lastErrorMsg();
}

$data = array();
$page = 0;
if (isset($_GET["page"])) $page = $_GET["page"];
$start = $page * 10;
$end = $start + 10;
$count = 0;
if (isset($_GET["search"])){
    $data = $db->SearchPackage($_GET["search"], $page);
} else {    
    $data = $db->GetPage($page);
}
echo json_encode($data);