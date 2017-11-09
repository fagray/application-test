<?php
namespace DTApi\UseCases;

use Carbon\Carbon;

class NewBookingHasBeenRequested {

    public function __construct()
    {
        $this->handle();
    }

    protected function handle()
    {
        $immediatetime = 5;
        $consumer_type = $user->userMeta->consumer_type;
            $cuser = $user;
        // default response message
        $response['message'] = "Du måste fylla in alla fält";
        if (!isset($data['from_language_id'])) {
            $response['status'] = 'fail';
            $response['field_name'] = "from_language_id";
            return $response;
        }
        
        if (isset($data['duration']) && $data['duration'] == '') {
                $response['status'] = 'fail';
                $response['field_name'] = "duration";
                return $response;
        }
        if ($data['immediate'] == 'no') {
            if (isset($data['due_date']) && $data['due_date'] == '') {
                $response['status'] = 'fail';
                $response['field_name'] = "due_date";
                return $response;
            }
            if (isset($data['due_time']) && $data['due_time'] == '') {
                $response['status'] = 'fail';
                $response['field_name'] = "due_time";
                return $response;
            }
            if (!isset($data['customer_phone_type']) && !isset($data['customer_physical_type'])) {
                $response['status'] = 'fail';
                $response['message'] = "Du måste göra ett val här";
                $response['field_name'] = "customer_phone_type";
                return $response;
            }
        } 

        $data['customer_phone_type'] = 'no';
        $data['customer_physical_type'] = 'no';
        $response['customer_physical_type'] = 'no';

        if (isset($data['customer_phone_type'])) {
            $data['customer_phone_type'] = 'yes';
        }

        if (isset($data['customer_physical_type'])) {
            $data['customer_physical_type'] = 'yes';
            $response['customer_physical_type'] = 'yes';
        } 

        $due_carbon = Carbon::now()->addMinute($immediatetime);
        $data['due'] = $due_carbon->format('Y-m-d H:i:s');
        $data['immediate'] = 'yes';
        $data['customer_phone_type'] = 'yes';
        $response['type'] = 'immediate';

        if ( ! $data['immediate'] == 'yes') {
            $due = $data['due_date'] . " " . $data['due_time'];
            $response['type'] = 'regular';
            $due_carbon = Carbon::createFromFormat('m/d/Y H:i', $due);
            $data['due'] = $due_carbon->format('Y-m-d H:i:s');
            if ($due_carbon->isPast()) {
                $response['status'] = 'fail';
                $response['message'] = "Can't create booking in past";
                return $response;
            }
        } 
        if (in_array('male', $data['job_for'])) {
            $data['gender'] = 'male';
        } else if (in_array('female', $data['job_for'])) {
            $data['gender'] = 'female';
        }
        if (in_array('normal', $data['job_for'])) {
            $data['certified'] = 'normal';
        }
        else if (in_array('certified', $data['job_for'])) {
            $data['certified'] = 'yes';
        } else if (in_array('certified_in_law', $data['job_for'])) {
            $data['certified'] = 'law';
        } else if (in_array('certified_in_helth', $data['job_for'])) {
            $data['certified'] = 'health';
        }
        if (in_array('normal', $data['job_for']) && in_array('certified', $data['job_for'])) {
            $data['certified'] = 'both';
        }
        else if(in_array('normal', $data['job_for']) && in_array('certified_in_law', $data['job_for']))
        {
            $data['certified'] = 'n_law';
        }
        else if(in_array('normal', $data['job_for']) && in_array('certified_in_helth', $data['job_for']))
        {
            $data['certified'] = 'n_health';
        }
        if ($consumer_type == 'rwsconsumer')
            $data['job_type'] = 'rws';
        else if ($consumer_type == 'ngo')
            $data['job_type'] = 'unpaid';
        else if ($consumer_type == 'paid')
            $data['job_type'] = 'paid';
        $data['b_created_at'] = date('Y-m-d H:i:s');
        if (isset($due))
            $data['will_expire_at'] = TeHelper::willExpireAt($due, $data['b_created_at']);
        $data['by_admin'] = isset($data['by_admin']) ? $data['by_admin'] : 'no';

        $job = $cuser->jobs()->create($data);

        $response['status'] = 'success';
        $response['id'] = $job->id;
        $data['job_for'] = array();
        if ($job->gender != null) {
            if ($job->gender == 'male') {
                $data['job_for'][] = 'Man';
            } else if ($job->gender == 'female') {
                $data['job_for'][] = 'Kvinna';
            }
        }
        if ($job->certified != null) {
            if ($job->certified == 'both') {
                $data['job_for'][] = 'normal';
                $data['job_for'][] = 'certified';
            } else if ($job->certified == 'yes') {
                $data['job_for'][] = 'certified';
            } else {
                $data['job_for'][] = $job->certified;
            }
        }

        $data['customer_town'] = $cuser->userMeta->city;
        $data['customer_type'] = $cuser->userMeta->customer_type;

        return $data;
    }

        
}