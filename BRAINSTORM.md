# lzq

## Functionality

 - Queue jobs
   - To run now
   - To run later
   - To run after other jobs complete
   - To run after other criteria are met

 - Run jobs
   - As quickly and fairly as possible
   - Across multiple servers
   - Without using more resources than are available

## Usage

### Queueing a Job
~~~php
Q::push(
    $job_name = 'job_to_run',
                // 'silex_service'
                // 'Some\Class\Name'
                // 'silex_service#methodName'
                // 'Some\Class\Name#methodName'
    $job_params = array(
        'param1' => 'value1',
        'param2' => 'value2',
    ),
    $job_options = array(
         'queue_name' => 'default',
         'start_time' => new DateTime(),
                         // '+1 hour'
                         // '2014-07-20 00:15:00'
                         // 'now'
         'recur' => array(
              // how often to run the job
              'interval' => '12:00:00',
              // optional, will use the last run time
              // without a base_time
              'base_time' => '2014-07-20 03:00:00',
              // a name to refer to the job by
              'known_as' => 'job_to_run:every_12_hours',
         ),
         'depends_on' => array(
             12,         // job id
             $job,       // job object
             '#isReadyToRun',
             'Some\Other\Class#isReadyToRun',
             'silex_service#isReadyToRun',
         ),
         'priority'     => 10,
                           // use `nice`'s semantics:
                           // - lower numbers run sooner
                           // - allow -20 to 20
         'max_failures' => 3,
    )
);

// other $job_name possibility
$job_name = array(
    'type'   => 'pimple',
                // 'silex' - same as 'pimple'
                // 'class'
    'name'   => 'silex_service',
    'method' => 'methodName',
);
~~~

### Scheduling a Future Job
~~~php
Q::schedule(
    $job_name = 'job_to_run',
                // 'silex_service'
                // 'Some\Class\Name'
                // 'silex_service#methodName'
                // 'Some\Class\Name#methodName'
    $job_params = array(
        'param1' => 'value1',
        'param2' => 'value2',
    ),
    $start_time = new DateTime(),
                  // '+1 hour'
                  // '2014-07-20 00:15:00'
                  // 'now'
    $job_options = array(
         'queue_name' => 'default',
         'depends_on' => array(
             12,         // job id
             $job,       // job object
             '#isReadyToRun',
             'Some\Other\Class#isReadyToRun',
             'silex_service#isReadyToRun',
         ),
         'priority'     => 10,
                           // use `nice`'s semantics:
                           // - lower numbers run sooner
                           // - allow -20 to 20
         'max_failures' => 3,
    )
);
~~~

### Scheduling a Recurring Job
~~~php
Q::recur(
    $job_name = 'job_to_run',
                // 'silex_service'
                // 'Some\Class\Name'
                // 'silex_service#methodName'
                // 'Some\Class\Name#methodName'
    $job_params = array(
        'param1' => 'value1',
        'param2' => 'value2',
    ),
    // a name to refer to the job by
    $known_as = 'job_to_run:every_12_hours',
    // how often to run the job
    $interval = '12:00:00',
    // optional, will use the last run time
    // without a base_time
    $base_time = '2014-07-20 03:00:00',
    $job_options = array(
         'queue_name' => 'default',
         'depends_on' => array(
             12,         // job id
             $job,       // job object
             '#isReadyToRun',
             'Some\Other\Class#isReadyToRun',
             'silex_service#isReadyToRun',
         ),
         'priority'     => 10,
                           // use `nice`'s semantics:
                           // - lower numbers run sooner
                           // - allow -20 to 20
         'max_failures' => 3,
    )
);
~~~

### Handling a Job
~~~php
namespace Some\Class;

class Name extends Job
{
    public function run(array $job_params, array $job_options)
    {
        // do some stuff to process the job

        if ($something_went_wrong) {
            // something went wrong, but if there
            // are attempts remaining try again
            throw $this->retry();
        } elseif ($something_else_went_wrong) {
            // do not try again
            throw $this->fail();
        } elseif ($what_else_goes_wrong) {
            throw new Exception('same as $this->retry()');
        } elseif ($something_is_wrong_but_do_not_exit_yet) {
            $this->setStatus(self::STATUS_RETRY);
        } else {
            // success is assumed, otherwise
        }
    }

    public function isReadyToRun(array $job_params, array $job_options)
    {
         return $this->ready()
             // limit to 5 simultaneous jobs of `get_class($this)` jobs
             // per bucket name
             ->limitTo(
                 5,
                 array(
                     'bucket_name' => $job_params['bucket_name'],
                 )
             )
             // limit to 15 simultaneous jobs of `get_class($this)` jobs
             ->limitTo(15)
             ->check(function (array $job_params, array $job_options) {
                 return rand(1, 10) == 5;
             })
             // the chains are applied as an 'AND', but 'any()'
             // allows for this 'OR' that
             ->any(
                 $this->ready()
                     ->check(function (array $job_params, array $job_options) {
                         return rand(1, 3) == 1;
                     })
                     ->check(function (array $job_params, array $job_options) {
                         return rand(1, 3) == 2;
                     })
                 ,
                 $this->ready()
                     ->check(function (array $job_params, array $job_options) {
                         return rand(1, 10) == 3;
                     })
             )
         ;
    }
}
~~~

## Job Processing Flow

### Queued Job

Taken from experiences working with @zacharyrankin and @twenty7:

 - Application pushes job to buffer queue (RabbitMQ)
 - Job queue's buffer worker reads job from buffer queue (RabbitMQ)
 - Buffer queue puts job into database (PostgreSQL)
 - Superqueuer reads job from database (PostgreSQL)
 - Superqueuer pushes job to worker queue (RabbitMQ)
 - Job worker reads job from worker queue (RabbitMQ)
 - Job worker runs job

### Recurring Job

 - Recurring jobs are stored in PostgreSQL database
 - Scheduler worker reads job from schedule database (PostgreSQL)
 - Any jobs that are due to be ran are queued to buffer queue with recurring job
   ID
 - After job is complete, the worker will update the next run time for the
   recurring job ID
    - Is it possible for the job to successfully complete and be moved out of
      pending jobs but fail to be scheduled for the next run time?  How do we
      handle these failures?

 - Things to consider:
    - Should a job's next run time be an interval based on the start time or
      end time of the previous job? Ideas:
       - If there is a base time, all next run times should be an interval
         based on that time. So once a job finishes, the next run time would be
         the next available time that is a multiple of the defined interval plus
         the base start time.
       - If there is not a base time, all next run times should be the
         most recent completion time plus the interval. Using the start time
         instead would potentially cause problems since a job could take 5 hours
         to run and be ran at an interval of 1 hour, which would mean the job
         would be scheduled to immediately re-run.

### supervisor config

```
[program:program-name]
command=/path/to/process
process_name=%(program_name)s
numprocs=5
autostart=true
autorestart=true
startsecs=0
startretries=3
stopsignal=TERM
stopwaitsecs=10
redirect_stderr=false
stdout_logfile=/path/to/debug_log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=10
stderr_logfile=/path/to/error_log
stderr_logfile_maxbytes=10MB
stderr_logfile_backups=10
```
