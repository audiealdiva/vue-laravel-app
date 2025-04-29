<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTitleToNameInProjectsTable extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('title', 'name');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('name', 'title');
        });
    }
}
