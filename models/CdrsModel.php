<?php

namespace app\models;

use Yii;
use yii\base\Model;

class CdrsModel extends Model {

    public $start_date;
    public $end_date;
    public $toDelete = false;

    public $path;
    //public $rows_to_insert = "";
    //public $rows_to_insert = array();

    function __construct(){
        $this->path = $this->path = Yii::getAlias('@app').'/backup/';
        //ini_set('memory_limit', '1024M');
    }

    /*public function backup_OLD(){

        $filename = date('dmY', strtotime($this->start_date)).'_'.date('dmY', strtotime($this->end_date));

        $fullpath = $this->path.$filename.'.csv';

        $sql = <<<eof
            SELECT * INTO OUTFILE '$fullpath'
            FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
            LINES TERMINATED BY '\n'
            FROM cdrs 
            WHERE started_at >= :start_date
            AND DATE_ADD(started_at, INTERVAL duration SECOND) < :end_date
eof;

        $command = Yii::$app->db->createCommand($sql);
        $command->bindParam(':start_date', date('Y-m-d 00:00:00',strtotime($this->start_date)));
        $command->bindParam(':end_date', date('Y-m-d 00:00:00', strtotime($this->end_date)));
        $command->execute();

        $zip = new \ZipArchive();
        $zip->open($this->path.$filename.'.zip', \ZIPARCHIVE::CREATE);
        $zip->addFile($this->path.$filename.'.csv', $filename.'.csv');
        $zip->close();
        unlink($this->path.$filename.'.csv');

        $this->deleteSelected();

    }*/

    public function backup(){

        $limit = 12000;
        $offset = 0;
        $filename = date('dmY', strtotime($this->start_date)).'_'.date('dmY', strtotime($this->end_date));

        $fullpath = $this->path.$filename.'.csv';

        $sql = <<<eof
            SELECT COUNT(*) from cdrs 
            WHERE started_at >= :start_date
            AND DATE_ADD(started_at, INTERVAL duration SECOND) < :end_date
eof;
        $command = Yii::$app->db->createCommand($sql);
        $command->bindParam(':start_date', date('Y-m-d 00:00:00',strtotime($this->start_date)));
        $command->bindParam(':end_date', date('Y-m-d 00:00:00', strtotime($this->end_date)));
        $count = $command->queryScalar();

        if($count < 1) return;

        $handle = fopen($fullpath, 'w');

        while ($offset < $count){

            $sql = <<<eof
                SELECT * FROM cdrs 
                WHERE started_at >= :start_date
                AND DATE_ADD(started_at, INTERVAL duration SECOND) < :end_date
                ORDER BY started_at
                LIMIT $limit OFFSET $offset
eof;

            $command = Yii::$app->db->createCommand($sql);
            $command->bindParam(':start_date', date('Y-m-d 00:00:00',strtotime($this->start_date)));
            $command->bindParam(':end_date', date('Y-m-d 00:00:00', strtotime($this->end_date)));
            $results = $command->queryAll();

            foreach($results as $row){
                fputcsv($handle, $row);
            }

            $offset = $offset + $limit;
        }

        fclose($handle);
        $zip = new \ZipArchive();
        $zip->open($this->path.$filename.'.zip', \ZIPARCHIVE::CREATE);
        $zip->addFile($this->path.$filename.'.csv', $filename.'.csv');
        $zip->close();
        unlink($this->path.$filename.'.csv');

        $this->deleteSelected();

    }

    public function deleteSelected(){

        if ($this->toDelete == false){
            return;
        }

        $sql = <<<eof
        DELETE FROM cdrs 
            WHERE started_at >= :start_date
            AND DATE_ADD(started_at, INTERVAL duration SECOND) < :end_date
eof;


        $command = Yii::$app->db->createCommand($sql);
        $command->bindParam(':start_date', date('Y-m-d 00:00:00',strtotime($this->start_date)));
        $command->bindParam(':end_date', date('Y-m-d 00:00:00', strtotime($this->end_date)));
        $command->execute();

    }

