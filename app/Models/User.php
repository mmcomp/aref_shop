<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'avatar_path',
        'referrer_users_id',
        'saver_users_id',
        'pass_txt',
        'address',
        'postall',
        'cities_id',
        'groups_id',
        'national_code',
        'gender',
        'home_tell',
        'father_tell',
        'mother_tell',
        'grade',
        'description',
        'reading_station_id',
        'is_reading_station_user',
        'disabled',
        'school_id',
        'major',
        'average_grade',
        'konkur_year',
        'consultant_name',
    ];
    protected $hidden=[
        'pass_txt',
        'password',
        'groups_id'
    ];
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'groups_id')->where('is_deleted', false);
    }

    public function city()
    {
        return $this->hasOne('App\Models\City', 'id', 'cities_id')->where('is_deleted', false);
    }
    public function referrerUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'referrer_users_id')->select('id', 'email', 'first_name', 'last_name')->where('is_deleted', false);
    }
    public function saverUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'saver_users_id')->select('id', 'email', 'first_name', 'last_name')->where('is_deleted', false);
    }
    public function usersyncs()
    {
        return $this->hasMany('App\Models\UserSync', 'users_id', 'id');
    }
    public function orderDetail()
    {
        return $this->hasOne('App\Models\OrderDetail', 'users_id', 'id');
    }
    public function teamUser()
    {
       return $this->belongsTo('App\Models\TeamUser','id','user_id_creator');
    }
    public function menus()
    {
        $groupMenus = $this->group()->first()->menus()->with('menu')->get();
        $menus = [];
        $menuIndex = [];
        foreach ($groupMenus as $groupMenu) {
            if ($groupMenu->menu && $groupMenu->menu->parent_id == null) {
                $menuIndex[$groupMenu->menu->id] = count($menus);
                $groupMenu->menu->elements = [];
                unset($groupMenu->menu->created_at);
                unset($groupMenu->menu->updated_at);
                unset($groupMenu->menu->parent_id);
                $menus[] = $groupMenu->menu;
            }
        }
        foreach ($groupMenus as $groupMenu) {
            if ($groupMenu->menu && $groupMenu->menu->parent_id != null) {
                $parent_id = $groupMenu->menu->parent_id;
                if (!isset($menuIndex[$parent_id])) {
                    $menuIndex[$parent_id] = count($menus);
                    $parent = $groupMenu->menu->parent()->first();
                    $parent->elements = [];
                    $menus[] = $parent;
                }
                $elements = $menus[$menuIndex[$parent_id]]->elements;
                unset($groupMenu->menu->created_at);
                unset($groupMenu->menu->updated_at);
                unset($groupMenu->menu->parent_id);
                array_push($elements, $groupMenu->menu);
                $menus[$menuIndex[$parent_id]]->elements = $elements;
            }
        }
        return $menus;
    }

    function readingStationUser()
    {
        return $this->hasOne(ReadingStationUser::class)->where('status', 'active');
    }

    function readingStation()
    {
        return $this->belongsTo(ReadingStation::class);
    }

    function absentPresents($day = null)
    {
        $day = $day === null ? Carbon::now() : $day;
        return $this->hasMany(ReadingStationAbsentPresent::class)
                    ->where('day', $day->toDateString());
    }

    function gradePackage()
    {
        return $this->belongsTo(ReadingStationPackage::class, 'grade', 'grade');
    }

    function school()
    {
        return $this->belongsTo(School::class);
    }
}
