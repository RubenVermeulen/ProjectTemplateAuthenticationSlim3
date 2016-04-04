<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class UserPermission extends Eloquent
{
    /**
     * Database table.
     *
     * @var string
     */
    protected $table = 'users_permissions';

    /**
     * Fillable fields for user permission.
     *
     * @var array
     */
    protected $fillable = [
        'is_admin'
    ];

    /**
     * Defaults for a new record.
     *
     * @var array
     */
    public static $defaults = [
        'is_admin' => false
    ];
}