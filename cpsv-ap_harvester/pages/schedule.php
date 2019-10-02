<?php

header('Access-Control-Allow-Origin: *');

/*echo exec('crontab -r');
$output = shell_exec('crontab -l');
file_put_contents('/tmp/crontab.txt','/cpsv-ap_harvester/pages/clear.php'.PHP_EOL);
echo exec('crontab /tmp/crontab.txt');
*/
class Crontab {
    
    // In this class, array instead of string would be the standard input / output format.
    
    // Legacy way to add a job:
    // $output = shell_exec('(crontab -l; echo "'.$job.'") | crontab -');
    
    static private function stringToArray($jobs = '') {
        $array = explode("\r\n", trim($jobs)); // trim() gets rid of the last \r\n
        foreach ($array as $key => $item) {
            if ($item == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }
    
    static private function arrayToString($jobs = array()) {
        $string = implode("\r\n", $jobs);
        return $string;
    }
    
    static public function getJobs() {
        $output = shell_exec('crontab -l');
        return self::stringToArray($output);
    }
    
    static public function saveJobs($jobs = array()) {
        $output = shell_exec('echo "'.self::arrayToString($jobs).'" | crontab -');
        return $output;	
    }
    
    static public function doesJobExist($job = '') {
        $jobs = self::getJobs();
        if (in_array($job, $jobs)) {
            return true;
        } else {
            return false;
        }
    }
    
    static public function addJob($job = '') {
        if (self::doesJobExist($job)) {
            return false;
        } else {
            $jobs = self::getJobs();
            $jobs[] = $job;
            return self::saveJobs($jobs);
        }
    }
    
    static public function removeJob($job = '') {
        if (self::doesJobExist($job)) {
            $jobs = self::getJobs();
            unset($jobs[array_search($job, $jobs)]);
            return self::saveJobs($jobs);
        } else {
            return false;
        }
    }
    
}

// get cronExpression
$cronExpression = $_POST['cronExpression'];

$crontab = new Crontab();

// delete current job
$currentJobs = $crontab->getJobs();
$arrlength = count($currentJobs);
for($x = 0; $x < $arrlength; $x++) {
    
    if ( substr_count($currentJobs[$x], " bash /var/www/html/cpsv-ap_harvester/pages/schedule.sh") > 0 ) {
        $currentJob = $currentJobs[$x];
        $crontab->removeJob($currentJob);
    } else {

    }
}

// add new job
$crontab->addJob($cronExpression . " bash /var/www/html/cpsv-ap_harvester/pages/schedule.sh");

?>
