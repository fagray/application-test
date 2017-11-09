<?php 
namespace DTApi\Repository\Contracts;

interface BookingRepositoryInterface {

	// it must be injected to controllers who badly need an 
	// access to this repository
	// we would not give a hint to controllers whats going on
	// behind the scene when it consumes a function into 
	// this dedicated repository


	// it must contain a quick view of booking repository class
	// its more likely a "cheat sheet" of the dedicated repository
	public function initializeAndBootAdminLogger();
	public function giveMeJobsOf($cuser)
	public function segregateJobs()
	public function getUsersJobs($user_id)
	public function getJobHistoryOf($cuser,$results_per_page = 15)
	public function getUsersJobsHistory($user_id, Request $request)
	public function store($user, $data)
	public function storeJobEmail($data)
	public function composeDataForSendingPushNotification($job)
	public function jobToData($job)
	public function jobEnd($post_data = array())
	public function getPotentialJobIdsWithUserId($user_id)
	public function sendNotificationTranslator($job, $data = [], $exclude_user_id)
	public function sendSMSNotificationToTranslator($job)
	public function isNeedToDelayPush($user_id)
	public function isNeedToSendPush($user_id)
	public function sendPushNotificationToSpecificUsers($users, $job_id, $data, $msg_text, $is_need_delay)
	public function getPotentialTranslators(Job $job)
	public function updateJob($id, $data, $cuser)
	private function changeStatus($job, $data, $changedTranslator)
	private function changeTimedoutStatus($job, $data, $changedTranslator)
	private function changeCompletedStatus($job, $data)
	private function changeStartedStatus($job, $data)
	private function changePendingStatus($job, $data, $changedTranslator)
	public function sendSessionStartRemindNotification($user, $job, $language, $due, $duration)
	private function changeWithdrawafter24Status($job, $data)
	private function changeAssignedStatus($job, $data)
	private function changeTranslator($current_translator, $data, $job)
	private function changeDue($old_due, $new_due)
	public function sendChangedTranslatorNotification($job, $current_translator, $new_translator)
	public function sendChangedDateNotification($job, $old_time)
	public function sendChangedLangNotification($job, $old_lang)
	public function sendExpiredNotification($job, $user)
	                      /// the rest follows
}