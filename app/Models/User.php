<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $table = 'user';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Os atributos que podem ser preenchidos em massa.
     * 
     * Note que o arquivo CSV contém as colunas:
     * - name
     * - cpf
     * - email
     * - data_admissao
     *
     * Além disso, para atender à regra de negócio, incluímos:
     * - company: Identifica a empresa parceira (pode ser definida posteriormente)
     * - active: Indica se o funcionário está ativo (deve ser true para elegibilidade)
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'cpf',
        'email',
        'data_admissao',
        'company',
        'active'
    ];

    /**
     * Atributos que devem ser ocultados na conversão para JSON.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
}
