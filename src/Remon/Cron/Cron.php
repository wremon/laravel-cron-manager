<?php 
namespace Remon\Cron;

use CrontabJob;
use CrontabRepository;
use CrontabAdapter;
use CronJob;

class Cron {
	protected static $crontab;
	protected static $job;

	public function __construct()
    {
		self::$crontab = new CrontabRepository(new CrontabAdapter());
		self::$job = new CrontabJob();
	}

	/**
	 * Add new cron job
	 */
	public static function add($minutes, $hours, $day_of_month, $months, $day_of_week, $command, $enabled = TRUE, $comment)
	{
		if (self::exists(compact(['minutes', 'hours', 'day_of_month', 'months', 'day_of_week', 'command', 'enabled', 'comment']), $command))
		{
			echo 'Exists... aborting';
			return false;
		}

		$id = self::record(NULL, compact(['minutes', 'hours', 'day_of_month', 'months', 'day_of_week', 'command', 'enabled', 'comment']));

		self::getAttrs(self::$job, compact(['minutes', 'hours', 'day_of_month', 'months', 'day_of_week', 'command', 'enabled', 'id']));

		self::$crontab->addJob(self::$job);

		if (!self::$crontab->save())
		{
			echo 'Error saving job';
			return FALSE;
		}

		echo 'Job saved';
		return TRUE;
	}

	/**
	 * Update cron job
	 */
	public static function update($id, $params)
	{
		if(!$results = self::$crontab->find($id))
		{
			echo "No job found";
			return FALSE;
		}

		if (self::exists(array_merge(self::getRecord($id), $params), $id))
		{
			echo 'Exists... aborting';
			return FALSE;
		}

		self::record($id, $params);
	
		foreach ($results as $job)
		{
			self::getAttrs($job, $params);
		}

		self::$crontab->save();
		echo count($results)." Job(s) updated \r\n";

		return TRUE;
	}

	/**
	 * Delete cron job
	 */
	public static function delete($id)
	{
		if(!$results = self::$crontab->find($id))
		{
			echo "No job found \r\n";
			return FALSE;
		}

		foreach ($results as $job)
		{
			self::$crontab->removeJob($job);
		}

		self::$crontab->save();

		CronJob::find($id)->delete();

		echo count($results)." Job(s) deleted \r\n";

		return TRUE;
	}
	
	/**
	 * Check if a cronjob exists
	 */
	public static function has($id)
	{
		if (CronJob::find($id)) return TRUE;
	}

	/**
	 * Check if the command is already in use
	 *
	 * $params 	array of field and value
	 * $except 	id of record to exclude from query
	 */
	private static function exists($params, $except = FALSE)
	{
		$result = CronJob::where(function($query) use ($params, $except)
        {
        	foreach ($params as $key => $value)
        	{
        		$query->where($key, $value);
        	}

        	if ($except)
        	{
        		$query->whereNotIn('id', [$except]);
        	}
        })->first();

        if ($result)
        {
        	return TRUE;
        }
	}

	/**
	 * Set attributes for a job
	 */
	private static function getAttrs(&$job, $attr)
	{
		foreach ($attr as $key => $value)
		{
			if (property_exists($job, $key))
			{
				$job->{$key} = $value;
			}
		}
	}

	/**
	 * Save record to DB
	 */
	private static function record($id, $attr)
	{
		$record = CronJob::findOrNew($id);

		foreach ($attr as $key => $value)
		{
			$record->{$key} = $value;
		}
		$record->save();
		return $record->id;
	}

	/**
	 * Get the schedule and command
	 */
	private static function getRecord($id)
	{
		if ($result = CronJob::find($id))
		{
			return array_except($result["attributes"], ['id', 'comment', 'deleted_at', 'created_at', 'updated_at']);
		}
	}
}
