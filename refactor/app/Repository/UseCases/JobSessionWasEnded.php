<?php
namespace DTApi\UseCases;

use DTApi\Models\Job;
use DTApi\Mailers\AppMailer;
use Event;
use DTApi\Events\SessionEnded;

class JobSessionWasEnded {

    protected $tasks = [
                            'calculateTimeOfCompletion',
                            'sendEmailStatingTheInvoice',
                            'markJobAsComplete',
                            'fireAnEventForTheCompletionOfSession',
                            'sendEmailToTranslator'
                    ];

    public function __construct($post_data)
    {
        $this->handle($post_data);
    }

    protected function handle($post_data)
    {
        $jobid = $post_data["job_id"];
        $job = Job::with('translatorJobRel')->find($jobid);
         // calculate time of completion
        $this->calculateTimeOfCompletion($job);
        // send email invoice 
       $this->sendEmailStatingTheInvoice($job);

       // mark job as complete 
       $this->markJobAsComplete($job);

        $tr = $job->translatorJobRel->where('completed_at', Null)->where('cancel_at', Null)->first();

        Event::fire(new SessionEnded($job, ($post_data['userid'] == $job->user_id) ? $tr->user_id : $job->user_id));

        // send email to translator
        $this->sendEmailToTranslator($tr);

        // update the translation job
        $tr->completed_at = $completeddate;
        $tr->completed_by = $post_data['userid'];
        $tr->save();
    }

    public function calculateTimeOfCompletion($job)
    {
        $completeddate = date('Y-m-d H:i:s');
        $duedate = $job->due;
        $start = date_create($duedate);
        $end = date_create($completeddate);
        $diff = date_diff($end, $start);
        $interval = $diff->h . ':' . $diff->i . ':' . $diff->s;
        return ['time_of_completion' => $completeddate,'interval' => $interval];
    }

    public function sendEmailStatingTheInvoice($job){

        $user = $job->user()->get()->first();
        if (!empty($job->user_email)) {
            $email = $job->user_email;
        } else {
            $email = $user->email;
        }
        
        $name = $user->name;
        $subject = 'Information om avslutad tolkning för bokningsnummer # ' . $job->id;
        $session_explode = explode(':', $job->session_time);
        $session_time = $session_explode[0] . ' tim ' . $session_explode[1] . ' min';
        $data = [
            'user'         => $user,
            'job'          => $job,
            'session_time' => $session_time,
            'for_text'     => 'faktura'
        ];
        $mailer = new AppMailer();
        $mailer->send($email, $name, $subject, 'emails.session-ended', $data);
    }


    public function markJobAsComplete($job){
        $job->end_at = date('Y-m-d H:i:s');
        $job->status = 'completed';
        $job->session_time = $interval;
        $job->save();
    }

    public function sendEmailToTranslator($translator){
        $user = $translator->user()->first();
        $email = $user->email;
        $name = $user->name;
        $subject = 'Information om avslutad tolkning för bokningsnummer # ' . $job->id;
        $data = [
            'user'         => $user,
            'job'          => $job,
            'session_time' => $session_time,
            'for_text'     => 'lön'
        ];
        $mailer = new AppMailer();
        $mailer->send($email, $name, $subject, 'emails.session-ended', $data);
    }

}