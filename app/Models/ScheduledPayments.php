<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ScheduledPayments extends Model
{

    protected $table = 'scheduled_payments';

    protected $fillable = [
        'user_id',
        'payment_date',
        'amount',
        'transaction_id'
    ];

    public function getPaymentDateAttribute($date){
        return \DateTime::createFromFormat('Y-m-d',$date);
    }


    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function doPayment(){
        try {
            $result=\ChargeBee_Invoice::charge([
                "customerId" => $this->user->chargebee_id,
                "amount" => $this->amount*100,
                "description" => "Baselang Scheduled Payment, reference: ".$this->id,
                "dateFrom"=>gmdate('U'),
                "dateTo"=>gmdate('U',strtotime("+5 days"))
            ]);
            $this->transaction_id=$result->invoice()->id;
            $this->save();
            Error::reportInfo('Transaction created, invoice: '.$this->transaction_id);
        } catch (\Exception $e){
            Error::reportError('Error generating charge',$e->getLine(),$e->getMessage());
        }

    }

}