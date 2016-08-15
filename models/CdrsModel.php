<?php

namespace app\models;

use Yii;
use yii\base\Model;


class CdrsModel extends Model {

    public $start_date;
    public $end_date;
    public $to_delete;

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

}