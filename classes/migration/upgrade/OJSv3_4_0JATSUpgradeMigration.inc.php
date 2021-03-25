<?php

/**
 * @file classes/migration/upgrade/OJSv3_4_0JATSUpgradeMigration.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OJSv3_4_0JATSUpgradeMigration
 * @brief Add database tables for JATS operations
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class OJSv3_4_0JATSUpgradeMigration extends Migration {
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up() {
		Capsule::schema()->create('publication_jats_changes', function (Blueprint $table) {
			$table->bigInteger('change_id')->autoIncrement();
			$table->bigInteger('file_id');
			$table->bigInteger('publication_id');
			$table->bigInteger('user_id');
			$table->text('change');
			$table->boolean('applied');
			$table->datetime('created_at');
			$table->foreign('file_id')->references('file_id')->on('files');
			$table->foreign('publication_id')->references('publication_id')->on('publications');

		});
	}

	/**
	 * Reverse the downgrades
	 * @return void
	 */
	public function down() {
		Capsule::schema()->drop('publication_jats_changes');
	}
}
