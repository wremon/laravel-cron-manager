<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cron_jobs', function($table)
		{
			$table->increments('id');
			$table->string('minutes');
			$table->string('hours');
			$table->string('day_of_month');
			$table->string('months');
			$table->string('day_of_week');
			$table->string('command');
			$table->char('enabled', 1)->default(1);
			$table->string('comment')->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cron_jobs');
	}

}
