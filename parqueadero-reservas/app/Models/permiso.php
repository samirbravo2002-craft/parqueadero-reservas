<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Permiso extends Model
{
    protected $table = 'permiso';
    protected $primaryKey = 'codigo_permiso';
    public $timestamps = false;
 
    protected $fillable = [
        'descripcion_permiso',
        'peticion',
        'endpoint',
        'estado_permiso',
    ];
 
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'roles_permisos', 'codigo_permiso', 'id_rol');
    }
}