<?php

/*
 * Copyright 2013 Benjamin Legendre
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


/**
 * CrontabJob
 * Represent a Job of the crontab.
 * 
 * @author Benjamin Legendre
 */
class CrontabJob
{
    /**
     * Tell whether the cron job is enabled or not
     * This will add or not a # at the beginning of the cron line
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * Min (0 - 59)
     *
     * @var String/int
     *
     */
    public $minutes;
    
    /**
     * Hour (0 - 23)
     *
     * @var String/int
     */
    public $hours;
    
    /**
     * Day of month (1 - 31)
     *
     * @var String/int
     */
    public $day_of_month;
    
    /**
     * Month (1 - 12)
     *
     * @var String/int
     */
    public $months;
    
    /**
     * Day of week (0 - 6) (0 or 6 are Sunday to Saturday, or use names)
     *
     * @var String/int
     */
    public $day_of_week;

    /**
     * The task command line to be executed 
     *
     * @var String
     */
    public $command;
    
    /**
     * Optional comment that will be placed at the end of the crontab line 
     * and preceded by a #
     *
     * @var String
     */
    public $id;
    
    /**
     * Predefined scheduling definition
     * Shorcut définition that replace standard définition (preceded by @)
     * possibles values : yearly, monthly, weekly, daily, hourly, reboot
     * When a shortcut is defined, it overwrite stantard définition
     *
     * @var String
     */
    public $shortCut;
    
    /**
     * Factory method to create a CrontabJob from a crontab line.
     *
     * @param String $crontabLine
     * @throws InvalidArgumentException
     * @return CrontabJob
     */
    public static function createFromCrontabLine($crontabLine)
    {
        // Check crontab line format validity
        $crontabLineRegex = '/^[\s\t]*(#)?[\s\t]*(([*0-9,-\/]+)[\s\t]+([*0-9,-\/]+)'
            . '[\s\t]+([*0-9,-\/]+)[\s\t]+([*a-z0-9,-\/]+)[\s\t]+([*a-z0-9,-\/]+)|'
            . '(@(reboot|yearly|annually|monthly|weekly|daily|midnight|hourly)))'
            . '[\s\t]+([^#]+)([\s\t]+#(.+))?$/'
        ;

        if (!preg_match($crontabLineRegex, $crontabLine, $matches)) 
        {
            throw new \InvalidArgumentException(
                'Crontab line not well formated then can\'t be parsed'
            );
        }

        // Create the job from parsed crontab line values
        $crontabJob = new self();
      
        if (!empty($matches[1])) 
        {
            $crontabJob->enabled = false;
        }

        if (!empty($matches)) 
        {
            $crontabJob->minutes = $matches[3];
            $crontabJob->hours = $matches[4];
            $crontabJob->day_of_month = $matches[5];
            $crontabJob->months = $matches[6];
            $crontabJob->day_of_week = $matches[7];
        }
        
        if (!empty($matches[8])) 
        {
            $crontabJob->shortCut = $matches[9];
        }
        
        $crontabJob->command = $matches[10];
        if (!empty($matches[12])) 
        {
            $crontabJob->id = $matches[12];
        }
        
        return $crontabJob;
    }
    
    /**
     * Format the CrontabJob to a crontab line 
     *
     * @throws InvalidArgumentException
     * @return String
     */
    public function formatCrontabLine()
    {
        
        // Check if job has a task command line
        if (!isset($this->command) || empty($this->command)) 
        {
            throw new \InvalidArgumentException(
                'CrontabJob contain\'s no task command line'
            );
        }
        
        $taskPlanningNotation = (isset($this->shortCut) && !empty($this->shortCut))
            ? sprintf('@%s', $this->shortCut)
            : sprintf(
                '%s %s %s %s %s',
                (isset($this->minutes) ? $this->minutes : '*'),
                (isset($this->hours) ? $this->hours : '*'),
                (isset($this->day_of_month) ? $this->day_of_month : '*'),
                (isset($this->months) ? $this->months : '*'),
                (isset($this->day_of_week) ? $this->day_of_week : '*')
            )
        ;
        
        return sprintf(
            '%s%s %s%s',
            ($this->enabled ? '' : '#'),
            $taskPlanningNotation,
            $this->command,
            (isset($this->id) ? (' #' . $this->id) : '')
        );
    }
}
