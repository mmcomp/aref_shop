<?php

namespace App\Models;

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
        'pass_txt',
        'address',
        'postall',
        'cities_id',
        'groups_id'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }

    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'groups_id');
    }

    public function menus()
    {
        $groupMenus = $this->group()->first()->menus()->with('menu')->get();
        $menus = [];
        $menuIndex = [];
        foreach ($groupMenus as $groupMenu) {
            if ($groupMenu->menu && $groupMenu->menu->parent_id==null) {
                $menuIndex[$groupMenu->menu->id] = count($menus);
                $groupMenu->menu->elements = [];
                unset($groupMenu->menu->created_at);
                unset($groupMenu->menu->updated_at);
                unset($groupMenu->menu->parent_id);
                $menus[] = $groupMenu->menu;
            }
        }
        foreach ($groupMenus as $groupMenu) {
            if ($groupMenu->menu && $groupMenu->menu->parent_id!=null) {
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
}
