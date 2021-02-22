<?php namespace Gdoo\User\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;

use Gdoo\Index\Models\BaseModel;

class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable;

    protected $table = 'user';

    static public $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'user', 'url' => 'user/user/index', 'name' => '用户'],
            ['value' => 'role', 'url' => 'user/role/index', 'name' => '角色'],
            ['value' => 'department', 'url' => 'user/department/index', 'name' => '部门'],
            ['value' => 'group', 'url' => 'user/group/index', 'name' => '用户组'],
            ['value' => 'position', 'url' => 'user/position/index', 'name' => '职位'],
        ]
    ];

    static public $bys = [
        'name'  => 'by',
        'items' => [
            ['value' => '', 'name' => '全部'],
            ['value' => 'enabled', 'name' => '启用'],
            ['value' => 'disabled', 'name' => '禁用'],
            ['value' => 'divider'],
            ['value' => 'day', 'name' => '今日创建'],
            ['value' => 'week', 'name' => '本周创建'],
            ['value' => 'month', 'name' => '本月创建'],
        ]
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'password_text', 'remember_token', 'auth_secret'];

    /**
     * 设置字段黑名单
     */
    protected $guarded = ['id', 'password'];

    public function department()
    {
        return $this->belongsTo('Gdoo\User\Models\Department');
    }

    public function customer()
    {
        return $this->hasOne('Gdoo\Customer\Models\Customer');
    }

    public function supplier()
    {
        return $this->hasOne('Gdoo\Supplier\Models\Supplier');
    }

    public function role()
    {
        return $this->belongsTo('Gdoo\User\Models\Role');
    }

    public function hr()
    {
        return $this->hasOne('Gdoo\Hr\Models\Hr');
    }

    public function position()
    {
        return $this->belongsTo('Gdoo\User\Models\UserPosition', 'post');
    }

    public function tasks()
    {
        return $this->belongsToMany('Gdoo\Project\Models\Task');
    }

    /**
    * 查询用户组
    */
    public function scopeGroup($q, $type)
    {
        $group = UserGroup::where('key', $type)->first();
        return $q->where('user.group_id', $group->id);
    }

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)->pluck('name', 'id');
    }
}
