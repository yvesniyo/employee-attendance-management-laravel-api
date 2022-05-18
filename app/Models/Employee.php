<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema()
 */

class Employee extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory, CausesActivity, LogsActivity;
    use HasApiTokens;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'code',
        'national_id',
        'phone',
        'dob',
        'status',
        'position',
        'password',
    ];


    protected $casts = [
        "reset_code_expires_in" => "datetime",
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        "reset_code",
        "reset_code_expires_in"
    ];


    // /**
    //  * The employee code
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $code;

    // /**
    //  * The employee name
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $name;


    // /**
    //  * The employee email
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $email;

    // /**
    //  * The employee national_id
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $national_id;


    // /**
    //  * The employee phone
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $phone;


    // /**
    //  * The employee date of birth
    //  * @var date
    //  * @OA\Property()
    //  */
    // public string $dob;


    // /**
    //  * The employee status
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $status;

    // /**
    //  * The employee position
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $position;


    // /**
    //  * The employee create date
    //  * @var string
    //  * @OA\Property()
    //  */
    // public string $created_at;



    public function scopeManager($query)
    {
        $query->where("position", "MANAGER");
    }

    public function scopeNotManager($query)
    {
        $query->where("position", "<>", "MANAGER");
    }


    public function scopeActive($query)
    {
        $query->where("status", "ACTIVE");
    }

    public function scopeSuspended($query)
    {
        $query->where("status", "<>", "ACTIVE");
    }

    public function scopeCode($query, $code)
    {
        $query->whereCode($code);
    }


    public function isManager()
    {
        return $this->position == "MANAGER";
    }


    public function isActive()
    {
        return $this->status == "ACTIVE";
    }


    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function todayAttendance()
    {
        return $this->hasOne(Attendance::class)->whereDate("arrived_at", now());
    }
}
