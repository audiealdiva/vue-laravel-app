<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeProjectTable extends Migration
{
    public function up()
    {
        Schema::create('employee_project', function (Blueprint $table) {
            $table->id();
            $table->uuid('employee_id');
            $table->uuid('project_id');
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unique(['employee_id', 'project_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_project');
    }
}

