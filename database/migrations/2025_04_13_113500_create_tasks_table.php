<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('employee_id')->constrained()->onDelete('cascade');
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('duedate')->nullable();
            $table->boolean('is_done')->default(false);
            $table->string('document')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
