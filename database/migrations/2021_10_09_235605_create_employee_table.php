<?php

use App\Models\Employee;
use App\Services\CodeGenerator;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string("code", 7)->unique();
            $table->string("name");
            $table->string("national_id", 16)->unique();
            $table->string("phone", 13)->unique()->nullable();
            $table->string("email")->unique();
            $table->text("password")->nullable();
            $table->date("dob");
            $table->enum("status", ["ACTIVE", "INACTIVE"]);
            $table->enum("position", ["MANAGER", "DEVELOPER", "DESIGNER", "TESTER", "DEVOPS"]);
            $table->timestamps();
        });


        DB::table("employees")->insert([
            "code" => CodeGenerator::EMPLOYEE(),
            "national_id" => CodeGenerator::NATIONAL_ID(),
            "name" => "Manager",
            "email" => "admin@example.com",
            "status" => "ACTIVE",
            "position" => "MANAGER",
            "password" => bcrypt("password"),
            "dob" => Carbon::now()->subYears(20)
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
