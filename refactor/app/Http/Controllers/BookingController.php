<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        // Issues Found
        // $user_id was not defined
        // Assignment operator was used in IF statement instead of a Logical Operator
        // $response was not declared outside of the conditional statements and it was being returned outside the conditional statements
        // Response data is being returned in 2 different forms.
        // No exception handling was implemented
        // Instead of storing ADMIN_ROLE_ID and SUPERADMIN_ROLE_ID in env file, they should be defined in User model

        try{
            $user = Auth::user();

            if($user->isAdmin() || $user->isSuperAdmin()){
                return response($this->repository->getAll($user));
            }
            else{
                return response($this->repository->getUsersJobs($user->id));
            }
        }
        catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        // ISSUES FOUND
        // There was no exception handling for errors
        // The returned reponse was not properly handled

        try{
            $job = $this->repository->with('translatorJobRel.user')->find($id);

            if($job){
                return response()->json(['job'=>$job], 200);
            }
            else{
                return response()->json(['job'=>'No job found'], 404);
            }
        }
        catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        // ISSUES FOUND
        // No request validation was implemented
        // There was no exception handling for errors
        // The returned reponse was not properly handled

        try{
 
            $user = Auth::user();

            $data = $request->validate([
                'from_language_id' => 'required',
                'immediate' => 'required',
                'due_date' => 'required_if:immediate,no',
                'due_time' => 'required_if:immediate,no',
                'customer_phone_type' => 'required_if:immediate,no',
                'duration' => 'required',

                
            ]);

            $response = $this->repository->store($user, $data);

            return response()->json(['message' => 'Record Added Successfully', 'response' => $response], 200);
        }
        catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        // ISSUES FOUND
        // update function parameters order
        // No request validation was implemented
        // There was no exception handling for errors
        // The returned reponse was not properly handled
        // $cuser was not the appropriate variable name
        // php's array_except was used

        try{
 
            $user = Auth::user();

            $data = $request->validate([
                // Validation rules for the request data
            ]);
            $data = $request->except(['_token', 'submit']);

            $response = $this->repository->updateJob($id, $data, $user);

            return response()->json(['message' => 'Record Updated Successfully', 'response' => $response], 200);
        }
        catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        // ISSUES FOUND
        // $adminSenderEmail was declared but used anywhere
        // There was no exception handling for errors
        // The returned reponse was not properly handled
        
        try {
            $data = $request->validate([
                // Define validation rules for the request data
            ]);
    
            $response = $this->repository->storeJobEmail($data);
    
            return response()->json(['message' => 'Email sent successfully'], 200);
        } 
        catch (\Exception $e) {
            return response()->json(['error' =>  $e->getMessage()], 500);
        }
        
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        // Issues Found
        // $user_id was not defined
        // Assignment operator was used in IF statement instead of a Logical Operator
        // The returned reponse was not properly handled
        // Exception handling was not implemented in case of errors

        try {
            $userId = $request->get('user_id');
    
            if ($userId) {
                $response = $this->repository->getUsersJobsHistory($userId, $request);
                return response()->json(['response' => $response], 200);
            } 
            else {
                return response()->json(['error' => 'Provided a valid user id'], 400);
            }
        } 
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        // Issues Found
        // Authenticated user was not properly accessed
        // The returned reponse was not properly handled
        // Exception handling was not implemented in case of errors

        try {
            $user = Auth::user();
            $data = $request->all();
    
            $response = $this->repository->acceptJob($data, $user);
    
            return response()->json(['message' => 'Job accepted successfully', 'response', $response], 200);
        } 
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function acceptJobWithId(Request $request)
    {
        // Issues Found
        // Authenticated user was not properly accessed
        // The returned reponse was not properly handled
        // Exception handling was not implemented in case of errors
        // Variable name $data was appropriate for job_id

        try {
            $jobId = $request->input('job_id');
            $user = Auth::user();
    
            $response = $this->repository->acceptJobWithId($jobId, $user);
    
            return response()->json(['message' => 'Job accepted successfully', 'response' => $response], 200);
        } 
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        // Issues Found
        // Authenticated user was not properly accessed
        // The returned reponse was not properly handled
        // Exception handling was not implemented in case of errors

        try {
            $data = $request->all();
            $user = Auth::user();
    
            $response = $this->repository->cancelJobAjax($data, $user);
    
            return response()->json(['message' => 'Job has been canceled', 'response' => $response], 200);
        } 
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        // Issues Found
        // The returned reponse was not properly handled
        // Exception handling was not implemented in case of errors

        try {
            $data = $request->all();
    
            $response = $this->repository->endJob($data);
    
            return response()->json(['response' => $response], 200);
        } 
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function customerNotCall(Request $request)
    {
        // Issues Found
        // The returned reponse was not properly handled
        // Exception handling was not implemented in case of errors
        // Inappropriate function name        

        try {
            $data = $request->all();
    
            $response = $this->repository->customerNotCall($data);
    
            return response()->json(['response' => $response], 200);
        } 
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        // Issues Found
        // $data variable was declared but not used anywhere
        // Authenticated user was not properly accessed
        // The returned reponse was not properly handled
        // Exception handling was not implemented in case of errors

        try {
            $user = Auth::user();
    
            $jobs = $this->repository->getPotentialJobs($user);
    
            return response()->json(['jobs' => $jobs], 200);
        } 
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function distanceFeed(Request $request)
    {
        // ISSUES FOUND
        // Data was not validated
        // Unnecessary if-else statements
        // Boolean values were compared with string 'true' or 'false' instead of actual boolean true or false
        // $job_id is required for both queries at the end but $job_id was not a required field
        // There was no exeption handling
        // Inconsistent request variable (admincomment) and db field names (admin_comments)

        try {

            $validated = $request->validate([
                'distance' => 'string',
                'time' => 'string',
                'job_id' => 'required|exists:jobs,id',
                'session_time' => 'string',
                'flagged' => 'required|boolean',
                'manually_handled' => 'required|boolean',
                'by_admin' => 'required|boolean',
                'admin_comments' => 'required_if:flagged,true',
            ]);

            $updateDistanceData = [
                'distance' => $validated['distance'] ?? '',
                'time' => $validated['time'] ?? '',
            ];

            $updateJobData = [
                'admin_comments' => $validated['admin_comments'] ?? '',
                'flagged' => $validated['flagged'] ? 'yes' : 'no',
                'session_time' => $validated['session_time'] ?? '',
                'manually_handled' => $validated['manually_handled'] ? 'yes' : 'no',
                'by_admin' => $validated['by_admin'] ? 'yes' : 'no',
            ];
    
            Distance::where('job_id', $validated['job_id'])->update($updateDistanceData);
            Job::where('id', $validated['job_id'])->update($updateJobData);
            
            return response()->json(['message' => 'Record has been updated!'], 200);
        } 
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function reopen(Request $request)
    {
        // Issues Found
        // The returned reponse was not properly handled
        // Exception handling was not implemented in case of errors

        try {
            $data = $request->all();
    
            $response = $this->repository->reopen($data);
    
            return response()->json(['response' => $response], 200);
        } 
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function resendNotifications(Request $request)
    {
        // Issues Found
        // The returned reponse was not properly handled
        // Exception handling was not implemented in case of errors

        try {
            $data = $request->all();
    
            $job = $this->repository->find($data['job_id']);

            $jobData = $this->repository->jobToData($job);
            $this->repository->sendNotificationTranslator($job, $jobData, '*');
    
            return response()->json(['success'=>true, 'message' => 'Notification has been sent'], 200);
        } 
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }


        // $data = $request->all();
        // $job = $this->repository->find($data['jobid']);
        // $job_data = $this->repository->jobToData($job);
        // $this->repository->sendNotificationTranslator($job, $job_data, '*');

        // return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        // Issues Found
        // In response success was being sent for both success and error response

        try {
            $data = $request->all();
            $job = $this->repository->find($data['job_id']);
            
            $this->repository->jobToData($job);
            $this->repository->sendSMSNotificationToTranslator($job);

            return response(['success' => true, 'message'=> 'SMS has been sent']);
        } 
        catch (\Exception $e) {
            return response(['error' => $e->getMessage()]);
        }
    }

}
