<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_identifier',
        'remember_token',
        'active',
        'active_hash',
    ];

    public function setPassword($password) {
        $this->update([
            'password' => $password
        ]);
    }

    public function updateRememberCredentials($identifier, $token) {
        $this->update([
            'remember_identifier' => $identifier,
            'remember_token' => $token
        ]);
    }

    public function removeRememberCredentials() {
        $this->update([
            'remember_identifier' => null,
            'remember_token' => null
        ]);
    }

    public function activateAccount() {
        $this->update([
            'active' => true,
            'active_hash' => null,
        ]);
    }

    /**
     * Checks if the user has a certain permissions.
     *
     * @param $permission
     * @return bool
     */
    public function hasPermission($permission) {
        return (bool) $this->permissions->{$permission};
    }

    /**
     * Is a user an admin.
     *
     * @return bool
     */
    public function isAdmin() {
        return $this->hasPermission('is_admin');
    }

    /**
     * Link a permissions record to a user instance in the database.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function permissions() {
        return $this->hasOne('App\Models\UserPermission', 'user_id');
    }
}