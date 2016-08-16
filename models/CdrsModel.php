<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\VarDumper;


class CdrsModel extends Model {

    public $start_date;
    public $end_date;
    public $to_delete;

    public function backup(){


$sql = <<<eof
SELECT * 
FROM cdrs 
WHERE started_at >= :start_date
HAVING DATE_ADD(started_at, INTERVAL duration SECOND) < :end_date
eof;
        $command = Yii::$app->db->createCommand($sql);
        $command->bindParam(':start_date', date('Y-m-d 00:00:00',strtotime($this->start_date)));
        $command->bindParam(':end_date', date('Y-m-d 00:00:00', strtotime($this->end_date)));
        $result = $command->queryAll();
        //VarDumper::dump($result);

        $path = Yii::getAlias('@app').'/backup/';
        $filename = date('dmY', strtotime($this->start_date)).'_'.date('dmY', strtotime($this->end_date));

        $f = fopen($path.$filename.'.csv', 'w');
        foreach ($result as $row){
            fputcsv($f, $row);
        }
        fclose($f);

        $zip = new \ZipArchive();
        $zip->open($path.$filename.'.zip', \ZIPARCHIVE::CREATE);
        $zip->addFile($path.$filename.'.csv', $filename.'.csv');
        $zip->close();
        //rename($path.'/'.$date.'.zip', $path.'/backups/'.$date.'.zip');
        unlink($path.$filename.'.csv');
    }

    public function rules (){

        return
        [
          [['start_date', 'end_date'], 'required'],
            //['end_date', 'compare', 'compareAttribute' => 'start_date', 'operator' => '>', 'enableClientValidation' => true],
            ['end_date', 'validateDates']
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
            'to_delete'     => 'delete CDRS after backup'
        ];
    }

}