    public function readFiles(){
        $files = array();

        if (is_dir($this->path)){
            if ($dh = opendir($this->path)){
                while (($file = readdir($dh)) !== false){
                    if($file == '.' or $file == '..' or substr($file,-4) != '.zip') continue;

                    $files[] = [
                        "filename" => $file,
                        "size" => round(filesize($this->path.$file) / 1000) . ' KB',
                        "created" => date('d-m-Y H:i:s', filemtime($this->path.$file))
                    ];
                }
                closedir($dh);
            }

            usort($files, function($b, $a){
                return $a['created'] - $b['created'];
            });

            return $files;
        }

        return null;
    }
/*
    public function restoreBackup_OLD($filename){

        $zip = new \ZipArchive();
        $zip->open($this->path.$filename);
        $zip->extractTo($this->path);
        $zip->close();

        $table_name = 'temp_cdrs_'.substr($filename,0,strlen($filename)-4);

        $sql = <<<eof
            DROP TABLE IF EXISTS $table_name;
            CREATE TABLE $table_name (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `site_id` SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0',
                `user_id` INT(11) NOT NULL,
                `service_id` INT(11) NOT NULL,
                `callid` VARCHAR(255) NOT NULL DEFAULT '',
                `period` VARCHAR(10) NOT NULL,
                `direction` TINYINT(1) NULL DEFAULT NULL,
                `src` VARCHAR(255) NULL DEFAULT NULL,
                `dst` VARCHAR(255) NULL DEFAULT NULL,
                `destination` VARCHAR(60) NULL DEFAULT NULL,
                `started_at` DATETIME NULL DEFAULT NULL,
                `duration` INT(11) NOT NULL,
                `bill_sec` INT(11) NOT NULL,
                `rate` INT(11) NULL DEFAULT NULL,
                `bill_amount` INT(11) NULL DEFAULT NULL,
                `connection_charge` INT(11) NULL DEFAULT NULL,
                `commodity_id` INT(11) NULL DEFAULT NULL,
                `commodity_scu` INT(11) NULL DEFAULT NULL,
                `status` TINYINT(1) NULL DEFAULT NULL,
                `tbd` VARCHAR(20) NULL DEFAULT NULL,
                `clir` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                `reason` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                INDEX `idx_user` (`user_id`),
                INDEX `idx_service` (`service_id`),
                INDEX `idx_period` (`period`),
                INDEX `started_at` (`started_at`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
eof;

        $command = Yii::$app->db->createCommand($sql);
        $command->execute();

        $source_path = $this->path.substr($filename,0,strlen($filename)-4).'.csv';


        $sql = <<<eof
            LOAD DATA INFILE '$source_path'
            INTO TABLE $table_name
            FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
            LINES TERMINATED BY '\n'
eof;
        $command = Yii::$app->db->createCommand($sql);
        $command->execute();

        unlink($source_path);
    }
*/
    public function restoreBackup($filename){

        $zip = new \ZipArchive();
        $zip->open($this->path.$filename);
        $zip->extractTo($this->path);
        $zip->close();

        $table_name = 'temp_cdrs_'.substr($filename,0,strlen($filename)-4);

        $source_path = $this->path.substr($filename,0,strlen($filename)-4).'.csv';

        if (($handle = fopen($source_path, 'r')) !== false){

            $this->createTable($table_name);
            $counter = 1;
            $rows_to_insert = array();

            while ($data = fgetcsv($handle, 1000, ",")){
                $num = count($data);

                $row_to_insert = array();
                for($i = 0; $i < $num; $i++){
                    $row_to_insert[] = "'".$data[$i]."'";
                }

                $rows_to_insert[] = "(".implode($row_to_insert,",").")";

                $counter++;

               if ($counter % 10000 == 0){

                    $sql = "INSERT INTO $table_name VALUES ".implode($rows_to_insert,",");
                    Yii::$app->db->createCommand($sql)->execute();
                    unset($sql);
                    $rows_to_insert = array();
                }

            }

            $sql = "INSERT INTO $table_name VALUES ".implode($rows_to_insert,",");
            Yii::$app->db->createCommand($sql)->execute();

            fclose($handle);
        }

        unlink($source_path);
    }

    public function createTable($table_name){
        $sql = <<<eof
            DROP TABLE IF EXISTS $table_name;
            CREATE TABLE $table_name (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `site_id` SMALLINT(2) UNSIGNED NOT NULL DEFAULT '0',
                `user_id` INT(11) NOT NULL,
                `service_id` INT(11) NOT NULL,
                `callid` VARCHAR(255) NOT NULL DEFAULT '',
                `period` VARCHAR(10) NOT NULL,
                `direction` TINYINT(1) NULL DEFAULT NULL,
                `src` VARCHAR(255) NULL DEFAULT NULL,
                `dst` VARCHAR(255) NULL DEFAULT NULL,
                `destination` VARCHAR(60) NULL DEFAULT NULL,
                `started_at` DATETIME NULL DEFAULT NULL,
                `duration` INT(11) NOT NULL,
                `bill_sec` INT(11) NOT NULL,
                `rate` INT(11) NULL DEFAULT NULL,
                `bill_amount` INT(11) NULL DEFAULT NULL,
                `connection_charge` INT(11) NULL DEFAULT NULL,
                `commodity_id` INT(11) NULL DEFAULT NULL,
                `commodity_scu` INT(11) NULL DEFAULT NULL,
                `status` TINYINT(1) NULL DEFAULT NULL,
                `tbd` VARCHAR(20) NULL DEFAULT NULL,
                `clir` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                `reason` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                INDEX `idx_user` (`user_id`),
                INDEX `idx_service` (`service_id`),
                INDEX `idx_period` (`period`),
                INDEX `started_at` (`started_at`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
eof;

        $command = Yii::$app->db->createCommand($sql);
        $command->execute();
    }

    public function rules (){

        return
        [
            [['start_date', 'end_date'], 'required'],
            ['end_date', 'validateDates'],
            ['toDelete', 'boolean'],
        ];
    }

    public function validateDates(){
        if(strtotime($this->end_date) < strtotime($this->start_date)){
            $this->addError('start_date','Start date can not be after end date');
            $this->addError('end_date','End date can not be before start date');
        }

        if(strtotime($this->end_date) > strtotime($this->start_date.' + 180 days')){
            //$this->addError('start_date','Start date can not be after end date');
            $this->addError('end_date','End date must be within 6 months from start date');
        }
    }

    public function attributeLabels()
    {

        return [
            'start_date'    => 'Start date',
            'end_date'      => 'End date',
            'toDelete'     => 'delete CDRS after backup'
        ];
    }

}