<?php

/**
 * User Model
 * 
 * @author Adistwn
 * @package Illuminate\Database
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * hidden 
     *
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password'
    ];

}